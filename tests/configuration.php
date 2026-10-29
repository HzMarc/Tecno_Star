<?php

declare(strict_types=1);

// Se ejecuta con PHP CLI. No conecta ni modifica la base de datos.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function configCheck(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FALLO: ' . $message);
    }
    echo 'OK: ' . $message . "\n";
}

function configProcess(string $code, array $overrides = []): array
{
    $environment = array_replace(getenv(), [
        'APP_URL' => '/prueba/public', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '13308',
        'DB_NAME' => 'tecno_star_test_config', 'DB_USER' => 'config_test', 'DB_PASSWORD' => 'valor-solo-de-prueba',
    ], $overrides);
    $process = proc_open([PHP_BINARY, '-d', 'display_errors=stderr', '-r', $code], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__), $environment);
    if (!is_resource($process)) {
        throw new RuntimeException('No se pudo iniciar PHP de prueba.');
    }
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    return [proc_close($process), $output, $error];
}

$fixture = tempnam(sys_get_temp_dir(), 'tecno_env_');
$exitCode = 0;
try {
    require_once dirname(__DIR__) . '/config/Environment.php';
    file_put_contents($fixture, "# comentario\nTEST_CONFIG_PLAIN=valor\nTEST_CONFIG_QUOTED=\" clave # con = y \\ literal \"\nTEST_CONFIG_EMPTY=\nTEST_CONFIG_COMMENT=valor # comentario\n");
    $environment = new Environment($fixture);
    configCheck($environment->get('TEST_CONFIG_PLAIN') === 'valor', 'lectura de archivo local');
    configCheck($environment->get('TEST_CONFIG_QUOTED') === ' clave # con = y \\ literal ', 'valores entre comillas conservan caracteres y espacios');
    configCheck($environment->get('TEST_CONFIG_EMPTY') === '' && $environment->get('TEST_CONFIG_COMMENT') === 'valor', 'valor vacio y comentario');
    putenv('TEST_CONFIG_PLAIN=proceso');
    configCheck($environment->get('TEST_CONFIG_PLAIN') === 'proceso', 'el entorno tiene prioridad sobre el archivo');
    putenv('TEST_CONFIG_PLAIN=');
    configCheck($environment->get('TEST_CONFIG_PLAIN') === '', 'valor de entorno vacio tambien tiene prioridad');
    putenv('TEST_CONFIG_PLAIN');
    try {
        $environment->get('TEST_CONFIG_MISSING');
        throw new LogicException('Variable obligatoria no rechazada');
    } catch (RuntimeException $exception) {
        configCheck(str_contains($exception->getMessage(), 'TEST_CONFIG_MISSING'), 'variable obligatoria ausente produce error claro');
    }
    foreach (["CLAVE=\"secreto-sin-cerrar", "CLAVE=uno\nCLAVE=dos", 'linea-sin-asignacion'] as $invalid) {
        file_put_contents($fixture, $invalid);
        try {
            new Environment($fixture);
            throw new LogicException('Archivo invalido aceptado');
        } catch (RuntimeException $exception) {
            configCheck(!str_contains($exception->getMessage(), 'secreto-sin-cerrar'), 'formato invalido rechazado sin mostrar el valor');
        }
    }
    foreach (['/prueba/public', '/', 'https://tienda.example.test/subcarpeta/'] as $base) {
        [$status, $output, $error] = configProcess('require "config/config.php"; echo json_encode([APP_URL, APP_BASE_PATH, app_url("/login"), DB_USER, DB_PORT]);', ['APP_URL' => $base]);
        configCheck($status === 0 && $error === '', 'configuracion valida: ' . $base);
        $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        configCheck($result[2] === rtrim($base, '/') . '/login' && $result[3] === 'config_test' && $result[4] === '13308', 'URL y conexion toman los valores del entorno');
        $path = parse_url($base, PHP_URL_PATH);
        $path = rtrim($path ?? '', '/');
        foreach (['/' => 200, '/login' => 200, '/register' => 200, '/admin' => 403, '/shopping/state' => 401] as $route => $expected) {
            $code = '$_SERVER["REQUEST_URI"] = ' . var_export($path . $route, true) . '; $_SERVER["REQUEST_METHOD"] = "GET"; ini_set("session.save_path", sys_get_temp_dir()); ob_start(); require "public/index.php"; $html = ob_get_clean(); echo json_encode([http_response_code() ?: 200, session_get_cookie_params(), $html]); session_destroy();';
            [$status, $output, $error] = configProcess($code, ['APP_URL' => $base]);
            configCheck($status === 0 && $error === '', 'ejecucion limpia de ruta ' . $route);
            [$http, $cookie, $html] = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
            configCheck($http === $expected && $cookie['path'] === $path . '/', 'ruta y cookie corresponden a la base configurada');
            configCheck($cookie['secure'] === (strtolower((string) parse_url($base, PHP_URL_SCHEME)) === 'https'), 'seguridad de cookie corresponde al protocolo configurado');
            if ($route === '/login') {
                configCheck(str_contains($html, 'action="' . rtrim($base, '/') . '/login"') && str_contains($html, 'src="' . rtrim($base, '/') . '/assets/js/app.js"'), 'formulario y recursos usan la URL base');
                configCheck(!str_contains($html, 'valor-solo-de-prueba'), 'la pagina no publica la clave de conexion');
            }
        }
        foreach (['store/index.php', 'store/product.php', 'auth/profile.php', 'admin/index.php', 'admin/products.php', 'admin/product-form.php', 'admin/catalog.php', 'admin/inventory.php'] as $view) {
            $code = <<<'PHP'
require 'config/config.php';
$_SESSION['user'] = ['id' => 1, 'name' => 'Cuenta de prueba', 'role' => 'Administrador'];
$user = ['nombres' => 'Cuenta', 'apellidos' => 'Prueba', 'correo' => 'prueba@example.test', 'telefono' => '70000000', 'nombre_rol' => 'Administrador'];
$product = ['id' => 1, 'name' => 'Producto de prueba', 'category' => 'Prueba', 'brand' => 'Prueba', 'description' => 'Descripcion', 'price' => 5, 'stock' => 3, 'image' => '', 'is_offer' => false, 'old_price' => null, 'icon' => 'bi-box'];
$products = [];
$filters = ['q'=>'', 'category'=>'', 'brand'=>'', 'status'=>'', 'page'=>''];
$errors = []; $brands = []; $total = 0; $page = 1; $pages = 1;
$id = null; $values = []; $entities = []; $kind = 'categories'; $title = 'Categorías'; $q = ''; $state = '';
$inventory = [];
$categories = [];
$ordersByStatus = [];
$metrics = ['products' => 0, 'users' => 1, 'stock' => 0, 'pending_orders' => 0, 'sales_total' => 0];
$message = null;
$messageType = 'success';
ob_start();
PHP;
            if ($view === 'store/index.php') {
                $code .= '$products = [$product];';
            }
            $code .= 'require ' . var_export('app/Views/' . $view, true) . '; echo ob_get_clean();';
            [$status, $html, $error] = configProcess($code, ['APP_URL' => $base]);
            configCheck($status === 0 && $error === '' && str_contains($html, '<html'), 'vista con base configurable: ' . $view);
            configCheck(str_contains($html, rtrim($base, '/') . '/assets/'), 'recursos locales con base configurable');
            if (str_starts_with($view, 'store/')) {
                configCheck(str_contains($html, 'data-update-url="' . rtrim($base, '/') . '/shopping/update"') && str_contains($html, 'href="' . rtrim($base, '/') . '/profile"'), 'endpoints del carrito y cuenta autenticada con base configurable');
            }
        }
    }
    foreach (['//otro.example', 'javascript:alert(1)', '/tienda?x=1', '/tienda#x', '/tienda/../otra', '/tienda%2fotra', "https://example.test/\r\nX-Test: valor", 'https://usuario:clave@example.test'] as $invalid) {
        [$status] = configProcess('require "config/config.php";', ['APP_URL' => $invalid]);
        configCheck($status !== 0, 'URL insegura o ambigua rechazada');
    }
    foreach (['DB_PORT' => '0', 'DB_HOST' => 'localhost;dbname=otra', 'DB_NAME' => 'base;otra', 'DB_USER' => ''] as $key => $value) {
        // Windows elimina variables vacias al crear procesos: fijarla dentro
        // de PHP permite probar el caso vacio sin recurrir al archivo local.
        [$status] = configProcess('putenv(' . var_export($key . '=' . $value, true) . '); require "config/config.php";', [$key => $value]);
        configCheck($status !== 0, 'parametro de conexion invalido rechazado: ' . $key);
    }
    foreach (['LOGIN_PAIR_LIMIT' => '0', 'LOGIN_IP_LIMIT' => '-1', 'LOGIN_WINDOW_SECONDS' => 'texto', 'SESSION_PERSISTENT' => 'false', 'SESSION_IDLE_SECONDS' => '0', 'SESSION_ABSOLUTE_SECONDS' => '1', 'SESSION_REGENERATE_SECONDS' => '0', 'LOG_RETENTION_DAYS' => '0', 'APP_STORAGE_PATH' => 'ruta-relativa'] as $key => $value) {
        [$status] = configProcess('require "config/config.php";', [$key => $value]);
        configCheck($status !== 0, 'configuracion de seguridad invalida rechazada: ' . $key);
    }
    echo "Pruebas de configuracion completadas.\n";
} catch (Throwable $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . "\n");
    $exitCode = 1;
} finally {
    if (is_file($fixture)) {
        unlink($fixture);
    }
}
exit($exitCode);
