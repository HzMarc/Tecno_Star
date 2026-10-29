<?php

declare(strict_types=1);

final class Logger
{
    public static function write(string $event, string $level = 'error', ?Throwable $exception = null, array $context = []): void
    {
        $root = defined('APP_STORAGE_PATH') ? APP_STORAGE_PATH : dirname(__DIR__) . '/storage';
        $directory = $root . '/logs';
        try {
            if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
                throw new RuntimeException('Log directory unavailable');
            }
            $record = ['time' => gmdate('c'), 'level' => $level, 'event' => $event, 'request_id' => ErrorHandler::requestId()];
            // Lista cerrada: nunca serializar POST, cookies, credenciales ni mensajes SQL.
            foreach (['status', 'reason', 'retry_after'] as $key) {
                if (isset($context[$key]) && is_scalar($context[$key])) {
                    $record[$key] = $context[$key];
                }
            }
            if ($exception !== null) {
                $record['exception'] = get_class($exception);
                $record['code'] = (string) $exception->getCode();
                $record['source'] = self::source($exception->getFile());
                $record['line'] = $exception->getLine();
                $record['trace'] = array_map(static fn (array $frame): array => [
                    'source' => self::source($frame['file'] ?? ''),
                    'line' => $frame['line'] ?? null,
                    'call' => ($frame['class'] ?? '') . ($frame['type'] ?? '') . ($frame['function'] ?? ''),
                ], array_slice($exception->getTrace(), 0, 12));
            }
            $path = $directory . '/app-' . gmdate('Y-m-d') . '.log';
            $lock = @fopen($directory . '/.write.lock', 'c');
            if ($lock === false) {
                throw new RuntimeException('Log lock unavailable');
            }
            try {
                if (!flock($lock, LOCK_EX)) {
                    throw new RuntimeException('Log lock unavailable');
                }
                clearstatcache(true, $path);
                if (is_file($path) && filesize($path) >= 5 * 1024 * 1024) {
                    if (!@rename($path, $directory . '/app-' . gmdate('Y-m-d-His') . '-' . bin2hex(random_bytes(4)) . '.log')) {
                        throw new RuntimeException('Log rotation unavailable');
                    }
                }
                $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR) . "\n";
                if (@file_put_contents($path, $line, FILE_APPEND | LOCK_EX) === false) {
                    throw new RuntimeException('Log write unavailable');
                }
                @chmod($path, 0600);
            } finally {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
        } catch (Throwable) {
            // Mensaje fijo: tampoco se filtra informacion si falla el propio registro.
            @error_log('Tecno Star: no fue posible escribir el registro de la aplicacion. Referencia: ' . ErrorHandler::requestId());
        }
    }

    private static function source(string $path): string
    {
        $root = str_replace('\\', '/', dirname(__DIR__)) . '/';
        $path = str_replace('\\', '/', $path);
        return str_starts_with($path, $root) ? substr($path, strlen($root)) : basename($path);
    }
}
