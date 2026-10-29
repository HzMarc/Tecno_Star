<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database/cli.php';
require_once BASE_PATH . '/app/Models/ProductModel.php';

function storeCheck(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException('FALLO: ' . $message);
    echo "OK: $message\n";
}

function storeRequest(string $path, string &$cookie, ?array $data = null, ?string $csrf = null): array
{
    if ($path === '/shopping/update' && $data !== null && !isset($data['operation_id'])) {
        [, $stateBody] = storeRequest('/shopping/state', $cookie);
        $state = json_decode($stateBody, true, 512, JSON_THROW_ON_ERROR);
        $data += ['operation_id'=>bin2hex(random_bytes(32)), 'target_user_id'=>$state['user_id'], 'expected_revision'=>$state['revision']];
    }
    $headers = ['Connection: close'];
    if ($cookie !== '') $headers[] = 'Cookie: ' . $cookie;
    if ($csrf !== null) $headers[] = 'X-CSRF-Token: ' . $csrf;
    $json = $csrf !== null;
    if ($data !== null) $headers[] = 'Content-Type: ' . ($json ? 'application/json' : 'application/x-www-form-urlencoded');
    $context = stream_context_create(['http' => ['method' => $data === null ? 'GET' : 'POST', 'header' => implode("\r\n", $headers), 'content' => $data === null ? '' : ($json ? json_encode($data, JSON_THROW_ON_ERROR) : http_build_query($data)), 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 10]]);
    $body = file_get_contents('http://127.0.0.1:18014' . $path, false, $context);
    if ($body === false) throw new RuntimeException('Inicia primero el servidor aislado de pruebas en el puerto 18014.');
    $status = 0;
    foreach ($http_response_header as $header) {
        if (preg_match('~^HTTP/\S+ (\d+)~', $header, $match)) $status = (int) $match[1];
        if (preg_match('/^Set-Cookie: (TECNO_STAR_SESSION=[^;]*)/i', $header, $match)) $cookie = $match[1];
    }
    return [$status, $body];
}

function storeLogin(string $email, string &$cookie): string
{
    [, $html] = storeRequest('/login', $cookie);
    preg_match('/name="_csrf" value="([a-f0-9]{64})"/', $html, $token);
    [$status] = storeRequest('/login', $cookie, ['email' => $email, 'password' => 'StorePreview2026!', '_csrf' => $token[1] ?? '']);
    storeCheck($status === 302, 'Inicio de sesión de ' . $email);
    [, $html] = storeRequest('/store', $cookie);
    preg_match('/data-csrf-token="([a-f0-9]{64})"/', $html, $token);
    if (empty($token[1])) throw new RuntimeException('Falta token de compras.');
    return $token[1];
}

$database = null;
$permissionChanged = false;
$productsChanged = false;
$exit = 0;
try {
    $path = trim((string) file_get_contents(BASE_PATH . '/storage/store-preview-path.txt'));
    $metadata = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (!preg_match('/^tecno_star_test_store_[a-zA-Z0-9_]{1,20}$/D', $metadata['database']) || !preg_match('/^ts_store_[a-f0-9]{10}$/D', $metadata['user'])) throw new RuntimeException('Solo se permite la base y cuenta desechables preparadas por storefront-setup.php.');
    $database = migrationConnection(array_replace($metadata, ['user' => migrationCredentials()[0]]));
    $table = '`' . $metadata['database'] . '`.tbproductos';
    $account = $database->quote($metadata['user']) . "@'localhost'";
    $catalog = new ProductModel($database);
    storeCheck(count($catalog->catalog()) === 6 && count($catalog->catalog(true)) === 8, 'El catálogo público excluye agotados; la consulta completa conserva los registros');
    $cookieA = $cookieB = '';
    $csrfA = storeLogin('store-a@example.test', $cookieA);
    $csrfB = storeLogin('store-b@example.test', $cookieB);
    [, $html] = storeRequest('/store', $cookieA);
    storeCheck(str_contains($html, 'Hola, Alex.') && substr_count($html, 'data-product-card') === 8 && str_contains($html,'data-product-id="7"') && str_contains($html,'data-product-id="8"'), 'Contenido autenticado conserva las tarjetas de productos agotados');
    storeCheck(substr_count($html, 'store-card__badge--empty">Agotado') === 2 && substr_count($html, 'data-add-cart disabled') === 2, 'Agotados con etiqueta visible y compra deshabilitada');
    storeCheck(substr_count($html, 'class="store-new-product"') === 2 && !str_contains($html, 'data-catalog-available'), 'Novedades disponibles y sin filtro de disponibilidad redundante');
    storeCheck(!str_contains($html, '30%'), 'Sin promociones inventadas');
    try {
        $database->exec('UPDATE tbinventario SET stock_actual=10,stock_reservado=3 WHERE id_producto=1');
        [, $freshStock] = storeRequest('/store', $cookieA);
        storeCheck(str_contains($freshStock,'data-product-stock="7"') && str_contains($freshStock,'Disponible · 7'),'Tarjeta muestra el stock físico menos las reservas desde la BDD');
        foreach(['10.000','9.500'] as $reserved) {
            $database->exec('UPDATE tbinventario SET stock_reservado='.$reserved.' WHERE id_producto=1');
            [, $noStock] = storeRequest('/store', $cookieA);
            storeCheck(str_contains($noStock,'data-product-id="1"') && substr_count($noStock,'data-product-card')===8 && substr_count($noStock,'store-card__badge--empty">Agotado')===3 && substr_count($noStock,'data-add-cart disabled')===3,'Sin unidades completas disponibles el producto permanece visible como agotado, incluso con reservas');
        }
        $database->exec('UPDATE tbinventario SET stock_actual=25,stock_reservado=0 WHERE id_producto=1');
        [, $restocked] = storeRequest('/store', $cookieA);
        storeCheck(str_contains($restocked,'data-product-id="1"') && str_contains($restocked,'Disponible · 25') && substr_count($restocked,'store-card__badge--empty">Agotado')===2,'Al reponer o liberar stock se quita la etiqueta de agotado');
    } finally {$database->exec('UPDATE tbinventario SET stock_actual=25,stock_reservado=0 WHERE id_producto=1');}
    [, $stateA] = storeRequest('/shopping/state', $cookieA);
    [, $stateB] = storeRequest('/shopping/state', $cookieB);
    $beforeA = json_decode($stateA, true, 512, JSON_THROW_ON_ERROR);
    $beforeB = json_decode($stateB, true, 512, JSON_THROW_ON_ERROR);
    storeCheck(count($beforeA['cart']) > 0 && count($beforeA['wishlist']) > 0 && $beforeB['cart'] === [] && $beforeB['wishlist'] === [], 'Cuenta con datos y cuenta vacía separadas');
    [$status] = storeRequest('/shopping/update', $cookieA, ['action' => 'wishlist_add', 'product_id' => 5], $csrfA);
    storeCheck($status === 200, 'Favorito guardado con CSRF');
    $freshCookie = '';
    $freshCsrf = storeLogin('store-a@example.test', $freshCookie);
    [, $persisted] = storeRequest('/shopping/state', $freshCookie);
    storeCheck(in_array(5, array_column(json_decode($persisted, true)['wishlist'], 'id')), 'El favorito persiste en una nueva sesión');
    [, $unchangedB] = storeRequest('/shopping/state', $cookieB);
    storeCheck(json_decode($unchangedB, true) === $beforeB, 'Los cambios de A no llegan a B');
    [$status] = storeRequest('/shopping/update', $cookieA, ['action' => 'cart_add', 'product_id' => 7, 'quantity' => 1], $csrfA);
    storeCheck($status === 422, 'El servidor también rechaza añadir un agotado');
    [$status] = storeRequest('/shopping/update', $cookieA, ['action' => 'cart_add', 'product_id' => 1, 'quantity' => 1], str_repeat('0', 64));
    storeCheck($status === 403, 'Protección CSRF conservada');
    storeRequest('/shopping/update', $freshCookie, ['action' => 'wishlist_remove', 'product_id' => 5], $freshCsrf);
    $productsChanged = true;
    $database->exec("UPDATE tbproductos SET estado = 'inactivo'");
    [$status, $empty] = storeRequest('/store', $cookieA);
    storeCheck($status === 200 && str_contains($empty, 'No hay productos disponibles por ahora') && !str_contains($empty, 'data-product-card'), 'Estado de catálogo vacío');
    $database->exec("UPDATE tbproductos SET estado = 'activo'");
    $productsChanged = false;
    $permissionChanged = true;
    $database->exec("REVOKE SELECT ON $table FROM $account");
    [$status, $error] = storeRequest('/store', $cookieA);
    storeCheck($status === 503 && str_contains($error, 'No pudimos cargar el catálogo') && !str_contains($error, 'SQLSTATE'), 'Error de catálogo con orientación y sin detalles internos');
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    $exit = 1;
} finally {
    if ($database && $productsChanged) $database->exec("UPDATE tbproductos SET estado = 'activo'");
    if ($database && $permissionChanged) $database->exec("GRANT SELECT ON $table TO $account");
}
exit($exit);
