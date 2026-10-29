<?php
declare(strict_types=1);
require_once __DIR__.'/cli.php';
require_once __DIR__.'/OrderUpgrade.php';
try {
    if(!in_array(DB_HOST,['127.0.0.1','localhost'],true) || !preg_match('/^[a-zA-Z0-9_]{1,64}$/D',DB_NAME)) throw new RuntimeException('Solo instalación local configurada.');
    $dsn='mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4';
    $attributes=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
    $runtime=new PDO($dsn,DB_USER,DB_PASSWORD,$attributes);$current=(string)$runtime->query('SELECT CURRENT_USER()')->fetchColumn();
    if(!preg_match('/^([a-zA-Z][a-zA-Z0-9_]{0,31})@(localhost|127\.0\.0\.1)$/D',$current,$match) || $match[1]!==DB_USER || in_array(strtolower(DB_USER),['root','mysql'],true)) throw new RuntimeException('Se requiere la cuenta limitada configurada.');
    if(in_array('--verify',$argv,true)) {
        $runtime->query('SELECT retiro_solicitud,reserva_activa FROM tbpedidos LIMIT 0');$runtime->query('SELECT id_detalle FROM tbretirodetalles LIMIT 0');$runtime->query('SELECT id_historial FROM tbretirohistorial LIMIT 0');
        $grantee="'".$match[1]."'@'".$match[2]."'";
        foreach(['tbpedidos'=>['SELECT','INSERT','UPDATE'],'tbretirodetalles'=>['SELECT','INSERT'],'tbretirohistorial'=>['SELECT','INSERT'],'tbretiocorreos'=>['SELECT','INSERT','UPDATE']] as $table=>$required) {
            $q=$runtime->prepare('SELECT PRIVILEGE_TYPE FROM information_schema.TABLE_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=? AND TABLE_NAME=?');$q->execute([$grantee,DB_NAME,$table]);$rights=$q->fetchAll(PDO::FETCH_COLUMN);
            if(array_diff($required,$rights) || array_diff($rights,$required)) throw new RuntimeException('Permisos de pedidos inesperados.');
        }
        $q=$runtime->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMN_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=? AND TABLE_NAME='tbinventario' AND PRIVILEGE_TYPE='UPDATE'");$q->execute([$grantee,DB_NAME]);
        if(!in_array('stock_reservado',$q->fetchAll(PDO::FETCH_COLUMN),true)) throw new RuntimeException('Falta permiso de reserva.');
        echo "Plan 6 habilitado: pedidos, detalle e historial y reserva de stock.\n";exit;
    }
    if(!in_array('--apply',$argv,true)) {echo "Simulación: agrega datos de retiro, detalle e historial y amplía estados conservando pedidos y saldos. Concede INSERT/UPDATE de pedidos, SELECT/INSERT de detalle e historial y UPDATE solo de stock_reservado. No borra historial ni cambia usuarios o claves.\n";exit;}
    [$user,$password]=migrationCredentials();$admin=new PDO($dsn,$user,$password,$attributes);
    $before=$admin->query('SELECT id_producto,stock_actual,stock_reservado FROM tbinventario ORDER BY id_producto')->fetchAll();
    OrderUpgrade::apply($admin);
    if($before!==$admin->query('SELECT id_producto,stock_actual,stock_reservado FROM tbinventario ORDER BY id_producto')->fetchAll()) throw new RuntimeException('El inventario cambió durante la actualización. Revisa antes de conceder permisos.');
    $account=$admin->quote($match[1]).'@'.$admin->quote($match[2]);$schema='`'.DB_NAME.'`.';
    $admin->exec('GRANT INSERT,UPDATE ON '.$schema.'`tbpedidos` TO '.$account);
    foreach(['tbretirodetalles','tbretirohistorial'] as $table) $admin->exec('GRANT SELECT,INSERT ON '.$schema.'`'.$table.'` TO '.$account);
    $admin->exec('GRANT SELECT,INSERT,UPDATE ON '.$schema.'`tbretiocorreos` TO '.$account);
    $admin->exec('GRANT UPDATE (stock_reservado) ON '.$schema.'`tbinventario` TO '.$account);
    echo "Plan 6 activado. Pedidos anteriores y saldos conservados.\n";
} catch(Throwable $error) {fwrite(STDERR,"No se pudo habilitar/verificar el Plan 6. Revisa esquema, conexión y permisos.\n");if(!($error instanceof PDOException)) fwrite(STDERR,$error->getMessage()."\n");exit(1);}
