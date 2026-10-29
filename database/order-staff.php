<?php
declare(strict_types=1);
require_once __DIR__.'/cli.php';require_once BASE_PATH.'/config/Database.php';
if(!in_array('--list',$argv,true)){echo "Uso: php database/order-staff.php --list. Consulta empleados activos; autoriza sus IDs en ORDER_STAFF_USER_IDS de .env. No cambia cuentas ni roles.\n";exit;}
try{foreach(Database::connection()->query("SELECT u.id_usuario,u.nombres,u.apellidos FROM tbusuarios u JOIN tbroles r ON r.id_rol=u.id_rol WHERE u.estado='activo' AND r.nombre_rol='Empleado' ORDER BY u.id_usuario") as $user)echo $user['id_usuario'].' · '.$user['nombres'].' '.$user['apellidos']."\n";}catch(Throwable){fwrite(STDERR,"No se pudieron consultar los empleados.\n");exit(1);}
