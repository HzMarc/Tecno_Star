<?php
declare(strict_types=1);
require_once BASE_PATH.'/app/Models/OrderModel.php';
require_once BASE_PATH.'/app/Models/AdminTableModel.php';
require_once BASE_PATH.'/core/AdminTableRows.php';
require_once BASE_PATH.'/core/OrderMail.php';

final class OrderController
{
    private function model(): OrderModel {return new OrderModel(Database::connection());}
    private function available(): bool
    {
        if(OrderModel::enabled(Database::connection())) return true;
        ErrorHandler::respond(503,'El módulo de pedidos está pendiente de activación.');return false;
    }
    public function checkout(): void
    {
        if(!($user=Auth::requireUser()) || !$this->available()) return;
        $quote=$this->model()->quote((int)$user['id_usuario']);
        $query=Database::connection()->prepare('SELECT telefono FROM tbclientes WHERE id_usuario=? LIMIT 1');$query->execute([$user['id_usuario']]);
        $contact=['nombre'=>$user['nombres'].' '.$user['apellidos'],'correo'=>$user['correo'],'telefono'=>(string)($query->fetchColumn()?:'')];
        $request=bin2hex(random_bytes(32));$mode='checkout';$admin=false;$staff=false;
        require BASE_PATH.'/app/Views/orders/page.php';
    }
    public function index(): void {$this->listing(false);}
    public function admin(): void {$this->listing(true);}
    private function listing(bool $admin): void
    {
        $staff=$admin;$user=Auth::requireUser();
        if(!$user || !$this->available()) return;
        if($staff && !OrderModel::isStaff($user)){ErrorHandler::respond(403,'No tienes permiso para gestionar pedidos.');return;}
        $admin=$staff && $user['nombre_rol']==='Administrador';
        $this->model()->expireDue();
        if($staff){
            $buckets=['pending'=>['Pendientes',['pendiente']],'confirmed'=>['Confirmados',['confirmado','en_preparacion','listo']],'history'=>['Historial',['entregado','cancelado']]];
            $bucket=is_string($_GET['bucket']??null) && isset($buckets[$_GET['bucket']])?$_GET['bucket']:'pending';
            if(!isset($_GET['bucket']))foreach($buckets as $key=>$info)if(in_array($_GET['status']??'', $info[1],true))$bucket=$key;
            $counts=array_fill_keys(array_keys($buckets),0);
            foreach(Database::connection()->query('SELECT estado,COUNT(*) AS n FROM tbpedidos WHERE retiro_solicitud IS NOT NULL GROUP BY estado')->fetchAll() as $row)foreach($buckets as $key=>$info)if(in_array($row['estado'],$info[1],true))$counts[$key]+=(int)$row['n'];
        }else $cards=$this->model()->cards((int)$user['id_usuario'],$_GET);
        $mode='list';require BASE_PATH.'/app/Views/orders/page.php';
    }
    public function detail(): void
    {
        if(!($user=Auth::requireUser()) || !$this->available()) return;
        $admin=$user['nombre_rol']==='Administrador';$staff=OrderModel::isStaff($user);$this->model()->expireDue();
        $id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);
        $order=$id && $id>0?$this->model()->find($id,(int)$user['id_usuario'],$staff):null;
        if(!$order) {ErrorHandler::respond(404,'Pedido no encontrado.');return;}
        $events=OrderModel::readyMailEnabled(Database::connection());$q=Database::connection()->prepare('SELECT estado'.($events?',tipo':",'confirmado' AS tipo").' FROM tbretiocorreos WHERE id_pedido=? ORDER BY id_correo DESC LIMIT 1');$q->execute([$id]);$mailJob=$q->fetch();$mailStatus=$mailJob['estado']??null;$mailType=$mailJob['tipo']??'confirmado';
        $mode='detail';require BASE_PATH.'/app/Views/orders/page.php';
    }
    public function data(): void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';
        if(!($user=Auth::requireUser(true)) || !$this->available()) return;
        $admin=($_GET['scope']??'')==='admin';
        if($admin && !OrderModel::isStaff($user)) {ErrorHandler::respond(403,'Acceso no autorizado.');return;}
        try {
            $this->model()->expireDue();
            $result=(new AdminTableModel(Database::connection()))->data('orders',$_GET,$admin?null:(int)$user['id_usuario']);
            $result['data']=array_map(static fn($row)=>AdminTableRows::cells('orders',$row),$result['rows']);unset($result['rows']);
            header('Content-Type: application/json; charset=utf-8');echo json_encode($result,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE);
        }catch(InvalidArgumentException $error){ErrorHandler::respond(422,$error->getMessage());}
    }
    public function confirm(): void {$this->write(false);}
    public function state(): void {$this->write(true);}
    public function scan(): void
    {
        $code=$_GET['codigo']??'';
        if(!is_string($code) || !preg_match('/^TS-[0-9]{6}-[A-F0-9]{12}$/D',$code)){ErrorHandler::respond(422,'Código de pedido inválido.');return;}
        if(!Auth::current()) {$_SESSION['order_return_code']=$code;header('Location: '.app_url('/login'));return;}
        $user=Auth::requireUser();if(!$user || !$this->available())return;
        $q=Database::connection()->prepare('SELECT id_pedido FROM tbpedidos WHERE codigo_pedido=? AND retiro_solicitud IS NOT NULL'.(OrderModel::isStaff($user)?'':' AND id_usuario=?'));$q->execute(OrderModel::isStaff($user)?[$code]:[$code,$user['id_usuario']]);$id=$q->fetchColumn();
        if(!$id){ErrorHandler::respond(404,'Pedido no encontrado o sin permiso de acceso.');return;}
        header('Location: '.app_url((OrderModel::isStaff($user)?'/admin/cash':'/orders/detail').'?id='.(int)$id.(OrderModel::isStaff($user)?'#cash-payment':'')));
    }
    public function resend(): void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';
        if(!($user=Auth::requireUser(true)) || !$this->available())return;
        if(!OrderModel::isStaff($user)){ErrorHandler::respond(403,'No tienes permiso para enviar correos.');return;}
        if(($_POST['target_user_id']??'')!==(string)$user['id_usuario']){ErrorHandler::respond(409,'La cuenta cambió.');return;}
        $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
        if(!$id || !$this->model()->find($id,(int)$user['id_usuario'],true)){ErrorHandler::respond(404,'Pedido no encontrado.');return;}
        $type=$_POST['tipo']??null;if($type!==null && !in_array($type,['confirmado','listo'],true)){ErrorHandler::respond(422,'Tipo de aviso inválido.');return;}
        $status=(new OrderMail(Database::connection()))->sendOne($id,$type);
        header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'message'=>$status==='enviado'?'Correo enviado.':($status==='none'?'No hay un correo pendiente listo para enviar.':'Correo pendiente. Revisa la configuración y espera cinco minutos entre reintentos.'),'location'=>app_url('/orders/detail?id='.$id)],JSON_THROW_ON_ERROR);
    }
    private function write(bool $transition): void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';
        if(!($user=Auth::requireUser(true)) || !$this->available()) return;
        try {
            if(($_POST['target_user_id']??'')!==(string)$user['id_usuario']) {ErrorHandler::respond(409,'La cuenta cambió. Abre de nuevo el pedido con tu cuenta actual.');return;}
            if($transition) {
                $id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
                if(!$id || $id<1) throw new InvalidArgumentException('Pedido inválido.');
                if(!$this->model()->find($id,(int)$user['id_usuario'],OrderModel::isStaff($user))) {ErrorHandler::respond(404,'Pedido no encontrado.');return;}
                foreach(['estado','esperado','motivo'] as $key) if(!is_string($_POST[$key]??null)) throw new InvalidArgumentException('Datos de estado inválidos.');
                if(OrderModel::isStaff($user) && $_POST['estado']==='listo' && !OrderModel::readyMailEnabled(Database::connection())){ErrorHandler::respond(503,'El aviso de retiro está pendiente de activación. Completa la actualización de la cola antes de marcar el pedido listo.');return;}
                $order=$this->model()->transition((int)$user['id_usuario'],$id,$_POST['estado'],$_POST['esperado'],$_POST['motivo']);
            } else $order=$this->model()->create((int)$user['id_usuario'],$_POST);
            $mailMessage='';
            if($transition && in_array($order['estado'],['confirmado','listo'],true) && OrderModel::isStaff($user)) {
                $ready=$order['estado']==='listo';
                try{$sent=(new OrderMail(Database::connection()))->sendOne((int)$order['id_pedido'],$order['estado']);$mailMessage=$sent==='enviado'?($ready?' Aviso de retiro enviado por correo.':' Correo de confirmación enviado por correo.'):(in_array($sent,['pending','busy'],true)?($ready?' El aviso de retiro quedó pendiente de envío.':' El correo de confirmación quedó pendiente de envío.'):'');}catch(Throwable){$mailMessage=' El correo quedó pendiente de envío.';}
            }
            $returnToCash=$transition && OrderModel::isStaff($user) && ($_POST['return_to']??'')==='cash' && $order['estado']!=='listo';
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok'=>true,'message'=>(!empty($order['expired'])?'La reserva venció y se liberó.':(!empty($order['repeated'])?'La operación ya estaba registrada.':($transition?(!OrderModel::isStaff($user) && $order['estado']==='cancelado'?'Pedido cancelado.':'Estado actualizado.'):'Pedido creado.'))).$mailMessage,'location'=>app_url(($returnToCash?'/admin/cash?id=':'/orders/detail?id=').(int)$order['id_pedido'].($returnToCash?'#cash-payment':''))],JSON_THROW_ON_ERROR);
        }catch(OrderConflict $error){ErrorHandler::respond(409,$error->getMessage());}
        catch(InvalidArgumentException|DomainException $error){ErrorHandler::respond(422,$error->getMessage());}
    }
}
