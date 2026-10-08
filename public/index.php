<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once BASE_PATH . '/core/Router.php';

$router = new Router();
require_once BASE_PATH . '/routes/web.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$publicPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

if ($publicPath !== '' && str_starts_with($requestPath, $publicPath)) {
    $requestPath = substr($requestPath, strlen($publicPath)) ?: '/';
}

echo $router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $requestPath);
