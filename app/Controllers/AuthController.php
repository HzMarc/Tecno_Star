<?php

declare(strict_types=1);

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/LoginLimiter.php';

final class AuthController
{
    public function login(): void
    {
        $message = $_SESSION['google_error'] ?? null;
        unset($_SESSION['google_error']);
        $this->render('login', is_string($message) ? $message : null);
    }

    public function register(): void
    {
        $this->render('register');
    }

    public function authenticate(): void
    {
        if (!$this->validFields(['email', 'password'])) {
            return;
        }
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $limiter = new LoginLimiter(APP_STORAGE_PATH . '/security/login', LOGIN_PAIR_LIMIT, LOGIN_IP_LIMIT, LOGIN_WINDOW_SECONDS);
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $retry = $limiter->reserve($email, $ip);
        if ($retry > 0) {
            header('Retry-After: ' . $retry);
            ErrorHandler::respond(429, 'Demasiados intentos de acceso. Espera unos minutos e intenta nuevamente.');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $this->render('login', 'Ingresa un correo válido y una contraseña.', 'error');
            return;
        }

        $user = (new UserModel(Database::connection()))->findActiveByEmail($email);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            Logger::write('auth.login_failed', 'info');
            $this->render('login', 'El correo o la contraseña son incorrectos.', 'error');
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $limiter->clearPair($email, $ip);
        Auth::login($user);
        Logger::write('auth.login_succeeded', 'info');
        $_SESSION['flash_message'] = 'Bienvenido a Tecno Star. Ya puedes explorar nuestras categorías.';
        $_SESSION['flash_message_type'] = 'success';

        $destination = $user['nombre_rol'] === 'Administrador'
            ? app_url('/admin')
            : app_url('/store');
        $returnCode=$_SESSION['order_return_code']??null;unset($_SESSION['order_return_code']);
        if(is_string($returnCode) && preg_match('/^TS-[0-9]{6}-[A-F0-9]{12}$/D',$returnCode)) $destination=app_url('/orders/scan?codigo='.rawurlencode($returnCode));
        header('Location: ' . $destination);
        exit;
    }

    public function profile(): void
    {
        $user = Auth::requireUser();
        if ($user === null) {
            return;
        }

        $accountsEnabled=UserModel::accountsEnabled(Database::connection());
        require BASE_PATH . '/app/Views/auth/profile.php';
    }

    public function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        Logger::write('auth.logout', 'info');

        header('Location: ' . app_url('/login'));
        exit;
    }

    public function createAccount(): void
    {
        if (!$this->validFields(['names', 'last_names', 'phone', 'email', 'password', 'password_confirmation'])) {
            return;
        }
        $names = trim((string) ($_POST['names'] ?? ''));
        $lastNames = trim((string) ($_POST['last_names'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        if ($names === '' || $lastNames === '' || $phone === '') {
            $this->render('register', 'Ingresa tus nombres, apellidos y teléfono.', 'error');
            return;
        }

        if(mb_strlen($names)>100 || mb_strlen($lastNames)>100){$this->render('register','Los nombres y apellidos admiten hasta 100 caracteres.','error');return;}
        $phone=preg_replace('/[ -]/','',$phone);
        if(!preg_match('/^(?:\+?503)?[267][0-9]{7}$/D',$phone)){$this->render('register','El teléfono debe tener 8 dígitos y comenzar por 2, 6 o 7; por ejemplo, 7000-0000.','error');return;}

        if (strlen($email)>150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('register', 'Ingresa un correo electrónico válido.', 'error');
            return;
        }

        if (strlen($password) < 12 || strlen($password)>72 || str_contains($password,"\0")) {
            $this->render('register', 'La contraseña debe tener entre 12 y 72 bytes.', 'error');
            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->render('register', 'Las contraseñas no coinciden.', 'error');
            return;
        }

        $database = Database::connection();
        $userModel = new UserModel($database);

        if ($userModel->existsByEmail($email)) {
            $this->render('register', 'Ya existe una cuenta registrada con ese correo.', 'warning');
            return;
        }

        $username = $this->buildUsername($email);
        $userModel->createCustomer(
            $username,
            $names,
            $lastNames,
            $phone,
            $email,
            password_hash($password, PASSWORD_DEFAULT)
        );

        $this->render('login', 'Cuenta creada correctamente. Ya puedes iniciar sesión.', 'success');
    }

    private function render(string $view, ?string $message = null, string $messageType = 'error'): void
    {
        $messageType = in_array($messageType, ['error', 'warning', 'success'], true)
            ? $messageType
            : 'error';
        require BASE_PATH . '/app/Views/auth/' . $view . '.php';
    }

    private function validFields(array $keys): bool
    {
        foreach ($keys as $key) {
            if (isset($_POST[$key]) && !is_string($_POST[$key])) {
                ErrorHandler::respond(400, 'La solicitud no es válida.');
                return false;
            }
        }
        return true;
    }

    private function buildUsername(string $email): string
    {
        $username = strtolower((string) strstr($email, '@', true));
        $username = preg_replace('/[^a-z0-9._-]/', '', $username) ?: 'cliente';
        $username .= '-' . substr(hash('sha256', $email), 0, 8);

        return substr($username, 0, 50);
    }
}
