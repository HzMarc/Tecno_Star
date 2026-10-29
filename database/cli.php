<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/config.php';

function migrationCredentials(): array
{
    $settings = new Environment(BASE_PATH . '/.env.migrations');
    $legacyPassword = getenv('TECNO_STAR_MIGRATION_PASSWORD');
    return [
        $settings->get('MIGRATION_DB_USER', DB_USER),
        $legacyPassword === false ? $settings->get('MIGRATION_DB_PASSWORD', DB_PASSWORD) : $legacyPassword,
    ];
}

/** El nombre destino siempre es explicito para evitar alterar la base actual. */
function migrationOptions(): array
{
    $options = getopt('', ['database:', 'host:', 'port:', 'user:', 'create', 'demo', 'help']);
    if (isset($options['help'])) {
        echo "Uso: php database/migrate.php --database=NOMBRE [--create] [--host=127.0.0.1] [--port=3308] [--user=root]\n";
        echo "Datos de ejemplo: php database/seed.php --database=NOMBRE --demo\n";
        echo "Conexion administrativa: MIGRATION_DB_USER/MIGRATION_DB_PASSWORD en entorno o .env.migrations.\n";
        exit(0);
    }
    $name = $options['database'] ?? '';
    if (!is_string($name) || !preg_match('/^[a-zA-Z0-9_]{1,64}$/D', $name)) {
        throw new InvalidArgumentException('Indica --database con un nombre de 1 a 64 letras, numeros o guiones bajos.');
    }
    $host = $options['host'] ?? DB_HOST;
    $port = $options['port'] ?? DB_PORT;
    if (!is_string($host) || !preg_match('/^[a-zA-Z0-9.:-]+$/D', $host)) {
        throw new InvalidArgumentException('Host invalido.');
    }
    if (filter_var($port, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) === false) {
        throw new InvalidArgumentException('Puerto invalido.');
    }
    $user = $options['user'] ?? migrationCredentials()[0];
    if (!is_string($user)) {
        throw new InvalidArgumentException('Usuario invalido.');
    }
    return $options + ['database' => $name, 'host' => $host, 'port' => $port, 'user' => $user];
}

function migrationConnection(array $options, bool $allowCreate = false): PDO
{
    $password = migrationCredentials()[1];
    $dsn = 'mysql:host=' . $options['host'] . ';port=' . $options['port'] . ';charset=utf8mb4';
    $attributes = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
    if ($allowCreate && isset($options['create'])) {
        $connection = new PDO($dsn, $options['user'], $password, $attributes);
        $connection->exec('CREATE DATABASE IF NOT EXISTS `' . $options['database'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }
    return new PDO($dsn . ';dbname=' . $options['database'], $options['user'], $password, $attributes);
}
