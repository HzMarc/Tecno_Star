<?php
declare(strict_types=1);
require_once BASE_PATH.'/app/Models/ServiceModel.php';
require_once BASE_PATH.'/core/ServiceMail.php';

final class ServiceController
{
    private function available():bool
    {
        if(ServiceModel::installed(Database::connection()))return true;
        ErrorHandler::respond(503,'Los servicios todavía no están habilitados. Intenta más tarde.');return false;
    }

    private function user(bool $staff=false,bool $admin=false):?array
    {
        $u=Auth::requireUser(str_contains($_SERVER['HTTP_ACCEPT']??'','application/json'));if(!$u || !$this->available())return null;
        if(($admin && $u['nombre_rol']!=='Administrador') || ($staff && !ServiceModel::staff(Database::connection(),(int)$u['id_usuario']))){ErrorHandler::respond(403,'No tienes permiso para gestionar servicios.');return null;}return $u;
    }

    public function index():void
    {
        if(!$this->available())return;$model=new ServiceModel(Database::connection());$services=$model->catalog();$categories=$model->categories();$user=Auth::current();$staff=$user && ServiceModel::staff(Database::connection(),(int)$user['id_usuario']);$admin=false;$page='catalog';$title='Papelería y servicios';require BASE_PATH.'/app/Views/services/page.php';
    }

    public function request():void
    {
        if(!($user=$this->user()))return;
        try{$id=ServiceModel::integer($_GET['id']??null);$services=(new ServiceModel(Database::connection()))->catalog();$service=null;foreach($services as $s)if((int)$s['id_servicio']===$id)$service=$s;if(!$service){ErrorHandler::respond(404,'Servicio no encontrado.');return;}$staff=false;$admin=false;$page='request';$title='Solicitar '.$service['nombre'];require BASE_PATH.'/app/Views/services/page.php';}
        catch(InvalidArgumentException $e){ErrorHandler::respond(422,$e->getMessage());}
    }

    public function mine():void{$this->listing(false);}
    public function admin():void{$this->listing(true);}
    private function listing(bool $manage):void
    {
        if(!($user=$this->user($manage)))return;
        try{$staff=$manage;$admin=$manage && $user['nombre_rol']==='Administrador';$page='list';$title=$manage?'Solicitudes de servicios':'Mis solicitudes';$model=new ServiceModel(Database::connection());$listing=$model->listing((int)$user['id_usuario'],$staff,$_GET);$stateCounts=$manage?$model->stateCounts():[];require BASE_PATH.'/app/Views/services/page.php';}
        catch(InvalidArgumentException $e){ErrorHandler::respond(422,$e->getMessage());}
    }

    public function detail():void
    {
        if(!($user=$this->user()))return;
        try{$id=ServiceModel::integer($_GET['id']??null);$staff=ServiceModel::staff(Database::connection(),(int)$user['id_usuario']);$request=(new ServiceModel(Database::connection()))->find($id,(int)$user['id_usuario'],$staff);if(!$request){ErrorHandler::respond(404,'Solicitud no encontrada.');return;}$admin=$staff && $user['nombre_rol']==='Administrador';$page='detail';$title=$request['codigo'];require BASE_PATH.'/app/Views/services/page.php';}
        catch(InvalidArgumentException $e){ErrorHandler::respond(422,$e->getMessage());}
    }

    public function catalog():void
    {
        if(!($user=$this->user(false,true)))return;$model=new ServiceModel(Database::connection());$services=$model->catalog(true);$categories=$model->categories();$staff=true;$admin=true;$page='manage';$title='Catálogo de servicios';require BASE_PATH.'/app/Views/services/page.php';
    }

    public function data():void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';if(!($user=$this->user(true)))return;
        try{
            $start=$_GET['start']??'0';$draw=$_GET['draw']??'0';$length=$_GET['length']??'12';
            if(!is_string($start) || !ctype_digit($start) || (float)$start>10000000 || !is_string($draw) || !ctype_digit($draw) || (float)$draw>2147483647 || $length!=='12')throw new InvalidArgumentException('Paginación inválida.');
            $search=$_GET['search']??[];if(!is_array($search) || !is_string($search['value']??''))throw new InvalidArgumentException('Búsqueda inválida.');
            $model=new ServiceModel(Database::connection());$result=$model->listing((int)$user['id_usuario'],true,['page'=>(string)(intdiv((int)$start,12)+1),'q'=>$search['value']??'','status'=>$_GET['status']??'']);$all=$model->listing((int)$user['id_usuario'],true,[]);$e=static fn($v)=>htmlspecialchars((string)$v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');$rows=[];
            foreach($result['items'] as $row)$rows[]=['<strong>'.$e($row['nombre_servicio']).'</strong><small class="service-table-code">'.$e($row['codigo']).'</small>',$e($row['contacto_nombre']),'<span class="service-status service-status--'.$e($row['estado']).'">'.$e(ServiceModel::STATES[$row['estado']]).'</span>',$row['total']===null?($row['estado']==='cotizada'?'Por aprobar':'Por cotizar'):'$'.$e($row['total']),'<a class="btn btn-outline-primary service-table-open" href="'.app_url_html('/services/detail?id='.(int)$row['id_solicitud']).'">Abrir<span class="visually-hidden"> solicitud '.$e($row['codigo']).'</span></a>'];
            header('Content-Type: application/json');echo json_encode(['draw'=>(int)$draw,'recordsTotal'=>$all['total'],'recordsFiltered'=>$result['total'],'data'=>$rows],JSON_THROW_ON_ERROR);
        }catch(InvalidArgumentException $e){ErrorHandler::respond(422,$e->getMessage());}
    }

    public function download():void
    {
        if(!($user=$this->user()))return;
        try{$id=ServiceModel::integer($_GET['id']??null);$q=Database::connection()->prepare('SELECT * FROM tbservicioarchivos WHERE id_archivo=? AND eliminado_at IS NULL');$q->execute([$id]);$file=$q->fetch();$staff=ServiceModel::staff(Database::connection(),(int)$user['id_usuario']);
            if(!$file || !(new ServiceModel(Database::connection()))->find((int)$file['id_solicitud'],(int)$user['id_usuario'],$staff)){ErrorHandler::respond(404,'Archivo no encontrado.');return;}$path=ServiceUpload::path($file['ruta']);if(!is_file($path)){ErrorHandler::respond(410,'El archivo ya no está disponible.');return;}
            header('Content-Type: '.$file['mime']);header('Content-Disposition: attachment; filename="archivo.'.pathinfo($file['ruta'],PATHINFO_EXTENSION).'"; filename*=UTF-8\'\''.rawurlencode($file['nombre']));header('X-Content-Type-Options: nosniff');header("Content-Security-Policy: sandbox");header('Content-Length: '.filesize($path));session_write_close();readfile($path);
        }catch(InvalidArgumentException $e){ErrorHandler::respond(422,$e->getMessage());}
    }

    public function create():void{$this->write('create');}
    public function quote():void{$this->write('quote',true);}
    public function decide():void{$this->write('decide');}
    public function state():void{$this->write('transition');}
    public function message():void{$this->write('message');}
    public function material():void{$this->write('material',true);}
    public function result():void{$this->write('result',true);}
    public function pay():void{$this->write('pay',true);}
    public function refund():void{$this->write('refund',true,true);}
    public function save():void{$this->write('saveService',false,true);}
    public function category():void{$this->write('saveCategory',false,true);}

    private function write(string $action,bool $staff=false,bool $admin=false):void
    {
        $_SERVER['HTTP_ACCEPT']='application/json';if(!($user=$this->user($staff,$admin)))return;
        if(($_POST['target_user_id']??null)!==(string)$user['id_usuario']){ErrorHandler::respond(409,'La cuenta cambió. Abre otra vez el formulario.');return;}
        $files=[];$keep=false;
        try{
            if(in_array($action,['create','result'],true))$files=ServiceUpload::receive($_FILES['archivos']??[]);
            $model=new ServiceModel(Database::connection());$response=in_array($action,['create','result'],true)?$model->$action((int)$user['id_usuario'],$_POST,$files):$model->$action((int)$user['id_usuario'],$_POST);
            $keep=!($response['repeated']??false);$location=app_url(isset($response['id'])?'/services/detail?id='.$response['id']:'/admin/services/catalog');
            $mailStatus=null;$notice='';
            if(isset($response['id'])){
                try{$mailStatus=(new ServiceMail(Database::connection()))->sendOne((int)$response['id']);}
                catch(Throwable){$mailStatus='pending';Logger::write('service.mail_failed','warning');}
                if($mailStatus==='enviado')$notice=' Aviso enviado por correo.';
                elseif(in_array($mailStatus,['pending','failed','busy'],true))$notice=' El aviso de correo quedó pendiente de envío.';
            }
            header('Content-Type: application/json; charset=utf-8');echo json_encode(['ok'=>true,'message'=>(($response['repeated']??false)?'La operación ya estaba registrada.':'Datos guardados.').$notice,'mail_status'=>$mailStatus,'location'=>$location],JSON_THROW_ON_ERROR);
        }catch(ServiceConflict $e){ErrorHandler::respond(409,$e->getMessage());}catch(InvalidArgumentException|DomainException $e){ErrorHandler::respond(422,$e->getMessage());}
        finally{if(!$keep)ServiceUpload::discard($files);}
    }
}
