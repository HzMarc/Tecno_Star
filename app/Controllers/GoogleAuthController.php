<?php

declare(strict_types=1);

require_once BASE_PATH . '/core/GoogleOAuth.php';
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/LoginLimiter.php';
require_once BASE_PATH . '/app/Models/GoogleAccountModel.php';

final class GoogleAuthController
{
    public function start(): void
    {
        try {
            if (!GoogleOAuth::enabled()) {
                throw new GoogleOAuthException('El acceso con Google no está disponible en este momento.');
            }
            // Use the registered origin so the same session cookie reaches the callback.
            $origin = substr(GOOGLE_REDIRECT_URI, 0, -strlen('/auth/google/callback'));
            $target = parse_url($origin);
            $expectedHost = $target['host'] . (isset($target['port']) ? ':' . $target['port'] : '');
            $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
            if (strtolower($_SERVER['HTTP_HOST'] ?? '') !== strtolower($expectedHost) || $scheme !== $target['scheme']) {
                header('Location: ' . $origin . '/auth/google');
                return;
            }
            Database::connection()->query('SELECT google_subject FROM tbgoogleidentidades LIMIT 0');
            header('Location: ' . GoogleOAuth::begin());
        } catch (Throwable $error) {
            $this->failure($error);
        }
    }

    public function callback(): void
    {
        header('Referrer-Policy: no-referrer');
        try {
            $identity = GoogleOAuth::callback($_GET);
            $model = new GoogleAccountModel(Database::connection());
            $user = $model->linked($identity['subject']);
            if ($user !== null) {
                $this->finish($user);
                return;
            }
            session_regenerate_id(true);
            $_SESSION['google_pending'] = $identity;
            header('Location: ' . app_url('/auth/google/complete'));
        } catch (Throwable $error) {
            $this->failure($error);
        }
    }

    public function complete(): void
    {
        $identity = $_SESSION['google_pending'] ?? null;
        if (!is_array($identity) || !GoogleOAuth::fresh($identity)) {
            unset($_SESSION['google_pending']);
            $this->failure(new GoogleOAuthException('La verificación de Google venció. Inicia el acceso nuevamente.'));
            return;
        }
        $model = new GoogleAccountModel(Database::connection());
        $existingAccount = $model->emailExists($identity['email']);
        $message = null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $fields = [];
            foreach (['names', 'last_names', 'phone', 'password'] as $key) {
                if (isset($_POST[$key]) && (!is_string($_POST[$key]) || strlen($_POST[$key]) > 512)) {
                    ErrorHandler::respond(400, 'La solicitud no es válida.');
                    return;
                }
                $fields[$key] = $_POST[$key] ?? '';
            }
            $limiter = new LoginLimiter(APP_STORAGE_PATH . '/security/login', LOGIN_PAIR_LIMIT, LOGIN_IP_LIMIT, LOGIN_WINDOW_SECONDS);
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $retry = $limiter->reserve($identity['email'], $ip);
            if ($retry > 0) {
                header('Retry-After: ' . $retry);
                ErrorHandler::respond(429, 'Demasiados intentos. Espera unos minutos e intenta nuevamente.');
                return;
            }
            try {
                $user = $model->complete($identity, $fields);
                unset($_SESSION['google_pending']);
                $limiter->clearPair($identity['email'], $ip);
                $this->finish($user);
                return;
            } catch (GoogleOAuthException $error) {
                $message = $error->getMessage();
            }
        }
        require BASE_PATH . '/app/Views/auth/google-complete.php';
    }

    private function finish(array $user): void
    {
        Auth::login($user);
        unset($_SESSION['google_attempt'], $_SESSION['google_pending']);
        Logger::write('auth.google_succeeded', 'info');
        $_SESSION['flash_message'] = 'Bienvenido a Tecno Star.';
        $_SESSION['flash_message_type'] = 'success';
        $destination = app_url($user['nombre_rol'] === 'Administrador' ? '/admin' : '/store');
        $returnCode = $_SESSION['order_return_code'] ?? null;
        unset($_SESSION['order_return_code']);
        if (is_string($returnCode) && preg_match('/^TS-[0-9]{6}-[A-F0-9]{12}$/D', $returnCode)) {
            $destination = app_url('/orders/scan?codigo=' . rawurlencode($returnCode));
        }
        header('Location: ' . $destination);
    }

    private function failure(Throwable $error): void
    {
        unset($_SESSION['google_attempt'], $_SESSION['google_pending']);
        Logger::write('auth.google_failed', 'warning', $error);
        $_SESSION['google_error'] = $error instanceof GoogleOAuthException
            ? $error->getMessage() : 'No fue posible conectar con Google. Intenta nuevamente en unos minutos.';
        // Remove authorization codes from the address bar before rendering any assets.
        header('Location: ' . app_url('/login'));
    }
}
