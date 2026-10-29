<?php
declare(strict_types=1);
require_once BASE_PATH.'/app/Models/ReinvestmentModel.php';
final class ReinvestmentController
{
    private function user(bool $json=false):?array
    {
        $user=Auth::requireUser($json);if(!$user)return null;if($user['nombre_rol']!=='Administrador'){ErrorHandler::respond(403,'La reinversión solo está disponible para administración.');return null;}if(!ReinvestmentModel::enabled(Database::connection())){ErrorHandler::respond(503,'Reinversión está pendiente de activación en esta instalación.');return null;}return $user;
    }
    public function index():void
    {
        if(!($user=$this->user()))return;$admin=true;$staff=true;$title='Reinversión de ventas';$kind='reinvestment';$table=true;require BASE_PATH.'/app/Views/commerce/reinvestment.php';
    }
    public function data():void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';if(!$this->user(true))return;
        try{
            $result=(new ReinvestmentModel(Database::connection()))->data($_GET);$e=static fn($s)=>htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
            $result['data']=array_map(static function(array $row)use($e):array{
                $date=(new DateTimeImmutable($row['created_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('America/El_Salvador'))->format('d/m/Y H:i');
                return [$e($row['codigo_venta']?:'Venta anterior #'.$row['id_venta']),$e($row['codigo_pedido']??'—'),$date,'$'.$e($row['monto_venta']),'$'.$e($row['monto_reposicion']).'<small class="admin-table__subtext">'.$e($row['porcentaje_reposicion']).' %</small>','$'.$e($row['monto_crecimiento']).'<small class="admin-table__subtext">'.$e($row['porcentaje_crecimiento']).' %</small>','<strong>$'.$e($row['total_reinversion']).'</strong>',$row['estado']==='pagada'?'Vigente':($row['estado']==='anulada'?'Anulada · excluida':'No cobrada · excluida'),$row['cobro_solicitud']?'<a class="order-link" href="'.app_url_html('/sales/detail?id='.(int)$row['id_venta']).'">Ver venta</a>':'Registro anterior'];
            },$result['rows']);unset($result['rows']);header('Content-Type: application/json; charset=utf-8');echo json_encode($result,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
        }catch(InvalidArgumentException $error){ErrorHandler::respond(422,$error->getMessage());}
    }
}
