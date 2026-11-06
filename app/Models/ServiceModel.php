<?php
declare(strict_types=1);
require_once BASE_PATH.'/core/Cash.php';
require_once BASE_PATH.'/core/ServiceUpload.php';

final class ServiceConflict extends DomainException {}

final class ServiceModel
{
    public const STATES=['nueva'=>'Nueva','en_cotizacion'=>'En cotización','cotizada'=>'Cotizada','aceptada'=>'Aceptada','en_proceso'=>'En proceso','lista'=>'Lista para retirar','entregada'=>'Entregada','cancelada'=>'Cancelada'];
    public function __construct(private PDO $db) {}

    public static function installed(PDO $db): bool
    {
        static $cache; $cache??=new WeakMap();
        if(!isset($cache[$db]))$cache[$db]=(int)$db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND ((TABLE_NAME='tbventas' AND COLUMN_NAME='id_solicitud_servicio') OR (TABLE_NAME='tbpersonalpermisos' AND COLUMN_NAME='servicios'))")->fetchColumn()===2;
        return $cache[$db];
    }

    public static function staff(PDO $db, int $id): bool
    {
        if(!self::installed($db))return false;
        $q=$db->prepare("SELECT r.nombre_rol,p.servicios FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol LEFT JOIN tbpersonalpermisos p ON p.id_usuario=u.id_usuario WHERE u.id_usuario=? AND u.estado='activo'");$q->execute([$id]);$u=$q->fetch();
        return $u && ($u['nombre_rol']==='Administrador' || ($u['nombre_rol']==='Empleado' && (bool)$u['servicios']));
    }

    private function actor(int $id, bool $staff=false, bool $admin=false): array
    {
        $q=$this->db->prepare("SELECT u.*,r.nombre_rol,p.servicios FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol LEFT JOIN tbpersonalpermisos p ON p.id_usuario=u.id_usuario WHERE u.id_usuario=? AND u.estado='activo' FOR UPDATE");$q->execute([$id]);$u=$q->fetch();
        if(!$u || ($admin && $u['nombre_rol']!=='Administrador') || ($staff && $u['nombre_rol']!=='Administrador' && !($u['nombre_rol']==='Empleado' && (bool)$u['servicios'])))throw new DomainException('No tienes permiso para esta operación de servicios.');
        return $u;
    }

    public static function text(array $input,string $key,int $max,int $min=0):string
    {
        $s=$input[$key]??'';
        if(!is_string($s) || mb_strlen(trim($s))<$min || mb_strlen($s)>$max)throw new InvalidArgumentException('Revisa el campo '.str_replace('_',' ',$key).'.');
        return trim($s);
    }

    public static function integer(mixed $value,int $max=2147483647):int
    {
        if(!is_string($value) || !ctype_digit($value) || (float)$value<1 || (float)$value>$max)throw new InvalidArgumentException('Identificador o cantidad inválidos.');
        return (int)$value;
    }

    private static function key(array $input):string
    {
        $key=$input['operacion']??null;if(!is_string($key) || !preg_match('/^[a-f0-9]{64}$/D',$key))throw new InvalidArgumentException('Abre nuevamente el formulario.');return $key;
    }

    private function transaction(callable $run):mixed
    {
        $this->db->beginTransaction();
        try{$result=$run();$this->db->commit();return $result;}catch(Throwable $error){if($this->db->inTransaction())$this->db->rollBack();throw $error;}
    }

    public function catalog(bool $all=false):array
    {
        return $this->db->query('SELECT s.*,c.nombre AS categoria,c.estado AS categoria_estado FROM tbserviciocatalogo s JOIN tbserviciocategorias c ON c.id_categoria=s.id_categoria'.($all?'':" WHERE s.estado='activo' AND c.estado='activo'").' ORDER BY c.id_categoria,s.id_servicio')->fetchAll();
    }

    public function categories():array{return $this->db->query('SELECT * FROM tbserviciocategorias ORDER BY id_categoria')->fetchAll();}

    public function stateCounts():array
    {
        $counts=array_fill_keys(array_keys(self::STATES),0);
        foreach($this->db->query('SELECT estado,COUNT(*) AS cantidad FROM tbserviciosolicitudes GROUP BY estado') as $row)$counts[$row['estado']]=(int)$row['cantidad'];
        return $counts;
    }

    private function event(int $id,int $actor,string $action,string $detail,?string $key=null,?string $hash=null):void
    {
        $q=$this->db->prepare('INSERT INTO tbserviciohistorial(id_solicitud,id_actor,accion,detalle,operacion,huella,created_at) VALUES (?,?,?,?,?,?,UTC_TIMESTAMP())');$q->execute([$id,$actor,$action,$detail,$key,$hash]);
    }

    private function notify(int $id,string $type):void
    {
        $q=$this->db->prepare('INSERT INTO tbserviciocorreos(id_solicitud,revision,tipo,proximo_intento,created_at) SELECT id_solicitud,revision,?,UTC_TIMESTAMP(),UTC_TIMESTAMP() FROM tbserviciosolicitudes WHERE id_solicitud=?');$q->execute([$type,$id]);
    }

    private function attach(int $id,int $actor,array $files,string $type):void
    {
        $q=$this->db->prepare('INSERT INTO tbservicioarchivos(id_solicitud,id_actor,nombre,ruta,mime,bytes,sha256,tipo,created_at) VALUES (?,?,?,?,?,?,?,?,UTC_TIMESTAMP())');
        foreach($files as $f)$q->execute([$id,$actor,$f['nombre'],$f['ruta'],$f['mime'],$f['bytes'],$f['sha256'],$type]);
    }

    private static function day(string $value,bool $future=true):?string
    {
        if($value==='')return null;
        $d=DateTimeImmutable::createFromFormat('!Y-m-d',$value,new DateTimeZone('America/El_Salvador'));
        if(!$d || $d->format('Y-m-d')!==$value || ($future && $value<(new DateTimeImmutable('now',new DateTimeZone('America/El_Salvador')))->format('Y-m-d')))throw new InvalidArgumentException('Selecciona una fecha válida de hoy en adelante.');return $value;
    }

    public function create(int $actor,array $input,array $files):array
    {
        $service=self::integer($input['id_servicio']??null);$quantity=self::integer($input['cantidad']??null,10000);$description=self::text($input,'descripcion',4000,3);$phone=self::text($input,'telefono',20,7);$preferred=self::day(self::text($input,'fecha_preferida',10));
        $physical=self::text($input,'material_fisico',1);if(!in_array($physical,['0','1'],true))throw new InvalidArgumentException('Indica cómo entregarás el material.');
        $options=[];foreach(['papel','color','caras','medidas'] as $field)$options[$field]=self::text($input,$field,100);
        if($options['color']!=='' && !in_array($options['color'],['Blanco y negro','Color'],true))throw new InvalidArgumentException('Color inválido.');
        if($options['caras']!=='' && !in_array($options['caras'],['Una cara','Dos caras'],true))throw new InvalidArgumentException('Caras inválidas.');
        if(!$files && $physical!=='1')throw new InvalidArgumentException('Adjunta los archivos necesarios para este servicio.');
        $key=self::key($input);$fingerprint=array_map(static fn($f)=>[$f['nombre'],$f['sha256']],$files);$hash=hash('sha256',json_encode([$actor,$service,$quantity,$description,$phone,$preferred,$physical,$options,$fingerprint],JSON_THROW_ON_ERROR));
        return $this->transaction(function()use($actor,$input,$service,$quantity,$description,$phone,$preferred,$physical,$options,$files,$key,$hash){
            $user=$this->actor($actor);
            $q=$this->db->prepare('SELECT * FROM tbserviciosolicitudes WHERE operacion=?');$q->execute([$key]);
            if($old=$q->fetch()){if((int)$old['id_usuario']!==$actor || !hash_equals($old['huella'],$hash))throw new ServiceConflict('La solicitud ya se utilizó con otros datos.');return ['id'=>(int)$old['id_solicitud'],'repeated'=>true];}
            $q=$this->db->prepare("SELECT s.* FROM tbserviciocatalogo s JOIN tbserviciocategorias c ON c.id_categoria=s.id_categoria WHERE id_servicio=? AND s.estado='activo' AND c.estado='activo' LOCK IN SHARE MODE");$q->execute([$service]);$s=$q->fetch();if(!$s)throw new DomainException('Este servicio no está disponible.');
            if($s['material_fisico'] && $physical!=='1')throw new InvalidArgumentException('Este servicio requiere entregar los originales en el local.');
            $q=$this->db->prepare("SELECT id_usuario FROM tbusuarios WHERE id_usuario=? FOR UPDATE");$q->execute([$actor]);
            $q=$this->db->prepare("SELECT COUNT(*) FROM tbserviciosolicitudes WHERE id_usuario=? AND estado NOT IN ('entregada','cancelada')");$q->execute([$actor]);if((int)$q->fetchColumn()>=20)throw new DomainException('Ya tienes 20 solicitudes abiertas. Finaliza alguna antes de crear otra.');
            $code='SV-'.(new DateTimeImmutable('now',new DateTimeZone('America/El_Salvador')))->format('ymd').'-'.strtoupper(bin2hex(random_bytes(6)));
            $q=$this->db->prepare('INSERT INTO tbserviciosolicitudes(codigo,id_servicio,id_usuario,nombre_servicio,unidad,cantidad,descripcion,opciones,contacto_nombre,contacto_correo,contacto_telefono,fecha_preferida,material_fisico,operacion,huella,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP())');
            $q->execute([$code,$service,$actor,$s['nombre'],$s['unidad'],$quantity,$description,json_encode($options,JSON_THROW_ON_ERROR),$user['nombres'].' '.$user['apellidos'],$user['correo'],$phone,$preferred,$physical,$key,$hash]);$id=(int)$this->db->lastInsertId();$this->attach($id,$actor,$files,'original');$this->event($id,$actor,'nueva','Solicitud recibida.');$this->notify($id,'recibida');return ['id'=>$id,'repeated'=>false];
        });
    }

    public function listing(int $actor,bool $staff,array $input):array
    {
        $status=self::text($input,'status',30);$term=self::text($input,'q',150);$page=self::integer($input['page']??'1',1000000);$where=[];$params=[];
        if(!$staff){$where[]='id_usuario=?';$params[]=$actor;}
        if($status!==''){if(!isset(self::STATES[$status]))throw new InvalidArgumentException('Estado inválido.');$where[]='estado=?';$params[]=$status;}
        if($term!==''){$where[]="(codigo LIKE ? ESCAPE '!' OR nombre_servicio LIKE ? ESCAPE '!' OR contacto_nombre LIKE ? ESCAPE '!')";$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$term).'%';array_push($params,$pattern,$pattern,$pattern);}
        $clause=$where?' WHERE '.implode(' AND ',$where):'';$q=$this->db->prepare('SELECT COUNT(*) FROM tbserviciosolicitudes'.$clause);$q->execute($params);$total=(int)$q->fetchColumn();$pages=max(1,(int)ceil($total/12));$page=min($page,$pages);
        $q=$this->db->prepare('SELECT * FROM tbserviciosolicitudes'.$clause.' ORDER BY id_solicitud DESC LIMIT 12 OFFSET '.(($page-1)*12));$q->execute($params);
        return ['items'=>$q->fetchAll(),'total'=>$total,'pages'=>$pages,'page'=>$page];
    }

    public function find(int $id,int $actor,bool $staff):?array
    {
        $q=$this->db->prepare('SELECT * FROM tbserviciosolicitudes WHERE id_solicitud=?'.($staff?'':' AND id_usuario=?'));$q->execute($staff?[$id]:[$id,$actor]);$row=$q->fetch();if(!$row)return null;
        foreach(['quotes'=>'SELECT *,vence_at<=UTC_TIMESTAMP() AS vencida FROM tbserviciocotizaciones WHERE id_solicitud=? ORDER BY version DESC','files'=>'SELECT * FROM tbservicioarchivos WHERE id_solicitud=? AND eliminado_at IS NULL ORDER BY id_archivo','history'=>'SELECT h.*,CONCAT(u.nombres,\' \',u.apellidos) AS responsable FROM tbserviciohistorial h JOIN tbusuarios u ON u.id_usuario=h.id_actor WHERE h.id_solicitud=? ORDER BY id_evento DESC'] as $key=>$sql){$q=$this->db->prepare($sql);$q->execute([$id]);$row[$key]=$q->fetchAll();}
        $q=$this->db->prepare('SELECT * FROM tbventas WHERE id_solicitud_servicio=?');$q->execute([$id]);$row['sale']=$q->fetch()?:null;
        if($row['sale']){$q=$this->db->prepare('SELECT * FROM tbventasanulaciones WHERE id_venta=?');$q->execute([$row['sale']['id_venta']]);$row['sale']['cancellation']=$q->fetch()?:null;}
        return $row;
    }

    /** Bloqueo por solicitud y clave idempotente para todas las decisiones/cobros. */
    private function change(int $actor,array $input,string $action,callable $run,bool $staff=false,bool $admin=false):array
    {
        $id=self::integer($input['id_solicitud']??null);$key=self::key($input);$clean=$input;unset($clean['_csrf']);ksort($clean);$hash=hash('sha256',json_encode([$actor,$action,$clean],JSON_THROW_ON_ERROR));
        return $this->transaction(function()use($id,$key,$hash,$actor,$input,$action,$run,$staff,$admin){
            $user=$this->actor($actor,$staff,$admin);$q=$this->db->prepare('SELECT * FROM tbserviciosolicitudes WHERE id_solicitud=? FOR UPDATE');$q->execute([$id]);$s=$q->fetch();if(!$s)throw new DomainException('Solicitud no encontrada.');
            if(!$staff && !$admin && (int)$s['id_usuario']!==$actor)throw new DomainException('No tienes permiso para esta solicitud.');
            $q=$this->db->prepare('SELECT * FROM tbserviciohistorial WHERE operacion=?');$q->execute([$key]);if($old=$q->fetch()){if((int)$old['id_solicitud']!==$id || !hash_equals($old['huella'],$hash))throw new ServiceConflict('La operación ya se utilizó con otros datos.');return ['id'=>$id,'repeated'=>true];}
            if(!is_string($input['revision']??null) || !ctype_digit($input['revision']) || (int)$input['revision']!==(int)$s['revision'])throw new ServiceConflict('La solicitud cambió. Recarga para revisar su estado actual.');
            $detail=$run($s,$user);
            $q=$this->db->prepare('UPDATE tbserviciosolicitudes SET revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id_solicitud=?');$q->execute([$id]);$this->event($id,$actor,$action,$detail,$key,$hash);$this->notify($id,$action);return ['id'=>$id,'repeated'=>false];
        });
    }

    public function quote(int $actor,array $input):array
    {
        $conditions=self::text($input,'condiciones',2000,3);$date=self::day(self::text($input,'fecha_entrega',10));if(!$date)throw new InvalidArgumentException('Confirma la fecha estimada de entrega.');
        $date=(new DateTimeImmutable($date.' 18:00:00',new DateTimeZone('America/El_Salvador')))->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
        $names=$input['concepto']??null;$quantities=$input['unidades']??null;$prices=$input['precio']??null;
        if(!is_array($names) || !is_array($quantities) || !is_array($prices) || count($names)<1 || count($names)>20 || count($names)!==count($quantities) || count($names)!==count($prices))throw new InvalidArgumentException('Agrega entre 1 y 20 conceptos a la cotización.');
        $lines=[];$total=0;
        foreach($names as $i=>$name){$name=self::text(['concepto'=>$name],'concepto',200,1);$quantity=self::integer($quantities[$i]??null,10000);$price=Cash::cents($prices[$i]??null);$subtotal=$quantity*$price;$total+=$subtotal;if($total>999999999999)throw new InvalidArgumentException('El importe excede el máximo.');$lines[]=['concepto'=>$name,'cantidad'=>$quantity,'precio'=>Cash::decimal($price),'subtotal'=>Cash::decimal($subtotal)];}
        return $this->change($actor,$input,'cotizacion',function($s)use($actor,$lines,$total,$conditions,$date){
            if(!in_array($s['estado'],['nueva','en_cotizacion','cotizada','aceptada'],true))throw new ServiceConflict('No puedes cotizar un trabajo iniciado o cerrado.');
            $q=$this->db->prepare('SELECT COALESCE(MAX(version),0)+1 FROM tbserviciocotizaciones WHERE id_solicitud=?');$q->execute([$s['id_solicitud']]);$version=(int)$q->fetchColumn();
            $q=$this->db->prepare("UPDATE tbserviciocotizaciones SET decision='sustituida' WHERE id_solicitud=? AND decision IN ('pendiente','aceptada')");$q->execute([$s['id_solicitud']]);
            $q=$this->db->prepare('INSERT INTO tbserviciocotizaciones(id_solicitud,version,conceptos,total,condiciones,fecha_entrega,vence_at,id_actor,created_at) VALUES (?,?,?,?,?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 7 DAY),?,UTC_TIMESTAMP())');$q->execute([$s['id_solicitud'],$version,json_encode($lines,JSON_THROW_ON_ERROR),Cash::decimal($total),$conditions,$date,$actor]);
            $q=$this->db->prepare("UPDATE tbserviciosolicitudes SET estado='cotizada',total=NULL,fecha_entrega=? WHERE id_solicitud=?");$q->execute([$date,$s['id_solicitud']]);return 'Cotización versión '.$version.' por $'.Cash::decimal($total).'. Requiere aceptación del cliente.';
        },true);
    }

    public function decide(int $actor,array $input):array
    {
        $accept=self::text($input,'decision',10);if(!in_array($accept,['aceptar','rechazar'],true))throw new InvalidArgumentException('Decisión inválida.');$quote=self::integer($input['id_cotizacion']??null);
        return $this->change($actor,$input,$accept,function($s)use($quote,$accept){
            if($s['estado']!=='cotizada')throw new ServiceConflict('La solicitud no tiene una cotización pendiente.');
            $q=$this->db->prepare('SELECT *,vence_at<=UTC_TIMESTAMP() AS vencida FROM tbserviciocotizaciones WHERE id_solicitud=? ORDER BY version DESC LIMIT 1 FOR UPDATE');$q->execute([$s['id_solicitud']]);$c=$q->fetch();
            if(!$c || (int)$c['id_cotizacion']!==$quote || $c['decision']!=='pendiente' || $c['vencida'])throw new ServiceConflict('La cotización cambió o venció. Solicita una nueva al personal.');
            $q=$this->db->prepare('UPDATE tbserviciocotizaciones SET decision=?,decidida_at=UTC_TIMESTAMP() WHERE id_cotizacion=?');$q->execute([$accept==='aceptar'?'aceptada':'rechazada',$quote]);
            $q=$this->db->prepare('UPDATE tbserviciosolicitudes SET estado=?,total=? WHERE id_solicitud=?');$q->execute([$accept==='aceptar'?'aceptada':'cancelada',$accept==='aceptar'?$c['total']:null,$s['id_solicitud']]);return $accept==='aceptar'?'Cliente aceptó la cotización por $'.$c['total'].'.':'Cliente rechazó la cotización y canceló la solicitud.';
        });
    }

    public function transition(int $actor,array $input):array
    {
        $target=self::text($input,'estado',30);$reason=self::text($input,'motivo',1000,3);
        $staff=self::staff($this->db,$actor);
        return $this->change($actor,$input,'estado',function($s,$user)use($target,$reason,$staff){
            $allowed=match($s['estado']){'nueva'=>['en_cotizacion','cancelada'],'en_cotizacion','cotizada','aceptada'=>['cancelada'],'en_proceso'=>['lista','cancelada'],'lista'=>['cancelada'],default=>[]};
            if($s['estado']==='aceptada')$allowed[]='en_proceso';
            if(!in_array($target,$allowed,true))throw new ServiceConflict('Cambio de estado no permitido.');
            if(!$staff && ($target!=='cancelada' || in_array($s['estado'],['en_proceso','lista'],true)))throw new DomainException('Solo puedes cancelar antes de iniciar la preparación.');
            if($target==='cancelada' && in_array($s['estado'],['en_proceso','lista'],true) && $user['nombre_rol']!=='Administrador')throw new DomainException('Solo el administrador puede cancelar un trabajo iniciado.');
            if($target==='en_proceso' && $s['material_fisico'] && !$s['material_recibido'])throw new ServiceConflict('Registra primero la recepción del material físico.');
            $q=$this->db->prepare('UPDATE tbserviciosolicitudes SET estado=? WHERE id_solicitud=?');$q->execute([$target,$s['id_solicitud']]);return self::STATES[$target].': '.$reason;
        },$staff);
    }

    public function material(int $actor,array $input):array
    {
        return $this->change($actor,$input,'material',function($s){if(!$s['material_fisico'] || !in_array($s['estado'],['nueva','en_cotizacion','cotizada','aceptada'],true) || $s['material_recibido'])throw new ServiceConflict('No hay material pendiente de recepción.');$q=$this->db->prepare('UPDATE tbserviciosolicitudes SET material_recibido=1 WHERE id_solicitud=?');$q->execute([$s['id_solicitud']]);return 'Originales recibidos en el local.';},true);
    }

    public function message(int $actor,array $input):array
    {
        $message=self::text($input,'mensaje',1800,3);$staff=self::staff($this->db,$actor);
        return $this->change($actor,$input,'mensaje',function($s)use($message){if(in_array($s['estado'],['entregada','cancelada'],true))throw new ServiceConflict('La solicitud está cerrada.');return $message;},$staff);
    }

    public function result(int $actor,array $input,array $files):array
    {
        if(!$files)throw new InvalidArgumentException('Adjunta el resultado del trabajo.');
        $input['archivos_huella']=hash('sha256',json_encode(array_map(static fn($f)=>[$f['nombre'],$f['sha256']],$files),JSON_THROW_ON_ERROR));
        return $this->change($actor,$input,'resultado',function($s)use($actor,$files){if(!in_array($s['estado'],['en_proceso','lista'],true))throw new ServiceConflict('Adjunta resultados durante la preparación o cuando el trabajo esté listo.');$q=$this->db->prepare("SELECT COUNT(*) FROM tbservicioarchivos WHERE id_solicitud=? AND tipo='resultado'");$q->execute([$s['id_solicitud']]);if((int)$q->fetchColumn()+count($files)>10)throw new InvalidArgumentException('Se permiten hasta 10 archivos de resultado por trabajo.');$this->attach((int)$s['id_solicitud'],$actor,$files,'resultado');return 'Archivos de resultado disponibles para el cliente.';},true);
    }

    public function pay(int $actor,array $input):array
    {
        $received=Cash::cents($input['recibido']??null);if(($input['confirmado']??'')!=='1')throw new InvalidArgumentException('Confirma el efectivo recibido y la entrega.');
        return $this->change($actor,$input,'cobro',function($s,$u)use($actor,$input,$received){
            if($s['estado']!=='lista' || $s['total']===null)throw new ServiceConflict('Solo se cobran servicios listos con cotización aceptada.');$total=Cash::cents($s['total']);if($received<$total || $received>999999999999)throw new InvalidArgumentException('Revisa el efectivo recibido; debe cubrir el total.');
            $q=$this->db->prepare("INSERT INTO tbventas(codigo_venta,id_solicitud_servicio,id_usuario,estado,total,fecha_pago,metodo_pago,recibido,cambio,cobro_solicitud,cobro_huella,responsable_cobro,created_at) VALUES (?,?,?,'pagada',?,UTC_TIMESTAMP(),'efectivo',?,?,?,?,?,UTC_TIMESTAMP())");$q->execute(['VT-'.(new DateTimeImmutable('now',new DateTimeZone('America/El_Salvador')))->format('ymd').'-'.strtoupper(bin2hex(random_bytes(6))),$s['id_solicitud'],$actor,$s['total'],Cash::decimal($received),Cash::decimal($received-$total),self::key($input),hash('sha256',json_encode([$actor,$s['id_solicitud'],$received],JSON_THROW_ON_ERROR)),$u['nombres'].' '.$u['apellidos']]);
            $q=$this->db->prepare("UPDATE tbserviciosolicitudes SET estado='entregada' WHERE id_solicitud=?");$q->execute([$s['id_solicitud']]);return 'Cobro de $'.$s['total'].' y entrega registrados. Cambio: $'.Cash::decimal($received-$total).'.';
        },true);
    }

    public function refund(int $actor,array $input):array
    {
        $reason=self::text($input,'motivo',500,3);if(($input['reembolsado']??'')!=='1')throw new InvalidArgumentException('Confirma el reembolso completo.');
        return $this->change($actor,$input,'reembolso',function($s,$u)use($actor,$input,$reason){
            $q=$this->db->prepare('SELECT * FROM tbventas WHERE id_solicitud_servicio=? FOR UPDATE');$q->execute([$s['id_solicitud']]);$sale=$q->fetch();if(!$sale || $sale['estado']!=='pagada')throw new ServiceConflict('No existe un cobro pendiente de reembolso.');
            $q=$this->db->prepare("UPDATE tbventas SET estado='anulada' WHERE id_venta=?");$q->execute([$sale['id_venta']]);$q=$this->db->prepare('INSERT INTO tbventasanulaciones(id_venta,id_usuario,responsable,reembolso,stock_devuelto,motivo,solicitud,huella,created_at) VALUES (?,?,?,?,0,?,?,?,UTC_TIMESTAMP())');$q->execute([$sale['id_venta'],$actor,$u['nombres'].' '.$u['apellidos'],$sale['total'],$reason,self::key($input),hash('sha256',json_encode([$actor,$sale['id_venta'],$reason],JSON_THROW_ON_ERROR))]);return 'Reembolso completo de $'.$sale['total'].': '.$reason;
        },true,true);
    }

    public function saveService(int $actor,array $input):void
    {
        $id=($input['id_servicio']??'')===''?null:self::integer($input['id_servicio']);$name=self::text($input,'nombre',120,2);$description=self::text($input,'descripcion',2000,3);$unit=self::text($input,'unidad',60,1);$time=self::text($input,'tiempo',150,3);$category=self::integer($input['id_categoria']??null);$state=self::text($input,'estado',10);$price=self::text($input,'precio_base',15);$physical=self::text($input,'material_fisico',1);
        if(!in_array($state,['activo','inactivo'],true) || !in_array($physical,['0','1'],true))throw new InvalidArgumentException('Estado o material inválidos.');$price=$price===''?null:Cash::decimal(Cash::cents($price));if($price!==null && Cash::cents($price)>999999999999)throw new InvalidArgumentException('Precio demasiado alto.');
        $this->transaction(function()use($actor,$id,$name,$description,$unit,$time,$category,$state,$price,$physical,$input){$this->actor($actor,false,true);$q=$this->db->prepare('SELECT id_categoria FROM tbserviciocategorias WHERE id_categoria=?');$q->execute([$category]);if(!$q->fetchColumn())throw new InvalidArgumentException('Categoría inválida.');if($id===null){$q=$this->db->prepare('INSERT INTO tbserviciocatalogo(nombre,descripcion,unidad,tiempo,id_categoria,estado,precio_base,material_fisico) VALUES (?,?,?,?,?,?,?,?)');$q->execute([$name,$description,$unit,$time,$category,$state,$price,$physical]);return;}$revision=self::text($input,'revision',10);if(!ctype_digit($revision))throw new InvalidArgumentException('Revisión inválida.');$q=$this->db->prepare('UPDATE tbserviciocatalogo SET nombre=?,descripcion=?,unidad=?,tiempo=?,id_categoria=?,estado=?,precio_base=?,material_fisico=?,revision=revision+1 WHERE id_servicio=? AND revision=?');$q->execute([$name,$description,$unit,$time,$category,$state,$price,$physical,$id,$revision]);if(!$q->rowCount())throw new ServiceConflict('El servicio cambió. Recarga para editarlo.');});
    }

    public function saveCategory(int $actor,array $input):void
    {
        $name=self::text($input,'nombre',100,2);$state=self::text($input,'estado',10);$id=($input['id_categoria']??'')===''?null:self::integer($input['id_categoria']);if(!in_array($state,['activo','inactivo'],true))throw new InvalidArgumentException('Estado inválido.');
        try{$this->transaction(function()use($actor,$name,$state,$id){$this->actor($actor,false,true);$q=$this->db->prepare($id===null?'INSERT INTO tbserviciocategorias(nombre,estado) VALUES (?,?)':'UPDATE tbserviciocategorias SET nombre=?,estado=? WHERE id_categoria=?');$q->execute($id===null?[$name,$state]:[$name,$state,$id]);});}catch(PDOException $e){if((int)($e->errorInfo[1]??0)===1062)throw new InvalidArgumentException('Ya existe una categoría con ese nombre.');throw $e;}
    }
}
