<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/ProductModel.php';
require_once BASE_PATH . '/config/Database.php';

final class StoreController
{
    public function index(): void
    {
        $catalogError = false;
        try {
            $products = (new ProductModel(Database::connection()))->catalog(true);
        } catch (PDOException $exception) {
            $products = [];
            $catalogError = true;
            http_response_code(503);
            Logger::write('store.catalog_failed', 'error', $exception, ['status' => 503]);
        }
        $message = null;
        $messageType = 'error';

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (isset($_SESSION['flash_message'])) {
            $message = (string) $_SESSION['flash_message'];
            $messageType = (string) ($_SESSION['flash_message_type'] ?? 'error');
            unset($_SESSION['flash_message'], $_SESSION['flash_message_type']);
        }

        require BASE_PATH . '/app/Views/store/index.php';
    }
}
