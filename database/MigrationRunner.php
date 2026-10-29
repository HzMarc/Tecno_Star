<?php

declare(strict_types=1);

/** Migraciones hacia adelante. MySQL confirma DDL de forma implicita. */
final class MigrationRunner
{
    public function __construct(private PDO $database, private string $directory)
    {
    }

    public function migrate(): array
    {
        $lockName = 'tecno_star_migrate_' . substr(hash('sha256', (string) $this->database->query('SELECT DATABASE()')->fetchColumn()), 0, 32);
        $lock = $this->database->prepare('SELECT GET_LOCK(:name, 0)');
        $lock->execute(['name' => $lockName]);
        if ((int) $lock->fetchColumn() !== 1) {
            throw new RuntimeException('Otra migracion esta en curso.');
        }

        try {
            $tables = $this->database->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('schema_migrations', $tables, true)) {
                if ($tables !== []) {
                    throw new RuntimeException('La base contiene tablas sin historial de migraciones. Utiliza una base nueva; no se adopta ni sobrescribe el esquema existente.');
                }
                $this->database->exec("CREATE TABLE schema_migrations (
                    version VARCHAR(150) PRIMARY KEY,
                    checksum CHAR(64) NOT NULL,
                    status VARCHAR(20) NOT NULL,
                    applied_at TIMESTAMP NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }

            $files = glob($this->directory . '/*.sql') ?: [];
            sort($files, SORT_STRING);
            if ($files === []) {
                throw new RuntimeException('No hay migraciones disponibles.');
            }
            $history = $this->database->query('SELECT version, checksum, status FROM schema_migrations ORDER BY version')->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
            foreach ($history as $version => $record) {
                $path = $this->directory . '/' . $version;
                if (!in_array($path, $files, true) || !hash_equals($record['checksum'], hash_file('sha256', $path))) {
                    throw new RuntimeException('Migracion aplicada ausente o modificada: ' . $version);
                }
                if ($record['status'] !== 'applied') {
                    throw new RuntimeException('Migracion incompleta: ' . $version . '. Revisa el esquema y restaura el respaldo o utiliza una base nueva antes de continuar.');
                }
            }

            $applied = [];
            foreach ($files as $file) {
                $version = basename($file);
                if (isset($history[$version])) {
                    continue;
                }
                if ($history !== [] && strcmp($version, max(array_keys($history))) < 0) {
                    throw new RuntimeException('Migracion agregada fuera de orden: ' . $version);
                }
                $insert = $this->database->prepare("INSERT INTO schema_migrations (version, checksum, status) VALUES (?, ?, 'applying')");
                $insert->execute([$version, hash_file('sha256', $file)]);
                $sql = file_get_contents($file);
                if ($sql === false) {
                    throw new RuntimeException('No se pudo leer ' . $version);
                }
                // Solo SQL simple versionado: sin rutinas, DELIMITER o ';' en literales.
                $sql = preg_replace('/^\s*--[^\r\n]*/m', '', $sql);
                foreach (explode(';', $sql) as $statement) {
                    if (trim($statement) !== '') {
                        $this->database->exec($statement);
                    }
                }
                $update = $this->database->prepare("UPDATE schema_migrations SET status = 'applied', applied_at = CURRENT_TIMESTAMP WHERE version = ?");
                $update->execute([$version]);
                $applied[] = $version;
            }
            return $applied;
        } finally {
            $release = $this->database->prepare('SELECT RELEASE_LOCK(:name)');
            $release->execute(['name' => $lockName]);
        }
    }
}
