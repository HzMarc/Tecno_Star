<?php

declare(strict_types=1);

// Router exclusivo del servidor de pruebas de PHP. Apache bloquea /tests/.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}
$public = realpath(dirname(__DIR__) . '/public');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = realpath($public . '/' . rawurldecode($path));
if ($file !== false && str_starts_with($file, $public . DIRECTORY_SEPARATOR) && is_file($file)
    && preg_match('/\.(?:css|js|png|jpe?g|webp|svg|ico|woff2?)$/i', $file)) {
    return false;
}
require $public . '/index.php';
