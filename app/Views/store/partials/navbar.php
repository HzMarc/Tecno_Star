<?php

$isAuthenticated = isset($_SESSION['user']);
$currentUserName = $isAuthenticated ? (string) ($_SESSION['user']['name'] ?? '') : '';
$isCatalogPage = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) === parse_url(app_url('/store'), PHP_URL_PATH);
$navbarQuery = is_string($_GET['q'] ?? null) ? $_GET['q'] : '';
?>

<link rel="stylesheet" href="<?= app_url_html('/assets/css/store-navigation.css?v='.filemtime(BASE_PATH.'/public/assets/css/store-navigation.css')) ?>">
<header class="store-navbar-header">
    <nav class="navbar navbar-expand-lg store-navbar" aria-label="Navegación de la tienda">
        <div class="container">
            <a class="navbar-brand store-navbar__brand" href="<?= app_url_html('/store') ?>" aria-label="Ir al catálogo de Tecno Star">
                <img src="<?= app_url_html('/assets/images/logo-estrella.png') ?>" alt="">
                <span>Tecno Star</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#store-navigation" aria-controls="store-navigation" aria-expanded="false" aria-label="Abrir menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="store-navigation">
                <ul class="navbar-nav store-navbar__links">
                    <li class="nav-item">
                        <a class="nav-link <?= $isCatalogPage ? 'active' : '' ?>" <?= $isCatalogPage ? 'aria-current="page"' : '' ?> href="<?= app_url_html('/store') ?>">
                            <i class="bi bi-grid" aria-hidden="true"></i> Catálogo
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= app_url_html('/#servicios') ?>">
                            <i class="bi bi-printer" aria-hidden="true"></i> Servicios
                        </a>
                    </li>
                </ul>

                <div class="store-navbar__actions">
                    <form class="store-navbar__search" role="search" method="get" action="<?= app_url_html('/store') ?>">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input type="search" name="q" value="<?= htmlspecialchars($navbarQuery, ENT_QUOTES, 'UTF-8') ?>" data-catalog-search-nav placeholder="Buscar productos..." aria-label="Buscar productos" autocomplete="off">
                    </form>
                    <button class="store-navbar__icon-link" type="button" data-open-drawer="cart" aria-label="Carrito de compras" title="Carrito de compras">
                        <i class="bi bi-cart3" aria-hidden="true"></i>
                        <span class="store-navbar__badge" data-cart-count>0</span>
                    </button>

                    <button class="store-navbar__icon-link" type="button" data-open-drawer="wishlist" aria-label="Favoritos" title="Favoritos">
                        <i class="bi bi-heart" aria-hidden="true"></i>
                        <span class="store-navbar__badge" data-wishlist-count>0</span>
                    </button>

                    <?php if ($isAuthenticated): ?>
                        <div class="dropdown store-account-menu">
                            <button class="store-navbar__icon-link" type="button" id="store-account-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-controls="store-account-options" aria-label="Menú de mi cuenta" title="<?= htmlspecialchars($currentUserName, ENT_QUOTES, 'UTF-8') ?>">
                                <i class="bi bi-person-circle" aria-hidden="true"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end store-account-menu__options" id="store-account-options" aria-labelledby="store-account-toggle">
                                <li><a class="dropdown-item" href="<?= app_url_html('/orders') ?>"><i class="bi bi-receipt" aria-hidden="true"></i> Mis pedidos</a></li>
                                <li><a class="dropdown-item" href="<?= app_url_html('/purchases') ?>"><i class="bi bi-bag-check" aria-hidden="true"></i> Mis compras</a></li>
                                <?php if(class_exists('OrderModel') && OrderModel::isStaff(['id_usuario'=>$_SESSION['user']['id'],'nombre_rol'=>$_SESSION['user']['role'],'permiso_pedidos'=>$_SESSION['user']['permiso_pedidos']??null])): ?><li><a class="dropdown-item" href="<?= app_url_html('/admin/orders') ?>"><i class="bi bi-qr-code-scan" aria-hidden="true"></i> Gestionar pedidos</a></li><li><a class="dropdown-item" href="<?= app_url_html('/admin/sales') ?>"><i class="bi bi-cash-stack" aria-hidden="true"></i> Ventas</a></li><li><a class="dropdown-item" href="<?= app_url_html('/admin/cash') ?>"><i class="bi bi-cash-coin" aria-hidden="true"></i> Caja</a></li><?php endif; ?>
                                <li><a class="dropdown-item" href="<?= app_url_html('/profile') ?>"><i class="bi bi-person" aria-hidden="true"></i> Mi perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><form method="post" action="<?= app_url_html('/logout') ?>"><?= Csrf::field() ?><button class="dropdown-item store-account-menu__logout" type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Cerrar sesión</button></form></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a class="store-navbar__icon-link" href="<?= app_url_html('/login') ?>" aria-label="Cuenta de usuario" title="Iniciar sesión"><i class="bi bi-person-circle" aria-hidden="true"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>
