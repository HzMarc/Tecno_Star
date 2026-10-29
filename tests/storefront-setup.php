<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database/cli.php';
require_once BASE_PATH . '/database/MigrationRunner.php';
require_once BASE_PATH . '/database/RestrictedUser.php';
require_once BASE_PATH . '/app/Models/UserModel.php';
require_once BASE_PATH . '/app/Models/ShoppingModel.php';

try {
    $options = migrationOptions();
    if (!preg_match('/^tecno_star_test_store_[a-zA-Z0-9_]{1,20}$/D', $options['database'])) {
        throw new RuntimeException('Se requiere una base nueva con prefijo tecno_star_test_store_.');
    }
    $server = migrationConnection(array_replace($options, ['database' => 'information_schema']));
    $exists = $server->prepare('SELECT COUNT(*) FROM SCHEMATA WHERE SCHEMA_NAME = ?');
    $exists->execute([$options['database']]);
    if ((int) $exists->fetchColumn() !== 0) {
        throw new RuntimeException('La base ya existe. Usa otro sufijo; no se borra ni reutiliza.');
    }
    $options['create'] = true;
    $database = migrationConnection($options, true);
    (new MigrationRunner($database, BASE_PATH . '/database/migrations'))->migrate();
    $database->exec("INSERT INTO tbcategorias (nombre_categoria) VALUES ('Audio'), ('Cargadores y energía'), ('Accesorios para celular'), ('Cables'), ('Gaming')");
    $database->exec("INSERT INTO tbmarcas (nombre_marca) VALUES ('Tecno Star')");
    $insert = $database->prepare('INSERT INTO tbproductos (id_categoria, id_marca, codigo_producto, nombre_producto, precio_compra, precio_venta, descripcion, imagen, created_at) VALUES (?, 1, ?, ?, 1, ?, ?, ?, ?)');
    $stock = $database->prepare('INSERT INTO tbinventario (id_producto, stock_actual, stock_minimo) VALUES (?, ?, 3)');
    foreach ([
        [2, 'Cargador rápido USB-C', 18.99, 25, 'cargador-real.jpg'],
        [3, 'Funda protectora para celular', 12.50, 40, 'funda-real.jpg'],
        [1, 'Audífonos inalámbricos', 24.99, 18, 'audifonos-real.jpg'],
        [4, 'Cable USB de carga rápida', 9.99, 50, 'cable-real.jpg'],
        [5, 'Teclado gamer RGB', 34.99, 2, 'teclado-real.webp'],
        [1, 'Bocina Bluetooth portátil', 29.99, 20, 'bocina-real.jpg'],
        [1, 'Audífonos de estudio con micrófono y conexión USB para computadora', 45.50, 0, ''],
        [4, 'Cable de respaldo', 5.00, 0, 'missing-test-image.jpg'],
    ] as $index => [$category, $name, $price, $quantity, $image]) {
        $insert->execute([$category, 'STORE-TEST-' . ($index + 1), $name, $price, 'Producto exclusivo de pruebas de la tienda.', $image === '' ? null : 'http://localhost:8012/Tecno_Star/public/assets/images/products/' . $image, '2026-10-' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . ' 12:00:00']);
        $stock->execute([(int) $database->lastInsertId(), $quantity]);
    }
    $users = new UserModel($database);
    // Credenciales sinteticas para cuentas desechables de esta base aislada.
    $users->createCustomer('store-client-a', 'Alex', 'Prueba', '70000000', 'store-a@example.test', password_hash('StorePreview2026!', PASSWORD_DEFAULT));
    $users->createCustomer('store-client-b', 'Lucía', 'Prueba', '70000000', 'store-b@example.test', password_hash('StorePreview2026!', PASSWORD_DEFAULT));
    $client = $users->findActiveByEmail('store-a@example.test');
    $shopping = new ShoppingModel($database);
    $shopping->mutate((int) $client['id_usuario'], ['action' => 'cart_add', 'product_id' => 1, 'quantity' => 2]);
    $shopping->mutate((int) $client['id_usuario'], ['action' => 'wishlist_add', 'product_id' => 3]);
    $runtimeUser = 'ts_store_' . bin2hex(random_bytes(5));
    $runtimePassword = bin2hex(random_bytes(32));
    RestrictedUser::create($database, $options['database'], $runtimeUser, 'localhost', $runtimePassword);
    $directory = sys_get_temp_dir() . '/tecno_store_preview_' . bin2hex(random_bytes(8));
    mkdir($directory, 0700);
    mkdir($directory . '/sessions', 0700);
    $metadata = ['database' => $options['database'], 'host' => $options['host'], 'port' => $options['port'], 'user' => $runtimeUser, 'password' => $runtimePassword, 'storage' => $directory];
    file_put_contents($directory . '/connection.json', json_encode($metadata, JSON_THROW_ON_ERROR));
    chmod($directory . '/connection.json', 0600);
    file_put_contents(BASE_PATH . '/storage/store-preview-path.txt', $directory . '/connection.json');
    echo "Preparadas dos cuentas y ocho productos en una base nueva aislada. Metadatos privados guardados para iniciar la prueba.\n";
} catch (Throwable $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . "\n");
    exit(1);
}
