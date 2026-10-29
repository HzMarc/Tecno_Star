<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/config/config.php';
require_once BASE_PATH . '/core/LoginLimiter.php';

$options = getopt('', ['prune']);
$dryRun = !isset($options['prune']);
try {
    $limiter = new LoginLimiter(APP_STORAGE_PATH . '/security/login', LOGIN_PAIR_LIMIT, LOGIN_IP_LIMIT, LOGIN_WINDOW_SECONDS);
    $expired = $limiter->prune($dryRun);
    foreach(['recovery','reset'] as $area)$expired+=(new LoginLimiter(APP_STORAGE_PATH.'/security/'.$area,3,10,900))->prune($dryRun);
    $logs = 0;
    $directory = APP_STORAGE_PATH . '/logs';
    if (is_dir($directory)) {
        $lock = fopen($directory . '/.write.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX)) {
            throw new RuntimeException('No se pudo bloquear el registro.');
        }
        try {
            foreach (glob($directory . '/app-*.log') ?: [] as $path) {
                if (preg_match('/^app-\d{4}-\d{2}-\d{2}(?:-\d{6}-[a-f0-9]{8})?\.log$/D', basename($path))
                    && filemtime($path) < time() - LOG_RETENTION_DAYS * 86400) {
                    if (!$dryRun && !unlink($path)) {
                        throw new RuntimeException('No se pudo limpiar un registro vencido.');
                    }
                    $logs++;
                }
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }
    echo ($dryRun ? 'Simulacion' : 'Limpieza') . ': ' . $expired . ' contadores vencidos y ' . $logs . " registros fuera de retencion.\n";
} catch (Throwable $exception) {
    Logger::write('maintenance.failed', 'error', $exception);
    file_put_contents('php://stderr', "No se pudo completar el mantenimiento. Revisa los permisos del almacenamiento.\n");
    exit(1);
}
