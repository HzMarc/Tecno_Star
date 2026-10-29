<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database/cli.php';
require_once BASE_PATH . '/core/GoogleOAuth.php';
require_once BASE_PATH . '/app/Models/GoogleAccountModel.php';
require_once BASE_PATH . '/database/MigrationRunner.php';
require_once BASE_PATH . '/database/GoogleLoginUpgrade.php';

function googleCheck(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FALLO: ' . $message);
    }
    echo 'OK: ' . $message . PHP_EOL;
}

function googleReject(callable $action, string $message): void
{
    try {
        $action();
    } catch (GoogleOAuthException) {
        googleCheck(true, $message);
        return;
    }
    throw new RuntimeException('FALLO: ' . $message);
}

$server = null;
$testDatabase = 'tecno_star_test_google_' . bin2hex(random_bytes(5));
$testDatabaseCreated = false;
$exit = 1;
try {
    googleCheck(GoogleOAuth::enabled(), 'configuración privada de Google disponible');
    googleCheck(!GoogleOAuth::validRedirect('https://user@localhost' . APP_BASE_PATH . '/auth/google/callback'), 'callback rechaza credenciales en URL');
    googleCheck(!GoogleOAuth::validRedirect('http://192.0.2.1' . APP_BASE_PATH . '/auth/google/callback'), 'callback HTTP limitado a localhost');
    $_SESSION = [];
    parse_str((string) parse_url(GoogleOAuth::begin(), PHP_URL_QUERY), $query);
    $attempt = $_SESSION['google_attempt'];
    googleCheck($query['scope'] === 'openid email profile' && $query['access_type'] === 'online', 'solo identidad básica, sin acceso Gmail ni tokens offline');
    googleCheck($query['code_challenge_method'] === 'S256' && $query['nonce'] === $attempt['nonce'] && $query['state'] === $attempt['state'], 'state, nonce y PKCE por intento');
    googleReject(fn() => GoogleOAuth::callback(['state' => 'falso', 'code' => 'fake']), 'rechaza state ajeno antes de conectar con Google');
    googleCheck(!isset($_SESSION['google_attempt']), 'state consumido incluso tras un rechazo');
    $_SESSION['google_attempt'] = array_replace($attempt, ['created' => time() - GoogleOAuth::LIFETIME]);
    googleReject(fn() => GoogleOAuth::callback(['state' => $attempt['state'], 'code' => 'fake']), 'rechaza intentos vencidos');
    $_SESSION['google_attempt'] = $attempt;
    googleReject(fn() => GoogleOAuth::callback(['state' => $attempt['state'], 'error' => 'access_denied']), 'cancelación de Google controlada');

    $claims = ['iss' => 'https://accounts.google.com', 'aud' => GOOGLE_CLIENT_ID, 'iat' => time(), 'exp' => time() + 300, 'nonce' => $attempt['nonce'], 'sub' => '123456789', 'email' => 'synthetic@example.test', 'email_verified' => true, 'given_name' => 'Prueba', 'family_name' => 'Google'];
    foreach (['aud' => 'otro-cliente', 'iss' => 'https://attacker.example', 'nonce' => 'ajeno', 'email_verified' => false, 'exp' => time() - 1, 'sub' => '', 'email' => 'no-es-correo', 'iat' => time() + 300, 'azp' => 'otro-cliente'] as $field => $bad) {
        googleReject(fn() => GoogleOAuth::identity(array_replace($claims, [$field => $bad]), $attempt['nonce']), 'rechaza claim inválido: ' . $field);
    }
    $keyOptions = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
    if (is_file('C:/xampp/apache/conf/openssl.cnf')) {
        $keyOptions['config'] = 'C:/xampp/apache/conf/openssl.cnf';
    }
    $privateKey = openssl_pkey_new($keyOptions);
    $details = openssl_pkey_get_details($privateKey);
    $base64 = static fn(string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    $keys = ['keys' => [['kty' => 'RSA', 'kid' => 'synthetic-key', 'alg' => 'RS256', 'use' => 'sig', 'n' => $base64($details['rsa']['n']), 'e' => $base64($details['rsa']['e'])]]];
    $jwt = Firebase\JWT\JWT::encode($claims, $privateKey, 'RS256', 'synthetic-key');
    $mockClient = static function (string $token) use ($keys): Google\Client {
        $mock = new GuzzleHttp\Handler\MockHandler([
            new GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode(['access_token' => 'synthetic', 'token_type' => 'Bearer', 'expires_in' => 300, 'id_token' => $token], JSON_THROW_ON_ERROR)),
            new GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode($keys, JSON_THROW_ON_ERROR)),
        ]);
        $client = GoogleOAuth::client();
        $client->setCache(new Google\Auth\Cache\MemoryCacheItemPool());
        $client->setHttpClient(new GuzzleHttp\Client(['handler' => GuzzleHttp\HandlerStack::create($mock)]));
        return $client;
    };
    $_SESSION['google_attempt'] = $attempt;
    $identity = GoogleOAuth::callback(['state' => $attempt['state'], 'code' => 'synthetic'], $mockClient($jwt));
    googleCheck($identity['subject'] === '123456789' && !isset($_SESSION['google_attempt']) && !str_contains(json_encode($_SESSION), 'synthetic'), 'intercambio y firma RS256 verificados sin guardar tokens');
    googleReject(fn() => GoogleOAuth::callback(['state' => $attempt['state'], 'code' => 'synthetic']), 'callback no reutilizable');
    $otherKey = openssl_pkey_new($keyOptions);
    $badJwt = Firebase\JWT\JWT::encode($claims, $otherKey, 'RS256', 'synthetic-key');
    $_SESSION['google_attempt'] = $attempt;
    googleReject(fn() => GoogleOAuth::callback(['state' => $attempt['state'], 'code' => 'synthetic'], $mockClient($badJwt)), 'rechaza token firmado por una clave ajena');

    [$adminUser, $adminPassword] = migrationCredentials();
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
    $attributes = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
    $server = new PDO($dsn, $adminUser, $adminPassword, $attributes);
    $server->exec('CREATE DATABASE `' . $testDatabase . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    $testDatabaseCreated = true;
    $admin = new PDO($dsn . ';dbname=' . $testDatabase, $adminUser, $adminPassword, $attributes);
    (new MigrationRunner($admin, BASE_PATH . '/database/migrations'))->migrate();
    GoogleLoginUpgrade::apply($admin);
    GoogleLoginUpgrade::apply($admin);
    googleCheck(true, 'migración nueva e idempotencia de activación');
    $runtime = $admin;
    $model = new GoogleAccountModel($runtime);
    $fields = ['names' => 'Prueba', 'last_names' => 'Google', 'phone' => '7000-0000', 'password' => ''];
    googleReject(fn() => $model->complete($identity, array_replace($fields, ['phone' => '9999-9999'])), 'registro rechaza teléfono inválido');
    googleCheck((int) $admin->query('SELECT COUNT(*) FROM tbusuarios')->fetchColumn() === 0, 'registro inválido no deja filas parciales');
    $user = $model->complete($identity, $fields + ['id_rol' => '1']);
    googleCheck($user['nombre_rol'] === 'Cliente' && $user['telefono'] === '70000000', 'registro Google crea Cliente con teléfono normalizado');
    googleCheck($model->linked($identity['subject'])['id_usuario'] === $user['id_usuario'], 'acceso posterior usa subject estable');
    $admin->exec("UPDATE tbusuarios SET estado='inactivo' WHERE id_usuario=" . (int) $user['id_usuario']);
    googleReject(fn() => $model->linked($identity['subject']), 'cuenta inactiva no entra con Google');
    $admin->exec("UPDATE tbusuarios SET estado='activo' WHERE id_usuario=" . (int) $user['id_usuario']);
    $users = new UserModel($runtime);
    $users->createCustomer('existing', 'Cuenta', 'Existente', '70000001', 'existing@example.test', password_hash('ExistingGoogleTest2026!', PASSWORD_DEFAULT));
    $local = $users->findActiveByEmail('existing@example.test');
    $admin->exec("UPDATE tbusuarios SET id_rol=(SELECT id_rol FROM tbroles WHERE nombre_rol='Administrador') WHERE id_usuario=" . (int) $local['id_usuario']);
    $existingIdentity = array_replace($identity, ['subject' => '987654321', 'email' => 'existing@example.test']);
    googleReject(fn() => $model->complete($existingIdentity, ['password' => 'incorrecta']), 'correo coincidente no vincula sin contraseña válida');
    googleCheck($model->linked($existingIdentity['subject']) === null, 'fallo de contraseña no crea vínculo');
    $linked = $model->complete($existingIdentity, ['password' => 'ExistingGoogleTest2026!']);
    googleCheck($linked['id_usuario'] === $local['id_usuario'] && $linked['nombre_rol'] === 'Administrador', 'vinculación conserva cuenta, contraseña y rol del servidor');
    googleReject(fn() => $model->complete(array_replace($identity, ['email' => 'duplicate@example.test']), $fields), 'subject duplicado rechazado');
    googleCheck(!$users->existsByEmail('duplicate@example.test'), 'conflicto de vínculo revierte también la cuenta nueva');
    googleReject(fn() => $model->complete(array_replace($existingIdentity, ['subject' => 'second-subject']), ['password' => 'ExistingGoogleTest2026!']), 'una cuenta no admite otra identidad de Google');
    googleCheck((int) $admin->query('SELECT COUNT(*) FROM tbusuarios')->fetchColumn() === 2, 'sin usuarios duplicados ni cambios en otras cuentas');
    echo "Pruebas OAuth completadas en base aislada y con respuestas Google simuladas.\n";
    $exit = 0;
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . PHP_EOL);
} finally {
    if ($server !== null && $testDatabaseCreated) {
        $server->exec('DROP DATABASE `' . $testDatabase . '`');
    }
}
exit($exit);
