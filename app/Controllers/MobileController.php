<?php

declare(strict_types=1);

require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/LoginLimiter.php';
require_once BASE_PATH . '/app/Models/OrderModel.php';

final class MobileController
{
    private function respond(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    private function input(): array
    {
        $raw = file_get_contents('php://input');
        $input = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($input)) {
            throw new InvalidArgumentException('La solicitud no tiene un formato válido.');
        }
        return $input;
    }

    private function userPayload(array $user): array
    {
        $query = Database::connection()->prepare('SELECT telefono FROM tbclientes WHERE id_usuario=? LIMIT 1');
        $query->execute([(int) $user['id_usuario']]);

        return [
            'id' => (int) $user['id_usuario'],
            'name' => trim((string) $user['nombres'] . ' ' . (string) $user['apellidos']),
            'email' => (string) $user['correo'],
            'phone' => (string) ($query->fetchColumn() ?: ''),
            'role' => (string) $user['nombre_rol'],
        ];
    }

    private function mobileSessionPayload(?array $user): array
    {
        return [
            'ok' => true,
            'authenticated' => $user !== null,
            'csrf' => Csrf::token(),
            'user' => $user === null ? null : $this->userPayload($user),
        ];
    }

    public function session(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $this->respond($this->mobileSessionPayload(Auth::current()));
    }

    public function login(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        try {
            $input = $this->input();
            $email = is_string($input['email'] ?? null) ? trim($input['email']) : '';
            $password = is_string($input['password'] ?? null) ? $input['password'] : '';
            if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
                $this->respond(['ok' => false, 'error' => 'Ingresa un correo y una contraseña válidos.'], 422);
                return;
            }

            $limiter = new LoginLimiter(APP_STORAGE_PATH . '/security/login', LOGIN_PAIR_LIMIT, LOGIN_IP_LIMIT, LOGIN_WINDOW_SECONDS);
            $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $retry = $limiter->reserve($email, $ip);
            if ($retry > 0) {
                header('Retry-After: ' . $retry);
                $this->respond(['ok' => false, 'error' => 'Demasiados intentos de acceso. Espera unos minutos e intenta nuevamente.'], 429);
                return;
            }

            $user = (new UserModel(Database::connection()))->findActiveByEmail($email);
            if ($user === null || !password_verify($password, $user['password_hash'])) {
                Logger::write('auth.mobile_login_failed', 'info');
                $this->respond(['ok' => false, 'error' => 'El correo o la contraseña son incorrectos.'], 401);
                return;
            }

            $limiter->clearPair($email, $ip);
            Auth::login($user);
            Logger::write('auth.mobile_login_succeeded', 'info');
            $this->respond($this->mobileSessionPayload($user));
        } catch (InvalidArgumentException $error) {
            $this->respond(['ok' => false, 'error' => $error->getMessage()], 422);
        }
    }

    public function logout(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        Auth::forget();
        $this->respond($this->mobileSessionPayload(null));
    }

    public function reservations(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $user = Auth::requireUser(true);
        if ($user === null) return;
        try {
            $model = new OrderModel(Database::connection());
            if (!OrderModel::enabled(Database::connection())) {
                $this->respond(['ok' => false, 'error' => 'El módulo de reservas no está habilitado.'], 503);
                return;
            }

            $orders = $model->listing((int) $user['id_usuario'], false);
            $items = [];
            foreach ($orders as $order) {
                $details = $model->find((int) $order['id_pedido'], (int) $user['id_usuario'], false);
                if ($details === null) continue;
                $summary = implode(', ', array_map(static fn (array $item): string =>
                    (int) $item['cantidad'] . ' × ' . (string) $item['nombre_producto'], $details['items']));
                $items[] = [
                    'id' => (int) $order['id_pedido'],
                    'code' => (string) $order['codigo_pedido'],
                    'status' => (string) $order['estado'],
                    'status_label' => OrderModel::LABELS[$order['estado']] ?? (string) $order['estado'],
                    'summary' => $summary,
                    'total' => (float) $order['total'],
                    'created_at' => (string) $order['created_at'],
                    'expires_at' => (string) $order['fecha_vencimiento'],
                ];
            }
            $this->respond(['ok' => true, 'items' => $items]);
        } catch (InvalidArgumentException | DomainException $error) {
            $this->respond(['ok' => false, 'error' => $error->getMessage()], 422);
        }
    }

    public function createReservation(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $user = Auth::requireUser(true);
        if ($user === null) return;
        if (!OrderModel::enabled(Database::connection())) {
            $this->respond(['ok' => false, 'error' => 'El módulo de reservas no está habilitado.'], 503);
            return;
        }

        try {
            $reservation = (new OrderModel(Database::connection()))->reserveProduct((int) $user['id_usuario'], $this->input());
            $items = array_map(static fn (array $item): array => [
                'product_id' => (int) $item['id_producto'],
                'name' => (string) $item['nombre_producto'],
                'quantity' => (int) $item['cantidad'],
                'price' => (float) $item['precio_unitario'],
                'subtotal' => (float) $item['subtotal'],
            ], $reservation['items']);
            $this->respond([
                'ok' => true,
                'message' => !empty($reservation['repeated']) ? 'Esta reserva ya se había registrado.' : 'Reserva enviada a la tienda; pendiente de aceptación.',
                'reservation' => [
                    'id' => (int) $reservation['id_pedido'],
                    'code' => (string) $reservation['codigo_pedido'],
                    'status' => (string) $reservation['estado'],
                    'total' => (float) $reservation['total'],
                    'expires_at' => (string) $reservation['fecha_vencimiento'],
                    'items' => $items,
                ],
                'available_stock' => (int) $reservation['available_stock'],
            ], !empty($reservation['repeated']) ? 200 : 201);
        } catch (OrderConflict $error) {
            $this->respond(['ok' => false, 'error' => $error->getMessage()], 409);
        } catch (InvalidArgumentException | DomainException $error) {
            $this->respond(['ok' => false, 'error' => $error->getMessage()], 422);
        }
    }
}
