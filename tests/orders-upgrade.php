<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/database/cli.php';require_once BASE_PATH.'/database/OrderUpgrade.php';require_once BASE_PATH.'/database/seeders/demo.php';require_once BASE_PATH.'/app/Models/UserModel.php';
require_once BASE_PATH.'/app/Models/ShoppingModel.php';require_once BASE_PATH.'/app/Models/OrderModel.php';
$temp=sys_get_temp_dir().'/tecno_order_upgrade_'.bin2hex(random_bytes(8));
try {
    $options=migrationOptions();if(!preg_match('/^tecno_star_test_[a-zA-Z0-9_]{1,25}$/D',$options['database']))throw new RuntimeException('Solo una base nueva de pruebas.');
    $server=migrationConnection(array_replace($options,['database'=>'information_schema']));$find=$server->prepare('SELECT COUNT(*) FROM SCHEMATA WHERE SCHEMA_NAME=?');
    foreach(['','_legacy'] as $suffix){$find->execute([$options['database'].$suffix]);if((int)$find->fetchColumn()!==0)throw new RuntimeException('Destino existente.');}
    mkdir($temp);foreach(glob(BASE_PATH.'/database/migrations/00[1-4]_*.sql') as $file)copy($file,$temp.'/'.basename($file));
    foreach(['','_legacy'] as $suffix){
        $db=migrationConnection(array_replace($options,['database'=>$options['database'].$suffix,'create'=>true]),true);(new MigrationRunner($db,$temp))->migrate();seedDemo($db);
        (new UserModel($db))->createCustomer('upgrade','Pedido','Anterior','70000000','upgrade@orders.example.test',password_hash('TestOnly2026!',PASSWORD_DEFAULT));
        $uid=(int)$db->query('SELECT id_usuario FROM tbusuarios LIMIT 1')->fetchColumn();$cid=(int)$db->query('SELECT id_cliente FROM tbclientes LIMIT 1')->fetchColumn();
        if($suffix){
            $db->exec('DROP TABLE schema_migrations');
            $db->exec('ALTER TABLE tbdetallecarritos MODIFY cantidad DECIMAL(12,3) NOT NULL');
            $constraint=$db->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbpedidos' AND REFERENCED_TABLE_NAME='tbusuarios'")->fetchColumn();
            $db->exec('ALTER TABLE tbpedidos DROP FOREIGN KEY `'.$constraint.'`, DROP COLUMN id_usuario');
            $db->exec("ALTER TABLE tbpedidos ADD codigo_pedido VARCHAR(30) NOT NULL, ADD id_cliente INT UNSIGNED NOT NULL, ADD fecha_confirmacion DATETIME NULL, ADD fecha_vencimiento DATETIME NULL, ADD observaciones TEXT NULL, MODIFY estado ENUM('pendiente','confirmado','rechazado','cancelado','vencido','pagado','entregado') NOT NULL DEFAULT 'pendiente', ADD CONSTRAINT fk_pedidos_cliente FOREIGN KEY(id_cliente) REFERENCES tbclientes(id_cliente), ADD CONSTRAINT ck_pedidos_fechas CHECK(fecha_vencimiento IS NULL OR (fecha_confirmacion IS NOT NULL AND fecha_vencimiento>fecha_confirmacion))");
            $db->exec("INSERT INTO tbpedidos(codigo_pedido,id_cliente,estado,total,observaciones) VALUES ('ANTERIOR-1',$cid,'pagado',15.50,'Conservar sin adoptar')");
        }else $db->exec("INSERT INTO tbpedidos(id_usuario,estado,total) VALUES ($uid,'confirmado',15.50)");
        $order=$db->query('SELECT * FROM tbpedidos ORDER BY id_pedido')->fetchAll();$stock=$db->query('SELECT * FROM tbinventario ORDER BY id_producto')->fetchAll();
        OrderUpgrade::apply($db);OrderUpgrade::apply($db);
        if($stock!==$db->query('SELECT * FROM tbinventario ORDER BY id_producto')->fetchAll())throw new RuntimeException('Saldos alterados.');
        $after=$db->query('SELECT * FROM tbpedidos ORDER BY id_pedido')->fetchAll();foreach($order as $index=>$row)foreach($row as $key=>$value)if($after[$index][$key]!==$value)throw new RuntimeException('Pedido anterior alterado.');
        if((int)$db->query('SELECT COUNT(*) FROM tbpedidos WHERE retiro_solicitud IS NOT NULL OR reserva_activa=1')->fetchColumn()!==0 || (int)$db->query('SELECT COUNT(*) FROM tbretiocorreos')->fetchColumn()!==0)throw new RuntimeException('Datos antiguos adoptados.');
        echo 'OK: actualización y repetición de Plan 6 conserva pedidos anteriores, stock y estados sin crear reservas/correos ('.($suffix?'antiguo':'canónico').").\n";
        $product=(int)$db->query('SELECT id_producto FROM tbinventario WHERE stock_actual-stock_reservado>=1 ORDER BY id_producto LIMIT 1')->fetchColumn();
        (new ShoppingModel($db))->mutate($uid,['action'=>'cart_add','product_id'=>$product,'quantity'=>1]);$model=new OrderModel($db);$quote=$model->quote($uid);
        $input=['solicitud'=>bin2hex(random_bytes(32)),'cotizacion'=>$quote['fingerprint'],'nombre'=>'Pedido Compatible','telefono'=>'70000000','correo'=>'upgrade@orders.example.test','nota'=>'','acepto'=>'1'];
        $new=$model->create($uid,$input);
        if((int)$new['items'][0]['cantidad']!==1 || (int)$new['id_cliente']!==$cid) throw new RuntimeException('Pedido incompatible con esquema antiguo.');
        if($suffix && (!$new['fecha_confirmacion'] || (strtotime($new['fecha_vencimiento'])-strtotime($new['fecha_confirmacion']))!==86400))throw new RuntimeException('No se cumple la restricción de fechas del esquema local.');
        $repeat=$model->create($uid,$input);
        if(!$repeat['repeated'] || $repeat['id_pedido']!==$new['id_pedido'])throw new RuntimeException('El reintento no recupera el mismo pedido.');
        require_once BASE_PATH.'/app/Models/AdminTableModel.php';
        $listed=(new AdminTableModel($db))->data('orders',[],null);
        if($listed['recordsTotal']!==1 || (int)$listed['rows'][0]['id_pedido']!==(int)$new['id_pedido'])throw new RuntimeException('El pedido confirmado no aparece en la tabla.');
        echo "OK: confirmación con fechas válidas, reintento sin duplicar y listado administrativo.\n";
        $model->transition($uid,(int)$new['id_pedido'],'cancelado','pendiente','Comprobar liberación compatible');
        if($stock!==$db->query('SELECT * FROM tbinventario ORDER BY id_producto')->fetchAll())throw new RuntimeException('Reserva/liberación incompatible.');
        echo 'OK: confirmar y cancelar pedido funciona con cantidades '.($suffix?'DECIMAL antiguas':'enteras canónicas').".\n";
    }
}catch(Throwable $error){fwrite(STDERR,$error->getMessage()."\n");exit(1);}finally{if(is_dir($temp)){foreach(glob($temp.'/*.sql') as $file)unlink($file);rmdir($temp);}}
