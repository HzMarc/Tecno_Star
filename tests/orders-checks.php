<?php
declare(strict_types=1);
require_once BASE_PATH.'/app/Models/OrderModel.php';
require_once BASE_PATH.'/core/OrderMail.php';
require_once BASE_PATH.'/app/Models/SaleModel.php';
$orders=new OrderModel($runtime);$shop=new ShoppingModel($runtime);
$buyer=(int)$users->findActiveByEmail('client@catalog.example.test')['id_usuario'];$employee=(int)$users->findActiveByEmail('admin@catalog.example.test')['id_usuario'];
$users->createCustomer('order-other','Otro','Cliente','70000000','other@orders.example.test',password_hash('CatalogPreview2026!',PASSWORD_DEFAULT));$other=(int)$users->findActiveByEmail('other@orders.example.test')['id_usuario'];
$setCart=static function(int $userId,int $quantity=1) use($shop,$productId):void {
    foreach($shop->state($userId)['cart'] as $item) $shop->mutate($userId,['action'=>'cart_remove','product_id'=>$item['id']]);
    $shop->mutate($userId,['action'=>'cart_add','product_id'=>$productId,'quantity'=>$quantity]);
};
$input=static fn(int $id)=>['nombre'=>'Contacto de prueba','telefono'=>'7000-0000','correo'=>'client@catalog.example.test','nota'=>'Prueba sintética','acepto'=>'1','solicitud'=>bin2hex(random_bytes(32)),'cotizacion'=>$orders->quote($id)['fingerprint']];
$reject=static function(callable $call,string $message):void {try{$call();throw new LogicException('Se aceptó: '.$message);}catch(InvalidArgumentException|DomainException){catalogCheck(true,$message);}};
$stock=static fn()=> $admin->query('SELECT stock_actual,stock_reservado FROM tbinventario WHERE id_producto='.$productId)->fetch();
$admin->exec("UPDATE tbproductos SET estado='activo',precio_venta=24.99 WHERE id_producto=$productId");$admin->exec("UPDATE tbinventario SET stock_actual=10,stock_reservado=0 WHERE id_producto=$productId");
$q=$admin->prepare("INSERT INTO tbusuarios(id_rol,nombre_usuario,nombres,apellidos,correo,password_hash) SELECT id_rol,'legacy-no-client','Cuenta','Anterior','legacy@orders.example.test',password_hash FROM tbusuarios WHERE id_usuario=?");$q->execute([$employee]);$legacyBuyer=(int)$admin->lastInsertId();
$legacyIdentity=$admin->query('SELECT id_rol,password_hash FROM tbusuarios WHERE id_usuario='.$legacyBuyer)->fetch();
$setCart($legacyBuyer);$legacyRequest=$input($legacyBuyer);$legacyStock=$stock();
$admin->exec("CREATE TRIGGER fail_missing_profile BEFORE INSERT ON tbretirohistorial FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Fallo de prueba de perfil'");
try{try{$orders->create($legacyBuyer,$legacyRequest);throw new LogicException('Pedido fallido aceptado.');}catch(PDOException){catalogCheck((int)$admin->query('SELECT COUNT(*) FROM tbclientes WHERE id_usuario='.$legacyBuyer)->fetchColumn()===0 && $stock()===$legacyStock && count($shop->state($legacyBuyer)['cart'])===1,'fallo de pedido revierte también el perfil nuevo sin perder carrito ni stock');}}finally{$admin->exec('DROP TRIGGER fail_missing_profile');}
$legacyOrder=$orders->create($legacyBuyer,$legacyRequest);$legacyRepeat=$orders->create($legacyBuyer,$legacyRequest);
$legacyContact=$admin->query('SELECT * FROM tbclientes WHERE id_usuario='.$legacyBuyer)->fetch();
catalogCheck($legacyContact && (int)$legacyContact['id_cliente']===(int)$legacyOrder['id_cliente'] && $legacyContact['nombres']==='Cuenta' && $legacyContact['correo_contacto']==='legacy@orders.example.test' && $legacyIdentity===$admin->query('SELECT id_rol,password_hash FROM tbusuarios WHERE id_usuario='.$legacyBuyer)->fetch(),'cuenta anterior sin perfil confirma con su propio cliente sin cambiar rol ni contraseña');
catalogCheck($legacyRepeat['repeated'] && $legacyRepeat['id_pedido']===$legacyOrder['id_pedido'] && (int)$admin->query('SELECT COUNT(*) FROM tbclientes WHERE id_usuario='.$legacyBuyer)->fetchColumn()===1,'reintento de cuenta sin perfil conserva un solo cliente y pedido');
$orders->transition($legacyBuyer,(int)$legacyOrder['id_pedido'],'cancelado','pendiente','Cerrar prueba de perfil anterior');
$setCart($buyer,2);$request=$input($buyer);
[$status,$html]=catalogRequest('/checkout',$client);catalogCheck($status===200 && str_contains($html,'Confirma tu pedido') && str_contains($html,'24 horas'),'confirmación HTTP con contacto, importe y plazo de retiro');
$reject(fn()=> $orders->create($buyer,array_replace($request,['acepto'=>'0'])),'confirmación explícita obligatoria');
$admin->exec("UPDATE tbproductos SET precio_venta=25.50 WHERE id_producto=$productId");$reject(fn()=> $orders->create($buyer,$request),'precio cambiado exige revisar otra cotización');
$request=$input($buyer);
[$status,$body]=catalogRequest('/orders/confirm',$client,$request+['target_user_id'=>(string)$buyer,'_csrf'=>$clientToken]);$result=json_decode($body,true,512,JSON_THROW_ON_ERROR);
catalogCheck($status===200 && ($result['ok']??false),'confirmar pedido desde HTTP');
$order=$orders->listing($buyer,false)[0];$id=(int)$order['id_pedido'];$detail=$orders->find($id,$buyer,false);
catalogCheck($detail['total']==='51.00' && $stock()===['stock_actual'=>'10.000','stock_reservado'=>'2.000'] && $shop->state($buyer)['cart']===[],'reserva sin descontar físico, importe recalculado y carrito vaciado atómicamente');
$repeat=$orders->create($buyer,$request);catalogCheck($repeat['repeated'] && (int)$repeat['id_pedido']===$id && count($repeat['history'])===1,'reintento tras respuesta perdida conserva el mismo pedido sin duplicar reserva');
$reject(fn()=> $orders->create($buyer,array_replace($request,['nombre'=>'Otro contacto'])),'misma solicitud con otros datos rechazada');
$admin->exec("UPDATE tbproductos SET precio_venta=30.50,nombre_producto='Nombre posterior' WHERE id_producto=$productId");
$historical=$orders->find($id,$buyer,false);catalogCheck($historical['items'][0]['precio_unitario']==='25.50' && $historical['items'][0]['nombre_producto']!=='Nombre posterior','detalle conserva precio y nombre histórico');
$reject(fn()=> $orders->transition($buyer,$id,'entregado','pendiente','Intento no autorizado'),'cliente no puede entregar ni confirmar pedidos');
$reject(fn()=> $orders->transition($employee,$id,'listo','pendiente','Salto no permitido'),'administrador no puede saltar estados');
foreach(['confirmado','en_preparacion','listo','entregado'] as $state){$previous=$detail['estado'];if($state==='entregado' && OrderModel::cashEnabled($runtime)){(new SaleModel($runtime))->pay($employee,['id_pedido'=>(string)$id,'solicitud'=>bin2hex(random_bytes(32)),'recibido'=>$detail['total'],'confirmado'=>'1']);$detail=$orders->find($id,$buyer,false);}else $detail=$orders->transition($employee,$id,$state,$previous,'Avance verificado por empleado');}
$orders->transition($employee,$id,'entregado','listo','Reintento de entrega');
catalogCheck($stock()===['stock_actual'=>'8.000','stock_reservado'=>'0.000'] && count($orders->find($id,$buyer,false)['history'])===5,'entrega y su reintento descuentan físico y liberan reserva una sola vez');
catalogCheck((int)$admin->query("SELECT COUNT(*) FROM tbretiocorreos WHERE tipo='confirmado' AND id_pedido=".$id)->fetchColumn()===1,'confirmar encola exactamente un correo dentro de la transacción');
$reject(fn()=> $orders->transition($employee,$id,'cancelado','entregado','Cancelar después'),'pedido entregado es terminal');
$setCart($buyer,1);$cancel=$orders->create($buyer,$input($buyer));$cancelId=(int)$cancel['id_pedido'];
$orders->transition($buyer,$cancelId,'cancelado','pendiente','Cambio de planes');$orders->transition($buyer,$cancelId,'cancelado','pendiente','Reintento cancelación');
catalogCheck($stock()===['stock_actual'=>'8.000','stock_reservado'=>'0.000'] && count($orders->find($cancelId,$buyer,false)['history'])===2,'cancelación del propietario libera sin descontar físico una sola vez');
$setCart($buyer);$expired=$orders->create($buyer,$input($buyer));$expiredId=(int)$expired['id_pedido'];$admin->exec("UPDATE tbpedidos SET fecha_vencimiento=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 SECOND) WHERE id_pedido=$expiredId");
$shop->state($buyer);$orders->expireDue();$expired=$orders->find($expiredId,$buyer,false);
catalogCheck($expired['estado']==='cancelado' && count($expired['history'])===2 && $expired['history'][1]['id_usuario']===null && $stock()['stock_reservado']==='0.000','vencimiento libera y registra responsable Sistema una vez antes de usar stock');
$setCart($buyer);$before=$stock();$cartBefore=$shop->state($buyer);
try {$admin->exec("CREATE TRIGGER order_test_failure BEFORE INSERT ON tbretirohistorial FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='rollback test'");try{$orders->create($buyer,$input($buyer));throw new LogicException('Falta rollback');}catch(PDOException){}catalogCheck($stock()===$before && $shop->state($buyer)===$cartBefore,'fallo en historial revierte pedido, detalle, movimiento y carrito');}finally{$admin->exec('DROP TRIGGER IF EXISTS order_test_failure');}
foreach(['DELETE FROM tbpedidos','UPDATE tbretirodetalles SET cantidad=99','DELETE FROM tbretirohistorial','UPDATE tbretirohistorial SET motivo=\'alterado\''] as $sql){try{$runtime->exec($sql);throw new LogicException('Permiso excesivo');}catch(PDOException){catalogCheck(true,'permisos limitados protegen pedidos e historial');}}
$otherCookie='';catalogLogin('other@orders.example.test',$otherCookie);
[$status]=catalogRequest('/orders/detail?id='.$id,$otherCookie);catalogCheck($status===404 && $orders->find($id,$other,false)===null,'otro cliente no puede consultar el pedido por ID');
[$status,$data]=catalogRequest('/orders/data',$otherCookie);catalogCheck($status===200 && json_decode($data,true)['recordsTotal']===0,'conteos y resultados de DataTables limitados a pedidos propios');
[$status]=catalogRequest('/orders/data?scope=admin',$client);catalogCheck($status===403,'cliente no puede pedir alcance administrativo');
[$status]=catalogRequest('/orders/state',$client,['id'=>(string)$id]);catalogCheck($status===403,'escritura de pedido sin CSRF rechazada');
[$status]=catalogRequest('/orders/confirm',$client,$input($buyer)+['target_user_id'=>(string)$other,'_csrf'=>$clientToken]);catalogCheck($status===409,'formulario de otra cuenta no crea un pedido');
foreach(['/orders','/orders/detail?id='.$id,'/admin/orders'] as $path){$viewCookie=$path==='/admin/orders'?$cookie:$client;[$status,$html]=catalogRequest($path,$viewCookie);catalogCheck($status===200,'vista de pedidos disponible: '.$path);}
$race=static function(array $jobs) use($options,$runtimeUser,$runtimePassword,$admin,$productId):array {
    $children=[];$results=[];$admin->beginTransaction();$admin->query('SELECT id_producto FROM tbproductos WHERE id_producto='.$productId.' FOR UPDATE');
    try{foreach($jobs as $job){$child=proc_open([PHP_BINARY,BASE_PATH.'/tests/orders-worker.php'],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,BASE_PATH);fwrite($pipes[0],json_encode(['host'=>$options['host'],'port'=>$options['port'],'database'=>$options['database'],'user'=>$runtimeUser,'password'=>$runtimePassword]+$job,JSON_THROW_ON_ERROR));fclose($pipes[0]);$children[]=[$child,$pipes];}usleep(300000);$admin->commit();foreach($children as [$child,$pipes]){$results[]=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);if(proc_close($child)!==0)throw new RuntimeException('Concurrencia: '.$error);}}finally{if($admin->inTransaction())$admin->rollBack();}sort($results);return $results;
};
$admin->exec("UPDATE tbinventario SET stock_actual=1,stock_reservado=0 WHERE id_producto=$productId");$setCart($buyer);$setCart($other);
$jobs=[['action'=>'create','actor'=>$buyer,'input'=>$input($buyer)],['action'=>'create','actor'=>$other,'input'=>$input($other)]];
catalogCheck($race($jobs)===['conflict','saved'] && $stock()['stock_reservado']==='1.000','dos clientes compiten por la última unidad: solo uno reserva');
$winner=$admin->query("SELECT id_pedido,id_usuario FROM tbpedidos WHERE reserva_activa=1 ORDER BY id_pedido DESC LIMIT 1")->fetch();$winnerId=(int)$winner['id_pedido'];
$jobs=array_fill(0,2,['action'=>'transition','actor'=>(int)$winner['id_usuario'],'id'=>$winnerId,'target'=>'cancelado','expected'=>'pendiente']);catalogCheck($race($jobs)===['saved','saved'] && $stock()['stock_reservado']==='0.000' && count($orders->find($winnerId,$employee,true)['history'])===2,'cancelaciones concurrentes liberan una única vez');
$setCart($buyer);$new=$orders->create($buyer,$input($buyer));$newId=(int)$new['id_pedido'];$current='pendiente';foreach(['confirmado','en_preparacion','listo'] as $target){$orders->transition($employee,$newId,$target,$current,'Preparación sintética');$current=$target;}
$job=['action'=>'transition','actor'=>$employee,'id'=>$newId,'target'=>'entregado','expected'=>'listo'];catalogCheck($race([$job,$job])===['saved','saved'] && $stock()===['stock_actual'=>'0.000','stock_reservado'=>'0.000'],'entregas concurrentes descuentan la última unidad una sola vez');
$admin->exec("UPDATE tbinventario SET stock_actual=10 WHERE id_producto=$productId");$setCart($buyer);$mailOrder=$orders->create($buyer,$input($buyer));$mailId=(int)$mailOrder['id_pedido'];
$orders->transition($employee,$mailId,'confirmado','pendiente','Aceptar y enviar QR');$mailOrder=$orders->find($mailId,$buyer,false);
putenv('ORDER_PUBLIC_URL=http://127.0.0.1:18014');putenv('MAIL_USERNAME=sender@example.test');
$png=OrderMail::qr($mailOrder['codigo_pedido']);$qrFile=$storage.'/qr.png';file_put_contents($qrFile,$png);
$decoded=(new Zxing\QrReader($qrFile))->text();
catalogCheck(str_starts_with($png,"\x89PNG") && $decoded===OrderMail::url($mailOrder['codigo_pedido']),'imagen QR decodificada contiene el enlace con el código exacto del pedido');
$sent=0;$mailer=new OrderMail($runtime,static function($message) use(&$sent,$mailOrder):void {
    $sent++;$message->preSend();
    catalogCheck($message->getToAddresses()[0][0]==='client@catalog.example.test' && str_contains($message->Body,$mailOrder['codigo_pedido']) && str_contains($message->Body,'cid:pedido-qr') && str_contains($message->AltBody,$mailOrder['total']) && str_contains($message->getSentMIMEMessage(),'image/png'),'correo contiene destinatario, productos, total, código y QR incrustado');
});
catalogCheck($mailer->sendOne($mailId)==='enviado' && $mailer->sendOne($mailId)==='none' && $sent===1,'reintentar correo confirmado no duplica envío');
[$status]=catalogRequest('/orders/scan?codigo='.$mailOrder['codigo_pedido'],$cookie);catalogCheck($status===302,'QR abre el detalle para administrador');
[$status]=catalogRequest('/orders/scan?codigo='.$mailOrder['codigo_pedido'],$otherCookie);catalogCheck($status===404,'QR no permite que otro cliente consulte datos');
$staffCookie='';$staffToken=catalogLogin('staff@orders.example.test',$staffCookie);
[$status]=catalogRequest('/admin/orders',$staffCookie);catalogCheck($status===200,'empleado con permiso explícito accede al lector y pedidos');
[$status]=catalogRequest('/orders/scan?codigo='.$mailOrder['codigo_pedido'],$staffCookie);catalogCheck($status===302,'empleado autorizado abre el pedido al escanear');
[$status]=catalogRequest('/admin/inventory',$staffCookie);catalogCheck($status===403,'permiso de pedidos no concede acceso a inventario');
$admin->exec("UPDATE tbusuarios SET estado='inactivo' WHERE correo='staff@orders.example.test'");[$status]=catalogRequest('/admin/orders',$staffCookie);catalogCheck($status===302,'baja de empleado revoca acceso en la siguiente solicitud');
$setCart($buyer);$failedMail=$orders->create($buyer,$input($buyer));$failedId=(int)$failedMail['id_pedido'];$orders->transition($employee,$failedId,'confirmado','pendiente','Probar fallo de correo');
$failed=new OrderMail($runtime,static function(){throw new RuntimeException('Fallo simulado de SMTP');});
catalogCheck($failed->sendOne($failedId)==='pending' && $orders->find($failedId,$buyer,false)['estado']==='confirmado' && $admin->query('SELECT estado FROM tbretiocorreos WHERE id_pedido='.$failedId)->fetchColumn()==='pendiente','fallo SMTP conserva pedido confirmado y deja correo pendiente');
$admin->exec('UPDATE tbretiocorreos SET proximo_intento=UTC_TIMESTAMP() WHERE id_pedido='.$failedId);$orders->transition($employee,$failedId,'cancelado','confirmado','Cancelar antes del reintento');
catalogCheck($mailer->sendOne($failedId)==='omitido' && $sent===1,'no envía confirmaciones pendientes de pedidos cancelados');
$setCart($buyer);$expiry=$orders->create($buyer,$input($buyer));$expiryId=(int)$expiry['id_pedido'];$admin->exec('UPDATE tbpedidos SET fecha_vencimiento=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 SECOND) WHERE id_pedido='.$expiryId);
catalogCheck($race([['action'=>'expire'],['action'=>'expire']])===['saved','saved'] && count($orders->find($expiryId,$buyer,false)['history'])===2,'vencimientos concurrentes liberan y registran una única vez');
$setCart($buyer);$atomic=$orders->create($buyer,$input($buyer));$atomicId=(int)$atomic['id_pedido'];
try{$admin->exec("CREATE TRIGGER mail_queue_failure BEFORE INSERT ON tbretiocorreos FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='queue rollback test'");try{$orders->transition($employee,$atomicId,'confirmado','pendiente','Fallo controlado de cola');throw new LogicException('Falta rollback de cola');}catch(PDOException){}$after=$orders->find($atomicId,$buyer,false);catalogCheck($after['estado']==='pendiente' && count($after['history'])===1,'fallo al encolar correo revierte estado e historial de confirmación');}finally{$admin->exec('DROP TRIGGER IF EXISTS mail_queue_failure');}
$orders->transition($employee,$atomicId,'confirmado','pendiente','Cola lista para concurrencia');$mailFile=$storage.'/mail-race.txt';$mailJob=['action'=>'mail','id'=>$atomicId,'file'=>$mailFile];
catalogCheck($race([$mailJob,$mailJob])===['busy','saved'] && file_get_contents($mailFile)==="sent\n",'dos procesos de correo reclaman un único envío');
try{$runtime->exec('DELETE FROM tbretiocorreos');throw new LogicException('Permiso excesivo en cola');}catch(PDOException){catalogCheck(true,'cuenta SQL no puede borrar la cola de correo');}
$setCart($buyer);$late=$orders->create($buyer,$input($buyer));$lateId=(int)$late['id_pedido'];$orders->transition($employee,$lateId,'confirmado','pendiente','Probar correo vencido');$admin->exec('UPDATE tbpedidos SET fecha_vencimiento=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 SECOND) WHERE id_pedido='.$lateId);
catalogCheck($mailer->sendOne($lateId)==='omitido' && $orders->find($lateId,$buyer,false)['estado']==='cancelado','procesador de correos libera vencimientos y omite una confirmación fuera de plazo');
echo "Pruebas del Plan 6 completadas.\n";
