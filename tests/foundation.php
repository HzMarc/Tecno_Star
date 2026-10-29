<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/config/config.php';
require_once BASE_PATH . '/core/LoginLimiter.php';

ob_start();
function foundationCheck(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FALLO: ' . $message);
    }
    echo 'OK: ' . $message . "\n";
}

function foundationProcess(string $code, array $environment = []): array
{
    $source = tempnam(sys_get_temp_dir(), 'tecno_foundation_php_');
    file_put_contents($source, "<?php\n" . $code);
    $process = proc_open([PHP_BINARY, $source], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, BASE_PATH, array_replace(getenv(), $environment));
    if (!is_resource($process)) {
        throw new RuntimeException('No se pudo iniciar PHP de prueba.');
    }
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    unlink($source);
    return [$status, $output, $error];
}

$root = sys_get_temp_dir() . '/tecno_foundation_' . bin2hex(random_bytes(8));
mkdir($root, 0700);
$exitCode = 0;
try {
    $limiter = new LoginLimiter($root . '/limiter', 2, 3, 10);
    foundationCheck($limiter->reserve('Cuenta@Example.test', '192.0.2.1', 100) === 0, 'primer intento permitido');
    foundationCheck($limiter->reserve('cuenta@example.test', '192.0.2.1', 101) === 0, 'correo normalizado comparte contador');
    foundationCheck($limiter->reserve('cuenta@example.test', '192.0.2.1', 102) === 8, 'tercer intento bloqueado con espera precisa');
    foundationCheck($limiter->reserve('otra@example.test', '192.0.2.1', 102) === 0, 'otra cuenta dentro del limite de IP');
    foundationCheck($limiter->reserve('tercera@example.test', '192.0.2.1', 103) === 7, 'limite de IP impide rotar correos');
    $blockedFileCount = count(glob($root . '/limiter/*.json'));
    $limiter->reserve('cuarta@example.test', '192.0.2.1', 103);
    $limiter->reserve('quinta@example.test', '192.0.2.1', 103);
    foundationCheck(count(glob($root . '/limiter/*.json')) === $blockedFileCount, 'IP bloqueado no crea archivos para correos nuevos');
    foundationCheck($limiter->reserve('cuenta@example.test', '192.0.2.2', 103) === 0, 'otra IP no bloquea globalmente la cuenta');
    foundationCheck($limiter->reserve('cuenta@example.test', '192.0.2.1', 110) === 0, 'ventana vencida permite reintento');
    $limiter->clearPair('cuenta@example.test', '192.0.2.1');
    foundationCheck($limiter->reserve('cuenta@example.test', '192.0.2.1', 111) === 0, 'acceso correcto puede reiniciar el par sin reiniciar IP');
    $before = count(glob($root . '/limiter/*.json'));
    $cleanupTime = time() + 11;
    foundationCheck($limiter->prune(true, $cleanupTime) > 0 && count(glob($root . '/limiter/*.json')) === $before, 'mantenimiento en simulacion no elimina contadores');
    foundationCheck($limiter->prune(false, $cleanupTime) > 0 && glob($root . '/limiter/*.json') === [], 'mantenimiento elimina exclusivamente contadores vencidos');
    $limiter->reserve('privado@example.test', '192.0.2.55', 1000);
    $files = glob($root . '/limiter/*.json');
    foundationCheck(!str_contains(implode('', array_map('file_get_contents', $files)), 'privado@example.test'), 'contadores sin correo ni IP en texto');
    file_put_contents($files[0], 'json corrupto');
    try {
        $limiter->reserve('privado@example.test', '192.0.2.55', 1001);
        throw new LogicException('Almacenamiento corrupto aceptado');
    } catch (LoginStorageException) {
        foundationCheck(true, 'contador corrupto bloquea el acceso');
    }
    $workers = [];
    $code = 'require "core/LoginLimiter.php"; $limiter = new LoginLimiter(' . var_export($root . '/concurrent', true) . ', 3, 100, 10); echo $limiter->reserve("shared@example.test", "192.0.2.10", 1000);';
    for ($i = 0; $i < 8; $i++) {
        $process = proc_open([PHP_BINARY, '-r', $code], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, BASE_PATH);
        if (!is_resource($process)) {
            throw new RuntimeException('No se pudo iniciar un proceso concurrente.');
        }
        fclose($pipes[0]);
        $workers[] = [$process, $pipes];
    }
    $allowed = 0;
    foreach ($workers as [$process, $pipes]) {
        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        foundationCheck(proc_close($process) === 0 && $error === '', 'proceso concurrente sin errores');
        $allowed += $output === '0' ? 1 : 0;
    }
    foundationCheck($allowed === 3, 'ocho solicitudes simultaneas respetan limite de tres');

    ini_set('session.save_path', $root);
    SessionManager::start();
    SessionManager::authenticated(100000);
    foundationCheck(SessionManager::validAuthentication(100001), 'sesion dentro de los plazos');
    foundationCheck(!SessionManager::validAuthentication(100001 + SESSION_IDLE_SECONDS), 'inactividad vence exactamente en el limite');
    SessionManager::authenticated(100000);
    $_SESSION['auth_last_seen_at'] = 100000 + SESSION_ABSOLUTE_SECONDS - 1;
    foundationCheck(!SessionManager::validAuthentication(100000 + SESSION_ABSOLUTE_SECONDS), 'duracion absoluta vence aunque haya actividad reciente');
    SessionManager::clearAuthentication();
    foundationCheck(!SessionManager::validAuthentication(100000), 'sesion sin metadatos requiere nuevo acceso');
    SessionManager::authenticated(100000);
    $oldId = session_id();
    $_SESSION['auth_last_seen_at'] = 100000 + SESSION_REGENERATE_SECONDS;
    foundationCheck(SessionManager::validAuthentication(100000 + SESSION_REGENERATE_SECONDS) && session_id() !== $oldId, 'renovacion periodica del identificador');
    session_destroy();
    foreach (['0', '1'] as $persistent) {
        [, $output, $error] = foundationProcess('require "config/config.php"; ini_set("session.save_path", sys_get_temp_dir()); SessionManager::start(); echo json_encode([session_get_cookie_params(), ini_get("session.use_strict_mode")]); session_destroy();', ['SESSION_PERSISTENT' => $persistent]);
        [$cookie, $strict] = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        foundationCheck($error === '' && $cookie['lifetime'] === ($persistent === '1' ? SESSION_ABSOLUTE_SECONDS : 0) && $strict === '1', 'cookie y modo estricto segun politica de persistencia');
    }

    foreach (['throw new RuntimeException("CLAVE-PRIVADA-DE-PRUEBA");', 'trigger_error("CLAVE-PRIVADA-DE-PRUEBA", E_USER_WARNING);'] as $failure) {
        $code = 'require "core/ErrorHandler.php"; ErrorHandler::register(); require "config/config.php"; $_SERVER["REQUEST_URI"] = "/shopping/state"; echo "SALIDA-PARCIAL-PRIVADA"; ' . $failure;
        [, $output] = foundationProcess($code, ['APP_STORAGE_PATH' => $root . '/errors']);
        $payload = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        $logs = implode('', array_map('file_get_contents', glob($root . '/errors/logs/app-*.log') ?: []));
        foundationCheck(isset($payload['error'], $payload['request_id']) && str_contains($logs, $payload['request_id']), 'error con referencia correlacionada en el registro');
        foundationCheck(!str_contains($output . $logs, 'CLAVE-PRIVADA-DE-PRUEBA') && !str_contains($output, 'SALIDA-PARCIAL-PRIVADA'), 'error no filtra mensaje interno ni salida parcial');
    }
    $broken = $root . '/broken.php';
    file_put_contents($broken, '<?php syntax invalid !');
    [, $output] = foundationProcess('require "core/ErrorHandler.php"; ErrorHandler::register(); require "config/config.php"; $_SERVER["REQUEST_URI"] = "/product/data"; require ' . var_export($broken, true) . ';', ['APP_STORAGE_PATH' => $root . '/errors']);
    foundationCheck(isset(json_decode($output, true, 512, JSON_THROW_ON_ERROR)['request_id']) && !str_contains($output, 'ParseError'), 'error de sintaxis incluido recibe respuesta segura');

    $currentLog = $root . '/errors/logs/app-' . gmdate('Y-m-d') . '.log';
    file_put_contents($currentLog, str_repeat('x', 5 * 1024 * 1024), FILE_APPEND);
    [$rotationStatus, , $rotationError] = foundationProcess('require "core/ErrorHandler.php"; ErrorHandler::register(); require "config/config.php"; throw new RuntimeException("privado");', ['APP_STORAGE_PATH' => $root . '/errors']);
    clearstatcache(true, $currentLog);
    foundationCheck($rotationStatus === 0 && $rotationError === '' && count(glob($root . '/errors/logs/app-*.log')) >= 2 && filesize($currentLog) < 5 * 1024 * 1024, 'rotacion al alcanzar cinco MiB sin perder el registro actual');
    $unavailable = $root . '/not-a-directory';
    file_put_contents($unavailable, 'archivo');
    [, $output, $error] = foundationProcess('require "core/ErrorHandler.php"; ErrorHandler::register(); require "config/config.php"; $_SERVER["REQUEST_URI"] = "/shopping/state"; throw new RuntimeException("CLAVE-PRIVADA-DE-PRUEBA");', ['APP_STORAGE_PATH' => $unavailable]);
    $payload = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    foundationCheck(isset($payload['request_id']) && str_contains($error, $payload['request_id']) && !str_contains($output . $error, 'CLAVE-PRIVADA-DE-PRUEBA'), 'fallo del registro mantiene respuesta segura y referencia en log PHP');

    // Datos temporales exclusivamente: prueba limpieza de logs sin tocar registros reales.
    $oldLog = $root . '/errors/logs/app-2000-01-01.log';
    file_put_contents($oldLog, "registro antiguo de prueba\n");
    touch($oldLog, time() - (LOG_RETENTION_DAYS + 1) * 86400);
    [, $output] = foundationProcess('$_SERVER["argv"] = ["database/maintenance.php"]; require "database/maintenance.php";', ['APP_STORAGE_PATH' => $root . '/errors']);
    foundationCheck(is_file($oldLog) && str_contains($output, 'Simulacion'), 'mantenimiento no borra registros sin --prune');
    $process = proc_open([PHP_BINARY, 'database/maintenance.php', '--prune'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, BASE_PATH, array_replace(getenv(), ['APP_STORAGE_PATH' => $root . '/errors']));
    fclose($pipes[0]);
    stream_get_contents($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    clearstatcache(true, $oldLog);
    foundationCheck($status === 0 && $error === '' && !is_file($oldLog) && count(glob($root . '/errors/logs/app-*.log')) > 0, 'retencion elimina logs antiguos y conserva los recientes');
    echo "Pruebas de limites, sesiones y errores completadas.\n";
} catch (Throwable $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . "\n");
    $exitCode = 1;
}
exit($exitCode);
