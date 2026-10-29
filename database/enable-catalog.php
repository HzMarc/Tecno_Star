<?php

declare(strict_types=1);

require_once __DIR__ . '/cli.php';

try {
    if (!in_array(DB_HOST, ['127.0.0.1','localhost'], true) || !preg_match('/^[a-zA-Z0-9_]{1,64}$/D', DB_NAME)) throw new RuntimeException('Solo se admite la instalación local configurada.');
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $runtime = new PDO($dsn, DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $current = (string)$runtime->query('SELECT CURRENT_USER()')->fetchColumn();
    if (!preg_match('/^([a-zA-Z][a-zA-Z0-9_]{0,31})@(localhost|127\.0\.0\.1)$/D', $current, $match) || $match[1] !== DB_USER || in_array(strtolower(DB_USER), ['root','mysql'], true)) throw new RuntimeException('Se requiere la cuenta limitada de la aplicación.');
    if (in_array('--verify', $argv, true)) {
        $grantee = "'" . $match[1] . "'@'" . $match[2] . "'";
        $tablePrivileges = $runtime->prepare('SELECT PRIVILEGE_TYPE FROM information_schema.TABLE_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=? AND TABLE_NAME=?');
        foreach (['tbproductos','tbcategorias','tbmarcas'] as $table) {
            $tablePrivileges->execute([$grantee, DB_NAME, $table]);
            $permissions = $tablePrivileges->fetchAll(PDO::FETCH_COLUMN);
            if (array_diff(['SELECT','INSERT','UPDATE'], $permissions) || in_array('DELETE', $permissions, true)) throw new RuntimeException('Permisos de catálogo incompletos.');
        }
        $columns = $runtime->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMN_PRIVILEGES WHERE GRANTEE=? AND TABLE_SCHEMA=? AND TABLE_NAME='tbinventario' AND PRIVILEGE_TYPE='INSERT'");
        $columns->execute([$grantee, DB_NAME]);
        if ($columns->fetchAll(PDO::FETCH_COLUMN) !== ['id_producto']) throw new RuntimeException('Permisos de inventario inesperados.');
        $tablePrivileges->execute([$grantee, DB_NAME, 'tbinventario']);
        if (array_intersect(['INSERT','UPDATE','DELETE'], $tablePrivileges->fetchAll(PDO::FETCH_COLUMN))) throw new RuntimeException('Inventario tiene permisos excesivos.');
        echo "Verificado: catálogo con SELECT, INSERT y UPDATE, sin DELETE; inventario con INSERT solo de id_producto, sin permisos de escritura a nivel de tabla. Los permisos por columna del Plan 4 se verifican con enable-inventory.php --verify.\n";
        exit;
    }
    if (!in_array('--apply', $argv, true)) {
        echo "Simulación: habilitar INSERT y UPDATE de productos, categorías y marcas; INSERT solo de id_producto en inventario. Sin DELETE, DDL ni cambios de stock. Ejecuta con --apply para aplicar.\n";
        exit;
    }
    [$user, $password] = migrationCredentials();
    $admin = new PDO($dsn, $user, $password, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $account = $admin->quote($match[1]) . '@' . $admin->quote($match[2]);
    foreach (['tbproductos','tbcategorias','tbmarcas'] as $table) {
        $admin->query('SELECT 1 FROM `' . DB_NAME . '`.`' . $table . '` LIMIT 0');
    }
    foreach (['tbproductos','tbcategorias','tbmarcas'] as $table) $admin->exec('GRANT INSERT, UPDATE ON `' . DB_NAME . '`.`' . $table . '` TO ' . $account);
    $admin->exec('GRANT INSERT (id_producto) ON `' . DB_NAME . '`.`tbinventario` TO ' . $account);
    echo "Permisos del catálogo aplicados a la cuenta local configurada. No se cambiaron claves, registros ni existencias.\n";
} catch (Throwable) {
    fwrite(STDERR, "No se pudieron comprobar o habilitar los permisos. Revisa la cuenta limitada y la conexión administrativa local. No se muestran credenciales.\n");
    exit(1);
}
