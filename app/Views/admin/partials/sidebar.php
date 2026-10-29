<?php
$shellName = (string) ($_SESSION['user']['name'] ?? 'Administrador');
$shellEscape = static fn (string $text): string => htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
$shellItems = [
    ['dashboard', 'Inicio', 'bi-grid-1x2-fill', '/admin'],
    ['products', 'Productos', 'bi-box-seam', '/admin/products'],
    ['categories', 'Categorías', 'bi-tags', '/admin/catalog?kind=categories'],
    ['brands', 'Marcas', 'bi-bookmark', '/admin/catalog?kind=brands'],
    ['inventory', 'Inventario', 'bi-clipboard2-data', '/admin/inventory'],
    ['orders', 'Pedidos', 'bi-receipt', '/admin/orders'],
    ['cash', 'Caja', 'bi-cash-coin', '/admin/cash'],
    ['sales', 'Ventas', 'bi-graph-up-arrow', '/admin/sales'],
    ['users', 'Usuarios', 'bi-people', '/admin/users'],
];
?>
<button class="admin-nav-toggle" type="button" data-admin-nav-toggle aria-controls="admin-navigation" aria-expanded="true" aria-label="Contraer menú lateral" title="Contraer menú lateral"><i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i></button>
<button class="admin-nav-backdrop" type="button" data-admin-nav-backdrop hidden tabindex="-1" aria-hidden="true" aria-label="Cerrar menú lateral"></button>
<aside class="admin-sidebar" id="admin-navigation" aria-label="Menú lateral">
    <div class="admin-sidebar__head">
        <a class="admin-sidebar__brand" href="<?= app_url_html('/admin') ?>" aria-label="Inicio de Tecno Star" title="Tecno Star">
            <img src="<?= app_url_html('/assets/images/logo-estrella.png') ?>" alt=""><span class="admin-sidebar__label">Tecno Star</span>
        </a>
        <button class="admin-nav-close" type="button" data-admin-nav-close aria-label="Cerrar menú lateral"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <div class="admin-sidebar__scroll">
        <nav class="admin-sidebar__nav" aria-label="Administración">
            <?php foreach ($shellItems as [$shellSection, $shellLabel, $shellIcon, $shellPath]): ?>
                <?php if ($shellPath !== null): ?>
                    <a class="<?= ($adminSection ?? '') === $shellSection ? 'is-active' : '' ?>" href="<?= app_url_html($shellPath) ?>" aria-label="<?= $shellLabel ?>" title="<?= $shellLabel ?>" <?= ($adminSection ?? '') === $shellSection ? 'aria-current="page"' : '' ?>><i class="bi <?= $shellIcon ?>" aria-hidden="true"></i><span class="admin-sidebar__label"><?= $shellLabel ?></span></a>
                <?php else: ?>
                    <span class="admin-sidebar__pending" aria-disabled="true" aria-label="<?= $shellLabel ?>, próximamente" title="<?= $shellLabel ?> · Próximamente"><i class="bi <?= $shellIcon ?>" aria-hidden="true"></i><span class="admin-sidebar__label"><?= $shellLabel ?><small>Próximamente</small></span></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </div>
</aside>
<details class="admin-account" data-admin-account>
    <summary aria-label="Menú de mi cuenta" title="Mi cuenta"><span class="admin-account__avatar"><i class="bi bi-person-fill" aria-hidden="true"></i></span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
    <div class="admin-account__menu">
        <div class="admin-account__identity"><strong><?= $shellEscape($shellName) ?></strong><small>Administrador</small></div>
        <a href="<?= app_url_html('/profile') ?>"><i class="bi bi-person" aria-hidden="true"></i> Mi perfil</a>
        <a href="<?= app_url_html('/store') ?>"><i class="bi bi-shop" aria-hidden="true"></i> Ver tienda</a>
        <form method="post" action="<?= app_url_html('/logout') ?>"><?= Csrf::field() ?><button type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Cerrar sesión</button></form>
    </div>
</details>
