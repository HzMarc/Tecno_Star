<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database/cli.php';

function storeProcess(array $arguments, ?array $environment = null): int
{
    $process = proc_open($arguments, [STDIN, STDOUT, STDERR], $pipes, BASE_PATH, $environment);
    if (!is_resource($process)) throw new RuntimeException('No se pudo iniciar PHP de pruebas.');
    return proc_close($process);
}

$server = null;
$metadata = null;
$code = 1;
try {
    $socket = @stream_socket_client('tcp://127.0.0.1:18014', $errno, $error, 0.2);
    if ($socket !== false) { fclose($socket); throw new RuntimeException('El puerto temporal 18014 está ocupado.'); }
    if (is_file(BASE_PATH . '/storage/store-preview-path.txt')) throw new RuntimeException('Ya existe una preparación de tienda. Finalízala antes de iniciar otra.');
    if (storeProcess(array_merge([PHP_BINARY, BASE_PATH . '/tests/storefront-setup.php'], array_slice($argv, 1))) !== 0) throw new RuntimeException('Falló la preparación aislada.');
    $metadataPath = trim((string) file_get_contents(BASE_PATH . '/storage/store-preview-path.txt'));
    $metadata = json_decode((string) file_get_contents($metadataPath), true, 512, JSON_THROW_ON_ERROR);
    $environment = array_replace(getenv(), ['APP_URL' => '/', 'DB_HOST' => $metadata['host'], 'DB_PORT' => (string) $metadata['port'], 'DB_NAME' => $metadata['database'], 'DB_USER' => $metadata['user'], 'DB_PASSWORD' => $metadata['password'], 'APP_STORAGE_PATH' => $metadata['storage'], 'LOGIN_PAIR_LIMIT' => '20', 'LOGIN_IP_LIMIT' => '1000']);
    $server = proc_open([PHP_BINARY, '-d', 'session.save_path=' . $metadata['storage'] . '/sessions', '-S', '127.0.0.1:18014', '-t', 'public', 'tests/storefront-router.php'], [0 => ['pipe', 'r'], 1 => ['file', $metadata['storage'] . '/http.log', 'a'], 2 => ['file', $metadata['storage'] . '/http-error.log', 'a']], $pipes, BASE_PATH, $environment);
    if (!is_resource($server)) throw new RuntimeException('No se pudo iniciar el servidor temporal.');
    fclose($pipes[0]);
    $ready = false;
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $socket = @stream_socket_client('tcp://127.0.0.1:18014', $errno, $error, 0.2);
        if ($socket !== false) { fclose($socket); $ready = true; break; }
        usleep(100000);
    }
    if (!$ready) throw new RuntimeException('El servidor temporal no respondió.');
    $code = storeProcess([PHP_BINARY, BASE_PATH . '/tests/storefront.php']);
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
} finally {
    if (is_resource($server)) { proc_terminate($server); proc_close($server); }
    if ($metadata !== null) {
        $admin = migrationConnection(array_replace($metadata, ['user' => migrationCredentials()[0]]));
        if (!preg_match('/^ts_store_[a-f0-9]{10}$/D', $metadata['user'])) throw new RuntimeException('Cuenta temporal inválida.');
        $admin->exec('DROP USER ' . $admin->quote($metadata['user']) . "@'localhost'");
        unlink(BASE_PATH . '/storage/store-preview-path.txt');
        // La base y los registros de prueba se conservan para diagnóstico.
    }
}
exit($code);
