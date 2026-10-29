<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/database/cli.php';
require_once BASE_PATH.'/database/MigrationRunner.php';
require_once BASE_PATH.'/database/seeders/demo.php';
require_once BASE_PATH.'/database/InventoryUpgrade.php';
$temp=sys_get_temp_dir().'/tecno_inventory_upgrade_'.bin2hex(random_bytes(8));
try {
    $options=migrationOptions();
    if (!preg_match('/^tecno_star_test_[a-zA-Z0-9_]{1,25}$/D',$options['database'])) throw new RuntimeException('Solo una base nueva de pruebas.');
    $server=migrationConnection(array_replace($options,['database'=>'information_schema']));
    $find=$server->prepare('SELECT COUNT(*) FROM SCHEMATA WHERE SCHEMA_NAME=?'); $find->execute([$options['database']]);
    if ((int)$find->fetchColumn()!==0) throw new RuntimeException('La base ya existe.');
    mkdir($temp);
    foreach(['001_initial_schema.sql','002_required_roles.sql'] as $name) copy(BASE_PATH.'/database/migrations/'.$name,$temp.'/'.$name);
    $db=migrationConnection($options+['create'=>true],true);
    (new MigrationRunner($db,$temp))->migrate(); seedDemo($db);
    $db->exec('UPDATE tbinventario SET stock_reservado=1,stock_minimo=3 WHERE stock_actual>=1');
    $before=$db->query('SELECT * FROM tbinventario ORDER BY id_producto')->fetchAll();
    $runner=new MigrationRunner($db,BASE_PATH.'/database/migrations');
    if($runner->migrate()!==['003_inventory_movements.sql','004_shopping_consistency.sql','005_pickup_orders.sql','006_sales_accounts.sql']) throw new RuntimeException('Actualización inesperada.');
    $after=$db->query('SELECT * FROM tbinventario ORDER BY id_producto')->fetchAll();
    $history=$db->query('SELECT * FROM tbstockmovimientos ORDER BY id_producto')->fetchAll();
    $normalize=static function(array $rows):array {return array_map(static function($row){foreach(['stock_actual','stock_reservado','stock_minimo'] as $key) $row[$key]=(float)$row[$key];return $row;},$rows);};
    if($normalize($before)!==$normalize($after) || count($history)!==count($before)) throw new RuntimeException('Saldo alterado.');
    foreach($history as $i=>$row) if((float)$row['stock_despues']!==(float)$before[$i]['stock_actual'] || (float)$row['reservado']!==(float)$before[$i]['stock_reservado'] || (float)$row['minimo_despues']!==(float)$before[$i]['stock_minimo'] || $row['id_usuario']!==null || $row['tipo']!=='inicial') throw new RuntimeException('Apertura incorrecta.');
    if($runner->migrate()!==[] || (int)$db->query('SELECT COUNT(*) FROM tbstockmovimientos')->fetchColumn()!==count($history)) throw new RuntimeException('Apertura duplicada.');
    echo "OK: actualización desde Plan 3 conserva físico, reservado y mínimo; apertura identifica al sistema y repetir no duplica saldos.\n";
    $legacyOptions=array_replace($options,['database'=>$options['database'].'_legacy','create'=>true]);
    $find->execute([$legacyOptions['database']]); if((int)$find->fetchColumn()!==0) throw new RuntimeException('Base antigua de prueba ya existe.');
    $legacy=migrationConnection($legacyOptions,true);
    (new MigrationRunner($legacy,$temp))->migrate(); seedDemo($legacy);
    // Simula únicamente en esta base nueva la estructura previa sin historial canónico.
    $legacy->exec('DROP TABLE schema_migrations');
    $legacy->exec('ALTER TABLE tbinventario MODIFY stock_actual DECIMAL(12,3) NOT NULL, MODIFY stock_reservado DECIMAL(12,3) NOT NULL, MODIFY stock_minimo DECIMAL(12,3) NOT NULL');
    $legacy->exec("CREATE TABLE tbmovimientosinventario (id_movimiento INT PRIMARY KEY,motivo VARCHAR(255)) ENGINE=InnoDB");
    $legacy->exec("INSERT INTO tbmovimientosinventario VALUES (1,'Historial anterior preservado')");
    $legacy->exec('UPDATE tbinventario SET stock_actual=19.875,stock_reservado=1.125,stock_minimo=2.500');
    $before=$legacy->query('SELECT * FROM tbinventario ORDER BY id_producto')->fetchAll();
    InventoryUpgrade::apply($legacy); InventoryUpgrade::apply($legacy);
    if($legacy->query('SELECT * FROM tbinventario ORDER BY id_producto')->fetchAll()!==$before || $legacy->query('SELECT motivo FROM tbmovimientosinventario')->fetchColumn()!=='Historial anterior preservado' || (int)$legacy->query('SELECT COUNT(*) FROM tbstockmovimientos')->fetchColumn()!==count($before)) throw new RuntimeException('Actualización antigua alteró datos.');
    if($legacy->query('SELECT stock_despues FROM tbstockmovimientos LIMIT 1')->fetchColumn()!=='19.875') throw new RuntimeException('Se perdieron decimales.');
    echo "OK: esquema antiguo sin historial canónico actualizado sin tocar sus tablas ni movimientos anteriores; decimales y apertura conservados.\n";
} catch(Throwable $error) { fwrite(STDERR,$error->getMessage()."\n"); exit(1); }
finally { if(is_dir($temp)) {foreach(glob($temp.'/*.sql') as $file) unlink($file); rmdir($temp);} }
