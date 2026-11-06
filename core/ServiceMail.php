<?php
declare(strict_types=1);
require_once BASE_PATH.'/vendor/autoload.php';
require_once BASE_PATH.'/core/MailTemplate.php';
require_once BASE_PATH.'/app/Models/ServiceModel.php';

final class ServiceMail
{
    public function __construct(private PDO $db,private ?Closure $transport=null) {}

    public function sendOne(?int $requestId=null):string
    {
        $config=new Environment(BASE_PATH.'/.env');
        if(!$this->transport && ($config->get('MAIL_ENABLED','0')!=='1' || $config->get('MAIL_USERNAME','')==='' || $config->get('MAIL_PASSWORD','')===''))return 'pending';
        $q=$this->db->prepare("SELECT * FROM tbserviciocorreos WHERE estado IN ('pendiente','procesando') AND proximo_intento<=UTC_TIMESTAMP()".($requestId===null?'':' AND id_solicitud=?').' ORDER BY '.($requestId===null?'id_correo':'revision DESC,id_correo').' LIMIT 1');$q->execute($requestId===null?[]:[$requestId]);$job=$q->fetch();if(!$job)return 'none';
        $token=bin2hex(random_bytes(32));$q=$this->db->prepare("UPDATE tbserviciocorreos SET estado='procesando',bloqueo=?,intentos=intentos+1,proximo_intento=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 5 MINUTE) WHERE id_correo=? AND estado IN ('pendiente','procesando') AND proximo_intento<=UTC_TIMESTAMP()");$q->execute([$token,$job['id_correo']]);if(!$q->rowCount())return 'busy';
        try{
            $request=(new ServiceModel($this->db))->find((int)$job['id_solicitud'],0,true);
            if(!$request || (int)$request['revision']!==(int)$job['revision'])$state='omitido';
            else{
                $base=normalize_app_url($config->get('ORDER_PUBLIC_URL',''));
                if(!preg_match('~^https?://~i',$base))throw new RuntimeException('Configura ORDER_PUBLIC_URL para los enlaces de servicios.');
                $url=$base.'/services/detail?id='.$request['id_solicitud'];$labels=['recibida'=>'Solicitud recibida','cotizacion'=>'Cotización disponible','aceptar'=>'Cotización aceptada','rechazar'=>'Solicitud cancelada','estado'=>ServiceModel::STATES[$request['estado']],'material'=>'Originales recibidos','resultado'=>'Resultado disponible','mensaje'=>'Mensaje sobre tu servicio','cobro'=>'Pago y entrega registrados','reembolso'=>'Reembolso registrado'];$title=$labels[$job['tipo']]??'Actualización de tu servicio';$mail=MailTemplate::mailer();$mail->addAddress($request['contacto_correo'],$request['contacto_nombre']);$mail->Subject=$title.' · '.$request['codigo'];$e=MailTemplate::escape(...);
                $body='<p>'.$e($request['nombre_servicio']).' · '.$e($request['codigo']).'</p><p>Estado: '.$e(ServiceModel::STATES[$request['estado']]).'.</p>';
                if($job['tipo']==='cotizacion' && isset($request['quotes'][0]))$body.='<p>Total propuesto: <strong>$'.$e($request['quotes'][0]['total']).'</strong>. Revisa las condiciones y acepta la cotización desde tu cuenta.</p>';
                if($request['estado']==='lista')$body.='<p>Tu trabajo está listo. Paga en efectivo al retirarlo en la tienda.</p>';
                $body.='<p><a href="'.$e($url).'">Consultar mi solicitud</a></p>';$mail->Body=MailTemplate::frame($mail,$title,'Papelería y servicios de Tecno Star',$body);$mail->AltBody=$title."\n".$request['codigo'].' · '.$request['nombre_servicio']."\nEstado: ".ServiceModel::STATES[$request['estado']]."\n".$url;$mail->MessageID='<servicio-'.$job['id_correo'].'@'.parse_url($base,PHP_URL_HOST).'>';
                if($this->transport)($this->transport)($mail);elseif(!$mail->send())throw new RuntimeException('Envío no confirmado.');$state='enviado';
            }
            $q=$this->db->prepare('UPDATE tbserviciocorreos SET estado=?,bloqueo=NULL WHERE id_correo=? AND bloqueo=?');$q->execute([$state,$job['id_correo'],$token]);return $state;
        }catch(Throwable){$q=$this->db->prepare("UPDATE tbserviciocorreos SET estado='pendiente',bloqueo=NULL,proximo_intento=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 15 MINUTE) WHERE id_correo=? AND bloqueo=?");$q->execute([$job['id_correo'],$token]);Logger::write('service.mail_failed','warning');return 'failed';}
    }
}
