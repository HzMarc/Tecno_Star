<?php
declare(strict_types=1);

final class OrderConflict extends DomainException {}

final class OrderModel
{
    public function cards(int $owner,array $input):array
    {
        $filter=$input['filter']??'all';if(!is_string($filter) || !in_array($filter,['all','active','closed'],true))$filter='all';
        $where=' WHERE o.id_usuario=? AND o.retiro_solicitud IS NOT NULL';if($filter==='active')$where.=" AND o.estado IN ('pendiente','confirmado','en_preparacion','listo')";if($filter==='closed')$where.=" AND o.estado IN ('entregado','cancelado')";
        $q=$this->db->prepare('SELECT COUNT(*) FROM tbpedidos o'.$where);$q->execute([$owner]);$total=(int)$q->fetchColumn();$pages=max(1,(int)ceil($total/4));$page=min($pages,max(1,(int)(is_scalar($input['page']??1)?($input['page']??1):1)));
        $q=$this->db->prepare("SELECT o.*,(SELECT GROUP_CONCAT(CONCAT(d.cantidad,' × ',d.nombre_producto) ORDER BY d.id_producto SEPARATOR ', ') FROM tbretirodetalles d WHERE d.id_pedido=o.id_pedido) AS products FROM tbpedidos o".$where.' ORDER BY o.id_pedido DESC LIMIT 4 OFFSET '.(($page-1)*4));$q->execute([$owner]);return ['items'=>$q->fetchAll(),'total'=>$total,'pages'=>$pages,'page'=>$page,'filter'=>$filter];
    }
    public const LABELS=['pendiente'=>'Pendiente','confirmado'=>'Confirmado','en_preparacion'=>'En preparación','listo'=>'Listo para retirar','entregado'=>'Entregado','cancelado'=>'Cancelado'];
    public const NEXT=['pendiente'=>['confirmado','cancelado'],'confirmado'=>['en_preparacion','cancelado'],'en_preparacion'=>['listo','cancelado'],'listo'=>['entregado','cancelado'],'entregado'=>[],'cancelado'=>[]];
    public function __construct(private PDO $db) {}

    public static function isStaff(array $user): bool
    {
        if($user['nombre_rol']==='Administrador') return true;
        if(array_key_exists('permiso_pedidos',$user) && $user['permiso_pedidos']!==null)return $user['nombre_rol']==='Empleado' && (bool)$user['permiso_pedidos'];
        $ids=explode(',',(new Environment(BASE_PATH.'/.env'))->get('ORDER_STAFF_USER_IDS',''));
        return $user['nombre_rol']==='Empleado' && in_array((string)$user['id_usuario'],array_map('trim',$ids),true);
    }

    public static function enabled(PDO $db): bool
    {
        static $connections=null;
        $connections??=new WeakMap();
        if(!isset($connections[$db])) $connections[$db]=(bool)$db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbpedidos' AND COLUMN_NAME='retiro_solicitud'")->fetchColumn();
        return $connections[$db];
    }

    private static function units(mixed $value): int
    {
        if((!is_int($value) && !is_string($value)) || !preg_match('/^[0-9]+(?:\.0{1,3})?$/D',(string)$value) || (float)$value<1 || (float)$value>1000000) throw new DomainException('El carrito contiene una cantidad inválida.');
        return (int)$value;
    }
    private static function scaled(string $value,int $places): int
    {
        if(!preg_match('/^([0-9]+)(?:\.([0-9]+))?$/D',$value,$parts)) throw new RuntimeException('Saldo o precio inválido.');
        return (int)$parts[1]*(10**$places)+(int)str_pad(substr($parts[2]??'',0,$places),$places,'0');
    }
    private static function decimal(int $value,int $places=3): string
    {
        return ($value<0?'-':'').intdiv(abs($value),10**$places).'.'.str_pad((string)(abs($value)%(10**$places)),$places,'0',STR_PAD_LEFT);
    }
    private function begin(): void {$this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');$this->db->beginTransaction();}
    private function retry(Closure $operation): array
    {
        for($attempt=0;$attempt<3;$attempt++) {
            try{return $operation();}catch(PDOException $error){
                if((string)$error->getCode()!=='40001' && (int)($error->errorInfo[1]??0)!==1213) throw $error;
                if($attempt===2) throw new OrderConflict('Otra operación está usando estas existencias. Reintenta la misma solicitud.');
            }
        }
        throw new LogicException('Reintento de pedido inválido.');
    }
    private function actor(int $id,bool $exclusive=false): array
    {
        $permission=self::cashEnabled($this->db)?'(SELECT pedidos FROM tbpersonalpermisos WHERE id_usuario=u.id_usuario)':'NULL';
        $query=$this->db->prepare("SELECT u.id_usuario,u.nombres,u.apellidos,u.correo,r.nombre_rol,$permission AS permiso_pedidos,(SELECT id_cliente FROM tbclientes WHERE id_usuario=u.id_usuario LIMIT 1) AS id_cliente FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol WHERE u.id_usuario=? AND u.estado='activo'".($exclusive?' FOR UPDATE':' LOCK IN SHARE MODE'));$query->execute([$id]);$user=$query->fetch();
        if(!$user) throw new DomainException('La cuenta ya no está disponible.');return $user;
    }
    private function lines(int $userId,bool $lock=false): array
    {
        $query=$this->db->prepare("SELECT id_carrito,revision FROM tbcarritos WHERE id_usuario=? AND estado='activo'".($lock?' FOR UPDATE':''));$query->execute([$userId]);$cart=$query->fetch();
        if(!$cart) return ['cart_id'=>0,'revision'=>0,'items'=>[],'total'=>'0.00','valid'=>false,'fingerprint'=>''];
        $query=$this->db->prepare('SELECT id_producto,cantidad,precio_visto FROM tbdetallecarritos WHERE id_carrito=? ORDER BY id_producto'.($lock?' FOR UPDATE':''));$query->execute([$cart['id_carrito']]);$rows=$query->fetchAll();
        if(count($rows)>100) throw new DomainException('El carrito admite hasta 100 productos por pedido.');
        // Adquiere todos los productos en orden antes de bloquear categorías compartidas.
        if($lock) {
            $products=$this->db->prepare('SELECT id_producto FROM tbproductos WHERE id_producto=? FOR UPDATE');
            foreach($rows as $row){$products->execute([$row['id_producto']]);$products->fetchColumn();}
        }
        $items=[];$total=0;$valid=count($rows)>0;
        foreach($rows as $row) {
            $query=$this->db->prepare("SELECT p.id_producto,p.codigo_producto,p.nombre_producto,p.precio_venta,(p.estado='activo' AND c.estado='activo' AND (m.id_marca IS NULL OR m.estado='activo')) AS activo,COALESCE(i.stock_actual,0) AS fisico,COALESCE(i.stock_reservado,0) AS reservado FROM tbproductos p JOIN tbcategorias c ON c.id_categoria=p.id_categoria LEFT JOIN tbmarcas m ON m.id_marca=p.id_marca LEFT JOIN tbinventario i ON i.id_producto=p.id_producto WHERE p.id_producto=?".($lock?' FOR UPDATE':''));$query->execute([$row['id_producto']]);$product=$query->fetch();
            if(!$product) throw new DomainException('Un producto del carrito ya no existe.');
            $quantity=self::units($row['cantidad']);$price=self::scaled((string)$product['precio_venta'],2);
            if($price>intdiv(999999999999-$total,$quantity)) throw new DomainException('El importe supera el máximo permitido por pedido.');
            $subtotal=$price*$quantity;$total+=$subtotal;
            $available=intdiv(self::scaled((string)$product['fisico'],3)-self::scaled((string)$product['reservado'],3),1000);
            $issues=[];
            if(!$product['activo']) $issues[]='Producto no disponible.';
            elseif($available<$quantity) $issues[]='Solo hay '.max(0,$available).' unidades disponibles.';
            if($row['precio_visto']!==null && self::scaled((string)$row['precio_visto'],2)!==$price) $issues[]='El precio cambió; revisa el importe actual.';
            if(!$product['activo'] || $available<$quantity) $valid=false;
            $items[]=['id'=>(int)$product['id_producto'],'code'=>$product['codigo_producto'],'name'=>$product['nombre_producto'],'quantity'=>$quantity,'price'=>self::decimal($price,2),'subtotal'=>self::decimal($subtotal,2),'available'=>max(0,$available),'active'=>(bool)$product['activo'],'issues'=>$issues];
        }
        $fingerprint=hash('sha256',json_encode([$userId,(int)$cart['revision'],array_map(static fn($item)=>[$item['id'],$item['quantity'],$item['price'],$item['active']],$items)],JSON_THROW_ON_ERROR));
        return ['cart_id'=>(int)$cart['id_carrito'],'revision'=>(int)$cart['revision'],'items'=>$items,'total'=>self::decimal($total,2),'valid'=>$valid,'fingerprint'=>$fingerprint];
    }
    public function quote(int $userId): array
    {
        $this->expireDue();$this->db->beginTransaction();
        try {$result=$this->lines($userId);$this->db->commit();return $result;}catch(Throwable $error){$this->db->rollBack();throw $error;}
    }
    private static function contact(array $input): array
    {
        $result=[];
        foreach(['nombre'=>201,'telefono'=>30,'correo'=>254,'nota'=>500] as $key=>$length) {
            if(!is_string($input[$key]??'')) throw new InvalidArgumentException('Los datos de contacto no son válidos.');
            $result[$key]=trim($input[$key]??'');if(mb_strlen($result[$key])>$length) throw new InvalidArgumentException('Los datos de contacto son demasiado largos.');
        }
        $phone=preg_replace('/[ -]/','',$result['telefono']);
        if(mb_strlen($result['nombre'])<3) throw new InvalidArgumentException('El nombre de quien retira debe tener al menos 3 caracteres.');
        if(!preg_match('/^(?:\+?503)?[267][0-9]{7}$/D',$phone)) throw new InvalidArgumentException('El teléfono debe tener 8 dígitos y comenzar por 2, 6 o 7; por ejemplo, 7000-0000.');
        if(!filter_var($result['correo'],FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Introduce un correo de contacto válido.');
        if(($input['acepto']??'')!=='1') throw new InvalidArgumentException('Confirma que revisaste los productos, precios y plazo de retiro.');return $result;
    }
    public function create(int $userId,array $input): array
    {
        return $this->retry(fn()=> $this->createOnce($userId,$input));
    }
    private function createOnce(int $userId,array $input): array
    {
        $contact=self::contact($input);$key=$input['solicitud']??'';$quote=$input['cotizacion']??'';
        if(!is_string($key) || !preg_match('/^[a-f0-9]{64}$/D',$key) || !is_string($quote) || !preg_match('/^[a-f0-9]{64}$/D',$quote)) throw new InvalidArgumentException('La solicitud del pedido no es válida. Abre de nuevo la confirmación.');
        $hash=hash('sha256',json_encode([$userId,$contact,$quote],JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE));$this->expireDue();$this->begin();
        try {
            $actor=$this->actor($userId,true);
            $cart=$this->db->prepare("SELECT id_carrito FROM tbcarritos WHERE id_usuario=? AND estado='activo' FOR UPDATE");$cart->execute([$userId]);$cart->fetch();
            $previous=$this->db->prepare('SELECT id_pedido,id_usuario,retiro_huella FROM tbpedidos WHERE retiro_solicitud=?');$previous->execute([$key]);$existing=$previous->fetch();
            if($existing) {
                if((int)$existing['id_usuario']!==$userId || !hash_equals($existing['retiro_huella'],$hash)) throw new OrderConflict('Esta solicitud ya se utilizó con otros datos.');
                $this->db->commit();return $this->find((int)$existing['id_pedido'],$userId,false)+['repeated'=>true];
            }
            $current=$this->lines($userId,true);
            if(!hash_equals($current['fingerprint'],$quote)) throw new OrderConflict('El carrito o sus precios cambiaron. Revisa de nuevo la confirmación.');
            if(!$current['valid']) throw new OrderConflict('Revisa el carrito: hay productos sin disponibilidad suficiente.');
            if(!$actor['id_cliente']) {
                // Las cuentas anteriores, incluido el administrador, pueden comprar sin cambiar de rol.
                // El bloqueo de la cuenta serializa los intentos; el perfil se revierte si falla el pedido.
                $query=$this->db->prepare('SELECT id_cliente FROM tbclientes WHERE id_usuario=? FOR UPDATE');$query->execute([$userId]);$actor['id_cliente']=$query->fetchColumn();
                if(!$actor['id_cliente']) {
                    $query=$this->db->prepare('INSERT INTO tbclientes(id_usuario,nombres,apellidos,telefono,correo_contacto) VALUES (?,?,?,?,?)');
                    $query->execute([$userId,$actor['nombres'],$actor['apellidos'],preg_replace('/[ -]/','',$contact['telefono']),$actor['correo']]);
                    $actor['id_cliente']=(int)$this->db->lastInsertId();
                }
            }
            $code='TS-'.(new DateTimeImmutable('now',new DateTimeZone('America/El_Salvador')))->format('ymd').'-'.strtoupper(bin2hex(random_bytes(6)));
            // El esquema antiguo exige una fecha inicial para permitir el vencimiento.
            // Corresponde a la confirmación del cliente; la aceptación de la tienda sigue en el historial.
            $legacyDate=(bool)$this->db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbpedidos' AND COLUMN_NAME='fecha_confirmacion'")->fetchColumn();
            $query=$this->db->prepare("INSERT INTO tbpedidos(codigo_pedido,id_usuario,id_cliente,estado,total,retiro_solicitud,retiro_huella,contacto_nombre,contacto_telefono,contacto_correo,nota_retiro,fecha_vencimiento,reserva_activa,created_at,updated_at".($legacyDate?',fecha_confirmacion':'').") VALUES (?,?,?,'pendiente',?,?,?,?,?,?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 24 HOUR),1,UTC_TIMESTAMP(),UTC_TIMESTAMP()".($legacyDate?',UTC_TIMESTAMP()':'').")");
            $query->execute([$code,$userId,$actor['id_cliente'],$current['total'],$key,$hash,$contact['nombre'],$contact['telefono'],$contact['correo'],$contact['nota']]);$id=(int)$this->db->lastInsertId();
            foreach($current['items'] as $item) {
                $query=$this->db->prepare('INSERT INTO tbretirodetalles(id_pedido,id_producto,codigo_producto,nombre_producto,cantidad,precio_unitario,subtotal) VALUES (?,?,?,?,?,?,?)');$query->execute([$id,$item['id'],$item['code'],$item['name'],$item['quantity'],$item['price'],$item['subtotal']]);
                $this->stock($id,$item['id'],$item['quantity'],'reserva',$userId,$actor['nombres'].' '.$actor['apellidos'],'Reserva del pedido '.$code);
            }
            $this->history($id,null,'pendiente',$userId,$actor['nombres'].' '.$actor['apellidos'],'Pedido enviado a la tienda; pendiente de aceptación.');
            $query=$this->db->prepare('DELETE FROM tbdetallecarritos WHERE id_carrito=?');$query->execute([$current['cart_id']]);
            $query=$this->db->prepare('UPDATE tbcarritos SET revision=revision+1 WHERE id_carrito=?');$query->execute([$current['cart_id']]);
            $this->db->commit();return $this->find($id,$userId,false)+['repeated'=>false];
        }catch(Throwable $error){if($this->db->inTransaction()) $this->db->rollBack();throw $error;}
    }
    private function stock(int $order,int $product,int $quantity,string $type,?int $actor,string $name,string $reason): void
    {
        $lock=$this->db->prepare('SELECT id_producto FROM tbproductos WHERE id_producto=? FOR UPDATE');$lock->execute([$product]);
        $lock=$this->db->prepare('SELECT stock_actual,stock_reservado,stock_minimo FROM tbinventario WHERE id_producto=? FOR UPDATE');$lock->execute([$product]);$row=$lock->fetch();
        if(!$row) throw new DomainException('El producto no tiene inventario.');
        $physical=self::scaled((string)$row['stock_actual'],3);$reserved=self::scaled((string)$row['stock_reservado'],3);$minimum=self::scaled((string)$row['stock_minimo'],3);$units=$quantity*1000;
        $after=$type==='entrega'?$physical-$units:$physical;$newReserve=$type==='reserva'?$reserved+$units:$reserved-$units;
        if($after<0 || $newReserve<0 || $newReserve>$after) throw new OrderConflict('Las existencias cambiaron. No se pudo completar la operación.');
        $query=$this->db->prepare('UPDATE tbinventario SET stock_actual=?,stock_reservado=? WHERE id_producto=?');$query->execute([self::decimal($after),self::decimal($newReserve),$product]);
        $query=$this->db->prepare('INSERT INTO tbstockmovimientos(id_producto,id_usuario,responsable,tipo,cantidad,stock_antes,stock_despues,reservado,reservado_antes,minimo_antes,minimo_despues,motivo,id_pedido) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $query->execute([$product,$actor,$name,$type,self::decimal($after-$physical),self::decimal($physical),self::decimal($after),self::decimal($newReserve),self::decimal($reserved),self::decimal($minimum),self::decimal($minimum),$reason,$order]);
    }
    private function history(int $id,?string $from,string $to,?int $actor,string $name,string $reason): void
    {
        $query=$this->db->prepare('INSERT INTO tbretirohistorial(id_pedido,id_usuario,responsable,estado_anterior,estado_nuevo,motivo,created_at) VALUES (?,?,?,?,?,?,UTC_TIMESTAMP())');$query->execute([$id,$actor,$name,$from,$to,$reason]);
    }
    public function find(int $id,int $userId,bool $admin): ?array
    {
        $query=$this->db->prepare('SELECT * FROM tbpedidos WHERE id_pedido=? AND retiro_solicitud IS NOT NULL'.($admin?'':' AND id_usuario=?'));$query->execute($admin?[$id]:[$id,$userId]);$order=$query->fetch();if(!$order) return null;
        $query=$this->db->prepare('SELECT * FROM tbretirodetalles WHERE id_pedido=? ORDER BY id_producto');$query->execute([$id]);$order['items']=$query->fetchAll();
        $query=$this->db->prepare('SELECT * FROM tbretirohistorial WHERE id_pedido=? ORDER BY id_historial');$query->execute([$id]);$order['history']=$query->fetchAll();return $order;
    }
    public function listing(int $userId,bool $admin): array
    {
        $this->expireDue();$query=$this->db->prepare('SELECT id_pedido,codigo_pedido,contacto_nombre,estado,total,created_at,fecha_vencimiento FROM tbpedidos WHERE retiro_solicitud IS NOT NULL'.($admin?'':' AND id_usuario=?').' ORDER BY id_pedido DESC LIMIT 12');$query->execute($admin?[]:[$userId]);return $query->fetchAll();
    }
    private function finish(array $order,string $target,?int $actor,string $name,string $reason): void
    {
        $id=(int)$order['id_pedido'];
        if(in_array($target,['cancelado','entregado'],true)) {
            if(!(bool)$order['reserva_activa']) throw new RuntimeException('El pedido no tiene una reserva activa. Requiere revisión.');
            $query=$this->db->prepare('SELECT id_producto,cantidad FROM tbretirodetalles WHERE id_pedido=? ORDER BY id_producto');$query->execute([$id]);$items=$query->fetchAll();
            foreach($items as $item) $this->stock($id,(int)$item['id_producto'],(int)$item['cantidad'],$target==='entregado'?'entrega':'liberacion',$actor,$name,$reason);
        }
        $query=$this->db->prepare('UPDATE tbpedidos SET estado=?,reserva_activa=?,updated_at=UTC_TIMESTAMP() WHERE id_pedido=?');$query->execute([$target,in_array($target,['entregado','cancelado'],true)?0:1,$id]);
        $this->history($id,$order['estado'],$target,$actor,$name,$reason);
        if($target==='confirmado' || ($target==='listo' && self::readyMailEnabled($this->db))) {
            $events=self::readyMailEnabled($this->db);
            $query=$this->db->prepare('INSERT INTO tbretiocorreos(id_pedido'.($events?',tipo':'').',proximo_intento,created_at) VALUES (?'.($events?',?':'').',UTC_TIMESTAMP(),UTC_TIMESTAMP())');$query->execute($events?[$id,$target]:[$id]);
        }
    }
    public static function readyMailEnabled(PDO $db):bool
    {
        static $cache=null;$cache??=new WeakMap();if(!isset($cache[$db]))$cache[$db]=(bool)$db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbretiocorreos' AND COLUMN_NAME='tipo'")->fetchColumn();return $cache[$db];
    }
    public function transition(int $userId,int $id,string $target,string $expected,string $reason): array
    {
        return $this->retry(fn()=> $this->transitionOnce($userId,$id,$target,$expected,$reason));
    }
    private function transitionOnce(int $userId,int $id,string $target,string $expected,string $reason): array
    {
        if(!isset(self::LABELS[$target]) || mb_strlen(trim($reason))<3 || mb_strlen($reason)>500) throw new InvalidArgumentException('Selecciona un estado y explica el motivo (3–500 caracteres).');
        $this->expireDue();$this->begin();
        try {
            $actor=$this->actor($userId);$admin=self::isStaff($actor);
            $query=$this->db->prepare('SELECT *,fecha_vencimiento<=UTC_TIMESTAMP() AS expired FROM tbpedidos WHERE id_pedido=? AND retiro_solicitud IS NOT NULL FOR UPDATE');$query->execute([$id]);$order=$query->fetch();
            if(!$order || (!$admin && (int)$order['id_usuario']!==$userId)) throw new DomainException('Pedido no encontrado.');
            if(!$admin && ($target!=='cancelado' || !in_array($order['estado'],['pendiente','confirmado','cancelado'],true))) throw new DomainException('El cliente solo puede cancelar antes de la preparación.');
            if($order['estado']===$target) {$this->db->commit();return $this->find($id,$userId,$admin)+['repeated'=>true];}
            if($target==='entregado' && self::cashEnabled($this->db))throw new OrderConflict('Registra el cobro en efectivo para cobrar y entregar el pedido.');
            if($order['expired'] && $order['reserva_activa']) {$this->finish($order,'cancelado',null,'Sistema','Reserva vencida (24 horas).');$this->db->commit();return $this->find($id,$userId,$admin)+['expired'=>true];}
            if($order['estado']!==$expected || !in_array($target,self::NEXT[$order['estado']]??[],true)) throw new OrderConflict('El estado cambió o la transición no está permitida. Actualiza el pedido.');
            $this->finish($order,$target,$userId,$actor['nombres'].' '.$actor['apellidos'],trim($reason));$this->db->commit();return $this->find($id,$userId,$admin)+['repeated'=>false];
        }catch(Throwable $error){if($this->db->inTransaction()) $this->db->rollBack();throw $error;}
    }
    public static function cashEnabled(PDO $db):bool
    {
        static $cache=null;$cache??=new WeakMap();
        if(!isset($cache[$db]))$cache[$db]=(bool)$db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbventas' AND COLUMN_NAME='cobro_solicitud'")->fetchColumn();return $cache[$db];
    }
    public function deliverPaid(int $id,int $actor):void
    {
        if(!$this->db->inTransaction())throw new LogicException('La entrega cobrada requiere una transacción.');
        $user=$this->actor($actor);if(!self::isStaff($user))throw new DomainException('Sin permiso para entregar.');
        $q=$this->db->prepare("SELECT * FROM tbpedidos WHERE id_pedido=? AND estado='listo' AND reserva_activa=1 AND retiro_solicitud IS NOT NULL AND fecha_vencimiento>UTC_TIMESTAMP() FOR UPDATE");$q->execute([$id]);$order=$q->fetch();
        $q=$this->db->prepare("SELECT id_venta FROM tbventas WHERE id_pedido=? AND estado='pagada' AND cobro_solicitud IS NOT NULL");$q->execute([$id]);
        if(!$order || !$q->fetchColumn())throw new OrderConflict('El pedido no está listo, venció o no tiene un cobro registrado.');
        $this->finish($order,'entregado',$actor,$user['nombres'].' '.$user['apellidos'],'Cobro en efectivo y entrega en caja.');
    }
    public function returnStock(array $order,int $actor,string $name,string $reason):void
    {
        if(!$this->db->inTransaction())throw new LogicException('La devolución requiere una transacción.');
        foreach($order['items'] as $item){
            $product=(int)$item['id_producto'];$q=$this->db->prepare('SELECT id_producto FROM tbproductos WHERE id_producto=? FOR UPDATE');$q->execute([$product]);
            $q=$this->db->prepare('SELECT stock_actual,stock_reservado,stock_minimo FROM tbinventario WHERE id_producto=? FOR UPDATE');$q->execute([$product]);$before=$q->fetch();if(!$before)throw new DomainException('Inventario no encontrado.');
            $physical=self::scaled($before['stock_actual'],3);$delta=(int)$item['cantidad']*1000;$after=$physical+$delta;if($after>999999999999)throw new DomainException('La devolución supera el máximo de existencias.');
            $q=$this->db->prepare('UPDATE tbinventario SET stock_actual=? WHERE id_producto=?');$q->execute([self::decimal($after),$product]);
            $q=$this->db->prepare("INSERT INTO tbstockmovimientos(id_producto,id_usuario,responsable,tipo,cantidad,stock_antes,stock_despues,reservado,reservado_antes,minimo_antes,minimo_despues,motivo,id_pedido) VALUES (?,?,?,'devolucion',?,?,?,?,?,?,?,?,?)");$q->execute([$product,$actor,$name,self::decimal($delta),self::decimal($physical),self::decimal($after),$before['stock_reservado'],$before['stock_reservado'],$before['stock_minimo'],$before['stock_minimo'],$reason,$order['id_pedido']]);
        }
    }
    public function expireDue(int $limit=50): int
    {
        if(!self::enabled($this->db) || $this->db->inTransaction()) return 0;
        $limit=max(1,min(1000,$limit));$ids=$this->db->query("SELECT id_pedido FROM tbpedidos WHERE retiro_solicitud IS NOT NULL AND reserva_activa=1 AND fecha_vencimiento<=UTC_TIMESTAMP() ORDER BY fecha_vencimiento,id_pedido LIMIT $limit")->fetchAll(PDO::FETCH_COLUMN);$count=0;
        foreach($ids as $id) {
            $this->begin();
            try {
                $query=$this->db->prepare('SELECT *,fecha_vencimiento<=UTC_TIMESTAMP() AS expired FROM tbpedidos WHERE id_pedido=? FOR UPDATE');$query->execute([$id]);$order=$query->fetch();
                if($order && $order['reserva_activa'] && $order['expired']) {$this->finish($order,'cancelado',null,'Sistema','Reserva vencida (24 horas).');$count++;}
                $this->db->commit();
            }catch(Throwable $error){if($this->db->inTransaction()) $this->db->rollBack();throw $error;}
        }
        return $count;
    }
}
