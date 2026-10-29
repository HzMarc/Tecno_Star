<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database/cli.php';
require_once BASE_PATH . '/database/MigrationRunner.php';
require_once BASE_PATH . '/database/RestrictedUser.php';
require_once BASE_PATH . '/app/Models/CatalogAdminModel.php';
require_once BASE_PATH . '/app/Models/ProductModel.php';
require_once BASE_PATH . '/app/Models/ShoppingModel.php';
require_once BASE_PATH . '/app/Models/UserModel.php';
require_once BASE_PATH . '/core/CatalogImage.php';

function catalogCheck(bool $ok, string $message): void
{
    if (!$ok) throw new RuntimeException('FALLO: ' . $message);
    echo 'OK: ' . $message . "\n";
}
function catalogRequest(string $path, string &$cookie, ?array $data=null, ?array $file=null): array
{
    $headers=['Connection: close']; $body='';
    if ($cookie !== '') $headers[]='Cookie: ' . $cookie;
    if ($data !== null) {
        if ($file !== null) {
            $boundary='CatalogTest' . bin2hex(random_bytes(10));
            $headers[]='Content-Type: multipart/form-data; boundary=' . $boundary;
            foreach ($data as $key=>$value) $body.="--$boundary\r\nContent-Disposition: form-data; name=\"$key\"\r\n\r\n$value\r\n";
            $body.="--$boundary\r\nContent-Disposition: form-data; name=\"image\"; filename=\"" . $file['name'] . "\"\r\nContent-Type: " . $file['type'] . "\r\n\r\n" . $file['bytes'] . "\r\n--$boundary--\r\n";
        } else { $headers[]='Content-Type: application/x-www-form-urlencoded'; $body=http_build_query($data); }
    }
    $context=stream_context_create(['http'=>['method'=>$data===null?'GET':'POST','header'=>implode("\r\n",$headers),'content'=>$body,'ignore_errors'=>true,'follow_location'=>0,'timeout'=>10]]);
    $result=file_get_contents('http://127.0.0.1:18014' . $path,false,$context);
    if ($result===false) throw new RuntimeException('El servidor de prueba no respondió.');
    $status=0;
    foreach ($http_response_header as $header) {
        if (preg_match('~^HTTP/\S+ (\d+)~',$header,$m)) $status=(int)$m[1];
        if (preg_match('/^Set-Cookie: (TECNO_STAR_SESSION=[^;]*)/i',$header,$m)) $cookie=$m[1];
    }
    return [$status,$result,$http_response_header];
}
function catalogToken(string $html): string
{
    if (!preg_match('/name="_csrf" value="([a-f0-9]+)"/',$html,$match)) throw new RuntimeException('Falta token CSRF.');
    return $match[1];
}
function catalogLogin(string $email, string &$cookie): string
{
    [, $html]=catalogRequest('/login',$cookie);
    [$status]=catalogRequest('/login',$cookie,['email'=>$email,'password'=>'CatalogPreview2026!','_csrf'=>catalogToken($html)]);
    catalogCheck($status===302,'inicio de sesión de cuenta sintética');
    [, $html]=catalogRequest('/store',$cookie);
    return catalogToken($html);
}
$process=null; $admin=null; $account=null; $uploaded=[]; $exitCode=1;
$storage=sys_get_temp_dir() . '/tecno_catalog_' . bin2hex(random_bytes(8));
mkdir($storage,0700); mkdir($storage . '/sessions',0700);
try {
    $options=migrationOptions();
    if (!preg_match('/^tecno_star_test_catalog_[a-zA-Z0-9_]{1,16}$/D',$options['database'])) throw new RuntimeException('Usa una base NUEVA con prefijo tecno_star_test_catalog_.');
    $server=migrationConnection(array_replace($options,['database'=>'information_schema']));
    $find=$server->prepare('SELECT COUNT(*) FROM SCHEMATA WHERE SCHEMA_NAME=?'); $find->execute([$options['database']]);
    if ((int)$find->fetchColumn()!==0) throw new RuntimeException('La base ya existe. Utiliza otro nombre.');
    $admin=migrationConnection($options+['create'=>true],true);
    (new MigrationRunner($admin,BASE_PATH . '/database/migrations'))->migrate();
    $runtimeUser='ts_cat_' . bin2hex(random_bytes(5)); $runtimePassword=bin2hex(random_bytes(32));
    RestrictedUser::create($admin,$options['database'],$runtimeUser,'localhost',$runtimePassword);
    $account=$admin->quote($runtimeUser) . "@'localhost'";
    $runtime=new PDO('mysql:host=' . $options['host'] . ';port=' . $options['port'] . ';dbname=' . $options['database'] . ';charset=utf8mb4',$runtimeUser,$runtimePassword,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    $model=new CatalogAdminModel($runtime);
    $model->saveEntity('categories',null,'Audio','activo'); $model->saveEntity('brands',null,'Tecno Star','activo');
    $users=new UserModel($admin);
    foreach (['admin','client'] as $role) $users->createCustomer('catalog-' . $role,'Catálogo',ucfirst($role),'70000000',$role . '@catalog.example.test',password_hash('CatalogPreview2026!',PASSWORD_DEFAULT));
    $admin->exec("UPDATE tbusuarios SET id_rol=(SELECT id_rol FROM tbroles WHERE nombre_rol='Administrador') WHERE correo='admin@catalog.example.test'");
    $fixture=['codigo_producto'=>'CAT-001','nombre_producto'=>'Audífonos de prueba','descripcion'=>'Audio para tu día a día. Producto de prueba.','precio_compra'=>'8.25','precio_venta'=>'24.99','id_categoria'=>'1','id_marca'=>'1','estado'=>'activo'];
    $productId=$model->save($model->validate($fixture),null,null);
    catalogCheck((int)$admin->query('SELECT stock_actual FROM tbinventario WHERE id_producto=' . $productId)->fetchColumn()===0,'producto e inventario cero creados con cuenta limitada');
    for($i=2;$i<=14;$i++) $model->save($model->validate(array_replace($fixture,['codigo_producto'=>'CAT-' . str_pad((string)$i,3,'0',STR_PAD_LEFT),'nombre_producto'=>'Accesorio de prueba ' . $i])),null,null);
    $model->saveEntity('categories',null,'Cables','activo'); $model->saveEntity('brands',null,'Otra marca','activo');
    $other=$model->save($model->validate(array_replace($fixture,['codigo_producto'=>'OTHER','nombre_producto'=>'Cable USB de prueba','id_categoria'=>'2','id_marca'=>'2'])),null,null);
    catalogCheck(count($model->listing([])['products'])===12 && $model->listing([])['pages']===2 && count($model->listing(['page'=>'2'])['products'])===3,'paginación real desde SQL sin perder resultados');
    catalogCheck($model->listing(['q'=>'CAT-001','category'=>'1','brand'=>'1','status'=>'activo'])['total']===1,'búsqueda y filtros combinados en servidor');
    catalogCheck($model->listing(['q'=>"' OR 1=1 --"])['total']===0 && $model->listing(['q'=>'%'])['total']===0,'búsqueda parametrizada y comodines literales');
    $admin->exec('UPDATE tbinventario SET stock_actual=10 WHERE id_producto=' . $productId);
    $clientId=(int)$users->findActiveByEmail('client@catalog.example.test')['id_usuario'];
    $shopping=new ShoppingModel($runtime); $shopping->mutate($clientId,['action'=>'cart_add','product_id'=>$productId]); $shopping->mutate($clientId,['action'=>'wishlist_add','product_id'=>$productId]);
    $referenceCount=(int)$admin->query('SELECT COUNT(*) FROM tbdetallecarritos')->fetchColumn();
    $model->saveEntity('brands',1,'Tecno Star','inactivo');
    catalogCheck((new ProductModel($runtime))->findById($productId)===null && $shopping->state($clientId)['cart'][0]['available']===false,'marca inactiva oculta detalle y avisa en carrito conservando el producto');
    catalogCheck((int)$admin->query('SELECT COUNT(*) FROM tbdetallecarritos')->fetchColumn()===$referenceCount,'desactivar conserva referencias históricas');
    $model->saveEntity('brands',1,'Tecno Star','activo');
    $model->saveEntity('categories',1,'Audio','inactivo');
    catalogCheck((new ProductModel($runtime))->findById($productId)===null,'categoría inactiva oculta productos');
    $model->saveEntity('categories',1,'Audio','activo');
    catalogCheck((new ProductModel($runtime))->findById($productId)!==null,'reactivar restaura visibilidad sin reconstruir referencias');
    foreach (['DELETE FROM tbproductos WHERE id_producto=1','UPDATE tbinventario SET id_producto=id_producto','INSERT INTO tbinventario (id_producto,stock_actual) VALUES (99999,10)'] as $sql) {
        try { $runtime->exec($sql); throw new LogicException('Permiso excesivo.'); } catch(PDOException) { catalogCheck(true,'cuenta limitada sin DELETE de productos, cambio de referencias ni altas con stock arbitrario'); }
    }
    $socket=@stream_socket_client('tcp://127.0.0.1:18014',$errno,$error,.2);
    if($socket!==false) { fclose($socket); throw new RuntimeException('Puerto HTTP temporal ocupado.'); }
    $testStaff='';
    if(in_array('--orders',$argv,true)) {
        $users->createCustomer('order-staff','Empleado','Autorizado','70000000','staff@orders.example.test',password_hash('CatalogPreview2026!',PASSWORD_DEFAULT));
        $admin->exec("UPDATE tbusuarios SET id_rol=(SELECT id_rol FROM tbroles WHERE nombre_rol='Empleado') WHERE correo='staff@orders.example.test'");
        $testStaff=(string)$users->findActiveByEmail('staff@orders.example.test')['id_usuario'];
    }
    $environment=array_replace(getenv(),['APP_URL'=>'/','DB_HOST'=>$options['host'],'DB_PORT'=>(string)$options['port'],'DB_NAME'=>$options['database'],'DB_USER'=>$runtimeUser,'DB_PASSWORD'=>$runtimePassword,'APP_STORAGE_PATH'=>$storage,'LOGIN_PAIR_LIMIT'=>'20','LOGIN_IP_LIMIT'=>'1000','MAIL_ENABLED'=>'0','ORDER_PUBLIC_URL'=>'http://127.0.0.1:18014','ORDER_STAFF_USER_IDS'=>$testStaff]);
    $environment=array_replace($environment,['GOOGLE_CLIENT_ID'=>'','GOOGLE_CLIENT_SECRET'=>'','GOOGLE_REDIRECT_URI'=>'']);
    $process=proc_open([PHP_BINARY,'-d','session.save_path=' . $storage . '/sessions','-d','upload_max_filesize=5M','-d','post_max_size=8M','-S','127.0.0.1:18014','-t','public','tests/storefront-router.php'],[0=>['pipe','r'],1=>['file',$storage . '/http.log','a'],2=>['file',$storage . '/http-error.log','a']],$pipes,BASE_PATH,$environment);
    if(!is_resource($process)) throw new RuntimeException('No se pudo iniciar PHP temporal.'); fclose($pipes[0]);
    $ready=false; for($i=0;$i<40;$i++) { $socket=@stream_socket_client('tcp://127.0.0.1:18014',$errno,$error,.1); if($socket!==false) { fclose($socket); $ready=true; break; } usleep(100000); }
    if(!$ready) throw new RuntimeException('PHP temporal no responde.');
    $guest=''; [$status]=catalogRequest('/admin/products',$guest); catalogCheck($status===403,'visitante sin acceso al catálogo administrativo');
    $client=''; $clientToken=catalogLogin('client@catalog.example.test',$client);
    foreach(['/admin/products','/admin/products/edit','/admin/catalog?kind=brands'] as $path) { [$status]=catalogRequest($path,$client); catalogCheck($status===403,'cliente sin acceso de lectura administrativa'); }
    foreach(['/admin/products/save','/admin/products/state','/admin/catalog/save'] as $path) { [$status]=catalogRequest($path,$client,['id'=>(string)$productId,'estado'=>'inactivo','_csrf'=>$clientToken]); catalogCheck($status===403,'cliente no puede modificar catálogo ni con CSRF válido'); }
    $cookie=''; $token=catalogLogin('admin@catalog.example.test',$cookie);
    [$status,$tableJson]=catalogRequest('/admin/tables/data?kind=products&draw=7&start=0&length=12',$cookie);
    $tableData=json_decode($tableJson,true,512,JSON_THROW_ON_ERROR);
    catalogCheck($status===200 && $tableData['draw']===7 && $tableData['recordsTotal']===15 && $tableData['recordsFiltered']===15 && count($tableData['data'])===12,'DataTables devuelve conteos globales y primera página del servidor');
    [$status,$tableJson]=catalogRequest('/admin/tables/data?kind=products&draw=8&start=12&length=12',$cookie);
    catalogCheck($status===200 && count(json_decode($tableJson,true,512,JSON_THROW_ON_ERROR)['data'])===3,'DataTables consulta la segunda página completa');
    [$status,$tableJson]=catalogRequest('/admin/tables/data?' . http_build_query(['kind'=>'products','draw'=>'9','search'=>['value'=>'CAT-001'],'category'=>'1','brand'=>'1','status'=>'activo']),$cookie);
    $tableData=json_decode($tableJson,true,512,JSON_THROW_ON_ERROR);
    catalogCheck($status===200 && $tableData['recordsTotal']===15 && $tableData['recordsFiltered']===1 && count($tableData['data'])===1,'DataTables busca y filtra sobre todo el catálogo');
    foreach ([$guest,$client] as $deniedCookie) {
        [$status,$tableJson]=catalogRequest('/admin/tables/data?kind=products',$deniedCookie);
        catalogCheck($status===403 && isset(json_decode($tableJson,true,512,JSON_THROW_ON_ERROR)['error']),'endpoint DataTables rechaza visitante y cliente con JSON');
    }
    foreach ([['draw'=>'<script>'],['length'=>'100000'],['order'=>[['column'=>'0;DROP TABLE tbproductos','dir'=>'asc']]],['order'=>[['column'=>'0','dir'=>'DROP']]],['kind'=>'mysql.user'],['search'=>['value'=>'x','regex'=>'true']]] as $invalidTable) {
        [$status]=catalogRequest('/admin/tables/data?' . http_build_query(array_replace(['kind'=>'products'],$invalidTable)),$cookie);
        catalogCheck($status===422,'DataTables rechaza parámetros de tabla, orden o paginación inválidos');
    }
    [$status,$html]=catalogRequest('/admin/products?page=2',$cookie); catalogCheck($status===200 && str_contains($html,'Página 2 de 2'),'administrador consulta páginas reales');
    [$status]=catalogRequest('/admin/products/save',$cookie,$fixture); catalogCheck($status===403,'guardar sin CSRF se rechaza');
    $post=$fixture+['_csrf'=>$token];
    foreach ([['precio_venta'=>'-1'],['precio_venta'=>'1.234'],['codigo_producto'=>'../mal'],['nombre_producto'=>''],['id_categoria'=>'999999'],['estado'=>'otro'],['precio_venta'=>['1']],['codigo_producto'=>'cat-001']] as $invalid) {
        [$status]=catalogRequest('/admin/products/save',$cookie,array_replace($post,$invalid)); catalogCheck($status===422,'datos inválidos o código duplicado rechazados desde HTTP');
    }
    catalogCheck((int)$admin->query('SELECT COUNT(*) FROM tbproductos')->fetchColumn()===15,'validaciones fallidas no crean productos parciales');
    [$status]=catalogRequest('/admin/catalog/save',$cookie,['kind'=>'brands','name'=>'tecno star','estado'=>'activo','_csrf'=>$token]); catalogCheck($status===422,'marca duplicada rechazada');
    [$status]=catalogRequest('/admin/catalog/save',$cookie,['kind'=>'categories','name'=>'Papelería','estado'=>'activo','_csrf'=>$token]); catalogCheck($status===303,'crear categoría desde formulario');
    $newPost=array_replace($post,['codigo_producto'=>'HTTP-001','nombre_producto'=>'Producto con imagen']);
    [$status]=catalogRequest('/admin/products/save',$cookie,$newPost,['name'=>'excede-post.png','type'=>'image/png','bytes'=>str_repeat('A',9*1024*1024)]);
    catalogCheck($status===413,'formulario mayor que el límite PHP recibe error claro de tamaño');
    foreach ([['name'=>'archivo.jpg','type'=>'image/jpeg','bytes'=>'<?php echo "mal";'],['name'=>'imagen.svg','type'=>'image/svg+xml','bytes'=>'<svg xmlns="http://www.w3.org/2000/svg"></svg>'],['name'=>'grande.png','type'=>'image/png','bytes'=>str_repeat('A',4*1024*1024+1)]] as $badFile) {
        [$status]=catalogRequest('/admin/products/save',$cookie,$newPost,$badFile); catalogCheck($status===422,'imagen falsa, SVG o demasiado grande rechazada');
    }
    $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
    [$status]=catalogRequest('/admin/products/save',$cookie,$newPost,['name'=>'../../foto.php','type'=>'text/plain','bytes'=>$png]);
    catalogCheck($status===303,'imagen real aceptada según contenido y no según nombre o MIME declarado');
    $created=$admin->query("SELECT * FROM tbproductos WHERE codigo_producto='HTTP-001'")->fetch(); $uploaded[]=$created['imagen'];
    catalogCheck((bool)preg_match('~^/uploads/products/[a-f0-9]{48}\.png$~D',$created['imagen']),'nombre aleatorio y extensión segura sin usar nombre original');
    [$status,$html]=catalogRequest('/product?id=' . $created['id_producto'],$guest); catalogCheck($status===200 && str_contains($html,$created['imagen']),'producto válido aparece en la tienda con su imagen');
    [$status]=catalogRequest($created['imagen'],$guest); catalogCheck($status===200,'imagen subida accesible por URL pública');
    $edit=array_replace($newPost,['id'=>(string)$created['id_producto'],'precio_venta'=>'35.50']);
    [$status]=catalogRequest('/admin/products/save',$cookie,$edit,['name'=>'nueva.png','type'=>'image/png','bytes'=>$png]);
    $updated=$model->find((int)$created['id_producto']); $uploaded[]=$updated['imagen'];
    catalogCheck($status===303 && $updated['imagen']!==$created['imagen'] && $updated['precio_venta']==='35.50','reemplazo de imagen y precio guardados');
    [$status,$tableJson]=catalogRequest('/admin/tables/data?' . http_build_query(['kind'=>'products','order'=>[['column'=>'3','dir'=>'desc']]]),$cookie);
    catalogCheck($status===200 && str_contains(json_decode($tableJson,true,512,JSON_THROW_ON_ERROR)['data'][0][3],'35.50'),'DataTables ordena importes numéricamente en SQL');
    [$status,$tableJson]=catalogRequest('/admin/tables/data?kind=inventory&start=12&length=12',$cookie);
    $tableData=json_decode($tableJson,true,512,JSON_THROW_ON_ERROR);
    catalogCheck($status===200 && $tableData['recordsTotal']===16 && count($tableData['data'])===4,'inventario usa DataTables sobre todos sus registros');
    [$status,$html]=catalogRequest('/admin/inventory',$cookie);
    catalogCheck($status===200 && str_contains($html,'admin-inventory-table') && str_contains($html,'aria-label="Existencias por producto"'),'página de inventario disponible con tabla paginada');
    [$status]=catalogRequest('/admin/products/save',$cookie,array_replace($edit,['codigo_producto'=>'CAT-001']),['name'=>'rollback.png','type'=>'image/png','bytes'=>$png]);
    catalogCheck($status===422 && $model->find((int)$created['id_producto'])['imagen']===$updated['imagen'],'fallo al guardar conserva imagen anterior');
    [$status]=catalogRequest('/admin/products/save',$cookie,$edit+['remove_image'=>'1']); catalogCheck($status===303 && $model->find((int)$created['id_producto'])['imagen']===null,'quitar imagen sin eliminar producto');
    [$status]=catalogRequest('/admin/products/state',$cookie,['id'=>(string)$productId,'estado'=>'inactivo','_csrf'=>$token]);
    catalogCheck($status===303 && (new ProductModel($runtime))->findById($productId)===null && (int)$admin->query('SELECT stock_actual FROM tbinventario WHERE id_producto=' . $productId)->fetchColumn()===10,'desactivar desde HTTP conserva existencias');
    $model->saveEntity('categories',1,'Audio','inactivo');
    [$status]=catalogRequest('/admin/products/state',$cookie,['id'=>(string)$productId,'estado'=>'activo','_csrf'=>$token]); catalogCheck($status===422,'no se activa producto con categoría inactiva');
    $model->saveEntity('categories',1,'Audio','activo'); $model->setProductState($productId,'activo');
    [$status]=catalogRequest('/admin/catalog/save',$cookie,['kind'=>'brands','id'=>'2','name'=>'Marca editada','estado'=>'inactivo','_csrf'=>$token]);
    catalogCheck($status===303 && (new ProductModel($runtime))->findById($other)===null,'editar y desactivar marca por HTTP oculta sus productos');
    [$status]=catalogRequest('/admin/catalog/save',$cookie,['kind'=>'brands','id'=>'2','name'=>'Marca editada','estado'=>'activo','_csrf'=>$token]);
    catalogCheck($status===303 && (new ProductModel($runtime))->findById($other)!==null,'reactivar marca por HTTP restaura sus productos');
    for($i=1;$i<=12;$i++) $model->saveEntity('brands',null,'Marca de página ' . $i,'activo');
    [$status,$html]=catalogRequest('/admin/catalog?kind=brands&page=2',$cookie);
    catalogCheck($status===200 && str_contains($html,'Página 2 de 2'),'marcas con paginación del servidor');
    [$status,$tableJson]=catalogRequest('/admin/tables/data?kind=brands&start=12&length=12',$cookie);
    catalogCheck($status===200 && count(json_decode($tableJson,true,512,JSON_THROW_ON_ERROR)['data'])===2,'marcas consultan páginas DataTables más allá de las doce iniciales');
    [$status,$tableJson]=catalogRequest('/admin/tables/data?kind=categories',$cookie);
    catalogCheck($status===200 && json_decode($tableJson,true,512,JSON_THROW_ON_ERROR)['recordsTotal']===3,'categorías disponibles mediante DataTables');
    $model->saveEntity('brands',null,'<script>alert(1)</script>','activo');
    [$status,$tableJson]=catalogRequest('/admin/tables/data?' . http_build_query(['kind'=>'brands','search'=>['value'=>'<script>']]),$cookie);
    $tableData=json_decode($tableJson,true,512,JSON_THROW_ON_ERROR);
    catalogCheck($status===200 && str_contains($tableData['data'][0][0],'&lt;script&gt;') && !str_contains($tableData['data'][0][0],'<script>'),'celdas DataTables escapan nombres y atributos contra XSS');
    [$status,$html]=catalogRequest('/admin/catalog?kind=brands&q=editada&status=activo',$cookie);
    catalogCheck($status===200 && str_contains($html,'1 registros encontrados') && str_contains($html,'Marca editada'),'búsqueda y estado en marcas');
    [$status]=catalogRequest('/admin/products?category[]=1',$cookie); catalogCheck($status===422,'filtros estructurados inválidos rechazados sin error interno');
    $admin->exec("UPDATE tbusuarios SET id_rol=(SELECT id_rol FROM tbroles WHERE nombre_rol='Cliente') WHERE correo='admin@catalog.example.test'");
    [$status]=catalogRequest('/admin/products/state',$cookie,['id'=>(string)$productId,'estado'=>'inactivo','_csrf'=>$token]); catalogCheck($status===403,'revocación de administrador bloquea siguiente escritura');
    $admin->exec("UPDATE tbusuarios SET id_rol=(SELECT id_rol FROM tbroles WHERE nombre_rol='Administrador') WHERE correo='admin@catalog.example.test'");
    echo "Pruebas del catálogo completadas. Base de pruebas conservada.\n";
    if (in_array('--inventory',$argv,true)) require __DIR__ . '/inventory-checks.php';
    if (in_array('--shopping',$argv,true)) require __DIR__ . '/shopping-checks.php';
    if (in_array('--orders',$argv,true)) require __DIR__ . '/orders-checks.php';
    if (in_array('--accounts',$argv,true)) require __DIR__ . '/sales-accounts-checks.php';
    if (in_array('--customer',$argv,true)) require __DIR__ . '/customer-checks.php';
    if (in_array('--closure',$argv,true)) require __DIR__ . '/closure-checks.php';
    if (in_array('--purchase-cards',$argv,true)) require __DIR__ . '/purchase-cards-checks.php';
    if (in_array('--ready-mail',$argv,true)) require __DIR__ . '/ready-mail-checks.php';
    if(in_array('--preview',$argv,true)) { echo "Vista de prueba: http://127.0.0.1:18014/admin/products | admin@catalog.example.test / CatalogPreview2026!\nPulsa Enter para cerrar la vista temporal.\n"; fgets(STDIN); }
    $exitCode=0;
} catch(Throwable $exception) { fwrite(STDERR,$exception->getMessage() . "\n"); }
finally {
    if(is_resource($process)) { proc_terminate($process); proc_close($process); }
    if($admin instanceof PDO && $account!==null) $admin->exec('DROP USER ' . $account);
    foreach($uploaded as $path) CatalogImage::discard($path);
}
exit($exitCode);
