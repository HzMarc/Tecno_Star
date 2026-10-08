<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Controllers/HomeController.php';

$router->get('/', [HomeController::class, 'index']);

