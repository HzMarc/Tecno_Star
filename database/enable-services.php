<?php
declare(strict_types=1);
require_once __DIR__.'/cli.php';require_once __DIR__.'/ServiceUpgrade.php';require_once BASE_PATH.'/config/Database.php';
try{
    $runtime=Database::connection();
    if(in_array('--verify',$argv,true)){ServiceUpgrade::verify($runtime);echo "Servicios disponibles para la cuenta actual.\n";exit;}
    if(!in_array('--apply',$argv,true)){echo "Simulación: crea el catálogo y solicitudes de servicios, añade su permiso independiente e integra ventas sin inventario. Crea un respaldo completo antes de cambiar el esquema. Usa --apply para habilitar.\n";exit;}
    if(!in_array(DB_HOST,['localhost','127.0.0.1'],true))throw new RuntimeException('La activación automática requiere una base local.');
    $current=(string)$runtime->query('SELECT CURRENT_USER()')->fetchColumn();if(!preg_match('/^([a-zA-Z][a-zA-Z0-9_]{0,31})@(localhost|127\.0\.0\.1)$/D',$current,$account) || $account[1]!==DB_USER || in_array(strtolower(DB_USER),['root','mysql'],true))throw new RuntimeException('Se requiere la cuenta limitada actual de la aplicación.');
    [$user,$password]=migrationCredentials();$db=new PDO('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4',$user,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
    $backup=BASE_PATH.'/storage/backups/services-'.gmdate('Ymd-His').'-'.bin2hex(random_bytes(4));if(!mkdir($backup,0700,true))throw new RuntimeException('No se pudo preparar el respaldo.');
    $process=proc_open(['C:/xampp/mysql/bin/mysqldump.exe','--no-defaults','--single-transaction','--skip-lock-tables','--hex-blob','--host='.DB_HOST,'--port='.DB_PORT,'--user='.$user,DB_NAME],[0=>['pipe','r'],1=>['file',$backup.'/database.sql','w'],2=>['file',$backup.'/dump-error.log','w']],$pipes,BASE_PATH,array_replace(getenv(),['MYSQL_PWD'=>$password]));if(!is_resource($process))throw new RuntimeException('No se pudo iniciar el respaldo.');fclose($pipes[0]);if(proc_close($process)!==0)throw new RuntimeException('Falló el respaldo. No se modificó el esquema.');
    $snapshot=static function(PDO $db):string{$rows=[];foreach(['tbusuarios'=>'id_usuario','tbpedidos'=>'id_pedido','tbventas'=>'id_venta','tbinventario'=>'id_producto','tbpersonalpermisos'=>'id_usuario'] as $table=>$key){$data=$db->query('SELECT * FROM '.$table.' ORDER BY '.$key)->fetchAll();if($table==='tbventas')foreach($data as &$row)unset($row['id_solicitud_servicio']);if($table==='tbpersonalpermisos')foreach($data as &$row)unset($row['servicios']);unset($row);$rows[]=$data;}return hash('sha256',json_encode($rows,JSON_THROW_ON_ERROR));};
    $before=$snapshot($db);ServiceUpgrade::apply($db);if($before!==$snapshot($db))throw new RuntimeException('Los datos existentes cambiaron durante la actualización. Revisa el respaldo.');
    foreach(ServiceUpgrade::PERMISSIONS as $table=>$permissions)$db->exec('GRANT '.$permissions.' ON `'.DB_NAME.'`.'.$table.' TO '.$db->quote($account[1]).'@'.$db->quote($account[2]));
    ServiceUpgrade::verify($runtime);echo "Servicios habilitados con respaldo completo y permisos SQL limitados. Cuentas, pedidos, ventas e inventario conservados; ningún empleado obtuvo acceso automáticamente.\n";
}catch(Throwable $error){fwrite(STDERR,"No se pudo habilitar servicios.\n".($error instanceof PDOException?'SQLSTATE '.$error->getCode():$error->getMessage())."\n");exit(1);}
