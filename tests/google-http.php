<?php

declare(strict_types=1);
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once dirname(__DIR__) . '/config/config.php';
$base = substr(GOOGLE_REDIRECT_URI, 0, -strlen('/auth/google/callback'));
if (parse_url($base, PHP_URL_HOST) !== 'localhost') {
    throw new RuntimeException('Estas pruebas HTTP requieren el callback local.');
}
$cookie = '';
$request = static function (string $path, ?array $post = null) use ($base, &$cookie): array {
    $headers = ['Connection: close'];
    if ($cookie !== '') {
        $headers[] = 'Cookie: ' . $cookie;
    }
    if ($post !== null) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    }
    $response = file_get_contents($base . $path, false, stream_context_create(['http' => [
        'method' => $post === null ? 'GET' : 'POST', 'header' => implode("\r\n", $headers),
        'content' => $post === null ? '' : http_build_query($post),
        'ignore_errors' => true, 'follow_location' => 0, 'timeout' => 15,
    ]]));
    $status = 0;
    $location = '';
    foreach ($http_response_header as $header) {
        if (preg_match('~^HTTP/\S+ (\d+)~', $header, $match)) $status = (int) $match[1];
        if (preg_match('/^Set-Cookie: (TECNO_STAR_SESSION=[^;]*)/i', $header, $match)) $cookie = $match[1];
        if (preg_match('/^Location: (.+)$/i', $header, $match)) $location = $match[1];
    }
    return [$status, $response, $location];
};
$check = static function (bool $ok, string $message): void {
    if (!$ok) throw new RuntimeException('FALLO: ' . $message);
    echo 'OK: ' . $message . PHP_EOL;
};
foreach (['/login', '/register'] as $path) {
    [$status, $html] = $request($path);
    $check($status === 200 && str_contains($html, 'Continuar con Google'), 'botón Google visible en ' . $path);
    $check(!str_contains($html, GOOGLE_CLIENT_SECRET), 'secreto OAuth ausente del HTML');
}
[$status, , $location] = $request('/auth/google');
$check($status === 302 && parse_url($location, PHP_URL_HOST) === 'accounts.google.com', 'redirección a Google');
parse_str((string) parse_url($location, PHP_URL_QUERY), $parameters);
$check(($parameters['redirect_uri'] ?? '') === GOOGLE_REDIRECT_URI && ($parameters['scope'] ?? '') === 'openid email profile' && ($parameters['code_challenge_method'] ?? '') === 'S256', 'callback exacto, permisos mínimos y PKCE');
[$status, , $location] = $request('/auth/google/callback?state=' . rawurlencode($parameters['state']) . '&error=access_denied');
$check($status === 302 && $location === app_url('/login'), 'cancelación limpia URL y vuelve al login');
[$status, $html] = $request('/login');
$check($status === 200 && str_contains($html, 'Se canceló el acceso con Google'), 'cancelación con mensaje comprensible');
[$status, , $location] = $request('/auth/google/callback?state=forged&code=forged');
$check($status === 302 && $location === app_url('/login'), 'callback ajeno no autentica');
[$status] = $request('/auth/google/complete', ['password' => 'forged']);
$check($status === 403, 'vinculación POST requiere CSRF');
[$status, , $location] = $request('/auth/google/complete');
$check($status === 302 && $location === app_url('/login'), 'no completa registro sin identidad verificada en sesión');
echo "Pruebas HTTP de Google completadas sin crear cuentas reales.\n";
