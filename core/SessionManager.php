<?php

declare(strict_types=1);

final class SessionManager
{
    public static function start(): void
    {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string) SESSION_ABSOLUTE_SECONDS);
        session_name('TECNO_STAR_SESSION');
        session_set_cookie_params([
            'lifetime' => SESSION_PERSISTENT ? SESSION_ABSOLUTE_SECONDS : 0,
            'path' => APP_BASE_PATH . '/',
            'secure' => strtolower((string) parse_url(APP_URL, PHP_URL_SCHEME)) === 'https' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function authenticated(?int $now = null): void
    {
        $now ??= time();
        $_SESSION['auth_started_at'] = $now;
        $_SESSION['auth_last_seen_at'] = $now;
        $_SESSION['auth_regenerated_at'] = $now;
    }

    public static function validAuthentication(?int $now = null): bool
    {
        $now ??= time();
        $started = $_SESSION['auth_started_at'] ?? null;
        $seen = $_SESSION['auth_last_seen_at'] ?? null;
        $regenerated = $_SESSION['auth_regenerated_at'] ?? null;
        if (!is_int($started) || !is_int($seen) || !is_int($regenerated)
            || $started < 0 || $seen < $started || $regenerated < $started
            || $started > $now || $seen > $now || $regenerated > $now
            || $now - $started >= SESSION_ABSOLUTE_SECONDS || $now - $seen >= SESSION_IDLE_SECONDS) {
            return false;
        }
        $_SESSION['auth_last_seen_at'] = $now;
        if ($now - $regenerated >= SESSION_REGENERATE_SECONDS && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION['auth_regenerated_at'] = $now;
        }
        return true;
    }

    public static function clearAuthentication(): void
    {
        unset($_SESSION['auth_started_at'], $_SESSION['auth_last_seen_at'], $_SESSION['auth_regenerated_at']);
    }
}
