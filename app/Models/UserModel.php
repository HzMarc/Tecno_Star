<?php

declare(strict_types=1);

final class UserModel
{
    public function __construct(private PDO $database)
    {
    }

    public static function accountsEnabled(PDO $db):bool
    {
        static $cache=null;$cache??=new WeakMap();
        if(!isset($cache[$db]))$cache[$db]=(bool)$db->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tbusuarios' AND COLUMN_NAME='session_version'")->fetchColumn();
        return $cache[$db];
    }
    private function permissionSql():string{return self::accountsEnabled($this->database)?'(SELECT pedidos FROM tbpersonalpermisos WHERE id_usuario=u.id_usuario)':'NULL';}

    public function existsByEmail(string $email): bool
    {
        $statement = $this->database->prepare(
            'SELECT 1 FROM tbusuarios WHERE correo = :correo LIMIT 1'
        );
        $statement->execute(['correo' => $email]);

        return $statement->fetchColumn() !== false;
    }

    public function findActiveByEmail(string $email): ?array
    {
        $statement = $this->database->prepare(
            "SELECT u.*,r.nombre_rol,c.telefono,".$this->permissionSql()." AS permiso_pedidos
             FROM tbusuarios u
             INNER JOIN tbroles r ON r.id_rol = u.id_rol
             LEFT JOIN tbclientes c ON c.id_usuario = u.id_usuario
             WHERE u.correo = :correo AND u.estado = 'activo'
             LIMIT 1"
        );
        $statement->execute(['correo' => $email]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function findActiveById(int $userId): ?array
    {
        $statement = $this->database->prepare(
            "SELECT u.*,r.nombre_rol,c.telefono,".$this->permissionSql()." AS permiso_pedidos
             FROM tbusuarios u
             INNER JOIN tbroles r ON r.id_rol = u.id_rol
             LEFT JOIN tbclientes c ON c.id_usuario = u.id_usuario
             WHERE u.id_usuario = :id_usuario AND u.estado = 'activo'
             LIMIT 1"
        );
        $statement->execute(['id_usuario' => $userId]);
        $user = $statement->fetch();

        return $user === false ? null : $user;
    }

    public function createCustomer(
        string $username,
        string $names,
        string $lastNames,
        string $phone,
        string $email,
        string $passwordHash
    ): void {
        $roleStatement = $this->database->prepare(
            "SELECT id_rol FROM tbroles WHERE nombre_rol = 'Cliente' LIMIT 1"
        );
        $roleStatement->execute();
        $roleId = $roleStatement->fetchColumn();

        if ($roleId === false) {
            throw new RuntimeException('El rol Cliente no está configurado.');
        }

        $this->database->beginTransaction();

        try {
            $userStatement = $this->database->prepare(
                'INSERT INTO tbusuarios
                    (id_rol, nombre_usuario, nombres, apellidos, correo, password_hash)
                 VALUES
                    (:id_rol, :nombre_usuario, :nombres, :apellidos, :correo, :password_hash)'
            );
            $userStatement->execute([
                'id_rol' => $roleId,
                'nombre_usuario' => $username,
                'nombres' => $names,
                'apellidos' => $lastNames,
                'correo' => $email,
                'password_hash' => $passwordHash,
            ]);

            $clientStatement = $this->database->prepare(
                'INSERT INTO tbclientes
                    (id_usuario, nombres, apellidos, telefono, correo_contacto)
                 VALUES
                    (:id_usuario, :nombres, :apellidos, :telefono, :correo_contacto)'
            );
            $clientStatement->execute([
                'id_usuario' => $this->database->lastInsertId(),
                'nombres' => $names,
                'apellidos' => $lastNames,
                'telefono' => $phone,
                'correo_contacto' => $email,
            ]);

            $this->database->commit();
        } catch (Throwable $exception) {
            $this->database->rollBack();
            throw $exception;
        }
    }
}
