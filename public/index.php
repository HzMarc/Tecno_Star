<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/core/ErrorHandler.php';
ErrorHandler::register();
header('X-Request-ID: ' . ErrorHandler::requestId());
require_once dirname(__DIR__) . '/config/config.php';
require_once BASE_PATH . '/core/Router.php';
require_once BASE_PATH . '/core/Auth.php';

// Account-specific markup (including the shopping user ID) must be rendered
// from the current session on every navigation.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

SessionManager::start();

$router = new Router();
require_once BASE_PATH . '/routes/web.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$publicPath = APP_BASE_PATH;

if ($publicPath !== '' && ($requestPath === $publicPath || str_starts_with($requestPath, $publicPath . '/'))) {
    $requestPath = substr($requestPath, strlen($publicPath)) ?: '/';
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
try {
    // Actualiza tambien el nombre/rol mostrado en las paginas publicas.
    Auth::current();
    $postLimit = trim((string) ini_get('post_max_size'));
    $postLimitBytes = 0;
    if (preg_match('/^(\d+)\s*([KMG])?$/i', $postLimit, $sizeMatch)) {
        $postLimitBytes = (int) $sizeMatch[1] * (1024 ** (['K'=>1,'M'=>2,'G'=>3][strtoupper($sizeMatch[2] ?? '')] ?? 0));
    }
    if ($method === 'POST' && $postLimitBytes > 0 && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $postLimitBytes) {
        ErrorHandler::respond(413, 'El formulario supera el tamaño máximo permitido. Usa una imagen de hasta 4 MiB y vuelve a enviarlo.');
    } elseif (!in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)
        && !Csrf::valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? null))) {
        ErrorHandler::respond(403, 'La sesión del formulario venció. Recarga la página e intenta nuevamente.');
    } else {
        echo $router->dispatch($method, $requestPath);
    }
} catch (Throwable $exception) {
    ErrorHandler::handle($exception);
}
ErrorHandler::finish();
