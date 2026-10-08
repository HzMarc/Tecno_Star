<?php

declare(strict_types=1);

final class UserModel
{
    public function __construct(private PDO $database)
    {
    }

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
            "SELECT id_usuario, id_rol, nombres, apellidos, correo, password_hash
             FROM tbusuarios
             WHERE correo = :correo AND estado = 'activo'
             LIMIT 1"
        );
        $statement->execute(['correo' => $email]);
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
