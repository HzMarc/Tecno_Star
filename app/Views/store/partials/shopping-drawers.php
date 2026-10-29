<div class="shopping-backdrop" data-shopping-backdrop data-csrf-token="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>" data-user-id="<?= (int) ($_SESSION['user']['id'] ?? 0) ?>" data-state-url="<?= app_url_html('/shopping/state') ?>" data-update-url="<?= app_url_html('/shopping/update') ?>" data-product-url="<?= app_url_html('/product/data') ?>" hidden></div>
<script src="<?= app_url_html('/assets/js/shopping-client.js?v='.filemtime(BASE_PATH.'/public/assets/js/shopping-client.js')) ?>"></script>
<link rel="stylesheet" href="<?= app_url_html('/assets/css/shopping.css?v='.filemtime(BASE_PATH.'/public/assets/css/shopping.css')) ?>">
<div class="shopping-toasts" data-shopping-toasts role="status" aria-live="polite"></div>
<div class="shopping-sync-status" data-shopping-status-container hidden><p data-shopping-status role="status"></p><button type="button" data-shopping-retry>Reintentar / actualizar</button></div>
<aside class="shopping-drawer" id="cart-drawer" data-shopping-drawer="cart" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="cart-drawer-title" inert>
    <div class="shopping-drawer__header">
        <div><span class="eyebrow">Tus compras</span><h2 id="cart-drawer-title">Carrito</h2></div>
        <button class="shopping-drawer__close" type="button" data-close-drawer aria-label="Cerrar carrito"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="shopping-drawer__items" data-cart-items></div>
    <div class="shopping-review" data-shopping-review hidden><p>Revisa los avisos antes de continuar. Las cantidades y precios se volverán a validar al confirmar un pedido.</p><button type="button" data-cart-review hidden>He revisado los precios actuales</button></div>
    <div class="shopping-drawer__footer"><div class="shopping-drawer__total"><span>Total estimado</span><strong data-cart-total>$0.00</strong></div><small>Revisa y reserva para retirar en tienda.</small>
        <?php if (!empty($_SESSION['user']['id'])): ?><button class="shopping-checkout" type="button" data-checkout-url="<?= app_url_html('/checkout') ?>" disabled>Continuar con el pedido <i class="bi bi-arrow-right" aria-hidden="true"></i></button><?php endif; ?>
        <?php if (!empty($_SESSION['user']['id'])): ?><small class="shopping-drawer__login-note" data-shopping-save-state>Consultando lo guardado en tu cuenta…</small><?php else: ?><small class="shopping-drawer__login-note">Inicia sesión para guardar el carrito en tu cuenta. <a href="<?= app_url_html('/login') ?>">Iniciar sesión</a></small><?php endif; ?>
    </div>
</aside>
<aside class="shopping-drawer" id="wishlist-drawer" data-shopping-drawer="wishlist" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="wishlist-drawer-title" inert>
    <div class="shopping-drawer__header">
        <div><span class="eyebrow">Guardados para después</span><h2 id="wishlist-drawer-title">Favoritos</h2></div>
        <button class="shopping-drawer__close" type="button" data-close-drawer aria-label="Cerrar favoritos"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <div class="shopping-drawer__items" data-wishlist-items></div>
    <?php if (!empty($_SESSION['user']['id'])): ?><small class="shopping-drawer__login-note" data-shopping-save-state>Consultando lo guardado en tu cuenta…</small><?php else: ?><small class="shopping-drawer__login-note">Inicia sesión para guardar tus favoritos en tu cuenta. <a href="<?= app_url_html('/login') ?>">Iniciar sesión</a></small><?php endif; ?>
</aside>
