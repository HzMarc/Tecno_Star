<?php

declare(strict_types=1);

final class IndexController
{
    public function index(): void
    {
        require BASE_PATH . '/app/Views/index.php';
    }
}
