<?php
declare(strict_types=1);
require_once BASE_PATH.'/app/Models/SaleModel.php';
final class SaleController
{
    public static function available():bool{if(OrderModel::cashEnabled(Database::connection()))return true;ErrorHandler::respond(503,'El Plan 7 está pendiente de activación.');return false;}
    public function index():void{$this->listing(false);}
    public function purchases():void{$this->listing(true);}
    private function listing(bool $own):void
    {
        $user=Auth::requireUser();if(!$user || !self::available())return;$staff=OrderModel::isStaff($user);if(!$own && !$staff){ErrorHandler::respond(403,'No tienes permiso de caja.');return;}
        $kind=$own?'purchases':'sales';$admin=!$own && $user['nombre_rol']==='Administrador';$title=$own?'Mis compras':'Ventas en efectivo';
        if($own){
            try{$cards=(new SaleModel(Database::connection()))->purchaseCards((int)$user['id_usuario'],$_GET);}
            catch(InvalidArgumentException $error){ErrorHandler::respond(422,$error->getMessage());return;}
            require BASE_PATH.'/app/Views/commerce/purchases.php';return;
        }
        require BASE_PATH.'/app/Views/commerce/list.php';
    }
    public function detail():void
    {
        if(!($user=Auth::requireUser()) || !self::available())return;$staff=OrderModel::isStaff($user);$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);$sale=$id?(new SaleModel(Database::connection()))->find($id,(int)$user['id_usuario'],$staff):null;if(!$sale){ErrorHandler::respond(404,'Venta no encontrada.');return;}$admin=$user['nombre_rol']==='Administrador';$reinvestment=$admin && ReinvestmentModel::enabled(Database::connection())?(new ReinvestmentModel(Database::connection()))->forSale((int)$id):null;require BASE_PATH.'/app/Views/commerce/sale.php';
    }
    public function pay():void{$this->write(false);}
    public function cancel():void{$this->write(true);}
    private function write(bool $cancel):void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';if(!($user=Auth::requireUser(true)) || !self::available())return;
        if(!OrderModel::isStaff($user) || ($cancel && $user['nombre_rol']!=='Administrador')){ErrorHandler::respond(403,'No tienes permiso para esta operación.');return;}
        if(($_POST['target_user_id']??'')!==(string)$user['id_usuario']){ErrorHandler::respond(409,'La cuenta cambió. Abre de nuevo el formulario.');return;}
        if(!$cancel && !ReinvestmentModel::enabled(Database::connection())){ErrorHandler::respond(503,'Caja está pendiente de activación. Solicita al administrador completar la habilitación antes de cobrar.');return;}
        try{$model=new SaleModel(Database::connection());$sale=$cancel?$model->cancel((int)$user['id_usuario'],$_POST):$model->pay((int)$user['id_usuario'],$_POST);header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'message'=>!empty($sale['repeated'])?'La operación ya estaba registrada.':($cancel?'Venta anulada y reembolso registrado.':'Cobro y entrega registrados.'),'location'=>app_url(!$cancel && ($_POST['return_to']??'')==='cash'?'/admin/cash?id='.(int)$sale['id_pedido']:'/sales/detail?id='.(int)$sale['id_venta'])],JSON_THROW_ON_ERROR);}
        catch(OrderConflict $error){ErrorHandler::respond(409,$error->getMessage());}catch(InvalidArgumentException|DomainException $error){ErrorHandler::respond(422,$error->getMessage());}
    }
}
