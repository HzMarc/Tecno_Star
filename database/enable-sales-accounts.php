<?php
declare(strict_types=1);
require_once __DIR__.'/cli.php';require_once __DIR__.'/SalesAccountsUpgrade.php';
try{
    if(!in_array(DB_HOST,['localhost','127.0.0.1'],true) || !preg_match('/^[a-zA-Z0-9_]{1,64}$/D',DB_NAME))throw new RuntimeException('Solo instalación local.');
    $dsn='mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4';$attributes=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];$runtime=new PDO($dsn,DB_USER,DB_PASSWORD,$attributes);$current=(string)$runtime->query('SELECT CURRENT_USER()')->fetchColumn();
    if(!preg_match('/^([a-zA-Z][a-zA-Z0-9_]{0,31})@(localhost|127\.0\.0\.1)$/D',$current,$match) || $match[1]!==DB_USER || in_array(strtolower(DB_USER),['root','mysql'],true))throw new RuntimeException('Se requiere la cuenta SQL limitada actual.');
    $updates=['tbusuarios'=>['nombres','apellidos','id_rol','estado','password_hash','session_version','cuenta_revision'],'tbclientes'=>['nombres','apellidos','telefono'],'tbventas'=>['estado']];
    $tables=['tbventasanulaciones'=>['SELECT','INSERT'],'tbcuentashistorial'=>['SELECT','INSERT'],'tbclaverecuperacion'=>['SELECT','INSERT','UPDATE'],'tbpersonalpermisos'=>['SELECT','INSERT','UPDATE']];
    if(in_array('--verify',$argv,true)){
        $runtime->query('SELECT cobro_solicitud FROM tbventas LIMIT 0');$runtime->query('SELECT session_version,cuenta_revision FROM tbusuarios LIMIT 0');$grantee="'".$match[1]."'@'".$match[2]."'";
        foreach($updates as $table=>$expected){$q=$runtime->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMN_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=? AND TABLE_NAME=? AND PRIVILEGE_TYPE='UPDATE'");$q->execute([$grantee,DB_NAME,$table]);$actual=$q->fetchAll(PDO::FETCH_COLUMN);sort($expected);sort($actual);if($actual!==$expected)throw new RuntimeException('Permisos por columna inesperados.');}
        foreach($tables as $table=>$expected){$runtime->query('SELECT 1 FROM `'.$table.'` LIMIT 0');$q=$runtime->prepare('SELECT PRIVILEGE_TYPE FROM information_schema.TABLE_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=? AND TABLE_NAME=?');$q->execute([$grantee,DB_NAME,$table]);$actual=$q->fetchAll(PDO::FETCH_COLUMN);sort($actual);sort($expected);if($actual!==$expected)throw new RuntimeException('Permisos de historial o recuperación inesperados.');}
        echo "Plan 7 habilitado: caja, anulaciones, cuentas, permisos y recuperación.\n";exit;
    }
    if(!in_array('--apply',$argv,true)){echo "Simulación: prepara ventas en efectivo y anulaciones, historial de cuentas, permisos de empleados, sesiones y recuperación. Concede escritura por columna de cuentas/clientes/estado de venta y acceso específico a las tablas nuevas. No modifica cuentas, claves, roles asignados, ventas ni saldos existentes; no concede DELETE.\n";exit;}
    [$name,$password]=migrationCredentials();$admin=new PDO($dsn,$name,$password,$attributes);
    $snap=static fn(PDO $db)=>hash('sha256',json_encode([$db->query('SELECT id_usuario,id_rol,estado,password_hash FROM tbusuarios ORDER BY id_usuario')->fetchAll(),$db->query('SELECT id_producto,stock_actual,stock_reservado,stock_minimo FROM tbinventario ORDER BY id_producto')->fetchAll(),$db->query('SELECT id_venta,id_pedido,total,estado FROM tbventas ORDER BY id_venta')->fetchAll()],JSON_THROW_ON_ERROR));
    $before=$snap($admin);SalesAccountsUpgrade::apply($admin);if($before!==$snap($admin))throw new RuntimeException('Los datos cambiaron durante la migración; revisa antes de conceder permisos.');
    $account=$admin->quote($match[1]).'@'.$admin->quote($match[2]);$schema='`'.DB_NAME.'`.';
    foreach($updates as $table=>$columns)$admin->exec('GRANT UPDATE ('.implode(',',$columns).') ON '.$schema.'`'.$table.'` TO '.$account);
    $admin->exec('GRANT INSERT ON '.$schema.'`tbventas` TO '.$account);
    foreach($tables as $table=>$rights)$admin->exec('GRANT '.implode(',',$rights).' ON '.$schema.'`'.$table.'` TO '.$account);
    echo "Plan 7 activado; cuentas, claves, roles asignados, ventas y saldos anteriores conservados.\n";
}catch(Throwable $error){fwrite(STDERR,"No se pudo habilitar/verificar el Plan 7. Revisa conexión, esquema y permisos.\n");if($error instanceof PDOException)fwrite(STDERR,'SQLSTATE '.$error->getCode().'; código del controlador '.(int)($error->errorInfo[1]??0).".\n");else fwrite(STDERR,$error->getMessage()."\n");exit(1);}
