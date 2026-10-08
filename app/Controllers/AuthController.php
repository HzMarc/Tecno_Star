<?php

declare(strict_types=1);

final class AuthController
{
    public function login(): void
    {
        $this->render('login');
    }

    public function register(): void
    {
        $this->render('register');
    }

    public function authenticate(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $this->render('login', 'Ingresa un correo válido y una contraseña.', 'error');
            return;
        }

        try {
            $user = (new UserModel(Database::connection()))->findActiveByEmail($email);
        } catch (PDOException $exception) {
            $this->render('login', 'No fue posible conectar con la base de datos. Intenta nuevamente.', 'error');
            return;
        }

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            $this->render('login', 'El correo o la contraseña son incorrectos.', 'error');
            return;
        }

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id_usuario'],
            'role_id' => (int) $user['id_rol'],
            'name' => $user['nombres'] . ' ' . $user['apellidos'],
            'email' => $user['correo'],
        ];
        $_SESSION['flash_message'] = 'Bienvenido a Tecno Star. Ya puedes explorar nuestras categorías.';
        $_SESSION['flash_message_type'] = 'success';

        header('Location: /Tecno_Star/public/store');
        exit;
    }

    public function createAccount(): void
    {
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

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('register', 'Ingresa un correo electrónico válido.', 'error');
            return;
        }

        if (strlen($password) < 8) {
            $this->render('register', 'La contraseña debe tener al menos 8 caracteres.', 'error');
            return;
        }

        if ($password !== $passwordConfirmation) {
            $this->render('register', 'Las contraseñas no coinciden.', 'error');
            return;
        }

        try {
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
        } catch (PDOException|RuntimeException $exception) {
            $this->render('register', 'No fue posible completar el registro. Intenta nuevamente.', 'error');
            return;
        }

        $this->render('login', 'Cuenta creada correctamente. Ya puedes iniciar sesión.', 'success');
    }

    public function google(): void
    {
        $this->render('login', 'El acceso con Google requiere configurar las credenciales OAuth.', 'warning');
    }

    private function render(string $view, ?string $message = null, string $messageType = 'error'): void
    {
        $messageType = in_array($messageType, ['error', 'warning', 'success'], true)
            ? $messageType
            : 'error';
        require BASE_PATH . '/app/Views/auth/' . $view . '.php';
    }

    private function buildUsername(string $email): string
    {
        $username = strtolower((string) strstr($email, '@', true));
        $username = preg_replace('/[^a-z0-9._-]/', '', $username) ?: 'cliente';
        $username .= '-' . substr(hash('sha256', $email), 0, 8);

        return substr($username, 0, 50);
    }
}
