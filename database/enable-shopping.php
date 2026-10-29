<?php
declare(strict_types=1);
require_once __DIR__.'/cli.php';
require_once __DIR__.'/ShoppingUpgrade.php';
try {
    if(!in_array(DB_HOST,['127.0.0.1','localhost'],true) || !preg_match('/^[a-zA-Z0-9_]{1,64}$/D',DB_NAME)) throw new RuntimeException('Solo instalación local configurada.');
    $dsn='mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4';
    $attributes=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
    $runtime=new PDO($dsn,DB_USER,DB_PASSWORD,$attributes);$current=(string)$runtime->query('SELECT CURRENT_USER()')->fetchColumn();
    if(!preg_match('/^([a-zA-Z][a-zA-Z0-9_]{0,31})@(localhost|127\.0\.0\.1)$/D',$current,$match) || $match[1]!==DB_USER || in_array(strtolower(DB_USER),['root','mysql'],true)) throw new RuntimeException('Se requiere la cuenta limitada configurada.');
    if(in_array('--verify',$argv,true)) {
        $runtime->query('SELECT revision FROM tbcarritos LIMIT 0');$runtime->query('SELECT precio_visto FROM tbdetallecarritos LIMIT 0');$runtime->query('SELECT solicitud FROM tbshoppingoperaciones LIMIT 0');
        $query=$runtime->prepare("SELECT PRIVILEGE_TYPE FROM information_schema.TABLE_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=? AND TABLE_NAME='tbshoppingoperaciones'");$query->execute(["'".$match[1]."'@'".$match[2]."'",DB_NAME]);$rights=$query->fetchAll(PDO::FETCH_COLUMN);
        if(array_diff(['SELECT','INSERT'],$rights) || array_intersect(['UPDATE','DELETE'],$rights)) throw new RuntimeException('Permisos del diario inesperados.');
        require_once BASE_PATH.'/app/Models/ShoppingModel.php';
        $testUser=(int)$runtime->query("SELECT id_usuario FROM tbusuarios WHERE estado='activo' ORDER BY id_usuario LIMIT 1")->fetchColumn();
        if($testUser>0) {
            $state=(new ShoppingModel($runtime))->state($testUser);
            if($state['user_id']!==$testUser || !is_array($state['cart']) || !is_array($state['wishlist'])) throw new RuntimeException('No se pudo verificar la lectura del modelo.');
        }
        echo "Plan 5 habilitado: revisión, precio de referencia y diario con SELECT/INSERT sin UPDATE/DELETE.\n";exit;
    }
    if(!in_array('--apply',$argv,true)) {echo "Simulación: agrega revisión al carrito, precio visto a sus detalles y diario de solicitudes. Concede SELECT/INSERT solo del diario; no cambia cantidades, stock, usuarios ni claves.\n";exit;}
    [$user,$password]=migrationCredentials();$admin=new PDO($dsn,$user,$password,$attributes);
    $before=$admin->query('SELECT id_carrito,id_producto,cantidad FROM tbdetallecarritos ORDER BY id_carrito,id_producto')->fetchAll();
    ShoppingUpgrade::apply($admin);
    if($before!==$admin->query('SELECT id_carrito,id_producto,cantidad FROM tbdetallecarritos ORDER BY id_carrito,id_producto')->fetchAll()) throw new RuntimeException('El carrito cambió durante la actualización. Revisa antes de habilitar permisos.');
    $admin->exec('GRANT SELECT,INSERT ON `'.DB_NAME.'`.`tbshoppingoperaciones` TO '.$admin->quote($match[1]).'@'.$admin->quote($match[2]));
    echo "Plan 5 habilitado. Cantidades conservadas; sin cambios en existencias, cuentas ni contraseñas.\n";
} catch(Throwable $error) {fwrite(STDERR,"No se pudo habilitar/verificar el Plan 5. Revisa conexión, esquema y permisos locales.\n");if(!($error instanceof PDOException)) fwrite(STDERR,$error->getMessage()."\n");exit(1);}
