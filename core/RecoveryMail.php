<?php
declare(strict_types=1);
require_once BASE_PATH.'/core/OrderMail.php';
final class RecoveryMail
{
    public function __construct(private ?Closure $transport=null){}
    public function message(array $reset):PHPMailer\PHPMailer\PHPMailer
    {
        $mail=MailTemplate::mailer();$mail->addAddress($reset['correo']);$mail->Subject='Cambia tu contraseña · Tecno Star';$url=OrderMail::baseUrl().'/reset-password#token='.$reset['token'];
        $content='<p style="margin:0 0 12px;color:#2054dd;font-size:12px;font-weight:bold;letter-spacing:1px">SEGURIDAD DE TU CUENTA</p><h1 style="margin:0 0 20px;font-size:27px;line-height:1.3;color:#142033">Cambia tu contraseña</h1><p>Recibimos una solicitud para cambiar tu contraseña de Tecno Star.</p><p>Este enlace estará disponible durante <strong>20 minutos</strong> y solo se puede utilizar una vez.</p>'.MailTemplate::button($url,'Cambiar mi contraseña').'<p style="background:#f8faff;padding:16px;border-radius:10px;color:#64748b;font-size:13px">Si no solicitaste este cambio, ignora este correo. Tu contraseña actual seguirá funcionando.</p>';
        $mail->Body=MailTemplate::frame($mail,$mail->Subject,'Usa este enlace de un solo uso para recuperar tu cuenta.',$content);$mail->AltBody="Recibimos una solicitud para cambiar tu contraseña de Tecno Star.\n\nAbre este enlace durante los próximos 20 minutos:\n$url\n\nSolo puede usarse una vez. Si no lo solicitaste, ignora este mensaje.";return $mail;
    }
    public function send(array $reset):void
    {
        if(!$this->transport && (new Environment(BASE_PATH.'/.env'))->get('MAIL_ENABLED','0')!=='1')throw new RuntimeException('Correo desactivado.');$mail=$this->message($reset);if($this->transport)($this->transport)($mail);else $mail->send();
    }
}
