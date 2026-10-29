<?php

declare(strict_types=1);

require_once __DIR__ . '/cli.php';
require_once __DIR__ . '/seeders/demo.php';

try {
    $options = migrationOptions();
    if (!isset($options['demo'])) {
        throw new InvalidArgumentException('Indica --demo para cargar los productos de ejemplo opcionales. Los roles se crean al migrar.');
    }
    $database = migrationConnection($options);
    $pending = (int) $database->query("SELECT COUNT(*) FROM schema_migrations WHERE status <> 'applied'")->fetchColumn();
    $versions = $database->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    $expected = array_map('basename', glob(__DIR__ . '/migrations/*.sql') ?: []);
    if ($pending > 0 || array_diff($expected, $versions) !== []) {
        throw new RuntimeException('Completa las migraciones antes de cargar ejemplos.');
    }
    seedDemo($database);
    echo "Datos de demostracion disponibles. No se crearon cuentas ni contrasenas predeterminadas.\n";
} catch (Throwable $exception) {
    file_put_contents('php://stderr', 'Error: ' . $exception->getMessage() . "\n");
    exit(1);
}
