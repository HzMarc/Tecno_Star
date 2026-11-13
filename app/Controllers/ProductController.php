<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/ProductModel.php';
require_once BASE_PATH . '/config/Database.php';

final class ProductController
{
    public function catalog(): void
    {
        try {
            $products = (new ProductModel(Database::connection()))->catalog(true);
            $availableProducts = array_values(array_filter(
                $products,
                static fn (array $product): bool => (int) floor((float) ($product['stock'] ?? 0)) > 0
            ));
            usort($availableProducts, static fn (array $first, array $second): int =>
                strcmp((string) ($second['created_at'] ?? ''), (string) ($first['created_at'] ?? ''))
            );
            $newArrivalCount = count($availableProducts) < 4
                ? 0
                : min(3, intdiv(count($availableProducts), 3));
            $newArrivalIds = [];
            foreach (array_slice($availableProducts, 0, $newArrivalCount) as $newArrival) {
                $newArrivalIds[(string) $newArrival['id']] = true;
            }

            $items = array_map(static function (array $product) use ($newArrivalIds): array {
                $stock = max(0, (int) floor((float) ($product['stock'] ?? 0)));

                return [
                    'id' => (string) $product['id'],
                    'name' => (string) $product['name'],
                    'brand' => (string) $product['brand'],
                    'category' => (string) $product['category'],
                    'description' => (string) ($product['description'] ?? ''),
                    'specs' => (string) ($product['description'] ?? ''),
                    'price' => (float) $product['price'],
                    'image' => (string) ($product['image'] ?? ''),
                    'stock' => $stock,
                    'created_at' => (string) ($product['created_at'] ?? ''),
                    'is_new' => isset($newArrivalIds[(string) $product['id']]),
                    'is_offer' => (bool) ($product['is_offer'] ?? false),
                    'old_price' => $product['old_price'] ?? null,
                ];
            }, $products);

            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: public, max-age=60');
            echo json_encode([
                'items' => $items,
                'count' => count($items),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        } catch (PDOException $exception) {
            http_response_code(503);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'El catálogo no está disponible en este momento.',
            ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            Logger::write('store.catalog_api_failed', 'error', $exception, ['status' => 503]);
        }
    }

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
