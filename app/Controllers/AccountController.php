<?php
declare(strict_types=1);
require_once BASE_PATH.'/app/Models/AccountModel.php';require_once BASE_PATH.'/app/Controllers/SaleController.php';require_once BASE_PATH.'/core/LoginLimiter.php';require_once BASE_PATH.'/core/OrderMail.php';
require_once BASE_PATH.'/core/RecoveryMail.php';
final class AccountController
{
    public function users():void
    {
        if(!($user=Auth::requireRole('Administrador')) || !SaleController::available())return;$kind='users';$title='Usuarios y permisos';$admin=true;$staff=true;require BASE_PATH.'/app/Views/commerce/list.php';
    }
    public function editor():void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';if(!Auth::requireRole('Administrador') || !SaleController::available())return;$id=filter_var($_GET['id']??null,FILTER_VALIDATE_INT);$q=Database::connection()->prepare('SELECT u.id_usuario,u.nombres,u.apellidos,u.correo,u.estado,u.cuenta_revision,r.nombre_rol,c.telefono,(SELECT pedidos FROM tbpersonalpermisos WHERE id_usuario=u.id_usuario) AS permiso_pedidos FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol LEFT JOIN tbclientes c ON c.id_usuario=u.id_usuario WHERE u.id_usuario=?');$q->execute([$id]);$row=$q->fetch();if(!$row){ErrorHandler::respond(404,'Usuario no encontrado.');return;}$row['pedidos']=OrderModel::isStaff($row)?'1':'0';header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'user'=>$row],JSON_THROW_ON_ERROR);
    }
    public function save():void{$this->write('admin');}
    public function profile():void{$this->write('profile');}
    public function password():void{$this->write('password');}
    private function write(string $action):void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';$user=$action==='admin'?Auth::requireRole('Administrador'):Auth::requireUser(true);if(!$user || !SaleController::available())return;
        if(($_POST['target_user_id']??'')!==(string)$user['id_usuario']){ErrorHandler::respond(409,'La cuenta cambió. Abre de nuevo el formulario.');return;}
        try{$model=new AccountModel(Database::connection());$location=app_url('/profile');
            if($action==='admin'){$id=$_POST['id']??'';if(!is_string($id) || ($id!=='' && (!ctype_digit($id) || (int)$id<1)))throw new InvalidArgumentException('Usuario inválido.');$model->save((int)$user['id_usuario'],$id===''?null:(int)$id,$_POST);$location=app_url('/admin/users');}
            elseif($action==='profile')$model->profile((int)$user['id_usuario'],$_POST);
            else{$model->changePassword((int)$user['id_usuario'],$_POST);Auth::forget();$location=app_url('/login');}
            header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'message'=>$action==='password'?'Contraseña cambiada. Inicia sesión de nuevo.':'Datos guardados.','location'=>$location],JSON_THROW_ON_ERROR);
        }catch(OrderConflict $error){ErrorHandler::respond(409,$error->getMessage());}catch(InvalidArgumentException|DomainException $error){ErrorHandler::respond(422,$error->getMessage());}
    }
    public function forgot():void{$mode='forgot';require BASE_PATH.'/app/Views/auth/recovery.php';}
    public function resetForm():void{header('Referrer-Policy: no-referrer');$mode='reset';require BASE_PATH.'/app/Views/auth/recovery.php';}
    public function requestReset():void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';if(!SaleController::available())return;$email=$_POST['correo']??null;if(!is_string($email) || strlen($email)>150 || !filter_var($email,FILTER_VALIDATE_EMAIL)){ErrorHandler::respond(422,'Introduce un correo válido.');return;}
        $limiter=new LoginLimiter(APP_STORAGE_PATH.'/security/recovery',3,10,900);$wait=$limiter->reserve($email,$_SERVER['REMOTE_ADDR']??'');if($wait>0){header('Retry-After: '.$wait);ErrorHandler::respond(429,'Espera antes de solicitar otro enlace.');return;}
        $reset=(new AccountModel(Database::connection()))->requestReset(trim($email));
        $body=json_encode(['ok'=>true,'message'=>'Si existe una cuenta activa con ese correo, recibirás un enlace para cambiar tu contraseña. Revisa también spam.'],JSON_THROW_ON_ERROR);
        // Finaliza la respuesta antes del SMTP para mantener el mismo mensaje y tiempo de respuesta.
        header('Content-Type: application/json; charset=utf-8');header('Content-Length: '.strlen($body));header('Connection: close');echo $body;
        if(session_status()===PHP_SESSION_ACTIVE)session_write_close();ignore_user_abort(true);while(ob_get_level()>0)ob_end_flush();if(function_exists('fastcgi_finish_request'))fastcgi_finish_request();else flush();
        if($reset)$this->sendReset($reset);
    }
    private function sendReset(array $reset):void
    {
        try{(new RecoveryMail())->send($reset);}catch(Throwable){try{$q=Database::connection()->prepare('UPDATE tbclaverecuperacion SET used_at=UTC_TIMESTAMP() WHERE id_recuperacion=?');$q->execute([$reset['id']]);}catch(Throwable){}Logger::write('account.recovery_mail_failed','warning');}
    }
    public function reset():void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';if(!SaleController::available())return;
        $limiter=new LoginLimiter(APP_STORAGE_PATH.'/security/reset',10,30,900);$wait=$limiter->reserve('reset',$_SERVER['REMOTE_ADDR']??'');if($wait>0){header('Retry-After: '.$wait);ErrorHandler::respond(429,'Espera antes de intentar de nuevo.');return;}
        try{(new AccountModel(Database::connection()))->reset($_POST);Auth::forget();header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'message'=>'Contraseña actualizada. Inicia sesión con tu nueva contraseña.','location'=>app_url('/login')],JSON_THROW_ON_ERROR);}catch(InvalidArgumentException|DomainException $error){ErrorHandler::respond(422,$error->getMessage());}
    }
}
