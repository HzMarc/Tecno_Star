<?php

declare(strict_types=1);

final class HomeController
{
    public function index(): void
    {
        require BASE_PATH . '/app/Views/home.php';
    }
}

