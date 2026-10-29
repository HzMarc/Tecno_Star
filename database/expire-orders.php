<?php
declare(strict_types=1);
require_once __DIR__.'/cli.php';
require_once BASE_PATH.'/config/Database.php';
require_once BASE_PATH.'/app/Models/OrderModel.php';
if(!in_array('--run',$argv,true)) {echo "Uso: php database/expire-orders.php --run (libera hasta 1000 reservas vencidas).\n";exit;}
try {echo 'Reservas vencidas liberadas: '.(new OrderModel(Database::connection()))->expireDue(1000)."\n";}catch(Throwable $error){fwrite(STDERR,"No se pudieron liberar las reservas. Revisa el registro de errores.\n");exit(1);}
