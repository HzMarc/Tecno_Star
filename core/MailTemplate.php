<?php
declare(strict_types=1);
require_once BASE_PATH.'/vendor/autoload.php';
final class MailTemplate
{
    public static function escape(mixed $value):string{return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
    public static function mailer():PHPMailer\PHPMailer\PHPMailer
    {
        $config=new Environment(BASE_PATH.'/.env');$mail=new PHPMailer\PHPMailer\PHPMailer(true);$mail->CharSet='UTF-8';$mail->isSMTP();$mail->Host='smtp.gmail.com';$mail->Port=587;$mail->SMTPAuth=true;$mail->SMTPSecure=PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;$mail->Username=$config->get('MAIL_USERNAME','');$mail->Password=$config->get('MAIL_PASSWORD','');$mail->Timeout=15;$mail->setFrom($mail->Username,$config->get('MAIL_FROM_NAME','Tecno Star'));return $mail;
    }
    public static function frame(PHPMailer\PHPMailer\PHPMailer $mail,string $title,string $preview,string $content):string
    {
        $mail->isHTML(true);$mail->addEmbeddedImage(BASE_PATH.'/public/assets/images/logo-estrella.png','tecno-star-logo','tecno-star-logo.png','base64','image/png');
        $e=self::escape(...);ob_start();require BASE_PATH.'/app/Views/mail/layout.php';return (string)ob_get_clean();
    }
    public static function button(string $url,string $label):string
    {
        return '<table role="presentation" cellspacing="0" cellpadding="0" style="margin:24px auto"><tr><td bgcolor="#2054dd" style="border-radius:10px"><a href="'.self::escape($url).'" style="display:inline-block;padding:15px 26px;color:#ffffff;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;text-decoration:none">'.self::escape($label).'</a></td></tr></table>';
    }
}
