<?php

declare(strict_types=1);

require_once __DIR__ . '/UserModel.php';

final class GoogleAccountModel
{
    public function __construct(private PDO $database) {}

    public function linked(string $subject): ?array
    {
        $query = $this->database->prepare('SELECT id_usuario FROM tbgoogleidentidades WHERE google_subject = ?');
        $query->execute([$subject]);
        $id = $query->fetchColumn();
        if ($id === false) {
            return null;
        }
        $user = (new UserModel($this->database))->findActiveById((int) $id);
        if ($user === null) {
            throw new GoogleOAuthException('Esta cuenta no está disponible. Contacta con Tecno Star.');
        }
        return $user;
    }

    public function emailExists(string $email): bool
    {
        return (new UserModel($this->database))->existsByEmail($email);
    }

    /** An existing email is never enough to link an account: require its local password. */
    public function complete(array $identity, array $fields): array
    {
        $this->database->beginTransaction();
        try {
            $query = $this->database->prepare('SELECT * FROM tbusuarios WHERE correo = ? FOR UPDATE');
            $query->execute([$identity['email']]);
            $user = $query->fetch();
            if ($user !== false) {
                if ($user['estado'] !== 'activo' || !password_verify($fields['password'] ?? '', $user['password_hash'])) {
                    throw new GoogleOAuthException('No fue posible vincular la cuenta. Revisa tu contraseña de Tecno Star.');
                }
                $userId = (int) $user['id_usuario'];
            } else {
                $names = trim($fields['names'] ?? '');
                $lastNames = trim($fields['last_names'] ?? '');
                $phone = preg_replace('/[ -]/', '', trim($fields['phone'] ?? ''));
                if ($names === '' || $lastNames === '' || mb_strlen($names) > 100 || mb_strlen($lastNames) > 100) {
                    throw new GoogleOAuthException('Ingresa tus nombres y apellidos, de hasta 100 caracteres cada uno.');
                }
                if (!preg_match('/^(?:\+?503)?[267][0-9]{7}$/D', $phone)) {
                    throw new GoogleOAuthException('Ingresa un teléfono de El Salvador, por ejemplo, 7000-0000.');
                }
                $role = $this->database->query("SELECT id_rol FROM tbroles WHERE nombre_rol = 'Cliente' LIMIT 1")->fetchColumn();
                if ($role === false) {
                    throw new RuntimeException('El rol Cliente no está configurado.');
                }
                $username = 'google-' . substr(hash('sha256', $identity['subject']), 0, 40);
                // No shared/default password: Google customers can set one through recovery later.
                $passwordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
                $query = $this->database->prepare('INSERT INTO tbusuarios (id_rol,nombre_usuario,nombres,apellidos,correo,password_hash) VALUES (?,?,?,?,?,?)');
                $query->execute([$role, $username, $names, $lastNames, $identity['email'], $passwordHash]);
                $userId = (int) $this->database->lastInsertId();
                $query = $this->database->prepare('INSERT INTO tbclientes (id_usuario,nombres,apellidos,telefono,correo_contacto) VALUES (?,?,?,?,?)');
                $query->execute([$userId, $names, $lastNames, $phone, $identity['email']]);
            }
            $query = $this->database->prepare('INSERT INTO tbgoogleidentidades (google_subject,id_usuario) VALUES (?,?)');
            $query->execute([$identity['subject'], $userId]);
            $result = (new UserModel($this->database))->findActiveById($userId);
            if ($result === null) {
                throw new GoogleOAuthException('Esta cuenta no está disponible.');
            }
            $this->database->commit();
            return $result;
        } catch (Throwable $error) {
            if ($this->database->inTransaction()) {
                $this->database->rollBack();
            }
            if ($error instanceof PDOException && $error->getCode() === '23000') {
                throw new GoogleOAuthException('La cuenta ya está registrada o vinculada. Inicia sesión nuevamente.');
            }
            throw $error;
        }
    }
}
