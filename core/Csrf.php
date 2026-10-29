<?php

declare(strict_types=1);

final class Csrf
{
    public static function token(): string
    {
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            self::rotate();
        }
        return $_SESSION['csrf_token'];
    }

    public static function rotate(): void
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function valid(mixed $token): bool
    {
        return is_string($token) && isset($_SESSION['csrf_token'])
            && is_string($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}
