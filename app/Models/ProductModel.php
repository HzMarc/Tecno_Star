<?php

declare(strict_types=1);
require_once __DIR__.'/OrderModel.php';

final class ProductModel
{
    public function __construct(private PDO $database)
    {
    }

    public function catalog(bool $includeUnavailable = false): array
    {
        (new OrderModel($this->database))->expireDue();
        $availability = $includeUnavailable ? '' : ' AND COALESCE(i.stock_actual - i.stock_reservado, 0) >= 1';
        $statement = $this->database->query(
            "SELECT p.id_producto AS id, p.nombre_producto AS name, c.nombre_categoria AS category,
                    COALESCE(m.nombre_marca, 'Sin marca') AS brand,
                    p.precio_venta AS price, p.descripcion AS description,
                    p.imagen AS image, p.created_at AS created_at,
                    COALESCE(i.stock_actual - i.stock_reservado, 0) AS stock
             FROM tbproductos p
             INNER JOIN tbcategorias c ON c.id_categoria = p.id_categoria
             LEFT JOIN tbmarcas m ON m.id_marca = p.id_marca
             LEFT JOIN tbinventario i ON i.id_producto = p.id_producto
             WHERE p.estado = 'activo'
               AND c.estado = 'activo'
               AND (m.id_marca IS NULL OR m.estado = 'activo')
               " . $availability . "
             ORDER BY p.id_producto"
        );

        $icons = [
            'carg' => 'bi-lightning-charge',
            'fund' => 'bi-phone',
            'audi' => 'bi-headphones',
            'cabl' => 'bi-usb-plug',
            'tecl' => 'bi-keyboard',
            'boci' => 'bi-speaker',
        ];

        return array_map(static function (array $product) use ($icons): array {
            $name = strtolower((string) $product['name']);
            $icon = 'bi-box-seam';
            foreach ($icons as $term => $termIcon) {
                if (str_contains($name, $term)) {
                    $icon = $termIcon;
                    break;
                }
            }

            return [
                ...$product,
                'image' => product_image_url($product['image']),
                'old_price' => null,
                'is_offer' => false,
                'icon' => $icon,
            ];
        }, $statement->fetchAll());
    }

    public function findById(int $productId): ?array
    {
        (new OrderModel($this->database))->expireDue();
        $statement = $this->database->prepare(
            "SELECT p.id_producto AS id, p.nombre_producto AS name, c.nombre_categoria AS category,
                    COALESCE(m.nombre_marca, 'Sin marca') AS brand,
                    p.precio_venta AS price, p.descripcion AS description,
                    p.imagen AS image,
                    COALESCE(i.stock_actual - i.stock_reservado, 0) AS stock
             FROM tbproductos p
             INNER JOIN tbcategorias c ON c.id_categoria = p.id_categoria
             LEFT JOIN tbmarcas m ON m.id_marca = p.id_marca
             LEFT JOIN tbinventario i ON i.id_producto = p.id_producto
             WHERE p.id_producto = :id
               AND p.estado = 'activo'
               AND c.estado = 'activo'
               AND (m.id_marca IS NULL OR m.estado = 'activo')
             LIMIT 1"
        );
        $statement->execute(['id' => $productId]);
        $product = $statement->fetch();

        if ($product === false) {
            return null;
        }

        return [
            ...$product,
            'image' => product_image_url($product['image']),
            'old_price' => null,
            'is_offer' => false,
        ];
    }
}
