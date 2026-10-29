<?php

declare(strict_types=1);
require_once __DIR__ . '/cli.php';
require_once __DIR__ . '/GoogleLoginUpgrade.php';
require_once BASE_PATH . '/config/Database.php';

try {
    $runtime = Database::connection();
    if (in_array('--verify', $argv, true)) {
        GoogleLoginUpgrade::verify($runtime);
        echo "Esquema de Google disponible para la cuenta de la aplicación.\n";
        exit;
    }
    if (!in_array('--apply', $argv, true)) {
        echo "Simulación: crea la tabla de identidades de Google y concede SELECT e INSERT a la cuenta local actual. Añade --apply para activar.\n";
        exit;
    }
    if (!in_array(DB_HOST, ['localhost', '127.0.0.1'], true)) {
        throw new RuntimeException('La activación automática requiere una base local.');
    }
    $current = (string) $runtime->query('SELECT CURRENT_USER()')->fetchColumn();
    if (!preg_match('/^([a-zA-Z][a-zA-Z0-9_]{0,31})@(localhost|127\.0\.0\.1)$/D', $current, $account)
        || $account[1] !== DB_USER || in_array(strtolower(DB_USER), ['root', 'mysql'], true)) {
        throw new RuntimeException('Se requiere la cuenta limitada de la aplicación.');
    }
    [$migrationUser, $migrationPassword] = migrationCredentials();
    $db = new PDO('mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4', $migrationUser, $migrationPassword, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $backup = BASE_PATH . '/storage/backups/google-' . gmdate('Ymd-His') . '-' . bin2hex(random_bytes(4));
    if (!mkdir($backup, 0700, true)) {
        throw new RuntimeException('No se pudo preparar el respaldo.');
    }
    $process = proc_open(['C:/xampp/mysql/bin/mysqldump.exe', '--no-defaults', '--single-transaction', '--skip-lock-tables', '--hex-blob', '--host=' . DB_HOST, '--port=' . DB_PORT, '--user=' . $migrationUser, DB_NAME], [0 => ['pipe', 'r'], 1 => ['file', $backup . '/database.sql', 'w'], 2 => ['file', $backup . '/dump-error.log', 'w']], $pipes, BASE_PATH, array_replace(getenv(), ['MYSQL_PWD' => $migrationPassword]));
    if (!is_resource($process)) {
        throw new RuntimeException('No se pudo iniciar el respaldo.');
    }
    fclose($pipes[0]);
    if (proc_close($process) !== 0) {
        throw new RuntimeException('Falló el respaldo. No se actualizó el esquema.');
    }
    $snapshot = static fn(): string => hash('sha256', json_encode($db->query('SELECT * FROM tbusuarios ORDER BY id_usuario')->fetchAll(), JSON_THROW_ON_ERROR));
    $before = $snapshot();
    GoogleLoginUpgrade::apply($db);
    if ($before !== $snapshot()) {
        throw new RuntimeException('Las cuentas cambiaron durante la actualización. Revisa el respaldo.');
    }
    $db->exec('GRANT SELECT, INSERT ON `' . DB_NAME . '`.tbgoogleidentidades TO ' . $db->quote($account[1]) . '@' . $db->quote($account[2]));
    GoogleLoginUpgrade::verify($runtime);
    echo "Google activado. Respaldo creado, cuentas existentes conservadas y permisos limitados a SELECT e INSERT en la tabla nueva.\n";
} catch (Throwable $error) {
    fwrite(STDERR, "No se pudo activar Google.\n");
    fwrite(STDERR, $error instanceof PDOException ? 'SQLSTATE ' . $error->getCode() . "\n" : $error->getMessage() . "\n");
    exit(1);
}
