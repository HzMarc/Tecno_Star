<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once __DIR__ . '/Environment.php';
require_once __DIR__ . '/urls.php';
require_once BASE_PATH . '/core/ErrorHandler.php';
require_once BASE_PATH . '/core/Csrf.php';
require_once BASE_PATH . '/core/SessionManager.php';

$environment = new Environment(BASE_PATH . '/.env');
define('APP_NAME', $environment->get('APP_NAME', 'Tecno Star'));
define('APP_URL', normalize_app_url($environment->get('APP_URL')));
define('APP_BASE_PATH', (string) (parse_url(APP_URL, PHP_URL_PATH) ?? ''));
define('GOOGLE_CLIENT_ID', trim($environment->get('GOOGLE_CLIENT_ID', '')));
define('GOOGLE_CLIENT_SECRET', trim($environment->get('GOOGLE_CLIENT_SECRET', '')));
define('GOOGLE_REDIRECT_URI', trim($environment->get('GOOGLE_REDIRECT_URI', '')));

define('DB_HOST', $environment->get('DB_HOST'));
define('DB_PORT', $environment->get('DB_PORT'));
define('DB_NAME', $environment->get('DB_NAME'));
define('DB_USER', $environment->get('DB_USER'));
define('DB_PASSWORD', $environment->get('DB_PASSWORD'));

$integerSetting = static function (string $key, string $default, int $minimum, int $maximum) use ($environment): int {
    $value = filter_var($environment->get($key, $default), FILTER_VALIDATE_INT, ['options' => ['min_range' => $minimum, 'max_range' => $maximum]]);
    if ($value === false) {
        throw new RuntimeException('Configuracion numerica invalida: ' . $key);
    }
    return $value;
};
define('LOGIN_PAIR_LIMIT', $integerSetting('LOGIN_PAIR_LIMIT', '5', 1, 100));
define('LOGIN_IP_LIMIT', $integerSetting('LOGIN_IP_LIMIT', '30', 1, 1000));
define('LOGIN_WINDOW_SECONDS', $integerSetting('LOGIN_WINDOW_SECONDS', '900', 1, 86400));
define('SESSION_IDLE_SECONDS', $integerSetting('SESSION_IDLE_SECONDS', '1800', 1, 86400));
define('SESSION_ABSOLUTE_SECONDS', $integerSetting('SESSION_ABSOLUTE_SECONDS', '43200', 1, 2592000));
define('SESSION_REGENERATE_SECONDS', $integerSetting('SESSION_REGENERATE_SECONDS', '900', 1, 86400));
define('SESSION_PERSISTENT', $integerSetting('SESSION_PERSISTENT', '0', 0, 1) === 1);
define('LOG_RETENTION_DAYS', $integerSetting('LOG_RETENTION_DAYS', '14', 1, 365));
define('APP_STORAGE_PATH', rtrim($environment->get('APP_STORAGE_PATH', BASE_PATH . '/storage'), '/\\'));
if (!preg_match('~^(?:[A-Za-z]:[/\\\\]|/)~', APP_STORAGE_PATH) || SESSION_IDLE_SECONDS > SESSION_ABSOLUTE_SECONDS) {
    throw new RuntimeException('Ruta de almacenamiento o politica de sesiones invalida.');
}

if (!preg_match('/^[a-zA-Z0-9.:-]+$/D', DB_HOST)
    || filter_var(DB_PORT, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) === false
    || !preg_match('/^[a-zA-Z0-9_]{1,64}$/D', DB_NAME)
    || DB_USER === '') {
    throw new RuntimeException('Configuracion de base de datos invalida. Revisa host, puerto, nombre y usuario.');
}
unset($environment, $integerSetting);
