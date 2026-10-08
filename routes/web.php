<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Controllers/IndexController.php';
require_once BASE_PATH . '/app/Controllers/AuthController.php';
require_once BASE_PATH . '/app/Controllers/StoreController.php';
require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/app/Models/UserModel.php';

$router->get('/', [IndexController::class, 'index']);
$router->get('/store', [StoreController::class, 'index']);
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->get('/register', [AuthController::class, 'register']);
$router->post('/register', [AuthController::class, 'createAccount']);
$router->get('/auth/google', [AuthController::class, 'google']);
