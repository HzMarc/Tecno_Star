<?php

declare(strict_types=1);

function product_image_url(?string $path): string
{
    $path ??= '';
    return str_starts_with($path, '/uploads/products/') ? app_url($path) : $path;
}

function normalize_app_url(string $value): string
{
    $value = trim($value);
    if (str_starts_with($value, '//')) {
        throw new RuntimeException('APP_URL no admite URLs relativas al protocolo.');
    }
    $value = rtrim($value, '/');
    if ($value === '') {
        return '';
    }
    if (preg_match('/[\x00-\x20\x7F\\\\]/', $value) || str_starts_with($value, '//')) {
        throw new RuntimeException('APP_URL invalida. Usa una ruta local o una URL http/https sin consulta ni fragmento.');
    }
    $parts = parse_url($value);
    if ($parts === false || isset($parts['query']) || isset($parts['fragment']) || isset($parts['user']) || isset($parts['pass'])) {
        throw new RuntimeException('APP_URL invalida.');
    }
    if (isset($parts['scheme'])) {
        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true) || empty($parts['host']) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('APP_URL debe usar http o https y un servidor valido.');
        }
    } elseif (!str_starts_with($value, '/')) {
        throw new RuntimeException('APP_URL debe comenzar por / o ser una URL http/https.');
    }
    $path = $parts['path'] ?? '';
    if (!preg_match('#^(/[a-zA-Z0-9._~-]+)*$#D', $path) || preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $path)) {
        throw new RuntimeException('La ruta de APP_URL contiene segmentos invalidos.');
    }
    return $value;
}

/** URL interna sin escape, para redirecciones y construccion de datos. */
function app_url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

/** URL escapada para atributos HTML. */
function app_url_html(string $path = ''): string
{
    return htmlspecialchars(app_url($path), ENT_QUOTES, 'UTF-8');
}
