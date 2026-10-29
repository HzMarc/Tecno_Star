<?php
declare(strict_types=1);
require_once BASE_PATH.'/vendor/autoload.php';
require_once BASE_PATH.'/app/Models/OrderModel.php';
require_once BASE_PATH.'/core/MailTemplate.php';

final class OrderMail
{
    public function __construct(private PDO $db,private ?Closure $transport=null) {}
    public static function baseUrl(): string
    {
        $value=normalize_app_url((new Environment(BASE_PATH.'/.env'))->get('ORDER_PUBLIC_URL',''));
        if(!preg_match('~^https?://~i',$value)) throw new RuntimeException('Configura ORDER_PUBLIC_URL con una URL completa accesible desde el lector.');
        return $value;
    }
    public static function url(string $code): string
    {
        if(!preg_match('/^TS-[0-9]{6}-[A-F0-9]{12}$/D',$code)) throw new InvalidArgumentException('Código de pedido inválido.');
        return self::baseUrl().'/orders/scan?codigo='.rawurlencode($code);
    }
    public static function qr(string $code): string
    {
        $qr=new Endroid\QrCode\QrCode(data:self::url($code),errorCorrectionLevel:Endroid\QrCode\ErrorCorrectionLevel::Medium,size:320,margin:16);
        return (new Endroid\QrCode\Writer\PngWriter())->write($qr)->getString();
    }
    public function message(array $order,string $type='confirmado'): PHPMailer\PHPMailer\PHPMailer
    {
        if(!in_array($type,['confirmado','listo'],true))throw new InvalidArgumentException('Tipo de aviso inválido.');
        $ready=$type==='listo';$mail=MailTemplate::mailer();$mail->addAddress($order['contacto_correo'],$order['contacto_nombre']);$mail->Subject=($ready?'Listo para retirar':'Pedido confirmado').' · '.$order['codigo_pedido'];
        $domain=(string)parse_url(self::baseUrl(),PHP_URL_HOST);$mail->MessageID='<pedido-'.$type.'-'.$order['codigo_pedido'].'@'.$domain.'>';
        // El QR se reserva para el aviso de "Listo para retirar".
        if($ready) $mail->addStringEmbeddedImage(self::qr($order['codigo_pedido']),'pedido-qr','pedido-'.$order['codigo_pedido'].'.png','base64','image/png');
        $e=MailTemplate::escape(...);$expires=(new DateTimeImmutable($order['fecha_vencimiento'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('America/El_Salvador'))->format('d/m/Y H:i');
        ob_start();require BASE_PATH.'/app/Views/mail/order.php';$content=(string)ob_get_clean();$mail->Body=MailTemplate::frame($mail,$mail->Subject,$ready?'Tu compra está lista. Retírala antes de que venza la reserva.':'La tienda aceptó tu pedido. Te avisaremos cuando esté listo.',$content);
        $plain='';foreach($order['items'] as $item)$plain.=$item['cantidad'].' × '.$item['nombre_producto'].' — $'.$item['subtotal']."\n";
        $mail->AltBody="Tecno Star\n".($ready?'Listo para retirar: ':'Pedido confirmado: ').$order['codigo_pedido']."\n".$plain.'Total a pagar en tienda: $'.$order['total']."\nReserva hasta $expires (El Salvador). ".($ready?'Ya puedes retirar tu compra. Presenta el código o QR al empleado.':'Espera el aviso de Listo para retirar antes de acudir.')."\n".self::url($order['codigo_pedido']);return $mail;
    }
    public function sendOne(?int $orderId=null,?string $type=null): string
    {
        $config=new Environment(BASE_PATH.'/.env');
        if(!$this->transport && ($config->get('MAIL_ENABLED','0')!=='1' || $config->get('MAIL_USERNAME','')==='' || $config->get('MAIL_PASSWORD','')==='')) return 'pending';
        (new OrderModel($this->db))->expireDue(1000);
        if($type!==null && !in_array($type,['confirmado','listo'],true))throw new InvalidArgumentException('Tipo de aviso inválido.');
        $events=OrderModel::readyMailEnabled($this->db);if(!$events && $type==='listo')return 'none';
        $sql="SELECT id_correo,id_pedido,".($events?'tipo':"'confirmado' AS tipo")." FROM tbretiocorreos WHERE estado IN ('pendiente','procesando') AND proximo_intento<=UTC_TIMESTAMP()".($orderId?' AND id_pedido=?':'').($events && $type!==null?' AND tipo=?':'').' ORDER BY id_correo LIMIT 1';
        $q=$this->db->prepare($sql);$params=$orderId?[$orderId]:[];if($events && $type!==null)$params[]=$type;$q->execute($params);$job=$q->fetch();if(!$job) return 'none';
        $token=bin2hex(random_bytes(32));
        $claim=$this->db->prepare("UPDATE tbretiocorreos SET estado='procesando',intentos=intentos+1,bloqueo=?,proximo_intento=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 5 MINUTE) WHERE id_correo=? AND estado IN ('pendiente','procesando') AND proximo_intento<=UTC_TIMESTAMP()");$claim->execute([$token,$job['id_correo']]);if($claim->rowCount()!==1) return 'busy';
        try {
            $order=(new OrderModel($this->db))->find((int)$job['id_pedido'],0,true);
            if(!$order || in_array($order['estado'],['cancelado','entregado'],true) || ($job['tipo']==='listo' && $order['estado']!=='listo') || ($job['tipo']==='confirmado' && $order['estado']==='listo') || new DateTimeImmutable($order['fecha_vencimiento'],new DateTimeZone('UTC'))<=new DateTimeImmutable('now',new DateTimeZone('UTC'))) $state='omitido';
            else {$mail=$this->message($order,$job['tipo']);if($this->transport) ($this->transport)($mail);else $mail->send();$state='enviado';}
            $q=$this->db->prepare("UPDATE tbretiocorreos SET estado=?,enviado_at=IF(?='enviado',UTC_TIMESTAMP(),NULL),bloqueo=NULL WHERE id_correo=? AND bloqueo=?");$q->execute([$state,$state,$job['id_correo'],$token]);return $state;
        }catch(Throwable $error){
            $q=$this->db->prepare("UPDATE tbretiocorreos SET estado='pendiente',bloqueo=NULL,proximo_intento=DATE_ADD(UTC_TIMESTAMP(),INTERVAL 5 MINUTE) WHERE id_correo=? AND bloqueo=?");$q->execute([$job['id_correo'],$token]);
            Logger::write('order.mail_failed','warning',null,['order_id'=>(int)$job['id_pedido']]);return 'pending';
        }
    }
}
