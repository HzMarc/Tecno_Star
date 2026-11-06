<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/database/cli.php';require_once BASE_PATH.'/database/MigrationRunner.php';require_once BASE_PATH.'/database/ServiceUpgrade.php';
$temp=sys_get_temp_dir().'/tecno_services_upgrade_'.bin2hex(random_bytes(8));
try{
    $options=migrationOptions();if(!preg_match('/^tecno_star_test_services_[a-zA-Z0-9_]{1,16}$/D',$options['database']))throw new RuntimeException('Usa una base nueva con prefijo tecno_star_test_services_.');
    $server=migrationConnection(array_replace($options,['database'=>'information_schema']));$find=$server->prepare('SELECT COUNT(*) FROM SCHEMATA WHERE SCHEMA_NAME=?');foreach(['','_legacy'] as $suffix){$find->execute([$options['database'].$suffix]);if($find->fetchColumn())throw new RuntimeException('La base ya existe. Usa otro nombre.');}
    mkdir($temp,0700);foreach(glob(BASE_PATH.'/database/migrations/00*.sql') as $file)copy($file,$temp.'/'.basename($file));
    foreach(['','_legacy'] as $suffix){
        $db=migrationConnection(array_replace($options,['database'=>$options['database'].$suffix,'create'=>true]),true);(new MigrationRunner($db,$temp))->migrate();
        $db->exec("INSERT INTO tbusuarios(id_rol,nombre_usuario,nombres,apellidos,correo,password_hash) SELECT id_rol,'service-upgrade','Cliente','Previo','service-upgrade@example.test','hash-de-prueba-no-utilizable' FROM tbroles WHERE nombre_rol='Cliente'");$user=(int)$db->lastInsertId();
        $db->exec("INSERT INTO tbpedidos(id_usuario,estado,total) VALUES ($user,'entregado',12.34)");$order=(int)$db->lastInsertId();$db->exec("INSERT INTO tbventas(id_pedido,total,estado) VALUES ($order,12.34,'pagada')");
        $db->exec("CREATE TABLE tbservicios(id_servicio INT UNSIGNED PRIMARY KEY,nombre_servicio VARCHAR(100) NOT NULL,precio DECIMAL(12,2) NOT NULL) ENGINE=InnoDB");
        $db->exec("INSERT INTO tbservicios VALUES (1,'Servicio heredado',3.50)");
        $db->exec('CREATE TABLE tbdetalleventaservicio(id_detalle INT PRIMARY KEY,id_servicio INT UNSIGNED NOT NULL,FOREIGN KEY(id_servicio) REFERENCES tbservicios(id_servicio)) ENGINE=InnoDB');
        $db->exec('INSERT INTO tbdetalleventaservicio VALUES (1,1)');
        $users=$db->query('SELECT * FROM tbusuarios')->fetchAll();$orders=$db->query('SELECT * FROM tbpedidos')->fetchAll();$sales=$db->query('SELECT * FROM tbventas')->fetchAll();if($suffix)$db->exec('DROP TABLE schema_migrations');
        ServiceUpgrade::apply($db);ServiceUpgrade::apply($db);ServiceUpgrade::verify($db);$after=$db->query('SELECT * FROM tbventas')->fetchAll();foreach($after as &$row)unset($row['id_solicitud_servicio']);unset($row);
        if($users!==$db->query('SELECT * FROM tbusuarios')->fetchAll() || $orders!==$db->query('SELECT * FROM tbpedidos')->fetchAll() || $sales!==$after)throw new RuntimeException('Se modificaron datos previos.');
        if((int)$db->query('SELECT COUNT(*) FROM tbserviciocatalogo')->fetchColumn()!==8)throw new RuntimeException('Catálogo inicial incorrecto.');
        if($db->query('SELECT nombre_servicio FROM tbservicios WHERE id_servicio=1')->fetchColumn()!=='Servicio heredado' || (int)$db->query('SELECT COUNT(*) FROM tbdetalleventaservicio d JOIN tbservicios s ON s.id_servicio=d.id_servicio')->fetchColumn()!==1)throw new RuntimeException('Se modificaron servicios heredados o sus referencias.');
        try{$db->exec('INSERT INTO tbventas(total) VALUES (1)');throw new RuntimeException('Se permitió una venta sin origen.');}catch(PDOException){}
        echo 'OK: actualización '.($suffix?'sin historial global':'con historial global')." repetible, sin modificar cuentas, pedidos ni ventas; origen obligatorio.\n";
    }
}catch(Throwable $e){fwrite(STDERR,$e->getMessage()."\n");exit(1);}finally{if(is_dir($temp)){foreach(glob($temp.'/*.sql') as $f)unlink($f);rmdir($temp);}}
