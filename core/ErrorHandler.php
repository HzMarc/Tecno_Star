<?php

declare(strict_types=1);

require_once __DIR__ . '/Logger.php';

final class ErrorHandler
{
    private static ?string $requestId = null;
    private static int $bufferLevel = 0;
    private static bool $handled = false;

    public static function requestId(): string
    {
        return self::$requestId ??= bin2hex(random_bytes(8));
    }

    public static function register(): void
    {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(E_ALL);
        self::$bufferLevel = ob_get_level();
        ob_start();
        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        set_exception_handler(static function (Throwable $exception): void {
            self::handle($exception);
        });
        register_shutdown_function(static function (): void {
            $error = error_get_last();
            if (!self::$handled && $error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                self::handle(new ErrorException('Fatal application error', 0, $error['type'], $error['file'], $error['line']));
            }
        });
    }

    public static function handle(Throwable $exception): void
    {
        self::$handled = true;
        while (ob_get_level() > self::$bufferLevel) {
            ob_end_clean();
        }
        $status = $exception instanceof PDOException || $exception instanceof LoginStorageException ? 503 : 500;
        Logger::write('request.failed', 'error', $exception, ['status' => $status]);
        self::respond($status, 'No fue posible completar la solicitud. Intenta nuevamente.');
    }

    public static function respond(int $status, string $message): void
    {
        http_response_code($status);
        header('Cache-Control: no-store');
        header('X-Request-ID: ' . self::requestId());
        if (self::wantsJson()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $message, 'request_id' => self::requestId()], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } else {
            header('Content-Type: text/html; charset=utf-8');
            echo '<!doctype html><html lang="es"><meta charset="UTF-8"><title>Solicitud no completada</title><main><h1>Solicitud no completada</h1><p>'
                . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p><p>Referencia: '
                . self::requestId() . '</p></main></html>';
        }
    }

    public static function wantsJson(): bool
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        return (bool) preg_match('~/(?:shopping/(?:state|update)|product/data)$~', $path)
            || str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
            || str_contains(strtolower($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json');
    }

    public static function finish(): void
    {
        if (ob_get_level() > self::$bufferLevel) {
            ob_end_flush();
        }
    }
}
