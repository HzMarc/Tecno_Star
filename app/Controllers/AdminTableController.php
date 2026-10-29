<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/AdminTableModel.php';
require_once BASE_PATH . '/core/AdminTableRows.php';
require_once BASE_PATH . '/app/Models/OrderModel.php';

final class AdminTableController
{
    public function data(): void
    {
        // Es un endpoint JSON incluso cuando se abre directamente sin cabecera Accept.
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        if (Auth::requireRole('Administrador') === null) return;
        $kind=$_GET['kind'] ?? '';
        try {
            if (!is_string($kind)) throw new InvalidArgumentException('Tabla inválida.');
            (new OrderModel(Database::connection()))->expireDue();
            $result=(new AdminTableModel(Database::connection()))->data($kind,$_GET);
            $rows=$result['rows']; unset($result['rows']);
            $result['data']=array_map(static fn ($row) => AdminTableRows::cells($kind,$row),$rows);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        } catch (InvalidArgumentException $exception) { ErrorHandler::respond(422,$exception->getMessage()); }
    }
}
