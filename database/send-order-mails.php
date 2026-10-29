<?php
declare(strict_types=1);
require_once __DIR__.'/cli.php';require_once BASE_PATH.'/config/Database.php';require_once BASE_PATH.'/core/OrderMail.php';
if(!in_array('--run',$argv,true)){echo "Uso: php database/send-order-mails.php --run (hasta 50 correos pendientes).\n";exit;}
try{$sent=0;$mail=new OrderMail(Database::connection());for($i=0;$i<50;$i++){$result=$mail->sendOne();if(in_array($result,['none','busy','pending'],true)) break;if($result==='enviado')$sent++;}echo "Correos enviados: $sent. Los fallidos quedan pendientes de reintento.\n";}catch(Throwable){fwrite(STDERR,"No se pudieron procesar los correos. Revisa configuración y registros.\n");exit(1);}
