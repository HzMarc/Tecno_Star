<?php

declare(strict_types=1);

final class Router
{
    private array $routes = [];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            http_response_code(404);
            return 'Página no encontrada';
        }

        if (is_array($handler)) {
            [$class, $action] = $handler;
            return (new $class())->$action();
        }

        return $handler();
    }
}

