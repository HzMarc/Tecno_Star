<?php

declare(strict_types=1);

final class GoogleLoginUpgrade
{
    public static function apply(PDO $db): void
    {
        $lock = 'ts_google_' . substr(hash('sha256', (string) $db->query('SELECT DATABASE()')->fetchColumn()), 0, 32);
        $query = $db->prepare('SELECT GET_LOCK(?,0)');
        $query->execute([$lock]);
        if ((int) $query->fetchColumn() !== 1) {
            throw new RuntimeException('Otra actualización está en curso.');
        }
        try {
            $sql = file_get_contents(__DIR__ . '/migrations/009_google_login.sql');
            $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
            $historyTable = in_array('schema_migrations', $tables, true) ? 'schema_migrations' : 'google_module_migrations';
            if (!in_array($historyTable, $tables, true)) {
                $db->exec('CREATE TABLE google_module_migrations (version VARCHAR(150) PRIMARY KEY, checksum CHAR(64) NOT NULL, status VARCHAR(20) NOT NULL, applied_at DATETIME NULL) ENGINE=InnoDB');
            }
            $query = $db->prepare('SELECT checksum,status FROM ' . $historyTable . ' WHERE version=?');
            $query->execute(['009_google_login.sql']);
            $old = $query->fetch(PDO::FETCH_ASSOC);
            if ($old !== false) {
                if ($old['status'] !== 'applied' || !hash_equals($old['checksum'], hash('sha256', $sql))) {
                    throw new RuntimeException('Actualización de Google incompleta o modificada.');
                }
                self::verify($db);
                return;
            }
            if (in_array('tbgoogleidentidades', $tables, true)) {
                throw new RuntimeException('Existe una tabla de Google sin historial. Revisa antes de continuar.');
            }
            $query = $db->prepare('INSERT INTO ' . $historyTable . " (version,checksum,status) VALUES (?,?,'applying')");
            $query->execute(['009_google_login.sql', hash('sha256', $sql)]);
            $db->exec($sql);
            self::verify($db);
            $db->exec('UPDATE ' . $historyTable . " SET status='applied',applied_at=UTC_TIMESTAMP() WHERE version='009_google_login.sql'");
        } finally {
            $query = $db->prepare('SELECT RELEASE_LOCK(?)');
            $query->execute([$lock]);
        }
    }

    public static function verify(PDO $db): void
    {
        $db->query('SELECT google_subject,id_usuario,created_at FROM tbgoogleidentidades LIMIT 0');
        $unique = [];
        foreach ($db->query('SHOW INDEX FROM tbgoogleidentidades')->fetchAll(PDO::FETCH_ASSOC) as $index) {
            if (!(int) $index['Non_unique']) {
                $unique[$index['Key_name']][] = $index['Column_name'];
            }
        }
        if (!in_array(['google_subject'], $unique, true) || !in_array(['id_usuario'], $unique, true)) {
            throw new RuntimeException('La identidad de Google requiere índices únicos.');
        }
    }
}
