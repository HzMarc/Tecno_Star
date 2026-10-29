<?php
declare(strict_types=1);
require_once BASE_PATH.'/app/Models/ShoppingModel.php';
$shop=new ShoppingModel($runtime);
$buyer=(int)$users->findActiveByEmail('client@catalog.example.test')['id_usuario'];
$second=(int)$users->findActiveByEmail('admin@catalog.example.test')['id_usuario'];
$command=static fn(string $action,array $extra=[])=>array_replace(['action'=>$action,'operation_id'=>bin2hex(random_bytes(32)),'target_user_id'=>$buyer,'expected_revision'=>$shop->state($buyer)['revision'],'product_id'=>$productId,'quantity'=>1],$extra);
$jsonRequest=static function(string $path,string &$session,?array $data=null,?string $csrf=null):array {
    $headers=['Connection: close','Accept: application/json'];if($session!=='') $headers[]='Cookie: '.$session;
    if($csrf!==null) $headers[]='X-CSRF-Token: '.$csrf;
    if($data!==null) $headers[]='Content-Type: application/json';
    $context=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>implode("\r\n",$headers),'content'=>$data===null?'':json_encode($data,JSON_THROW_ON_ERROR),'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $body=file_get_contents('http://127.0.0.1:18014'.$path,false,$context);$status=0;
    foreach($http_response_header as $header) if(preg_match('~^HTTP/\S+ (\d+)~',$header,$match)) $status=(int)$match[1];
    return [$status,json_decode($body,true,512,JSON_THROW_ON_ERROR)];
};
$post=static fn(array $data)=>$jsonRequest('/shopping/update',$client,$data,$clientToken);
$reject=static function(callable $call,string $label):void {try {$call();throw new LogicException('Se aceptó: '.$label);}catch(InvalidArgumentException|DomainException){catalogCheck(true,$label);}};
$shop->mutate($buyer,$command('cart_remove'));
$admin->exec('UPDATE tbinventario SET stock_actual=20,stock_reservado=0 WHERE id_producto='.$productId);
$sync=$command('sync',['cart'=>[['id'=>$productId,'quantity'=>3,'price'=>24.99]],'wishlist'=>[['id'=>$productId]]]);
[$status,$state]=$post($sync);catalogCheck($status===200 && $state['cart'][0]['quantity']===3,'sincronización visitante agrega sus unidades una vez');
$revision=$state['revision'];[$status,$again]=$post($sync);
catalogCheck($status===200 && $again['repeated'] && $again['cart'][0]['quantity']===3 && $again['revision']===$revision,'repetir sincronización tras perder respuesta no suma ni incrementa revisión');
$changed=$sync;$changed['cart'][0]['quantity']=4;[$status]=$post($changed);catalogCheck($status===409,'misma clave con otros datos se rechaza');
$request=$command('cart_add',['quantity'=>2]);[$status,$state]=$post($request);[$status,$state]=$post($request);
catalogCheck($status===200 && $state['cart'][0]['quantity']===5 && $state['repeated'],'reintento de agregar tampoco duplica cantidades');
$stale=$command('cart_set_quantity',['quantity'=>2]);$shop->mutate($buyer,$command('cart_change',['delta'=>1]));[$status,$state]=$post($stale);
catalogCheck($status===409 && $state['state']['cart'][0]['quantity']===6,'edición absoluta obsoleta devuelve conflicto y estado actualizado');
$other=$command('cart_add',['target_user_id'=>$second]);[$status]=$post($other);catalogCheck($status===409 && $shop->state($second)['cart']===[],'solicitud de página antigua no modifica la cuenta nueva');
[$status]=$jsonRequest('/shopping/update',$client,$command('cart_add'));catalogCheck($status===403,'solicitud de carrito sin CSRF se rechaza');
foreach([['quantity'=>0],['quantity'=>-1],['quantity'=>1.5],['quantity'=>['1']],['quantity'=>1000001],['product_id'=>['1']],['operation_id'=>'bad'],['action'=>['sync']]] as $invalid) {
    [$status]=$post(array_replace($command('cart_add'),$invalid));catalogCheck(in_array($status,[400,409],true),'API rechaza cantidades y parámetros inválidos');
}
$admin->exec('UPDATE tbproductos SET precio_venta=30.50 WHERE id_producto='.$productId);
[$status,$state]=$jsonRequest('/shopping/state',$client);catalogCheck($status===200 && $state['cart'][0]['price']===30.5 && $state['cart'][0]['price_changed'] && count($state['cart'][0]['issues'])>0,'cambio de precio se muestra sin ocultarlo ni aceptar automáticamente');
[$status]=$post($command('cart_review',['prices'=>[$productId=>24.99]]));catalogCheck($status===409,'no se acepta un precio antiguo');
[$status,$state]=$post($command('cart_review',['prices'=>[$productId=>30.50]]));catalogCheck($status===200 && !$state['cart'][0]['price_changed'],'revisión explícita confirma los precios actuales');
$admin->exec('UPDATE tbinventario SET stock_actual=2 WHERE id_producto='.$productId);
[$status,$state]=$jsonRequest('/shopping/state',$client);catalogCheck($state['cart'][0]['quantity']===6 && $state['cart'][0]['stock']===2 && count($state['cart'][0]['issues'])>0,'reducción de stock conserva cantidad y avisa para revisión');
[$status]=$post($command('cart_add'));catalogCheck($status===422,'sin stock suficiente no se presenta el agregado como guardado');
[$status,$state]=$post($command('cart_set_quantity',['quantity'=>2]));catalogCheck($status===200 && $state['cart'][0]['quantity']===2,'se puede ajustar explícitamente a lo disponible');
$admin->exec("UPDATE tbproductos SET estado='inactivo' WHERE id_producto=".$productId);
[$status,$state]=$jsonRequest('/shopping/state',$client);catalogCheck($state['cart'][0]['available']===false && $state['wishlist'][0]['available']===false,'desactivado permanece en carrito y favoritos con aviso');
[$status]=$post($command('wishlist_add'));catalogCheck($status===422,'producto desactivado no se agrega a favoritos');
[$status,$state]=$post($command('cart_remove'));catalogCheck($status===200 && $state['cart']===[],'producto desactivado se puede retirar del carrito');
$admin->exec("UPDATE tbproductos SET estado='activo',precio_venta=24.99 WHERE id_producto=".$productId);$admin->exec('UPDATE tbinventario SET stock_actual=10 WHERE id_producto='.$productId);
$sync=$command('sync',['cart'=>[['id'=>$productId,'quantity'=>15],['id'=>999999,'quantity'=>1]],'wishlist'=>[]]);
[$status,$state]=$post($sync);catalogCheck($status===200 && $state['cart'][0]['quantity']===10 && count($state['warnings'])===2,'sincronización comunica ajustes por stock y productos omitidos');
$before=$shop->state($buyer);$count=(int)$admin->query('SELECT COUNT(*) FROM tbshoppingoperaciones')->fetchColumn();
try {
    $admin->exec("CREATE TRIGGER shopping_test_failure BEFORE INSERT ON tbshoppingoperaciones FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='rollback test'");
    try {$shop->mutate($buyer,$command('cart_remove'));throw new LogicException('Falta rollback.');}catch(PDOException){}
    catalogCheck($shop->state($buyer)===$before && (int)$admin->query('SELECT COUNT(*) FROM tbshoppingoperaciones')->fetchColumn()===$count,'fallo al registrar solicitud revierte carrito y revisión');
} finally {$admin->exec('DROP TRIGGER IF EXISTS shopping_test_failure');}
foreach(['DELETE FROM tbshoppingoperaciones','UPDATE tbshoppingoperaciones SET avisos=\'[]\''] as $sql) {try{$runtime->exec($sql);throw new LogicException('Permiso excesivo.');}catch(PDOException){catalogCheck(true,'cuenta SQL no puede borrar ni alterar solicitudes confirmadas');}}
$shop->mutate($buyer,$command('cart_remove'));
$race=static function(array $inputs) use($admin,$buyer,$options,$runtimeUser,$runtimePassword):array {
    $admin->beginTransaction();$admin->query('SELECT id_carrito FROM tbcarritos WHERE id_usuario='.$buyer.' FOR UPDATE');$children=[];$result=[];
    try {
        foreach($inputs as $input) {$process=proc_open([PHP_BINARY,BASE_PATH.'/tests/shopping-worker.php'],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,BASE_PATH);fwrite($pipes[0],json_encode(['host'=>$options['host'],'port'=>$options['port'],'database'=>$options['database'],'user'=>$runtimeUser,'password'=>$runtimePassword,'actor'=>$buyer,'input'=>$input],JSON_THROW_ON_ERROR));fclose($pipes[0]);$children[]=[$process,$pipes];}
        usleep(250000);$admin->commit();
        foreach($children as [$process,$pipes]) {$result[]=stream_get_contents($pipes[1]);$errors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);if(proc_close($process)!==0) throw new RuntimeException('Prueba concurrente: '.$errors);catalogCheck(true,'proceso concurrente de carrito sin error SQL');}
    } finally {if($admin->inTransaction()) $admin->rollBack();}
    sort($result);return $result;
};
$same=$command('sync',['cart'=>[['id'=>$productId,'quantity'=>2]],'wishlist'=>[]]);
catalogCheck($race([$same,$same])===['saved','saved'] && $shop->state($buyer)['cart'][0]['quantity']===2,'dos pestañas sincronizan la misma copia una sola vez');
catalogCheck($race([$command('cart_add'),$command('cart_add')])===['saved','saved'] && $shop->state($buyer)['cart'][0]['quantity']===4,'dos agregados consecutivos conservan ambos incrementos');
$a=$command('cart_set_quantity',['quantity'=>2]);$b=$command('cart_set_quantity',['quantity'=>3]);
catalogCheck($race([$a,$b])===['conflict','saved'],'dos ediciones absolutas simultáneas no sobrescriben silenciosamente');
catalogCheck($shop->state($second)['cart']===[],'las cuentas conservan sus carritos separados tras concurrencia');
echo "Pruebas del Plan 5 completadas.\n";
