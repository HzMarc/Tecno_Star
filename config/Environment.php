<?php

declare(strict_types=1);

final class Environment
{
    private array $values = [];

    public function __construct(string $path)
    {
        if (!is_file($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new RuntimeException('No se pudo leer la configuracion local.');
        }
        foreach ($lines as $index => $line) {
            $line = trim($index === 0 ? preg_replace('/^\xEF\xBB\xBF/', '', $line) : $line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!preg_match('/^([A-Z][A-Z0-9_]*)\s*=\s*(.*)$/D', $line, $match)) {
                throw new RuntimeException('Configuracion local invalida en linea ' . ($index + 1));
            }
            [$unused, $key, $value] = $match;
            if (array_key_exists($key, $this->values)) {
                throw new RuntimeException('Clave duplicada en configuracion local: ' . $key);
            }
            if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
                $quote = $value[0];
                if (strlen($value) < 2 || !str_ends_with($value, $quote)) {
                    throw new RuntimeException('Comillas sin cerrar en linea ' . ($index + 1));
                }
                $value = substr($value, 1, -1);
            } else {
                $value = rtrim(preg_replace('/\s+#.*$/', '', $value));
            }
            // Los valores son literales: no se ejecutan ni interpolan variables.
            $this->values[$key] = $value;
        }
    }

    public function get(string $key, ?string $default = null): string
    {
        $process = getenv($key);
        if ($process !== false) {
            return $process;
        }
        foreach ([$_ENV, $_SERVER] as $source) {
            if (isset($source[$key]) && is_string($source[$key])) {
                return $source[$key];
            }
        }
        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        }
        if ($default !== null) {
            return $default;
        }
        throw new RuntimeException('Falta la variable ' . $key . '. Copia .env.example a .env y configura sus valores.');
    }
}
