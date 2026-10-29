<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/ShoppingModel.php';
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/core/Auth.php';

final class ShoppingController
{
    public function state(): void
    {
        $userId = $this->authenticatedUserId();
        if ($userId === null) {
            return;
        }

        $this->respond((new ShoppingModel(Database::connection()))->state($userId));
    }

    public function update(): void
    {
        $userId = $this->authenticatedUserId();
        if ($userId === null) {
            return;
        }

        $data = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($data)) {
            ErrorHandler::respond(400, 'La solicitud no es válida.');
            return;
        }
        if(strlen((string)file_get_contents('php://input'))>131072) { ErrorHandler::respond(413,'La lista es demasiado grande.'); return; }
        if(!isset($data['operation_id'],$data['target_user_id'],$data['expected_revision']) || !is_string($data['operation_id']) || !preg_match('/^[a-f0-9]{64}$/D',$data['operation_id']) || !is_int($data['target_user_id']) || $data['target_user_id']!==$userId || !is_int($data['expected_revision']) || $data['expected_revision']<0) {
            ErrorHandler::respond(409,'La cuenta o la solicitud no coincide. Actualiza la página antes de continuar.'); return;
        }

        try {
            $state = (new ShoppingModel(Database::connection()))->mutate($userId, $data);
            $this->respond($state);
        } catch (ShoppingConflict $exception) {
            $this->respond(['error'=>$exception->getMessage(),'state'=>(new ShoppingModel(Database::connection()))->state($userId)],409);
        } catch (InvalidArgumentException $exception) {
            ErrorHandler::respond(400, $exception->getMessage());
        } catch (DomainException $exception) {
            $this->respond(['error'=>$exception->getMessage(),'state'=>(new ShoppingModel(Database::connection()))->state($userId)],422);
        }
    }

    private function authenticatedUserId(): ?int
    {
        $user = Auth::requireUser(true);
        return $user === null ? null : (int) $user['id_usuario'];
    }

    private function respond(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
