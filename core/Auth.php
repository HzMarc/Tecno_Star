<?php

declare(strict_types=1);

require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/app/Models/UserModel.php';
require_once __DIR__ . '/Csrf.php';

final class Auth
{
    /** Revalida contra la base. Nunca autoriza con el rol anterior de la sesion. */
    public static function current(): ?array
    {
        $id = filter_var($_SESSION['user']['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            if (isset($_SESSION['user'])) {
                self::forget();
            }
            return null;
        }
        if (!SessionManager::validAuthentication()) {
            Logger::write('auth.session_expired', 'info');
            self::forget();
            return null;
        }
        $user = (new UserModel(Database::connection()))->findActiveById($id);
        if ($user === null) {
            self::forget();
            return null;
        }
        $signature=hash('sha256',$user['password_hash'].'|'.($user['session_version']??0));
        if(!is_string($_SESSION['user']['credential_tag']??null) || !hash_equals($_SESSION['user']['credential_tag'],$signature)){self::forget();return null;}
        self::store($user);
        return $user;
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        Csrf::rotate();
        SessionManager::authenticated();
        self::store($user);
    }

    private static function store(array $user): void
    {
        $_SESSION['user'] = [
            'id' => (int) $user['id_usuario'],
            'role_id' => (int) $user['id_rol'],
            'role' => $user['nombre_rol'],
            'name' => $user['nombres'] . ' ' . $user['apellidos'],
            'email' => $user['correo'],
            'credential_tag'=>hash('sha256',$user['password_hash'].'|'.($user['session_version']??0)),
            'permiso_pedidos'=>$user['permiso_pedidos']??null,
        ];
    }

    public static function forget(): void
    {
        unset($_SESSION['user']);
        SessionManager::clearAuthentication();
        Csrf::rotate();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function requireUser(bool $json = false): ?array
    {
        $user = self::current();
        if ($user !== null) {
            return $user;
        }
        if ($json) {
            ErrorHandler::respond(401, 'Inicia sesión para continuar.');
        } else {
            header('Location: ' . app_url('/login'));
        }
        return null;
    }

    public static function requireRole(string $role): ?array
    {
        $user = self::current();
        if ($user !== null && $user['nombre_rol'] === $role) {
            return $user;
        }
        ErrorHandler::respond(403, 'Acceso no autorizado');
        return null;
    }
}
