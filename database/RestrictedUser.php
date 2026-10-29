<?php

declare(strict_types=1);

final class RestrictedUser
{
    public const PERMISSIONS = [
        'tbgoogleidentidades' => 'SELECT, INSERT',
        'tbreinversiones'=>'SELECT, INSERT',
        'tbroles' => 'SELECT', 'tbusuarios' => 'SELECT, INSERT, UPDATE (nombres, apellidos, id_rol, estado, password_hash, session_version, cuenta_revision)', 'tbclientes' => 'SELECT, INSERT, UPDATE (nombres, apellidos, telefono)',
        'tbcategorias' => 'SELECT, INSERT, UPDATE', 'tbmarcas' => 'SELECT, INSERT, UPDATE', 'tbproductos' => 'SELECT, INSERT, UPDATE',
        'tbinventario' => 'SELECT, INSERT (id_producto), UPDATE (stock_actual, stock_minimo, stock_reservado)',
        'tbstockmovimientos' => 'SELECT, INSERT', 'tbpedidos' => 'SELECT, INSERT, UPDATE', 'tbventas' => 'SELECT, INSERT, UPDATE (estado)',
        'tbventasanulaciones'=>'SELECT, INSERT','tbcuentashistorial'=>'SELECT, INSERT',
        'tbclaverecuperacion'=>'SELECT, INSERT, UPDATE','tbpersonalpermisos'=>'SELECT, INSERT, UPDATE',
        'tbretirodetalles'=>'SELECT, INSERT', 'tbretirohistorial'=>'SELECT, INSERT',
        'tbretiocorreos'=>'SELECT, INSERT, UPDATE',
        'tbcarritos' => 'SELECT, INSERT, UPDATE',
        'tbshoppingoperaciones' => 'SELECT, INSERT',
        'tbdetallecarritos' => 'SELECT, INSERT, UPDATE, DELETE',
        'tblistasdeseos' => 'SELECT, INSERT, UPDATE', 'tbdetallelistasdeseos' => 'SELECT, INSERT, DELETE',
    ];

    /** Solo crea cuentas nuevas. No modifica privilegios ni claves de cuentas existentes. */
    public static function create(PDO $admin, string $database, string $user, string $host, string $password): void
    {
        if (!preg_match('/^[a-zA-Z0-9_]{1,64}$/D', $database)
            || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{0,31}$/D', $user)
            || !in_array($host, ['localhost', '127.0.0.1'], true)
            || $password === '') {
            throw new InvalidArgumentException('Destino o cuenta local invalida.');
        }
        $find = $admin->prepare('SELECT COUNT(*) FROM mysql.user WHERE User = ? AND Host = ?');
        $find->execute([$user, $host]);
        if ((int) $find->fetchColumn() > 0) {
            throw new RuntimeException('La cuenta ya existe. No se cambia su clave ni sus permisos.');
        }
        foreach (array_keys(self::PERMISSIONS) as $table) {
            $admin->query('SELECT 1 FROM `' . $database . '`.`' . $table . '` LIMIT 0');
        }
        $account = $admin->quote($user) . '@' . $admin->quote($host);
        $admin->exec('CREATE USER ' . $account . ' IDENTIFIED BY ' . $admin->quote($password));
        try {
            foreach (self::PERMISSIONS as $table => $privileges) {
                $admin->exec('GRANT ' . $privileges . ' ON `' . $database . '`.`' . $table . '` TO ' . $account);
            }
        } catch (Throwable $exception) {
            $admin->exec('DROP USER ' . $account);
            throw $exception;
        }
    }
}
