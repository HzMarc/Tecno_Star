<?php
declare(strict_types=1);
require_once __DIR__ . '/cli.php';
require_once __DIR__ . '/MigrationRunner.php';
require_once __DIR__ . '/InventoryUpgrade.php';

try {
    if (!in_array(DB_HOST,['localhost','127.0.0.1'],true) || !preg_match('/^[a-zA-Z0-9_]{1,64}$/D', DB_NAME)) throw new RuntimeException();
    $dsn='mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4';
    $runtime=new PDO($dsn,DB_USER,DB_PASSWORD,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $current=(string)$runtime->query('SELECT CURRENT_USER()')->fetchColumn();
    if (!preg_match('/^([a-zA-Z][a-zA-Z0-9_]{0,31})@(localhost|127\.0\.0\.1)$/D',$current,$match) || $match[1]!==DB_USER || in_array(strtolower(DB_USER),['root','mysql'],true)) throw new RuntimeException();
    if (in_array('--verify',$argv,true)) {
        $runtime->query('SELECT id_movimiento FROM tbstockmovimientos LIMIT 0');
        $grantee="'".$match[1]."'@'".$match[2]."'";
        $query=$runtime->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMN_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=? AND TABLE_NAME='tbinventario' AND PRIVILEGE_TYPE='UPDATE' ORDER BY COLUMN_NAME");
        $query->execute([$grantee,DB_NAME]);
        $columns=$query->fetchAll(PDO::FETCH_COLUMN);
        if($columns!==['stock_actual','stock_minimo'] && $columns!==['stock_actual','stock_minimo','stock_reservado']) throw new RuntimeException();
        $query=$runtime->prepare("SELECT PRIVILEGE_TYPE FROM information_schema.TABLE_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=? AND TABLE_NAME=?");
        $query->execute([$grantee,DB_NAME,'tbinventario']);
        if (array_intersect(['UPDATE','DELETE','INSERT'],$query->fetchAll(PDO::FETCH_COLUMN))) throw new RuntimeException();
        $query->execute([$grantee,DB_NAME,'tbstockmovimientos']); $rights=$query->fetchAll(PDO::FETCH_COLUMN);
        if (array_diff(['SELECT','INSERT'],$rights) || array_intersect(['UPDATE','DELETE'],$rights)) throw new RuntimeException();
        echo "Inventario habilitado: permisos por columna e historial con SELECT/INSERT, sin borrar movimientos.\n";
        exit;
    }
    if (!in_array('--apply',$argv,true)) {
        echo "Simulación: migración 003 crea historial y conserva saldos existentes. Cuenta actual: UPDATE solo stock_actual/stock_minimo e INSERT/SELECT de movimientos. Sin borrar datos, cambiar reservas ni contraseñas.\n";
        exit;
    }
    [$user,$password]=migrationCredentials();
    $admin=new PDO($dsn,$user,$password,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $admin->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
    $snapshot=static function(PDO $db):array {
        return array_map(static function(array $row):array { foreach(['stock_actual','stock_reservado','stock_minimo'] as $key) $row[$key]=number_format((float)$row[$key],3,'.',''); return $row; },$db->query('SELECT id_producto,stock_actual,stock_reservado,stock_minimo FROM tbinventario ORDER BY id_producto')->fetchAll());
    };
    $before=$snapshot($admin);
    $tables=$admin->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $legacyCount=in_array('tbmovimientosinventario',$tables,true)?(int)$admin->query('SELECT COUNT(*) FROM tbmovimientosinventario')->fetchColumn():null;
    InventoryUpgrade::apply($admin);
    if($snapshot($admin)!==$before || ($legacyCount!==null && (int)$admin->query('SELECT COUNT(*) FROM tbmovimientosinventario')->fetchColumn()!==$legacyCount)) throw new RuntimeException('Los saldos cambiaron durante la actualización. Requiere revisión antes de habilitar permisos.');
    $account=$admin->quote($match[1]).'@'.$admin->quote($match[2]);
    $admin->exec('GRANT UPDATE (stock_actual,stock_minimo) ON `'.DB_NAME.'`.`tbinventario` TO '.$account);
    $admin->exec('GRANT SELECT,INSERT ON `'.DB_NAME.'`.`tbstockmovimientos` TO '.$account);
    echo 'Historial y permisos de inventario habilitados. Verificados '.count($before)." saldos conservados y el historial anterior intacto.\n";
} catch(Throwable $error) {
    fwrite(STDERR,"No se pudo habilitar/verificar inventario. Revisa conexión, migraciones y permisos administrativos locales. No se muestran credenciales.\n");
    if(!($error instanceof PDOException)) fwrite(STDERR,$error->getMessage()."\n");
    exit(1);
}
