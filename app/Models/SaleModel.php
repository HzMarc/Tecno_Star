<?php
declare(strict_types=1);
require_once __DIR__.'/OrderModel.php';require_once BASE_PATH.'/core/Cash.php';
require_once __DIR__.'/ReinvestmentModel.php';
final class SaleModel
{
    public function __construct(private PDO $db){}
    public function purchaseCards(int $owner,array $input):array
    {
        require_once __DIR__.'/AdminTableModel.php';
        $filters=[];
        foreach(['q','status','from','to'] as $key){
            $value=$input[$key]??'';
            if(!is_string($value))throw new InvalidArgumentException('Filtro de compras inválido.');
            $filters[$key]=trim($value);
        }
        $requested=$input['page']??'1';
        if(!is_string($requested) || !ctype_digit($requested) || (float)$requested<1 || (float)$requested>250000000)throw new InvalidArgumentException('Página inválida.');
        $page=(int)$requested;
        $query=['search'=>['value'=>$filters['q']],'status'=>$filters['status'],'from'=>$filters['from'],'to'=>$filters['to'],'length'=>'4','start'=>(string)(($page-1)*4)];
        $model=new AdminTableModel($this->db);$result=$model->data('purchases',$query,$owner);
        $pages=max(1,(int)ceil($result['recordsFiltered']/4));
        if($page>$pages){$page=$pages;$query['start']=(string)(($page-1)*4);$result=$model->data('purchases',$query,$owner);}
        return ['items'=>$result['rows'],'total'=>$result['recordsFiltered'],'all'=>$result['recordsTotal'],'summary'=>$result['summary'],'page'=>$page,'pages'=>$pages,'filters'=>$filters];
    }
    private function actor(int $id,bool $admin=false):array
    {
        $q=$this->db->prepare("SELECT u.id_usuario,u.nombres,u.apellidos,r.nombre_rol,(SELECT pedidos FROM tbpersonalpermisos WHERE id_usuario=u.id_usuario) AS permiso_pedidos FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol WHERE u.id_usuario=? AND u.estado='activo' LOCK IN SHARE MODE");$q->execute([$id]);$user=$q->fetch();if(!$user || !OrderModel::isStaff($user) || ($admin && $user['nombre_rol']!=='Administrador'))throw new DomainException('No tienes permiso para esta operación.');return $user;
    }
    private static function key(mixed $value):string {if(!is_string($value) || !preg_match('/^[a-f0-9]{64}$/D',$value))throw new InvalidArgumentException('Solicitud inválida. Abre de nuevo el formulario.');return $value;}
    private static function id(mixed $value):int{if(!is_string($value) || !ctype_digit($value) || (float)$value<1 || (float)$value>2147483647)throw new InvalidArgumentException('Registro inválido.');return (int)$value;}
    public function pay(int $actor,array $input):array
    {
        $id=self::id($input['id_pedido']??null);$key=self::key($input['solicitud']??null);$received=Cash::cents($input['recibido']??null);
        if(($input['confirmado']??'')!=='1')throw new InvalidArgumentException('Confirma que recibiste el efectivo y entregarás los productos.');
        $hash=hash('sha256',json_encode([$actor,$id,$received],JSON_THROW_ON_ERROR));(new OrderModel($this->db))->expireDue();
        $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');$this->db->beginTransaction();
        try{
            $person=$this->actor($actor);$q=$this->db->prepare('SELECT * FROM tbpedidos WHERE id_pedido=? AND retiro_solicitud IS NOT NULL FOR UPDATE');$q->execute([$id]);$order=$q->fetch();if(!$order)throw new DomainException('Pedido no encontrado.');
            $q=$this->db->prepare('SELECT * FROM tbventas WHERE cobro_solicitud=?');$q->execute([$key]);$old=$q->fetch();if($old){if(!hash_equals($old['cobro_huella'],$hash))throw new OrderConflict('La solicitud ya se utilizó con otros datos.');$this->db->commit();return $old+['repeated'=>true];}
            $q=$this->db->prepare('SELECT id_venta FROM tbventas WHERE id_pedido=? AND cobro_solicitud IS NOT NULL');$q->execute([$id]);if($q->fetchColumn())throw new OrderConflict('Este pedido ya tiene un cobro.');
            if($order['estado']!=='listo' || !(bool)$order['reserva_activa'])throw new OrderConflict('Solo puedes cobrar un pedido listo para retirar con reserva activa.');
            $total=Cash::cents($order['total']);if($received<$total)throw new InvalidArgumentException('El efectivo recibido no cubre el total.');$change=$received-$total;
            $code='VT-'.(new DateTimeImmutable('now',new DateTimeZone('America/El_Salvador')))->format('ymd').'-'.strtoupper(bin2hex(random_bytes(6)));
            $q=$this->db->prepare("INSERT INTO tbventas(codigo_venta,id_pedido,id_usuario,id_cliente,estado,total,fecha_pago,metodo_pago,recibido,cambio,cobro_solicitud,cobro_huella,responsable_cobro,created_at) VALUES (?,?,?,?,'pagada',?,UTC_TIMESTAMP(),'efectivo',?,?,?,?,?,UTC_TIMESTAMP())");$q->execute([$code,$id,$actor,$order['id_cliente'],$order['total'],Cash::decimal($received),Cash::decimal($change),$key,$hash,$person['nombres'].' '.$person['apellidos']]);$sale=(int)$this->db->lastInsertId();
            (new ReinvestmentModel($this->db))->record($sale,$order['total']);
            (new OrderModel($this->db))->deliverPaid($id,$actor);$this->db->commit();return $this->find($sale,0,true)+['repeated'=>false];
        }catch(Throwable $error){if($this->db->inTransaction())$this->db->rollBack();throw $error;}
    }
    public function cancel(int $actor,array $input):array
    {
        $id=self::id($input['id_venta']??null);$key=self::key($input['solicitud']??null);$reason=$input['motivo']??'';
        if(!is_string($reason) || mb_strlen(trim($reason))<3 || mb_strlen($reason)>500 || !in_array($input['devolucion']??null,['0','1'],true) || ($input['reembolsado']??'')!=='1')throw new InvalidArgumentException('Explica el motivo, indica la devolución física y confirma el reembolso completo.');
        $return=$input['devolucion']==='1';$hash=hash('sha256',json_encode([$actor,$id,trim($reason),$return],JSON_THROW_ON_ERROR));
        $this->db->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');$this->db->beginTransaction();
        try{
            $user=$this->actor($actor,true);
            $q=$this->db->prepare('SELECT id_pedido FROM tbventas WHERE id_venta=? AND cobro_solicitud IS NOT NULL');$q->execute([$id]);$orderId=(int)$q->fetchColumn();if(!$orderId)throw new DomainException('Venta no encontrada.');
            $q=$this->db->prepare('SELECT id_pedido FROM tbpedidos WHERE id_pedido=? FOR UPDATE');$q->execute([$orderId]);
            $q=$this->db->prepare('SELECT * FROM tbventas WHERE id_venta=? FOR UPDATE');$q->execute([$id]);$sale=$q->fetch();
            $q=$this->db->prepare('SELECT id_venta,huella FROM tbventasanulaciones WHERE solicitud=? OR id_venta=?');$q->execute([$key,$id]);$old=$q->fetch();if($old){if(!hash_equals($old['huella'],$hash))throw new OrderConflict('La venta ya se anuló o la solicitud cambió.');$this->db->commit();return $this->find($id,0,true)+['repeated'=>true];}
            if($sale['estado']!=='pagada')throw new OrderConflict('Solo puedes anular una venta pagada.');
            $order=(new OrderModel($this->db))->find($orderId,0,true);if(!$order || $order['estado']!=='entregado')throw new OrderConflict('La entrega de esta venta requiere revisión.');
            if($return)(new OrderModel($this->db))->returnStock($order,$actor,$user['nombres'].' '.$user['apellidos'],'Devolución de '.$sale['codigo_venta'].': '.trim($reason));
            $q=$this->db->prepare("UPDATE tbventas SET estado='anulada' WHERE id_venta=?");$q->execute([$id]);
            $q=$this->db->prepare('INSERT INTO tbventasanulaciones(id_venta,id_usuario,responsable,reembolso,stock_devuelto,motivo,solicitud,huella,created_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP())');$q->execute([$id,$actor,$user['nombres'].' '.$user['apellidos'],$sale['total'],$return?1:0,trim($reason),$key,$hash]);
            $this->db->commit();return $this->find($id,0,true)+['repeated'=>false];
        }catch(Throwable $error){if($this->db->inTransaction())$this->db->rollBack();throw $error;}
    }
    public function find(int $id,int $user,bool $staff):?array
    {
        $q=$this->db->prepare('SELECT s.*,o.contacto_nombre,o.id_usuario AS propietario,o.codigo_pedido,u.nombres AS cajero_nombres,u.apellidos AS cajero_apellidos FROM tbventas s JOIN tbpedidos o ON o.id_pedido=s.id_pedido JOIN tbusuarios u ON u.id_usuario=s.id_usuario WHERE s.id_venta=? AND s.cobro_solicitud IS NOT NULL'.($staff?'':' AND o.id_usuario=?'));$q->execute($staff?[$id]:[$id,$user]);$sale=$q->fetch();if(!$sale)return null;
        $sale['order']=(new OrderModel($this->db))->find((int)$sale['id_pedido'],$user,$staff);$q=$this->db->prepare('SELECT * FROM tbventasanulaciones WHERE id_venta=?');$q->execute([$id]);$sale['cancellation']=$q->fetch()?:null;return $sale;
    }
}
