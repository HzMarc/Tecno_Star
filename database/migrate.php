<?php

declare(strict_types=1);

require_once __DIR__ . '/cli.php';
require_once __DIR__ . '/MigrationRunner.php';

try {
    $options = migrationOptions();
    $applied = (new MigrationRunner(migrationConnection($options, true), __DIR__ . '/migrations'))->migrate();
    echo $applied === [] ? "Sin migraciones pendientes.\n" : 'Aplicadas: ' . implode(', ', $applied) . "\n";
} catch (Throwable $exception) {
    file_put_contents('php://stderr', 'Error: ' . $exception->getMessage() . "\n");
    exit(1);
}
