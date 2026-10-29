<?php

declare(strict_types=1);

/** Datos opcionales y reconocibles. No altera filas que ya existen. */
function seedDemo(PDO $database): void
{
    $database->beginTransaction();
    try {
        $database->exec("INSERT INTO tbcategorias (nombre_categoria)
            VALUES ('Demostración') ON DUPLICATE KEY UPDATE id_categoria = id_categoria");
        $category = (int) $database->query("SELECT id_categoria FROM tbcategorias WHERE nombre_categoria = 'Demostración'")->fetchColumn();
        $find = $database->prepare('SELECT id_producto FROM tbproductos WHERE codigo_producto = ?');
        $insert = $database->prepare('INSERT INTO tbproductos (id_categoria, codigo_producto, nombre_producto, descripcion, precio_compra, precio_venta) VALUES (?, ?, ?, ?, ?, ?)');
        $stock = $database->prepare('INSERT INTO tbinventario (id_producto, stock_actual, stock_minimo) VALUES (?, ?, ?)');
        foreach ([
            ['DEMO-CABLE-USB', 'Cable USB de demostración', 2.00, 5.00, 20, 5],
            ['DEMO-AUDIFONOS', 'Audífonos de demostración', 5.00, 10.00, 8, 2],
        ] as [$code, $name, $purchase, $price, $quantity, $minimum]) {
            $find->execute([$code]);
            if ($find->fetchColumn() !== false) {
                continue;
            }
            $insert->execute([$category, $code, $name, 'Producto de ejemplo para pruebas locales. No representa inventario real.', $purchase, $price]);
            $stock->execute([(int) $database->lastInsertId(), $quantity, $minimum]);
        }
        $database->commit();
    } catch (Throwable $exception) {
        $database->rollBack();
        throw $exception;
    }
}
