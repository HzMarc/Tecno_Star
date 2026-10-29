<?php

declare(strict_types=1);

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

final class GoogleOAuthException extends RuntimeException {}

/** Authorization code flow. Tokens never leave this request or enter the session/log. */
final class GoogleOAuth
{
    public const LIFETIME = 600;

    public static function enabled(): bool
    {
        return GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_SECRET !== ''
            && self::validRedirect(GOOGLE_REDIRECT_URI) && class_exists(Google\Client::class);
    }

    public static function validRedirect(string $uri): bool
    {
        $parts = parse_url($uri);
        return $parts !== false && filter_var($uri, FILTER_VALIDATE_URL) !== false
            && !isset($parts['user']) && !isset($parts['pass']) && !isset($parts['query']) && !isset($parts['fragment'])
            && ($parts['path'] ?? '') === APP_BASE_PATH . '/auth/google/callback'
            && (($parts['scheme'] ?? '') === 'https'
                || (($parts['scheme'] ?? '') === 'http' && ($parts['host'] ?? '') === 'localhost'));
    }

    public static function client(): Google\Client
    {
        if (!self::enabled()) {
            throw new GoogleOAuthException('El acceso con Google no está disponible en este momento.');
        }
        $client = new Google\Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $client->setScopes(['openid', 'email', 'profile']);
        $client->setAccessType('online');
        $client->setPrompt('select_account');
        $client->setHttpClient(new GuzzleHttp\Client(['connect_timeout' => 10, 'timeout' => 20]));
        return $client;
    }

    public static function begin(): string
    {
        $client = self::client();
        unset($_SESSION['google_pending']);
        $attempt = [
            'state' => bin2hex(random_bytes(32)),
            'nonce' => bin2hex(random_bytes(32)),
            'verifier' => bin2hex(random_bytes(32)),
            'created' => time(),
        ];
        $_SESSION['google_attempt'] = $attempt;
        $client->setState($attempt['state']);
        return $client->createAuthUrl(null, [
            'nonce' => $attempt['nonce'],
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $attempt['verifier'], true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ]);
    }

    public static function callback(array $query, ?Google\Client $client = null): array
    {
        $attempt = $_SESSION['google_attempt'] ?? null;
        unset($_SESSION['google_attempt'], $_SESSION['google_pending']);
        if (!is_array($attempt) || !self::fresh($attempt)
            || !is_string($query['state'] ?? null) || !is_string($attempt['state'] ?? null)
            || !hash_equals($attempt['state'], $query['state'])) {
            throw new GoogleOAuthException('La solicitud de Google venció o no es válida. Inicia el acceso nuevamente.');
        }
        if (isset($query['error'])) {
            throw new GoogleOAuthException('Se canceló el acceso con Google. Puedes intentarlo de nuevo.');
        }
        if (!is_string($query['code'] ?? null) || $query['code'] === '' || strlen($query['code']) > 4096) {
            throw new GoogleOAuthException('Google no devolvió un código de acceso válido.');
        }
        $client ??= self::client();
        $tokens = $client->fetchAccessTokenWithAuthCode($query['code'], $attempt['verifier']);
        if (isset($tokens['error']) || !is_string($tokens['id_token'] ?? null)) {
            throw new GoogleOAuthException('No fue posible verificar tu cuenta de Google. Vuelve a intentarlo.');
        }
        $claims = $client->verifyIdToken($tokens['id_token']);
        if (!is_array($claims)) {
            throw new GoogleOAuthException('No fue posible verificar tu cuenta de Google.');
        }
        return self::identity($claims, $attempt['nonce']);
    }

    /** Called only after Google\Client has verified the JWT signature. */
    public static function identity(array $claims, string $nonce): array
    {
        if (!in_array($claims['iss'] ?? null, ['https://accounts.google.com', 'accounts.google.com'], true)
            || ($claims['aud'] ?? null) !== GOOGLE_CLIENT_ID
            || (isset($claims['azp']) && $claims['azp'] !== GOOGLE_CLIENT_ID)
            || !is_int($claims['exp'] ?? null) || $claims['exp'] <= time()
            || !is_int($claims['iat'] ?? null) || $claims['iat'] > time() + 60
            || !is_string($claims['nonce'] ?? null) || !hash_equals($nonce, $claims['nonce'])
            || !is_string($claims['sub'] ?? null) || !preg_match('/^[A-Za-z0-9_-]{1,255}$/D', $claims['sub'])
            || ($claims['email_verified'] ?? null) !== true
            || !is_string($claims['email'] ?? null) || strlen($claims['email']) > 150
            || !filter_var($claims['email'], FILTER_VALIDATE_EMAIL)) {
            throw new GoogleOAuthException('Google no devolvió una identidad válida con correo verificado.');
        }
        return [
            'subject' => $claims['sub'],
            'email' => strtolower($claims['email']),
            'names' => mb_substr(is_string($claims['given_name'] ?? null) ? $claims['given_name'] : '', 0, 100),
            'last_names' => mb_substr(is_string($claims['family_name'] ?? null) ? $claims['family_name'] : '', 0, 100),
            'created' => time(),
        ];
    }

    public static function fresh(array $value): bool
    {
        return is_int($value['created'] ?? null) && $value['created'] <= time()
            && time() - $value['created'] < self::LIFETIME;
    }
}
