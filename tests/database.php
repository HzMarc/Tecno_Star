<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/database/cli.php';
require_once BASE_PATH . '/database/MigrationRunner.php';
require_once BASE_PATH . '/database/seeders/demo.php';
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/app/Models/UserModel.php';
require_once BASE_PATH . '/app/Models/ProductModel.php';
require_once BASE_PATH . '/app/Models/ShoppingModel.php';
require_once BASE_PATH . '/app/Controllers/AdminController.php';
require_once BASE_PATH . '/database/RestrictedUser.php';

ini_set('session.save_path', sys_get_temp_dir());
session_start();
ob_start();
register_shutdown_function(static function (): void {
    session_destroy();
});

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('FALLO: ' . $message);
    }
    echo 'OK: ' . $message . "\n";
}

function rejects(callable $operation, string $message, ?string $sqlState = null): void
{
    try {
        $operation();
    } catch (PDOException $exception) {
        if ($sqlState !== null && $exception->getCode() !== $sqlState) {
            throw $exception;
        }
        check(true, $message);
        return;
    } catch (RuntimeException|DomainException $exception) {
        if ($sqlState !== null) {
            throw $exception;
        }
        check(true, $message);
        return;
    }
    throw new RuntimeException('FALLO: debio rechazarse: ' . $message);
}

try {
    $options = migrationOptions();
    if (!preg_match('/^tecno_star_test_[a-zA-Z0-9_]{1,25}$/D', $options['database'])) {
        throw new RuntimeException('Las pruebas requieren una base NUEVA con prefijo tecno_star_test_ y sufijo de hasta 25 caracteres.');
    }
    $server = migrationConnection(array_replace($options, ['database' => 'information_schema']));
    $exists = $server->prepare('SELECT COUNT(*) FROM SCHEMATA WHERE SCHEMA_NAME = ?');
    foreach (['', '_legacy', '_failed'] as $suffix) {
        $exists->execute([$options['database'] . $suffix]);
        if ((int) $exists->fetchColumn() !== 0) {
            throw new RuntimeException('El destino de prueba ya existe. Usa otro nombre; no se borra ni reutiliza.');
        }
    }
    $options['create'] = false; // La presencia de la opcion habilita CREATE.
    $database = migrationConnection($options, true);
    $runner = new MigrationRunner($database, BASE_PATH . '/database/migrations');
    check(count($runner->migrate()) === count(glob(BASE_PATH . '/database/migrations/*.sql')), 'instalacion desde base vacia');
    check($runner->migrate() === [], 'segunda ejecucion sin cambios ni duplicados');
    check((int) $database->query("SELECT COUNT(*) FROM tbroles WHERE nombre_rol IN ('Cliente','Administrador','Empleado')")->fetchColumn() === 3, 'roles requeridos Cliente, Administrador y Empleado');

    $users = new UserModel($database);
    $password = bin2hex(random_bytes(16));
    $users->createCustomer('prueba-cliente', 'Cliente', 'Prueba', '70000000', 'cliente@example.test', password_hash($password, PASSWORD_DEFAULT));
    $user = $users->findActiveByEmail('cliente@example.test');
    check($user !== null && $user['nombre_rol'] === 'Cliente' && password_verify($password, $user['password_hash']), 'registro y consulta de cliente con hash valido');
    $userId = (int) $user['id_usuario'];
    check($users->findActiveById($userId) !== null, 'consulta del perfil');
    rejects(fn () => $users->createCustomer('otro-cliente', 'Otro', 'Prueba', '70000000', 'cliente@example.test', password_hash($password, PASSWORD_DEFAULT)), 'correo duplicado rechazado', '23000');
    check((int) $database->query('SELECT COUNT(*) FROM tbclientes')->fetchColumn() === 1, 'registro fallido no deja clientes parciales');

    $products = new ProductModel($database);
    check($products->catalog() === [], 'catalogo vacio antes de ejemplos');
    seedDemo($database);
    seedDemo($database);
    $catalog = $products->catalog();
    check(count($catalog) === 2, 'ejemplos repetibles sin duplicar productos');
    $productId = (int) $catalog[0]['id'];
    check($products->findById($productId) !== null, 'detalle de producto compatible');
    $database->exec('UPDATE tbinventario SET stock_actual = 19 WHERE id_producto = ' . $productId);
    seedDemo($database);
    check((int) $products->findById($productId)['stock'] === 19, 'repetir ejemplos conserva existencias modificadas');
    rejects(fn () => $database->exec('UPDATE tbinventario SET stock_reservado = stock_actual + 1 WHERE id_producto = ' . $productId), 'reserva mayor que existencias rechazada', '23000');
    rejects(fn () => $database->exec('UPDATE tbproductos SET precio_venta = -1 WHERE id_producto = ' . $productId), 'precio negativo rechazado', '23000');
    rejects(fn () => $database->exec("INSERT INTO tbproductos (id_categoria, codigo_producto, nombre_producto, precio_venta) VALUES (999999, 'HUERFANO', 'Invalido', 1)"), 'clave foranea invalida rechazada', '23000');

    $shopping = new ShoppingModel($database);
    $state = $shopping->mutate($userId, ['action' => 'cart_add', 'product_id' => $productId, 'quantity' => 2]);
    check(count($state['cart']) === 1 && $state['cart'][0]['quantity'] === 2, 'carrito y joins compatibles');
    $state = $shopping->mutate($userId, ['action' => 'cart_add', 'product_id' => $productId, 'quantity' => 1]);
    check(count($state['cart']) === 1 && $state['cart'][0]['quantity'] === 3, 'un carrito por usuario y un detalle por producto');
    $shopping->mutate($userId, ['action' => 'cart_set_quantity', 'product_id' => $productId, 'quantity' => 4]);
    $shopping->mutate($userId, ['action' => 'wishlist_add', 'product_id' => $productId]);
    $state = $shopping->mutate($userId, ['action' => 'wishlist_add', 'product_id' => $productId]);
    check(count($state['wishlist']) === 1 && $state['cart'][0]['quantity'] === 4, 'favoritos sin duplicados y cambio de cantidad');
    $shopping->mutate($userId, ['action' => 'wishlist_remove', 'product_id' => $productId]);
    $state = $shopping->mutate($userId, ['action' => 'cart_remove', 'product_id' => $productId]);
    check($state['cart'] === [] && $state['wishlist'] === [], 'eliminacion de carrito y favoritos');
    rejects(fn () => $shopping->mutate($userId, ['action' => 'cart_add', 'product_id' => 999999]), 'producto inexistente rechazado');
    check(!$database->inTransaction(), 'transaccion fallida revertida');

    // Inyecta SOLO en este proceso la conexion de prueba; no modifica config.php.
    (new ReflectionProperty(Database::class, 'connection'))->setValue(null, $database);
    Auth::login($users->findActiveById($userId));
    $_SESSION['user']['role'] = 'Administrador';
    ob_start();
    (new AdminController())->index();
    $denied = ob_get_clean();
    check(http_response_code() === 403 && str_contains($denied, 'Acceso no autorizado'), 'rol de sesion falsificado no autoriza');
    $database->exec("UPDATE tbusuarios SET id_rol = (SELECT id_rol FROM tbroles WHERE nombre_rol = 'Administrador') WHERE id_usuario = " . $userId);
    http_response_code(200);
    // Evita iniciar una sesion HTTP despues del texto de las comprobaciones.
    foreach (['index', 'products', 'inventory'] as $action) {
        ob_start();
        (new AdminController())->$action();
        $html = ob_get_clean();
        check(str_contains($html, '<html'), 'consultas y vista administrativa: ' . $action);
    }
    $database->exec("UPDATE tbusuarios SET id_rol = (SELECT id_rol FROM tbroles WHERE nombre_rol = 'Cliente') WHERE id_usuario = " . $userId);
    ob_start();
    (new AdminController())->products();
    ob_end_clean();
    check(http_response_code() === 403 && $_SESSION['user']['role'] === 'Cliente', 'cambio de rol revoca acceso en la misma sesion');
    $oldToken = Csrf::token();
    $database->exec("UPDATE tbusuarios SET estado = 'inactivo' WHERE id_usuario = " . $userId);
    check(Auth::current() === null && !isset($_SESSION['user']) && !Csrf::valid($oldToken), 'cuenta desactivada pierde sesion y token');
    $database->exec("UPDATE tbusuarios SET estado = 'activo' WHERE id_usuario = " . $userId);
    Auth::login($users->findActiveById($userId));
    check($_SESSION['user']['role'] === 'Cliente' && Csrf::valid(Csrf::token()), 'nuevo acceso con rol actual y token nuevo');
    http_response_code(200);

    $runtimeUser = 'ts_test_' . bin2hex(random_bytes(6));
    $runtimePassword = bin2hex(random_bytes(32));
    RestrictedUser::create($database, $options['database'], $runtimeUser, 'localhost', $runtimePassword);
    try {
        $runtime = new PDO('mysql:host=' . $options['host'] . ';port=' . $options['port'] . ';dbname=' . $options['database'] . ';charset=utf8mb4', $runtimeUser, $runtimePassword,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
        check(count((new ProductModel($runtime))->catalog()) === 2, 'cuenta limitada consulta catalogo');
        $runtimeUsers = new UserModel($runtime);
        $runtimeUsers->createCustomer('cuenta-limitada', 'Cuenta', 'Limitada', '70000000', 'limitada@example.test', password_hash($password, PASSWORD_DEFAULT));
        $limitedId = (int) $runtimeUsers->findActiveByEmail('limitada@example.test')['id_usuario'];
        $limitedShopping = new ShoppingModel($runtime);
        $limitedShopping->mutate($limitedId, ['action' => 'cart_add', 'product_id' => $productId]);
        $limitedShopping->mutate($limitedId, ['action' => 'cart_set_quantity', 'product_id' => $productId, 'quantity' => 2]);
        $limitedShopping->mutate($limitedId, ['action' => 'wishlist_add', 'product_id' => $productId]);
        $limitedShopping->mutate($limitedId, ['action' => 'wishlist_remove', 'product_id' => $productId]);
        $limitedState = $limitedShopping->mutate($limitedId, ['action' => 'cart_remove', 'product_id' => $productId]);
        check($limitedState['cart'] === [] && $limitedState['wishlist'] === [], 'cuenta limitada registra clientes y modifica carrito y favoritos');
        foreach (['CREATE TABLE no_permitida (id INT)', 'SELECT * FROM mysql.user', 'DROP TABLE tbproductos', 'UPDATE tbusuarios SET correo = correo', 'UPDATE tbinventario SET id_producto = id_producto', 'DELETE FROM tbstockmovimientos'] as $sql) {
            rejects(fn () => $runtime->exec($sql), 'operacion fuera de permisos rechazada');
        }
        rejects(fn () => RestrictedUser::create($database, $options['database'], $runtimeUser, 'localhost', bin2hex(random_bytes(32))), 'cuenta existente no se sobrescribe');
    } finally {
        $database->exec('DROP USER ' . $database->quote($runtimeUser) . "@'localhost'");
    }

    $legacy = migrationConnection(array_replace($options, ['database' => $options['database'] . '_legacy']), true);
    $legacy->exec('CREATE TABLE dato_existente (id INT PRIMARY KEY) ENGINE=InnoDB');
    $legacy->exec('INSERT INTO dato_existente VALUES (1)');
    rejects(fn () => (new MigrationRunner($legacy, BASE_PATH . '/database/migrations'))->migrate(), 'base existente sin historial protegida');
    check((int) $legacy->query('SELECT COUNT(*) FROM dato_existente')->fetchColumn() === 1 && count($legacy->query('SHOW TABLES')->fetchAll()) === 1, 'base existente permanece intacta');

    $temp = sys_get_temp_dir() . '/tecno_star_migrations_' . bin2hex(random_bytes(8));
    mkdir($temp);
    foreach (glob(BASE_PATH . '/database/migrations/*.sql') as $file) {
        copy($file, $temp . '/' . basename($file));
    }
    file_put_contents($temp . '/002_required_roles.sql', "\n-- modificacion\n", FILE_APPEND);
    rejects(fn () => (new MigrationRunner($database, $temp))->migrate(), 'checksum detecta migracion modificada');
    copy(BASE_PATH . '/database/migrations/002_required_roles.sql', $temp . '/002_required_roles.sql');
    file_put_contents($temp . '/999_interrupted.sql', 'CREATE TABLE prueba_parcial (id INT); SENTENCIA INVALIDA;');
    $failed = migrationConnection(array_replace($options, ['database' => $options['database'] . '_failed']), true);
    $failedRunner = new MigrationRunner($failed, $temp);
    rejects(fn () => $failedRunner->migrate(), 'fallo DDL registrado');
    rejects(fn () => $failedRunner->migrate(), 'migracion incompleta bloquea reintentos automaticos');
    check($failed->query("SELECT status FROM schema_migrations WHERE version = '999_interrupted.sql'")->fetchColumn() === 'applying', 'historial conserva evidencia del fallo');
    foreach (glob($temp . '/*.sql') as $file) {
        unlink($file);
    }
    rmdir($temp);
    echo "Pruebas completadas. Las bases de prueba se conservan para inspeccion.\n";
} catch (Throwable $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . "\n");
    exit(1);
}
