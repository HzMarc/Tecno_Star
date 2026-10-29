<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/database/cli.php';
require_once BASE_PATH.'/database/ShoppingUpgrade.php';
require_once BASE_PATH.'/database/seeders/demo.php';
require_once BASE_PATH.'/app/Models/UserModel.php';
$temp=sys_get_temp_dir().'/tecno_shopping_upgrade_'.bin2hex(random_bytes(8));
try {
    $options=migrationOptions();if(!preg_match('/^tecno_star_test_[a-zA-Z0-9_]{1,25}$/D',$options['database'])) throw new RuntimeException('Solo una base nueva de prueba.');
    $server=migrationConnection(array_replace($options,['database'=>'information_schema']));$find=$server->prepare('SELECT COUNT(*) FROM SCHEMATA WHERE SCHEMA_NAME=?');
    foreach(['','_legacy'] as $suffix){$find->execute([$options['database'].$suffix]);if((int)$find->fetchColumn()!==0) throw new RuntimeException('Destino existente.');}
    mkdir($temp);foreach(glob(BASE_PATH.'/database/migrations/00[1-3]_*.sql') as $file) copy($file,$temp.'/'.basename($file));
    foreach(['','_legacy'] as $suffix) {
        $db=migrationConnection(array_replace($options,['database'=>$options['database'].$suffix,'create'=>true]),true);
        (new MigrationRunner($db,$temp))->migrate();seedDemo($db);
        $user=(new UserModel($db))->createCustomer('upgrade','Prueba','Plan5','70000000','upgrade@example.test',password_hash('TestOnly2026!',PASSWORD_DEFAULT));
        $id=(int)$db->query('SELECT id_usuario FROM tbusuarios LIMIT 1')->fetchColumn();$product=(int)$db->query('SELECT id_producto FROM tbproductos LIMIT 1')->fetchColumn();
        $db->exec('INSERT INTO tbcarritos(id_usuario) VALUES ('.$id.')');$cart=(int)$db->lastInsertId();$db->exec('INSERT INTO tbdetallecarritos(id_carrito,id_producto,cantidad) VALUES ('.$cart.','.$product.',3)');
        if($suffix!=='') {$db->exec('DROP TABLE schema_migrations');$db->exec('ALTER TABLE tbdetallecarritos MODIFY cantidad DECIMAL(12,3) NOT NULL');}
        $before=$db->query('SELECT id_carrito,id_producto,cantidad FROM tbdetallecarritos')->fetchAll();$stock=$db->query('SELECT * FROM tbinventario ORDER BY id_producto')->fetchAll();
        ShoppingUpgrade::apply($db);ShoppingUpgrade::apply($db);
        if($before!==$db->query('SELECT id_carrito,id_producto,cantidad FROM tbdetallecarritos')->fetchAll() || $stock!==$db->query('SELECT * FROM tbinventario ORDER BY id_producto')->fetchAll() || (int)$db->query('SELECT COUNT(*) FROM tbshoppingoperaciones')->fetchColumn()!==0 || $db->query('SELECT precio_visto FROM tbdetallecarritos')->fetchColumn()===null) throw new RuntimeException('La actualización alteró cantidades o no preparó los precios.');
        echo 'OK: Plan 5 actualizado y repetido sin cambiar carrito, stock ni duplicar datos ('.($suffix?'antiguo':'canónico').").\n";
    }
} catch(Throwable $error){fwrite(STDERR,$error->getMessage()."\n");exit(1);}
finally{if(is_dir($temp)){foreach(glob($temp.'/*.sql') as $file) unlink($file);rmdir($temp);}}
