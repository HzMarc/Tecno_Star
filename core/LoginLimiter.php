<?php

declare(strict_types=1);

final class LoginStorageException extends RuntimeException {}

final class LoginLimiter
{
    public function __construct(private string $directory, private int $pairLimit, private int $ipLimit, private int $window) {}

    /** Reserva antes de verificar la clave para limitar tambien solicitudes simultaneas. */
    public function reserve(string $email, string $ip, ?int $now = null): int
    {
        $now ??= time();
        // Siempre IP antes del par. Un IP bloqueado no puede crear archivos
        // ilimitados enviando correos diferentes.
        $keys = [hash('sha256', 'ip|' . $ip) => $this->ipLimit,
            hash('sha256', 'pair|' . strtolower(trim($email)) . '|' . $ip) => $this->pairLimit];
        $handles = [];
        $guard = null;
        try {
            if (!is_dir($this->directory) && !@mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
                throw new LoginStorageException('Login storage unavailable');
            }
            $guard = $this->guard(LOCK_SH);
            $states = [];
            foreach ($keys as $key => $limit) {
                $file = @fopen($this->directory . '/' . $key . '.json', 'c+');
                if ($file === false) {
                    throw new LoginStorageException('Login storage unavailable');
                }
                $handles[$key] = $file;
                @chmod($this->directory . '/' . $key . '.json', 0600);
                if (!flock($file, LOCK_EX)) {
                    throw new LoginStorageException('Login lock unavailable');
                }
                $raw = stream_get_contents($file);
                $state = $raw === '' ? ['count' => 0, 'expires' => $now + $this->window] : json_decode($raw, true);
                if (!is_array($state) || !isset($state['count'], $state['expires']) || !is_int($state['count']) || !is_int($state['expires']) || $state['count'] < 0) {
                    throw new LoginStorageException('Invalid login storage');
                }
                if ($state['expires'] <= $now) {
                    $state = ['count' => 0, 'expires' => $now + $this->window];
                }
                if ($state['count'] >= $limit) {
                    return max(1, $state['expires'] - $now);
                }
                $states[$key] = $state;
            }
            foreach ($states as $key => $state) {
                $state['count']++;
                $encoded = json_encode($state, JSON_THROW_ON_ERROR);
                rewind($handles[$key]);
                if (!ftruncate($handles[$key], 0) || fwrite($handles[$key], $encoded) !== strlen($encoded) || !fflush($handles[$key])) {
                    throw new LoginStorageException('Login storage write failed');
                }
            }
            return 0;
        } finally {
            foreach ($handles as $file) {
                flock($file, LOCK_UN);
                fclose($file);
            }
            if (is_resource($guard)) {
                flock($guard, LOCK_UN);
                fclose($guard);
            }
        }
    }

    public function clearPair(string $email, string $ip): void
    {
        $guard = $this->guard(LOCK_SH);
        $path = $this->directory . '/' . hash('sha256', 'pair|' . strtolower(trim($email)) . '|' . $ip) . '.json';
        $file = @fopen($path, 'c+');
        if ($file === false) {
            flock($guard, LOCK_UN);
            fclose($guard);
            throw new LoginStorageException('Login storage unavailable');
        }
        try {
            if (!flock($file, LOCK_EX) || !ftruncate($file, 0) || !fflush($file)) {
                throw new LoginStorageException('Login storage reset failed');
            }
        } finally {
            flock($file, LOCK_UN);
            fclose($file);
            flock($guard, LOCK_UN);
            fclose($guard);
        }
    }

    private function guard(int $mode)
    {
        $guard = @fopen($this->directory . '/.maintenance.lock', 'c');
        if ($guard === false) {
            throw new LoginStorageException('Login maintenance unavailable');
        }
        if (!flock($guard, $mode)) {
            fclose($guard);
            throw new LoginStorageException('Login maintenance unavailable');
        }
        return $guard;
    }

    public function prune(bool $dryRun = true, ?int $now = null): int
    {
        if (!is_dir($this->directory)) {
            return 0;
        }
        $now ??= time();
        // Bloqueo global exclusivo: no borrar archivos abiertos por reservas.
        $guard = $this->guard(LOCK_EX);
        $count = 0;
        try {
            foreach (glob($this->directory . '/*.json') ?: [] as $path) {
                if (!preg_match('/^[a-f0-9]{64}\.json$/D', basename($path))) {
                    continue;
                }
                $raw = file_get_contents($path);
                $state = $raw === '' ? null : json_decode($raw, true);
                if (($raw === '' && filemtime($path) < $now - $this->window)
                    || (is_array($state) && isset($state['expires']) && is_int($state['expires']) && $state['expires'] <= $now)) {
                    if (!$dryRun && !unlink($path)) {
                        throw new LoginStorageException('Login cleanup failed');
                    }
                    $count++;
                }
            }
        } finally {
            flock($guard, LOCK_UN);
            fclose($guard);
        }
        return $count;
    }
}
