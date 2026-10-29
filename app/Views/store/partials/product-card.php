<?php
$productId = (int) $product['id'];
$stock = max(0, (int) floor((float) $product['stock']));
$isNew = in_array($productId, $newProductIds, true);
$created = strtotime((string) ($product['created_at'] ?? '')) ?: 0;
?>
<div class="catalog-product" data-product-card data-category="<?= htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8') ?>" data-price="<?= htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8') ?>" data-stock="<?= $stock ?>" data-created="<?= $created ?>" data-catalog-order="<?= (int) $productIndex ?>" data-search="<?= htmlspecialchars((string) ($product['name'] . ' ' . $product['brand'] . ' ' . $product['category']), ENT_QUOTES, 'UTF-8') ?>">
    <article class="product-card store-card" data-product-id="<?= $productId ?>" data-product-name="<?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?>" data-product-brand="<?= htmlspecialchars((string) $product['brand'], ENT_QUOTES, 'UTF-8') ?>" data-product-category="<?= htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8') ?>" data-product-description="<?= htmlspecialchars((string) $product['description'], ENT_QUOTES, 'UTF-8') ?>" data-product-price="<?= htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8') ?>" data-product-image="<?= htmlspecialchars((string) ($product['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-product-stock="<?= $stock ?>">
        <a class="store-card__photo" href="<?= app_url_html('/product?id=' . $productId) ?>" tabindex="-1" aria-hidden="true">
            <?php if (!empty($product['image'])): ?>
                <img src="<?= htmlspecialchars((string) $product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" width="320" height="240" data-store-image>
            <?php endif; ?>
            <span class="store-image-fallback" <?= !empty($product['image']) ? 'hidden' : '' ?>><i class="bi <?= htmlspecialchars((string) ($product['icon'] ?? 'bi-box-seam'), ENT_QUOTES, 'UTF-8') ?>"></i></span>
        </a>
        <?php if ($stock === 0): ?><span class="store-card__badge store-card__badge--empty">Agotado</span><?php elseif ($isNew): ?><span class="store-card__badge">Nuevo</span><?php endif; ?>
        <button class="store-card__favorite" type="button" data-add-wishlist aria-pressed="false" aria-label="Guardar <?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?> en favoritos" title="Guardar en favoritos"><i class="bi bi-heart" aria-hidden="true"></i></button>
        <div class="store-card__body">
            <span class="store-card__brand"><?= htmlspecialchars((string) $product['brand'], ENT_QUOTES, 'UTF-8') ?></span>
            <h3><a href="<?= app_url_html('/product?id=' . $productId) ?>"><?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?></a></h3>
            <p class="store-card__stock <?= $stock === 0 ? 'store-card__stock--empty' : '' ?>"><span aria-hidden="true"></span><?= $stock === 0 ? 'Agotado' : ($stock <= 5 ? 'Últimas ' . $stock . ($stock === 1 ? ' unidad' : ' unidades') : 'Disponible · ' . $stock) ?></p>
            <div class="store-card__bottom">
                <strong class="store-card__price">$<?= number_format((float) $product['price'], 2) ?></strong>
                <button class="store-card__add" type="button" data-add-cart <?= $stock === 0 ? 'disabled' : '' ?> aria-label="<?= $stock === 0 ? 'Agotado: ' : 'Añadir al carrito: ' ?><?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-cart-plus" aria-hidden="true"></i><span><?= $stock === 0 ? 'Agotado' : 'Añadir' ?></span></button>
            </div>
        </div>
    </article>
</div>
