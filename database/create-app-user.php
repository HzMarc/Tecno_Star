<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/config/config.php';
require_once __DIR__ . '/RestrictedUser.php';

function localSetting(string $key, string $value): string
{
    if (strpbrk($value, "\r\n") !== false) {
        throw new RuntimeException('La configuracion local no admite valores de varias lineas.');
    }
    return $key . '="' . $value . '"';
}

try {
    $options = getopt('', ['account:', 'account-host:', 'activate', 'help']);
    if (isset($options['help'])) {
        echo "Uso: php database/create-app-user.php --account=tecno_star_app --activate [--account-host=localhost]\n";
        echo "Crea una cuenta nueva para DB_NAME y guarda su clave aleatoria solo en .env.\n";
        exit;
    }
    if (!isset($options['activate']) || !isset($options['account']) || !is_string($options['account'])) {
        throw new RuntimeException('Indica --account=NOMBRE y --activate.');
    }
    if (!in_array(DB_HOST, ['127.0.0.1', 'localhost'], true)) {
        throw new RuntimeException('Esta herramienta esta limitada al servicio local.');
    }
    foreach (['DB_USER', 'DB_PASSWORD'] as $key) {
        if (getenv($key) !== false || isset($_SERVER[$key]) || isset($_ENV[$key])) {
            throw new RuntimeException('La conexion esta definida en el entorno. Configura la activacion en el servidor de forma explicita; no se sobrescribe desde esta herramienta.');
        }
    }
    $path = BASE_PATH . '/.env';
    if (!is_file($path) || !is_writable($path)) {
        throw new RuntimeException('Se requiere un .env local escribible.');
    }
    $local = file_get_contents($path);
    foreach (['DB_USER', 'DB_PASSWORD'] as $key) {
        if (preg_match_all('/^' . $key . '\s*=.*$/m', $local) !== 1) {
            throw new RuntimeException('Se requiere exactamente una asignacion local de ' . $key);
        }
    }
    $adminSettings = new Environment(BASE_PATH . '/.env.migrations');
    $adminUser = $adminSettings->get('MIGRATION_DB_USER', DB_USER);
    $adminPassword = $adminSettings->get('MIGRATION_DB_PASSWORD', DB_PASSWORD);
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $attributes = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
    $admin = new PDO($dsn, $adminUser, $adminPassword, $attributes);
    $password = bin2hex(random_bytes(32));
    $host = $options['account-host'] ?? 'localhost';
    if (!is_string($host)) {
        throw new RuntimeException('Host de cuenta invalido.');
    }
    RestrictedUser::create($admin, DB_NAME, $options['account'], $host, $password);
    $account = $admin->quote($options['account']) . '@' . $admin->quote($host);
    try {
        $runtime = new PDO($dsn, $options['account'], $password, $attributes);
        $runtime->query('SELECT id_usuario FROM tbusuarios LIMIT 0');
        $newLocal = preg_replace_callback('/^DB_USER\s*=.*$/m', fn () => localSetting('DB_USER', $options['account']), $local);
        $newLocal = preg_replace_callback('/^DB_PASSWORD\s*=.*$/m', fn () => localSetting('DB_PASSWORD', $password), $newLocal);
        // Conserva la conexion administrativa exclusivamente para herramientas CLI.
        if (!is_file(BASE_PATH . '/.env.migrations')) {
            $adminLocal = localSetting('MIGRATION_DB_USER', $adminUser) . "\n" . localSetting('MIGRATION_DB_PASSWORD', $adminPassword) . "\n";
            if (file_put_contents(BASE_PATH . '/.env.migrations', $adminLocal, LOCK_EX) === false) {
                throw new RuntimeException('No se pudo guardar la configuracion de migraciones.');
            }
        }
        $temporary = tempnam(BASE_PATH, '.env.');
        if ($temporary === false || file_put_contents($temporary, $newLocal, LOCK_EX) === false || !rename($temporary, $path)) {
            if (is_string($temporary) && is_file($temporary)) {
                unlink($temporary);
            }
            throw new RuntimeException('No se pudo activar la cuenta en .env.');
        }
    } catch (Throwable $exception) {
        $admin->exec('DROP USER ' . $account);
        throw $exception;
    }
    echo "Cuenta local creada y activada. Clave aleatoria guardada en .env, sin mostrarla.\n";
    echo "Permisos por tabla, sin DDL, privilegios globales ni acceso a otras bases.\n";
} catch (Throwable) {
    // CREATE USER puede incluir informacion sensible en el mensaje del servidor.
    file_put_contents('php://stderr', "No se pudo crear o activar la cuenta. Revisa los requisitos, la conexion administrativa y que la cuenta no exista. No se muestran credenciales.\n");
    exit(1);
}
