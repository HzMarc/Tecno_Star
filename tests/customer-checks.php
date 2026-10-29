<?php
declare(strict_types=1);
// Extensión de catalog.php: solo datos sintéticos de su base aislada.
if(!in_array('--orders',$argv,true) || !in_array('--accounts',$argv,true))throw new LogicException('--customer requiere --orders --accounts.');
$customerCookie='';$customerToken=catalogLogin('client@catalog.example.test',$customerCookie);
$customerId=(int)$users->findActiveByEmail('client@catalog.example.test')['id_usuario'];
$customerOrders=new OrderModel($runtime);$customerShop=new ShoppingModel($runtime);
$assertCustomerPage=static function(string $path)use(&$customerCookie):string {
    [$status,$html]=catalogRequest($path,$customerCookie);
    catalogCheck($status===200,'pantalla del cliente disponible: '.$path);
    catalogCheck(!preg_match('~<(?:a|form)\b[^>]*(?:href|action)="[^"]*/(?:admin(?:/|[?\"])|sales/(?:pay|cancel)|orders/mail)~',$html),'sin accesos ni formularios de administración en '.$path);
    return $html;
};
foreach(['/store','/product?id='.$productId,'/profile','/orders','/purchases','/'] as $path)$assertCustomerPage($path);
$profileHtml=$assertCustomerPage('/profile');
catalogCheck(str_contains($profileHtml,'/store#favoritos') && str_contains($profileHtml,'Mis pedidos') && !str_contains($profileHtml,'Tipo de cuenta'),'perfil con pedidos y favoritos funcionales, sin tipo de cuenta redundante');
[$status,$homeHtml]=catalogRequest('/',$customerCookie);
catalogCheck(str_contains($homeHtml,'Mi cuenta') && !str_contains($homeHtml,'>Crear cuenta</a>') && !str_contains($homeHtml,'>Iniciar sesión</a>'),'inicio reconoce al cliente autenticado');
foreach(['/admin','/admin/products','/admin/catalog?kind=brands','/admin/inventory','/admin/orders','/admin/sales','/admin/users','/admin/users/editor?id=1','/orders/data?scope=admin','/commerce/data?kind=sales','/commerce/data?kind=users'] as $path){[$status]=catalogRequest($path,$customerCookie);catalogCheck($status===403,'cliente sin acceso administrativo directo: '.$path);}
foreach(['/admin/products/state','/admin/catalog/save','/admin/inventory/move','/admin/users/save','/sales/pay','/sales/cancel','/orders/mail'] as $path){[$status]=catalogRequest($path,$customerCookie,['_csrf'=>$customerToken,'target_user_id'=>(string)$customerId]);catalogCheck($status===403,'cliente sin escritura administrativa aunque tenga CSRF: '.$path);}
$purchases=$assertCustomerPage('/purchases');
catalogCheck(!str_contains($purchases,'Caja · efectivo') && !str_contains($purchases,'Ventas pagadas') && !str_contains($purchases,'<th>Recibido</th>') && str_contains($purchases,'Importe reembolsado'),'compras propias con lenguaje y columnas para el cliente');
[$status,$json]=catalogRequest('/commerce/data?kind=purchases&order[0][column]=4&order[0][dir]=asc',$customerCookie);$table=json_decode($json,true,512,JSON_THROW_ON_ERROR);
catalogCheck($status===200 && count($table['data'])>0 && count($table['data'][0])===6,'DataTables de compras conserva orden, seis columnas y paginación');
$ownSale=(int)$admin->query('SELECT s.id_venta FROM tbventas s JOIN tbpedidos o ON o.id_pedido=s.id_pedido WHERE o.id_usuario='.$customerId.' AND s.cobro_solicitud IS NOT NULL ORDER BY s.id_venta DESC LIMIT 1')->fetchColumn();
$receipt=$assertCustomerPage('/sales/detail?id='.$ownSale);
catalogCheck(str_contains($receipt,'Tu comprobante') && !str_contains($receipt,'Anular venta') && !str_contains($receipt,'existencias restauradas'),'comprobante del cliente sin acciones de caja o inventario');
foreach($customerShop->state($customerId)['cart'] as $item)$customerShop->mutate($customerId,['action'=>'cart_remove','product_id'=>$item['id']]);
$customerShop->mutate($customerId,['action'=>'cart_add','product_id'=>$productId,'quantity'=>1]);
$checkout=$assertCustomerPage('/checkout');
$customerInput=['nombre'=>'Contacto del cliente','telefono'=>'7000-0000','correo'=>'client@catalog.example.test','nota'=>'Auditoría aislada del cliente','acepto'=>'1','solicitud'=>bin2hex(random_bytes(32)),'cotizacion'=>$customerOrders->quote($customerId)['fingerprint'],'_csrf'=>$customerToken,'target_user_id'=>(string)$customerId];
[$status,$json]=catalogRequest('/orders/confirm',$customerCookie,$customerInput);$confirmation=json_decode($json,true,512,JSON_THROW_ON_ERROR);
catalogCheck($status===200 && !empty($confirmation['ok']),'cliente confirma desde el carrito sin operaciones administrativas');
parse_str((string)parse_url($confirmation['location'],PHP_URL_QUERY),$query);$customerOrderId=(int)$query['id'];
$pending=$assertCustomerPage('/orders/detail?id='.$customerOrderId);
catalogCheck(str_contains($pending,'Cancelar pedido') && str_contains($pending,'Consultar estado') && !str_contains($pending,'<select name="estado"') && !str_contains($pending,'Cobrar y entregar') && !str_contains($pending,'Cambiar estado'),'pedido pendiente solo permite consulta y cancelación del cliente');
$customerOrders->transition($cashier,$customerOrderId,'confirmado','pendiente','Aceptar pedido sintético de revisión');
$customerOrders->transition($cashier,$customerOrderId,'en_preparacion','confirmado','Preparar pedido sintético de revisión');
$preparing=$assertCustomerPage('/orders/detail?id='.$customerOrderId);
catalogCheck(!str_contains($preparing,'data-order-open') && !str_contains($preparing,'/sales/pay'),'al comenzar preparación desaparece la cancelación y no aparece caja');
$customerOrders->transition($cashier,$customerOrderId,'listo','en_preparacion','Pedido sintético listo');
$ready=$assertCustomerPage('/orders/detail?id='.$customerOrderId);
catalogCheck(!str_contains($ready,'Cobrar y entregar') && str_contains($ready,'Tu pedido está listo.'),'cliente recibe instrucciones de retiro sin botones de cobro');
$customerOrders->transition($cashier,$customerOrderId,'cancelado','listo','Cerrar revisión automática del cliente');
$closed=$assertCustomerPage('/orders/detail?id='.$customerOrderId);
catalogCheck(!str_contains($closed,'data-order-refresh') && !str_contains($closed,'Conserva este número para retirar'),'pedido cerrado sin botón de estado ni instrucciones de retiro obsoletas');
$guestCookie='';
foreach(['/login','/register'] as $path){[$status,$html]=catalogRequest($path,$guestCookie);catalogCheck($status===200 && !str_contains($html,'auth-google-button') && !str_contains($html,'/auth/google'),'OAuth oculto sin credenciales en el servidor aislado: '.$path);}
[, $registrationForm]=catalogRequest('/register',$guestCookie);$registrationToken=catalogToken($registrationForm);
$registration=['names'=>'Nueva','last_names'=>'Cuenta de prueba','phone'=>'9998-8883','email'=>'customer-audit@example.test','password'=>'ClienteAuditoria2026!','password_confirmation'=>'ClienteAuditoria2026!','_csrf'=>$registrationToken];
[$status,$html]=catalogRequest('/register',$guestCookie,$registration);
catalogCheck($status===200 && str_contains($html,'El teléfono debe tener 8 dígitos') && !$users->existsByEmail($registration['email']),'registro rechaza teléfono incompatible con el retiro y no crea cuentas parciales');
$registration['phone']='+503 7000-0000';[$status,$html]=catalogRequest('/register',$guestCookie,$registration);
catalogCheck($status===200 && str_contains($html,'Cuenta creada correctamente') && $users->findActiveByEmail($registration['email'])['nombre_rol']==='Cliente','registro válido crea exclusivamente rol Cliente');
echo "Revisión HTTP del cliente, planes 1 a 7, completada.\n";
