<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/ProductModel.php';
require_once BASE_PATH . '/config/Database.php';

final class ProductController
{
    public function data(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $product = $id !== false && $id !== null && $id > 0
            ? (new ProductModel(Database::connection()))->findById($id)
            : null;

        if ($product === null) {
            ErrorHandler::respond(404, 'Producto no encontrado.');
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=300');
        echo json_encode([
            'id' => (int) $product['id'],
            'name' => (string) $product['name'],
            'brand' => (string) $product['brand'],
            'category' => (string) $product['category'],
            'description' => (string) ($product['description'] ?? ''),
            'price' => (float) $product['price'],
            'image' => (string) ($product['image'] ?? ''),
            'stock' => max(0, (int) floor((float) $product['stock'])),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function show(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $product = $id !== false && $id !== null && $id > 0
            ? (new ProductModel(Database::connection()))->findById($id)
            : null;

        if ($product === null) {
            ErrorHandler::respond(404, 'Producto no encontrado');
            return;
        }

        require BASE_PATH . '/app/Views/store/product.php';
    }
}
