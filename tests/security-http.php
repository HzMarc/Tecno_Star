<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database/cli.php';
require_once BASE_PATH . '/database/RestrictedUser.php';
ob_start();

function httpCheck(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FALLO: ' . $message);
    }
    echo 'OK: ' . $message . "\n";
}

function requestApp(string $base, string $path, string &$cookie, string $method = 'GET', array $data = [], ?string $token = null, bool $json = false, array $extraHeaders = []): array
{
    $headers = ['Connection: close'];
    $headers = array_merge($headers, $extraHeaders);
    if ($cookie !== '') {
        $headers[] = 'Cookie: ' . $cookie;
    }
    if ($token !== null) {
        $headers[] = 'X-CSRF-Token: ' . $token;
    }
    $body = '';
    if ($method === 'POST') {
        $headers[] = 'Content-Type: ' . ($json ? 'application/json' : 'application/x-www-form-urlencoded');
        $body = $json ? json_encode($data, JSON_THROW_ON_ERROR) : http_build_query($data);
    }
    $context = stream_context_create(['http' => ['method' => $method, 'header' => implode("\r\n", $headers), 'content' => $body, 'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 5]]);
    $output = @file_get_contents($base . $path, false, $context);
    if ($output === false) {
        throw new RuntimeException('No responde el servidor HTTP temporal.');
    }
    $status = 0;
    $location = '';
    foreach ($http_response_header as $header) {
        if (preg_match('~^HTTP/\S+ (\d+)~', $header, $match)) {
            $status = (int) $match[1];
        }
        if (preg_match('/^Set-Cookie: (TECNO_STAR_SESSION=[^;]*)/i', $header, $match)) {
            $cookie = $match[1];
        }
        if (stripos($header, 'Location: ') === 0) {
            $location = substr($header, 10);
        }
    }
    return [$status, $output, $location, $http_response_header];
}

function editTestSession(string $cookie, string $directory, string $mode): void
{
    if (!preg_match('/^TECNO_STAR_SESSION=([a-zA-Z0-9,-]+)$/D', $cookie, $match)) {
        throw new RuntimeException('Cookie de prueba invalida.');
    }
    ini_set('session.save_path', $directory);
    session_id($match[1]);
    session_start(['use_cookies' => 0, 'cache_limiter' => '']);
    if ($mode === 'idle') {
        $_SESSION['auth_last_seen_at'] = time() - SESSION_IDLE_SECONDS - 1;
    } elseif ($mode === 'absolute') {
        $_SESSION['auth_started_at'] = time() - SESSION_ABSOLUTE_SECONDS - 1;
    } elseif ($mode === 'renew') {
        $_SESSION['auth_started_at'] = time() - SESSION_REGENERATE_SECONDS - 2;
        $_SESSION['auth_regenerated_at'] = time() - SESSION_REGENERATE_SECONDS - 1;
    } elseif ($mode === 'legacy') {
        unset($_SESSION['auth_started_at'], $_SESSION['auth_last_seen_at'], $_SESSION['auth_regenerated_at']);
    }
    session_write_close();
}

function formToken(string $html): string
{
    if (!preg_match('/name="_csrf" value="([a-f0-9]{64})"/', $html, $match)) {
        throw new RuntimeException('Falta token CSRF en el formulario.');
    }
    return $match[1];
}

$process = null;
$admin = null;
$account = null;
$log = tempnam(sys_get_temp_dir(), 'tecno_http_');
$exitCode = 0;
$testStorage = sys_get_temp_dir() . '/tecno_http_storage_' . bin2hex(random_bytes(8));
mkdir($testStorage, 0700);
mkdir($testStorage . '/sessions', 0700);
try {
    $options = migrationOptions();
    if (!preg_match('/^tecno_star_test_[a-zA-Z0-9_]{1,25}$/D', $options['database'])) {
        throw new RuntimeException('Utiliza una base migrada de prueba con prefijo tecno_star_test_.');
    }
    $admin = migrationConnection($options);
    $runtimeUser = 'ts_http_' . bin2hex(random_bytes(5));
    $runtimePassword = bin2hex(random_bytes(32));
    RestrictedUser::create($admin, $options['database'], $runtimeUser, 'localhost', $runtimePassword);
    $account = $admin->quote($runtimeUser) . "@'localhost'";
    $port = 18014;
    // No usa la instancia de trabajo. Solo escucha en loopback mientras dura la prueba.
    $environment = array_replace(getenv(), [
        'APP_URL' => '/', 'DB_HOST' => $options['host'], 'DB_PORT' => (string) $options['port'],
        'DB_NAME' => $options['database'], 'DB_USER' => $runtimeUser, 'DB_PASSWORD' => $runtimePassword,
        'APP_STORAGE_PATH' => $testStorage, 'LOGIN_PAIR_LIMIT' => '2', 'LOGIN_IP_LIMIT' => '50',
    ]);
    $socket = @stream_socket_client('tcp://127.0.0.1:' . $port, $errno, $error, 0.2);
    if ($socket !== false) {
        fclose($socket);
        throw new RuntimeException('El puerto HTTP temporal esta ocupado.');
    }
    $process = proc_open([PHP_BINARY, '-d', 'session.save_path=' . $testStorage . '/sessions', '-S', '127.0.0.1:' . $port, '-t', 'public', 'public/index.php'],
        [0 => ['pipe', 'r'], 1 => ['file', $log, 'a'], 2 => ['file', $log, 'a']], $pipes, BASE_PATH, $environment);
    if (!is_resource($process)) {
        throw new RuntimeException('No se pudo iniciar el servidor de prueba.');
    }
    fclose($pipes[0]);
    $base = 'http://127.0.0.1:' . $port;
    $ready = false;
    for ($i = 0; $i < 30; $i++) {
        $socket = @stream_socket_client('tcp://127.0.0.1:' . $port, $errno, $error, 0.1);
        if ($socket !== false) {
            fclose($socket);
            $ready = true;
            break;
        }
        usleep(100000);
    }
    httpCheck($ready, 'servidor HTTP aislado con cuenta SQL limitada');
    $cookie = '';
    [$status, $html] = requestApp($base, '/register', $cookie);
    $token = formToken($html);
    httpCheck($status === 200, 'registro publica token de sesion');
    [$status] = requestApp($base, '/login', $cookie, 'POST', ['email' => ['invalido'], 'password' => 'invalida', '_csrf' => $token]);
    httpCheck($status === 400, 'estructura de formulario invalida se rechaza sin advertencias internas');
    $email = 'http-' . bin2hex(random_bytes(6)) . '@example.test';
    $password = bin2hex(random_bytes(16));
    $registration = ['names' => 'Prueba', 'last_names' => 'HTTP', 'phone' => '70000000', 'email' => $email, 'password' => $password, 'password_confirmation' => $password];
    [$status] = requestApp($base, '/register', $cookie, 'POST', $registration);
    $find = $admin->prepare('SELECT COUNT(*) FROM tbusuarios WHERE correo = ?');
    $find->execute([$email]);
    httpCheck($status === 403 && (int) $find->fetchColumn() === 0, 'registro sin CSRF rechazado sin escritura');
    [$status] = requestApp($base, '/register', $cookie, 'POST', $registration + ['_csrf' => $token]);
    $find->execute([$email]);
    httpCheck($status === 200 && (int) $find->fetchColumn() === 1, 'registro valido con permisos limitados');
    [$status] = requestApp($base, '/login', $cookie, 'POST', ['email' => $email, 'password' => $password, '_csrf' => 'incorrecto']);
    httpCheck($status === 403, 'login con token incorrecto rechazado');
    $anotherCookie = '';
    [, $anotherHtml] = requestApp($base, '/login', $anotherCookie);
    [$status] = requestApp($base, '/login', $cookie, 'POST', ['email' => $email, 'password' => $password, '_csrf' => formToken($anotherHtml)]);
    httpCheck($status === 403, 'token de otra sesion rechazado');
    $oldCookie = $cookie;
    [$status, , $location] = requestApp($base, '/login', $cookie, 'POST', ['email' => $email, 'password' => $password, '_csrf' => $token]);
    httpCheck($status === 302 && $location === '/store' && $cookie !== $oldCookie, 'login valido regenera la sesion');
    [$status, $html] = requestApp($base, '/store', $cookie);
    preg_match('/data-csrf-token="([a-f0-9]{64})"/', $html, $match);
    $shoppingToken = $match[1] ?? '';
    httpCheck($status === 200 && $shoppingToken !== '' && $shoppingToken !== $token, 'login rota token y pagina de tienda lo entrega');
    $productId = (int) $admin->query("SELECT id_producto FROM tbproductos WHERE codigo_producto = 'DEMO-CABLE-USB'")->fetchColumn();
    $stateUser=$admin->prepare('SELECT id_usuario FROM tbusuarios WHERE correo=?');$stateUser->execute([$email]);
    $mutation = ['action' => 'cart_add', 'product_id' => $productId, 'quantity' => 1,'operation_id'=>bin2hex(random_bytes(32)),'target_user_id'=>(int)$stateUser->fetchColumn(),'expected_revision'=>0];
    [$status] = requestApp($base, '/shopping/update', $cookie, 'POST', $mutation, null, true);
    httpCheck($status === 403, 'carrito JSON sin cabecera CSRF rechazado');
    [$status] = requestApp($base, '/shopping/update', $cookie, 'POST', $mutation, $token, true);
    httpCheck($status === 403, 'token anterior al login rechazado');
    [$status, $body] = requestApp($base, '/shopping/update', $cookie, 'POST', $mutation, $shoppingToken, true);
    $state = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    httpCheck($status === 200 && count($state['cart']) === 1 && $state['cart'][0]['quantity'] === 1, 'solo la peticion JSON valida modifica el carrito');
    [$status] = requestApp($base, '/admin', $cookie);
    httpCheck($status === 403, 'cliente sin permiso administrativo');
    $promote = $admin->prepare("UPDATE tbusuarios SET id_rol = (SELECT id_rol FROM tbroles WHERE nombre_rol = ?) WHERE correo = ?");
    $promote->execute(['Administrador', $email]);
    [$status, $html] = requestApp($base, '/admin', $cookie);
    httpCheck($status === 200 && str_contains($html, 'method="post"'), 'promocion aplicada sin nuevo login y salida POST');
    $promote->execute(['Cliente', $email]);
    [$status] = requestApp($base, '/admin/products', $cookie);
    httpCheck($status === 403, 'revocacion aplicada en la siguiente peticion');
    $admin->exec('REVOKE SELECT ON `' . $options['database'] . '`.`tbusuarios` FROM ' . $account);
    [$status] = requestApp($base, '/profile', $cookie);
    httpCheck($status === 503, 'fallo de validacion SQL bloquea el perfil');
    [$status, $body] = requestApp($base, '/shopping/state', $cookie);
    httpCheck($status === 503 && isset(json_decode($body, true, 512, JSON_THROW_ON_ERROR)['error']), 'fallo de validacion SQL devuelve error JSON sin autorizar');
    $errorPayload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
    $records = implode('', array_map('file_get_contents', glob($testStorage . '/logs/app-*.log') ?: []));
    httpCheck(isset($errorPayload['request_id']) && str_contains($records, $errorPayload['request_id']) && !str_contains($body, 'SQLSTATE'), 'referencia del error corresponde al registro privado');
    $admin->exec('GRANT SELECT ON `' . $options['database'] . '`.`tbusuarios` TO ' . $account);
    $disable = $admin->prepare("UPDATE tbusuarios SET estado = 'inactivo' WHERE correo = ?");
    $disable->execute([$email]);
    [$status] = requestApp($base, '/shopping/state', $cookie);
    httpCheck($status === 401, 'desactivacion bloquea API y elimina la identidad');
    [$status, , $location] = requestApp($base, '/profile', $cookie);
    httpCheck($status === 302 && $location === '/login', 'cuenta desactivada no abre perfil');
    $enable = $admin->prepare("UPDATE tbusuarios SET estado = 'activo' WHERE correo = ?");
    $enable->execute([$email]);
    [, $html] = requestApp($base, '/login', $cookie);
    requestApp($base, '/login', $cookie, 'POST', ['email' => $email, 'password' => $password, '_csrf' => formToken($html)]);
    [$status] = requestApp($base, '/logout', $cookie);
    httpCheck($status === 404, 'GET no permite cerrar sesion');
    [$status] = requestApp($base, '/shopping/state', $cookie);
    httpCheck($status === 200, 'GET de logout conserva la sesion');
    [$status] = requestApp($base, '/logout', $cookie, 'POST');
    httpCheck($status === 403, 'POST de logout sin token rechazado');
    [, $html] = requestApp($base, '/profile', $cookie);
    [$status, , $location] = requestApp($base, '/logout', $cookie, 'POST', ['_csrf' => formToken($html)]);
    httpCheck($status === 302 && $location === '/login', 'POST de logout valido cierra sesion');
    [$status] = requestApp($base, '/shopping/state', $cookie);
    httpCheck($status === 401, 'sesion cerrada no permite acceder al carrito');

    foreach (['renew', 'idle', 'absolute', 'legacy'] as $mode) {
        [, $html] = requestApp($base, '/login', $cookie);
        [$status] = requestApp($base, '/login', $cookie, 'POST', ['email' => $email, 'password' => $password, '_csrf' => formToken($html)]);
        httpCheck($status === 302, 'acceso previo a prueba de sesion: ' . $mode);
        editTestSession($cookie, $testStorage . '/sessions', $mode);
        $oldCookie = $cookie;
        [$status] = requestApp($base, '/shopping/state', $cookie);
        httpCheck($mode === 'renew' ? ($status === 200 && $cookie !== $oldCookie) : $status === 401, 'politica de sesion aplicada: ' . $mode);
    }
    $limitedCookie = '';
    [, $html] = requestApp($base, '/login', $limitedCookie);
    $limitedToken = formToken($html);
    for ($i = 0; $i < 2; $i++) {
        [$status] = requestApp($base, '/login', $limitedCookie, 'POST', ['email' => $email, 'password' => 'incorrecta', '_csrf' => $limitedToken]);
        httpCheck($status === 200, 'intento incorrecto contabilizado');
    }
    $newCookie = '';
    [, $html] = requestApp($base, '/login', $newCookie);
    [$status, , , $headers] = requestApp($base, '/login', $newCookie, 'POST', ['email' => $email, 'password' => $password, '_csrf' => formToken($html)], null, false, ['X-Forwarded-For: 203.0.113.10']);
    httpCheck($status === 429 && preg_match('/Retry-After: [1-9][0-9]*/i', implode("\n", $headers)), 'limite persiste con otra sesion y cabecera de proxy falsificada');
    $records = implode('', array_map('file_get_contents', glob($testStorage . '/logs/app-*.log') ?: []));
    httpCheck(!str_contains($records, $password) && !str_contains($records, $email) && !str_contains($records, $shoppingToken) && !str_contains($records, $runtimePassword), 'registros sin claves, correo, cookie o token');
    echo "Pruebas HTTP de seguridad completadas.\n";
} catch (Throwable $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . "\n");
    $exitCode = 1;
} finally {
    if (is_resource($process)) {
        proc_terminate($process);
        proc_close($process);
    }
    if ($admin instanceof PDO && $account !== null) {
        $admin->exec('DROP USER ' . $account);
    }
    if (is_string($log) && is_file($log)) {
        unlink($log);
    }
}
exit($exitCode);
