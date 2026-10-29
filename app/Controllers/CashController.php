<?php
declare(strict_types=1);
require_once BASE_PATH.'/app/Models/SaleModel.php';
final class CashController
{
    private function user(bool $json=false):?array
    {
        $user=Auth::requireUser($json);if(!$user)return null;if(!OrderModel::isStaff($user)){ErrorHandler::respond(403,'No tienes permiso de caja.');return null;}if(!SaleController::available())return null;return $user;
    }
    public function index():void
    {
        if(!($user=$this->user()))return;$db=Database::connection();$model=new OrderModel($db);$model->expireDue();$order=null;$sale=null;$problem='';
        if(isset($_GET['codigo'])){$code=$_GET['codigo'];if(!is_string($code) || !preg_match('/^TS-[0-9]{6}-[A-F0-9]{12}$/D',$code))$problem='Código de pedido inválido.';else{$q=$db->prepare('SELECT id_pedido FROM tbpedidos WHERE codigo_pedido=? AND retiro_solicitud IS NOT NULL');$q->execute([$code]);$id=$q->fetchColumn();if($id)$order=$model->find((int)$id,0,true);}}
        elseif(isset($_GET['id'])){$id=filter_var($_GET['id'],FILTER_VALIDATE_INT);if($id && $id>0)$order=$model->find($id,0,true);}
        if((isset($_GET['codigo']) || isset($_GET['id'])) && !$order && !$problem)$problem='Pedido no encontrado. Comprueba el código e intenta de nuevo.';
        if($order){$q=$db->prepare('SELECT id_venta FROM tbventas WHERE id_pedido=? AND cobro_solicitud IS NOT NULL');$q->execute([$order['id_pedido']]);if($id=$q->fetchColumn())$sale=(new SaleModel($db))->find((int)$id,0,true);}
        $canCharge=ReinvestmentModel::enabled($db);$admin=$user['nombre_rol']==='Administrador';$staff=true;$kind='cash';$title='Caja';$request=bin2hex(random_bytes(32));require BASE_PATH.'/app/Views/commerce/cash.php';
    }
    public function status():void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';if(!($user=$this->user(true)))return;
        $key=$_GET['solicitud']??'';$order=filter_var($_GET['id_pedido']??null,FILTER_VALIDATE_INT);
        if(!is_string($key) || !preg_match('/^[a-f0-9]{64}$/D',$key) || !$order || $order<1){ErrorHandler::respond(422,'Solicitud inválida.');return;}
        $q=Database::connection()->prepare('SELECT id_venta,id_usuario,id_pedido FROM tbventas WHERE cobro_solicitud=?');$q->execute([$key]);$sale=$q->fetch();
        if($sale && ((int)$sale['id_usuario']!==(int)$user['id_usuario'] || (int)$sale['id_pedido']!==$order)){ErrorHandler::respond(409,'La solicitud pertenece a otra operación.');return;}
        $q=Database::connection()->prepare('SELECT id_venta FROM tbventas WHERE id_pedido=? AND cobro_solicitud IS NOT NULL');$q->execute([$order]);$other=$q->fetchColumn();
        header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'found'=>(bool)$sale,'charged'=>(bool)$other,'location'=>$sale || $other?app_url('/admin/cash?id='.$order):null],JSON_THROW_ON_ERROR);
    }
}
