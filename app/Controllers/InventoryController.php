<?php
declare(strict_types=1);
require_once BASE_PATH . '/app/Models/InventoryModel.php';

final class InventoryController
{
    public function move(): void
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $user = Auth::requireRole('Administrador');
        if (!$user) return;
        header('Content-Type: application/json; charset=utf-8');
        try {
            $result = (new InventoryModel(Database::connection()))->move((int)$user['id_usuario'], $_POST);
            echo json_encode(['ok'=>true,'message'=>$result['repeated']?'Este movimiento ya estaba registrado.':'Movimiento registrado correctamente.'] + $result, JSON_THROW_ON_ERROR);
        } catch (InvalidArgumentException $error) {
            http_response_code(422);
            echo json_encode(['ok'=>false,'message'=>$error->getMessage()], JSON_THROW_ON_ERROR);
        }
    }
}
