<?php
declare(strict_types=1);
require_once BASE_PATH.'/app/Models/AdminTableModel.php';require_once BASE_PATH.'/core/AdminTableRows.php';require_once BASE_PATH.'/app/Controllers/SaleController.php';
final class CommerceTableController
{
    public function data():void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';if(!($user=Auth::requireUser(true)) || !SaleController::available())return;$kind=$_GET['kind']??'';
        if(!is_string($kind) || !in_array($kind,['sales','purchases','users'],true)){ErrorHandler::respond(422,'Tabla inválida.');return;}
        if(($kind==='users' && $user['nombre_rol']!=='Administrador') || ($kind==='sales' && !OrderModel::isStaff($user))){ErrorHandler::respond(403,'No tienes permiso para consultar esta tabla.');return;}
        try{$result=(new AdminTableModel(Database::connection()))->data($kind,$_GET,$kind==='purchases'?(int)$user['id_usuario']:null);$result['data']=array_map(static fn($row)=>AdminTableRows::cells($kind,$row),$result['rows']);unset($result['rows']);header('Content-Type: application/json; charset=utf-8');echo json_encode($result,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);}catch(InvalidArgumentException $error){ErrorHandler::respond(422,$error->getMessage());}
    }
}
