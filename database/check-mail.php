<?php
declare(strict_types=1);
require_once __DIR__.'/cli.php';require_once BASE_PATH.'/vendor/autoload.php';require_once BASE_PATH.'/core/OrderMail.php';
try {
    $settings=new Environment(BASE_PATH.'/.env');
    $enabled=$settings->get('MAIL_ENABLED','0')==='1';$username=$settings->get('MAIL_USERNAME','');$password=$settings->get('MAIL_PASSWORD','');
    echo 'Correo automático: '.($enabled?'activado':'desactivado')."\n";
    if(!filter_var($username,FILTER_VALIDATE_EMAIL) || $password==='')throw new RuntimeException('Falta remitente válido o contraseña de aplicación.');
    echo "Remitente y contraseña configurados (valores ocultos).\n";
    $url=OrderMail::baseUrl();echo 'Enlace QR: '.(in_array(parse_url($url,PHP_URL_HOST),['localhost','127.0.0.1'],true)?'local a esta computadora; para otros dispositivos configura la IP o dominio.':'URL de red configurada.')."\n";
    if(!extension_loaded('gd'))throw new RuntimeException('Falta GD para el QR PNG.');
    if(!in_array('--connect',$argv,true)){echo "Agrega --connect para comprobar TLS y autenticación SMTP sin enviar mensajes.\n";exit;}
    $mail=new PHPMailer\PHPMailer\PHPMailer(true);$mail->isSMTP();$mail->Host='smtp.gmail.com';$mail->Port=587;$mail->SMTPAuth=true;$mail->SMTPSecure=PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;$mail->Username=$username;$mail->Password=$password;$mail->Timeout=15;
    try {if(!$mail->smtpConnect())throw new RuntimeException('SMTP no confirmó la conexión.');echo "SMTP: conexión TLS y autenticación correctas. No se envió ningún correo.\n";}finally{$mail->smtpClose();}
}catch(Throwable $error){fwrite(STDERR,"Correo no verificado: revisa contraseña de aplicación, verificación en dos pasos, certificados y acceso al puerto 587. No se muestran claves ni respuestas privadas del servidor.\n");exit(1);}
