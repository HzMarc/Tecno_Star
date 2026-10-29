<?php
declare(strict_types=1);
// Se ejecuta con --inventory desde catalog.php, reutilizando únicamente sus cuentas sintéticas.
require_once BASE_PATH.'/app/Models/InventoryModel.php';
require_once BASE_PATH.'/app/Models/AdminTableModel.php';
$inventoryModel=new InventoryModel($runtime);
$actor=(int)$users->findActiveByEmail('admin@catalog.example.test')['id_usuario'];
$readStock=static fn()=> $admin->query('SELECT * FROM tbinventario WHERE id_producto='.$productId)->fetch();
$movementCount=static fn()=> (int)$admin->query('SELECT COUNT(*) FROM tbstockmovimientos')->fetchColumn();
$command=static fn(string $type,string $quantity,array $extra=[])=>array_replace(['id_producto'=>(string)$productId,'tipo'=>$type,'cantidad'=>$quantity,'motivo'=>'Prueba aislada de inventario','solicitud'=>bin2hex(random_bytes(32))],$extra);
$send=static function(array $input) use (&$cookie,$token):array { return catalogRequest('/admin/inventory/move',$cookie,$input+['_csrf'=>$token]); };
$reject=static function(callable $action,string $label):void {
    try { $action(); throw new LogicException('Se aceptó una operación inválida: '.$label); }
    catch(InvalidArgumentException) { catalogCheck(true,$label); }
};
$first=$command('entrada','5');
[$status,$body]=$send($first); $result=json_decode($body,true,512,JSON_THROW_ON_ERROR);
catalogCheck($status===200 && $result['ok'] && (int)$readStock()['stock_actual']===15,'entrada HTTP con responsable y motivo');
$count=$movementCount(); [$status,$body]=$send($first);
catalogCheck($status===200 && json_decode($body,true)['repeated'] && $movementCount()===$count && (int)$readStock()['stock_actual']===15,'reintentar misma solicitud no duplica existencias ni historial');
[$status]=$send(array_replace($first,['cantidad'=>'6'])); catalogCheck($status===422,'reutilizar solicitud con otros datos se rechaza');
[$status]=$send($command('salida','3')); catalogCheck($status===200 && (int)$readStock()['stock_actual']===12,'salida descuenta físico');
[$status]=$send($command('ajuste','8',['stock_esperado'=>'12'])); catalogCheck($status===200 && (int)$readStock()['stock_actual']===8,'ajuste absoluto a conteo físico');
[$status]=$send($command('ajuste','7',['stock_esperado'=>'12'])); catalogCheck($status===422 && (int)$readStock()['stock_actual']===8,'conteo obsoleto no sobrescribe cambios concurrentes');
$admin->exec('UPDATE tbinventario SET stock_reservado=4 WHERE id_producto='.$productId); // Reserva sintética, no operación de producción.
foreach ([$command('salida','5'),$command('ajuste','3',['stock_esperado'=>'8']),$command('entrada','0'),$command('salida','-1'),$command('entrada','1.0001'),$command('entrada','2147483648'),$command('entrada','1',['motivo'=>'']),$command('entrada','1',['cantidad'=>['1']]),$command('reservar','1'),$command('entrada','1',['id_producto'=>'999999'])] as $invalid) {
    $before=$readStock(); $count=$movementCount(); [$status]=$send($invalid);
    catalogCheck($status===422 && $readStock()===$before && $movementCount()===$count,'validación rechaza entrada inválida sin cambios parciales');
}
[$status]=$send($command('minimo','4'));
$tables=new AdminTableModel($runtime); $low=$tables->data('inventory',['low'=>'1','search'=>['value'=>'CAT-001']]);
catalogCheck($status===200 && $low['recordsFiltered']===1 && (int)$low['rows'][0]['stock_disponible']===4,'mínimo y alerta usan físico menos reservado');
$admin->exec('UPDATE tbinventario SET stock_reservado=stock_actual WHERE id_producto='.$productId);
$empty=$tables->data('inventory',['search'=>['value'=>'CAT-001']]);
catalogCheck($empty['rows'][0]['stock_status']==='Agotado','inventario distingue agotados cuando todo el stock está reservado');
$emptySearch=$tables->data('inventory',['length'=>'100','search'=>['value'=>'Agotado']]);
catalogCheck(in_array($productId,array_map('intval',array_column($emptySearch['rows'],'id_producto')),true),'búsqueda del inventario reconoce la etiqueta Agotado');
$admin->exec('UPDATE tbinventario SET stock_reservado=4 WHERE id_producto='.$productId);
$reject(fn()=> $inventoryModel->move($clientId,$command('entrada','1')),'modelo revalida administrador contra BDD');
foreach ([[$guest,null],[$client,$clientToken]] as [$otherCookie,$otherToken]) {
    [$status]=catalogRequest('/admin/inventory/move',$otherCookie,$command('entrada','1')+['_csrf'=>$otherToken??$token]);
    catalogCheck($status===403,'visitante o cliente no puede mover existencias');
}
[$status]=catalogRequest('/admin/inventory/move',$cookie,$command('entrada','1'));
catalogCheck($status===403,'movimiento requiere CSRF');
foreach (['DELETE FROM tbstockmovimientos','UPDATE tbstockmovimientos SET motivo=\'alterado\'','UPDATE tbinventario SET id_producto=id_producto'] as $sql) {
    try {$runtime->exec($sql); throw new LogicException('Permisos excesivos.');} catch(PDOException) {catalogCheck(true,'cuenta limitada no borra ni altera historial y no cambia referencias de inventario');}
}
$before=$readStock(); $count=$movementCount();
try {
    $admin->exec("CREATE TRIGGER inventory_test_failure BEFORE INSERT ON tbstockmovimientos FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test rollback'");
    try { $inventoryModel->move($actor,$command('entrada','2')); throw new LogicException('Falta rollback.'); } catch(PDOException) {}
    catalogCheck($readStock()===$before && $movementCount()===$count,'fallo al registrar historial revierte también existencias');
} finally { $admin->exec('DROP TRIGGER IF EXISTS inventory_test_failure'); }
$admin->exec('UPDATE tbinventario SET stock_reservado=0 WHERE id_producto='.$productId);
$inventoryModel->move($actor,$command('ajuste','10',['stock_esperado'=>'8']));
// Dos procesos independientes bloqueados sobre la misma fila, liberados al mismo tiempo.
$race=static function(array $inputs) use ($admin,$productId,$options,$runtimeUser,$runtimePassword,$actor):array {
    $children=[]; $results=[]; $admin->beginTransaction();
    $admin->query('SELECT id_producto FROM tbinventario WHERE id_producto='.$productId.' FOR UPDATE');
    try {
        foreach($inputs as $input) {
            $process=proc_open([PHP_BINARY,BASE_PATH.'/tests/inventory-worker.php'],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,BASE_PATH);
            if(!is_resource($process)) throw new RuntimeException('No se pudo lanzar prueba concurrente.');
            fwrite($pipes[0],json_encode(['host'=>$options['host'],'port'=>$options['port'],'database'=>$options['database'],'user'=>$runtimeUser,'password'=>$runtimePassword,'actor'=>$actor,'input'=>$input],JSON_THROW_ON_ERROR)); fclose($pipes[0]);
            $children[]=[$process,$pipes];
        }
        usleep(250000); $admin->commit();
        foreach($children as [$process,$pipes]) { $results[]=stream_get_contents($pipes[1]); fclose($pipes[1]); fclose($pipes[2]); catalogCheck(proc_close($process)===0,'proceso de concurrencia termina sin error SQL'); }
    } finally { if($admin->inTransaction()) $admin->rollBack(); }
    sort($results); return $results;
};
$count=$movementCount();
catalogCheck($race([$command('salida','7'),$command('salida','7')])===['rejected','saved'] && (int)$readStock()['stock_actual']===3 && $movementCount()===$count+1,'dos salidas simultáneas no sobrevenden ni pierden actualizaciones');
$same=$command('entrada','2'); $count=$movementCount();
catalogCheck($race([$same,$same])===['saved','saved'] && (int)$readStock()['stock_actual']===5 && $movementCount()===$count+1,'dos reintentos simultáneos crean un solo movimiento');
$count=$movementCount();
catalogCheck($race([$command('entrada','2'),$command('entrada','3')])===['saved','saved'] && (int)$readStock()['stock_actual']===10 && $movementCount()===$count+2,'entradas simultáneas conservan ambos incrementos');
for($i=0;$i<14;$i++) $inventoryModel->move($actor,$command('entrada','1',['motivo'=>'<script>alert(1)</script> prueba '.$i]));
$inventoryDay=(string)$admin->query('SELECT CURRENT_DATE()')->fetchColumn();
$history=$tables->data('movements',['product'=>(string)$productId,'type'=>'entrada','actor'=>(string)$actor,'from'=>$inventoryDay,'to'=>$inventoryDay,'start'=>'12','length'=>'12']);
catalogCheck($history['recordsFiltered']>=15 && count($history['rows'])>0 && count($history['rows'])<=12,'historial combina producto, tipo, responsable y fechas con segunda página');
[$status,$body]=catalogRequest('/admin/tables/data?'.http_build_query(['kind'=>'movements','product'=>(string)$productId,'search'=>['value'=>'<script>'],'length'=>'12']),$cookie);
$historyJson=json_decode($body,true,512,JSON_THROW_ON_ERROR);
catalogCheck($status===200 && $historyJson['recordsFiltered']===14 && str_contains($historyJson['data'][0][8],'&lt;script&gt;') && !str_contains($historyJson['data'][0][8],'<script>'),'historial HTTP escapa motivos contra XSS');
foreach ([['from'=>'2026-02-30'],['from'=>'2026-10-20','to'=>'2026-10-01'],['product'=>['1']],['type'=>'DROP'],['actor'=>'1 OR 1=1']] as $invalid) {
    [$status]=catalogRequest('/admin/tables/data?'.http_build_query(['kind'=>'movements']+$invalid),$cookie); catalogCheck($status===422,'historial rechaza filtros inválidos');
}
$last=$admin->query('SELECT * FROM tbstockmovimientos ORDER BY id_movimiento DESC LIMIT 1')->fetch();
catalogCheck((int)$last['id_usuario']===$actor && $last['responsable']==='Catálogo Admin' && (int)$last['stock_despues']===24,'historial conserva responsable y saldos antes/después');
$inventoryModel->move($actor,$command('ajuste','10',['stock_esperado'=>'24','motivo'=>'Conteo final de prueba']));
$inventoryModel->move($actor,$command('entrada','0.125'));
$inventoryModel->move($actor,$command('salida','0.025'));
catalogCheck($readStock()['stock_actual']==='10.100','milésimas exactas sin redondear ni perder stock');
$inventoryModel->move($actor,$command('ajuste','10',['stock_esperado'=>'10.100','motivo'=>'Restaurar saldo sintético']));
echo "Pruebas del Plan 4 completadas.\n";
