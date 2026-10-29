<?php
declare(strict_types=1);
require_once __DIR__.'/OrderModel.php';

final class ShoppingConflict extends DomainException {}

final class ShoppingModel
{
    public function __construct(private PDO $database) {}

    private static function integer(mixed $value, int $min=1, int $max=1000000): int
    {
        if ((!is_int($value) && !is_string($value)) || !preg_match('/^[0-9]+$/D',(string)$value) || (float)$value<$min || (float)$value>$max) throw new InvalidArgumentException('Cantidad o identificador inválido.');
        return (int)$value;
    }

    public function state(int $userId): array
    {
        $own = !$this->database->inTransaction();
        if($own) {(new OrderModel($this->database))->expireDue();$this->database->beginTransaction();}
        try {
            $revision=$this->database->prepare("SELECT revision FROM tbcarritos WHERE id_usuario=? AND estado='activo'"); $revision->execute([$userId]);
            $version=(int)($revision->fetchColumn() ?: 0);
            $select="SELECT p.id_producto AS id,p.nombre_producto AS name,COALESCE(m.nombre_marca,'Sin marca') AS brand,c.nombre_categoria AS category,p.descripcion AS description,p.precio_venta AS price,p.imagen AS image,
                GREATEST(COALESCE(i.stock_actual-i.stock_reservado,0),0) AS stock,
                (p.estado='activo' AND c.estado='activo' AND (m.id_marca IS NULL OR m.estado='activo')) AS available";
            $joins=' JOIN tbproductos p ON p.id_producto=d.id_producto JOIN tbcategorias c ON c.id_categoria=p.id_categoria LEFT JOIN tbmarcas m ON m.id_marca=p.id_marca LEFT JOIN tbinventario i ON i.id_producto=p.id_producto';
            $cart=$this->database->prepare($select.',d.cantidad AS quantity,d.precio_visto AS saved_price FROM tbcarritos ca JOIN tbdetallecarritos d ON d.id_carrito=ca.id_carrito'.$joins." WHERE ca.id_usuario=? AND ca.estado='activo' ORDER BY d.id_detalle"); $cart->execute([$userId]);
            $wishlist=$this->database->prepare($select.',1 AS quantity,NULL AS saved_price FROM tblistasdeseos l JOIN tbdetallelistasdeseos d ON d.id_lista=l.id_lista'.$joins.' WHERE l.id_usuario=? ORDER BY d.created_at DESC,p.id_producto'); $wishlist->execute([$userId]);
            $normalize=static function(array $rows,bool $isCart):array {
                return array_map(static function(array $item) use($isCart):array {
                    $available=(bool)$item['available']; $stock=$available?max(0,(int)floor((float)$item['stock'])):0;
                    $quantity=max(1,(int)$item['quantity']); $price=(float)$item['price']; $issues=[];
                    if(!$available) $issues[]='Este producto ya no está disponible. Puedes quitarlo de la lista.';
                    elseif($stock===0) $issues[]='Producto agotado.';
                    elseif($isCart && $quantity>$stock) $issues[]='Solo hay '.$stock.($stock===1?' unidad disponible.':' unidades disponibles.').' Reduce la cantidad.';
                    $changed=$isCart && $item['saved_price']!==null && (string)$item['saved_price']!==(string)$item['price'];
                    if($changed) $issues[]='El precio cambió de $'.number_format((float)$item['saved_price'],2).' a $'.number_format($price,2).'.';
                    return ['id'=>(int)$item['id'],'name'=>(string)$item['name'],'brand'=>(string)$item['brand'],'category'=>(string)$item['category'],'description'=>(string)($item['description']??''),'price'=>$price,'image'=>product_image_url($item['image']??''),'stock'=>$stock,'quantity'=>$quantity,'available'=>$available,'price_changed'=>$changed,'issues'=>$issues];
                },$rows);
            };
            $result=['user_id'=>$userId,'revision'=>$version,'cart'=>$normalize($cart->fetchAll(),true),'wishlist'=>$normalize($wishlist->fetchAll(),false)];
            if($own) $this->database->commit(); return $result;
        } catch(Throwable $error) { if($own && $this->database->inTransaction()) $this->database->rollBack(); throw $error; }
    }

    public function mutate(int $userId, array $data): array
    {
        $action=$data['action']??'';
        if(!is_string($action) || !in_array($action,['sync','cart_add','cart_set_quantity','cart_change','cart_remove','cart_review','wishlist_add','wishlist_remove'],true)) throw new InvalidArgumentException('Acción inválida.');
        // La API pública exige clave; llamadas internas antiguas reciben una nueva por operación.
        $key=$data['operation_id']??bin2hex(random_bytes(32));
        if(!is_string($key) || !preg_match('/^[a-f0-9]{64}$/D',$key)) throw new InvalidArgumentException('Identificador de solicitud inválido.');
        $hashData=$data; unset($hashData['operation_id']);
        $hash=hash('sha256',json_encode([$userId,$hashData],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE));
        (new OrderModel($this->database))->expireDue();
        $this->database->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $this->database->beginTransaction();
        try {
            // La fila única del carrito serializa todas las operaciones de esta cuenta.
            $cartId=$this->ensureCart($userId);
            $previous=$this->database->prepare('SELECT id_usuario,huella,avisos FROM tbshoppingoperaciones WHERE solicitud=?'); $previous->execute([$key]); $record=$previous->fetch();
            if($record) {
                if((int)$record['id_usuario']!==$userId || !hash_equals($record['huella'],$hash)) throw new ShoppingConflict('La solicitud ya se utilizó con otros datos. Actualiza antes de intentarlo de nuevo.');
                $state=$this->state($userId)+['warnings'=>json_decode($record['avisos'],true,512,JSON_THROW_ON_ERROR),'repeated'=>true];
                $this->database->commit(); return $state;
            }
            $version=$this->database->prepare('SELECT revision FROM tbcarritos WHERE id_carrito=? FOR UPDATE'); $version->execute([$cartId]); $revision=(int)$version->fetchColumn();
            if(isset($data['expected_revision']) && self::integer($data['expected_revision'],0,PHP_INT_MAX)!==$revision && !in_array($action,['cart_add','cart_change','sync'],true)) throw new ShoppingConflict('Tu lista cambió en otra pestaña. Se actualizó la vista; revisa el cambio e inténtalo de nuevo.');
            $warnings=[];
            if($action==='sync') $this->sync($userId,$cartId,$data,$warnings);
            elseif($action==='cart_review') {
                $prices=$data['prices']??null;
                if(!is_array($prices) || count($prices)>200) throw new InvalidArgumentException('Precios inválidos.');
                $state=$this->state($userId);
                foreach($state['cart'] as $item) {
                    if(!$item['available']) continue;
                    // Bloquea el producto también durante la confirmación del precio visto.
                    $product=$this->product($item['id']);
                    if(!isset($prices[$item['id']]) || !is_numeric($prices[$item['id']]) || number_format((float)$prices[$item['id']],2,'.','')!==number_format((float)$product['precio_venta'],2,'.','')) throw new ShoppingConflict('Los precios cambiaron de nuevo. Revisa los importes actuales.');
                    $update=$this->database->prepare('UPDATE tbdetallecarritos SET precio_visto=? WHERE id_carrito=? AND id_producto=?'); $update->execute([$product['precio_venta'],$cartId,$item['id']]);
                }
            } else {
                $id=self::integer($data['product_id']??null,1,2147483647);
                if($action==='cart_remove') { $query=$this->database->prepare('DELETE FROM tbdetallecarritos WHERE id_carrito=? AND id_producto=?');$query->execute([$cartId,$id]); }
                elseif(str_starts_with($action,'wishlist_')) {
                    if($action==='wishlist_add') $this->product($id);
                    $list=$this->ensureWishlist($userId);
                    $query=$this->database->prepare($action==='wishlist_add'?'INSERT IGNORE INTO tbdetallelistasdeseos(id_lista,id_producto) VALUES (?,?)':'DELETE FROM tbdetallelistasdeseos WHERE id_lista=? AND id_producto=?');$query->execute([$list,$id]);
                } else {
                    $quantity=self::integer($data['quantity']??1);
                    if($action==='cart_change') {
                        if(!in_array($data['delta']??null,[-1,1],true)) throw new InvalidArgumentException('Cambio de cantidad inválido.');
                        $query=$this->database->prepare('SELECT cantidad FROM tbdetallecarritos WHERE id_carrito=? AND id_producto=?');$query->execute([$cartId,$id]);$current=$query->fetchColumn();
                        if($current===false) throw new ShoppingConflict('El producto ya no está en el carrito.');
                        $quantity=(int)$current+$data['delta'];
                        if($quantity===0) { $query=$this->database->prepare('DELETE FROM tbdetallecarritos WHERE id_carrito=? AND id_producto=?');$query->execute([$cartId,$id]); }
                        else $this->putCart($cartId,$id,$quantity,false,false,$warnings,$data['expected_price']??null);
                    } else $this->putCart($cartId,$id,$quantity,$action==='cart_add',false,$warnings,$data['expected_price']??null);
                }
            }
            $query=$this->database->prepare('UPDATE tbcarritos SET revision=revision+1 WHERE id_carrito=?');$query->execute([$cartId]);
            $query=$this->database->prepare('INSERT INTO tbshoppingoperaciones(solicitud,id_usuario,huella,avisos) VALUES (?,?,?,?)');$query->execute([$key,$userId,$hash,json_encode($warnings,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE)]);
            $state=$this->state($userId)+['warnings'=>$warnings,'repeated'=>false];
            $this->database->commit(); return $state;
        } catch(Throwable $error) {if($this->database->inTransaction()) $this->database->rollBack();throw $error;}
    }

    private function sync(int $userId,int $cartId,array $data,array &$warnings): void
    {
        $cart=$data['cart']??[];$wishlist=$data['wishlist']??[];
        if(!is_array($cart) || !array_is_list($cart) || count($cart)>100 || !is_array($wishlist) || !array_is_list($wishlist) || count($wishlist)>200) throw new InvalidArgumentException('La lista de sincronización supera el límite o es inválida.');
        $items=[];
        foreach($cart as $item) {
            if(!is_array($item)) throw new InvalidArgumentException('Producto de sincronización inválido.');
            $id=self::integer($item['id']??null,1,2147483647); if(isset($items[$id])) throw new InvalidArgumentException('Producto duplicado en sincronización.');
            $items[$id]=['quantity'=>self::integer($item['quantity']??1),'price'=>$item['price']??null];
        }
        ksort($items);
        foreach($items as $id=>$item) {try {$this->putCart($cartId,$id,$item['quantity'],true,true,$warnings,$item['price']);}catch(DomainException $error){$warnings[]='Producto #'.$id.': '.$error->getMessage();}}
        $ids=[];foreach($wishlist as $item) $ids[]=self::integer(is_array($item)?($item['id']??null):$item,1,2147483647);
        $ids=array_unique($ids);sort($ids);
        foreach($ids as $id) {
            try {$this->product($id);$list=$this->ensureWishlist($userId);$query=$this->database->prepare('INSERT IGNORE INTO tbdetallelistasdeseos(id_lista,id_producto) VALUES (?,?)');$query->execute([$list,$id]);}
            catch(DomainException $error){$warnings[]='Favorito #'.$id.': '.$error->getMessage();}
        }
    }

    private function putCart(int $cartId,int $id,int $quantity,bool $add,bool $clamp,array &$warnings,mixed $seenPrice): void
    {
        $product=$this->product($id);$stock=max(0,(int)floor((float)$product['stock']));
        if($stock===0) throw new DomainException('Este producto está agotado.');
        $query=$this->database->prepare('SELECT cantidad FROM tbdetallecarritos WHERE id_carrito=? AND id_producto=?');$query->execute([$cartId,$id]);$current=$query->fetchColumn();
        if(!$add && $current===false) throw new ShoppingConflict('El producto ya no está en tu carrito.');
        $next=$add?(int)$current+$quantity:$quantity;
        if($next>1000000) throw new DomainException('La cantidad supera el límite de la lista.');
        if($next>$stock) {if(!$clamp) throw new DomainException('Solo hay '.$stock.' unidades disponibles. Revisa la cantidad.');$next=$stock;$warnings[]=$product['nombre_producto'].': se ajustó la cantidad a '.$stock.' unidades disponibles.';}
        $price=$product['precio_venta'];
        if($seenPrice!==null) {
            if((!is_string($seenPrice) && !is_int($seenPrice) && !is_float($seenPrice)) || !is_numeric($seenPrice) || !is_finite((float)$seenPrice) || (float)$seenPrice<0 || (float)$seenPrice>9999999999.99) throw new InvalidArgumentException('Precio de referencia inválido.');
            $price=number_format((float)$seenPrice,2,'.','');
            if($price!==number_format((float)$product['precio_venta'],2,'.','')) $warnings[]=$product['nombre_producto'].': revisa el precio actualizado.';
        }
        if($current===false) {$query=$this->database->prepare('INSERT INTO tbdetallecarritos(id_carrito,id_producto,cantidad,precio_visto) VALUES (?,?,?,?)');$query->execute([$cartId,$id,$next,$price]);}
        else {$query=$this->database->prepare('UPDATE tbdetallecarritos SET cantidad=? WHERE id_carrito=? AND id_producto=?');$query->execute([$next,$cartId,$id]);}
    }

    private function product(int $id): array
    {
        $query=$this->database->prepare("SELECT p.id_producto,p.nombre_producto,p.precio_venta,GREATEST(COALESCE(i.stock_actual-i.stock_reservado,0),0) AS stock FROM tbproductos p JOIN tbcategorias c ON c.id_categoria=p.id_categoria LEFT JOIN tbmarcas m ON m.id_marca=p.id_marca LEFT JOIN tbinventario i ON i.id_producto=p.id_producto WHERE p.id_producto=? AND p.estado='activo' AND c.estado='activo' AND (m.id_marca IS NULL OR m.estado='activo') FOR UPDATE");$query->execute([$id]);$product=$query->fetch();
        if(!$product) throw new DomainException('El producto ya no está disponible.');return $product;
    }

    private function ensureCart(int $userId): int
    {
        $query=$this->database->prepare("INSERT INTO tbcarritos(id_usuario,estado) VALUES (?,'activo') ON DUPLICATE KEY UPDATE estado='activo'");$query->execute([$userId]);
        $query=$this->database->prepare("SELECT id_carrito FROM tbcarritos WHERE id_usuario=? AND estado='activo' FOR UPDATE");$query->execute([$userId]);return (int)$query->fetchColumn();
    }

    private function ensureWishlist(int $userId): int
    {
        $query=$this->database->prepare('INSERT INTO tblistasdeseos(id_usuario) VALUES (?) ON DUPLICATE KEY UPDATE id_lista=LAST_INSERT_ID(id_lista)');$query->execute([$userId]);
        $query=$this->database->prepare('SELECT id_lista FROM tblistasdeseos WHERE id_usuario=?');$query->execute([$userId]);return (int)$query->fetchColumn();
    }
}
