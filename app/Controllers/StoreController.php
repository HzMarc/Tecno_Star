<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/ProductModel.php';

final class StoreController
{
    public function index(): void
    {
        $products = (new ProductModel())->catalog();
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
