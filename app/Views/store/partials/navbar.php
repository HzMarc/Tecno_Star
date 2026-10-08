<?php

$isAuthenticated = isset($_SESSION['user']);
$currentUserName = $isAuthenticated ? (string) ($_SESSION['user']['name'] ?? '') : '';
?>

<header class="store-navbar-header">
    <nav class="navbar navbar-expand-lg store-navbar" aria-label="Navegación de la tienda">
        <div class="container">
            <a class="navbar-brand store-navbar__brand" href="/Tecno_Star/public/store" aria-label="Ir al catálogo de Tecno Star">
                <img src="/Tecno_Star/public/assets/images/logo-estrella.png" alt="">
                <span>Tecno Star</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#store-navigation" aria-controls="store-navigation" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="store-navigation">
                <ul class="navbar-nav store-navbar__links">
                    <li class="nav-item">
                        <a class="nav-link active" href="/Tecno_Star/public/store">
                            <i class="bi bi-grid" aria-hidden="true"></i> Catálogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Tecno_Star/public/#servicios">
                            <i class="bi bi-printer" aria-hidden="true"></i> Servicios
                        </a>
                    </li>
                </ul>

                <div class="store-navbar__actions">
                    <div class="store-navbar__search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" data-catalog-search-nav placeholder="Buscar productos..." aria-label="Buscar productos">
                    </div>

                    <a class="store-navbar__icon-link" href="/Tecno_Star/public/login" aria-label="Carrito de compras" title="Carrito de compras">
                        <i class="bi bi-cart3" aria-hidden="true"></i>
                        <span class="store-navbar__badge">0</span>
                    </a>

                    <a class="store-navbar__icon-link" href="/Tecno_Star/public/login" aria-label="Favoritos" title="Favoritos">
                        <i class="bi bi-heart" aria-hidden="true"></i>
                    </a>

                    <a class="store-navbar__icon-link" href="/Tecno_Star/public/login" aria-label="Cuenta de usuario" title="<?= $isAuthenticated ? htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') : 'Iniciar sesión' ?>">
                        <i class="bi bi-person-circle" aria-hidden="true"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>
</header>
