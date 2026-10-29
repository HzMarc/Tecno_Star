<?php
$catalogError = $catalogError ?? false;
$signedIn = !empty($_SESSION['user']['id']);
$firstName = explode(' ', trim((string) ($_SESSION['user']['name'] ?? '')))[0];
$categoryCounts = [];
foreach ($products as $item) {
    $categoryCounts[(string) $item['category']] = ($categoryCounts[(string) $item['category']] ?? 0) + 1;
}
ksort($categoryCounts, SORT_NATURAL | SORT_FLAG_CASE);
$recentProducts = array_values(array_filter($products, static fn (array $item): bool => (float) $item['stock'] > 0 && !empty($item['created_at'])));
usort($recentProducts, static fn (array $a, array $b): int => strcmp((string) $b['created_at'], (string) $a['created_at']) ?: (int) $b['id'] <=> (int) $a['id']);
$recentProducts = count($products) >= 4 ? array_slice($recentProducts, 0, min(3, max(1, intdiv(count($products), 3)))) : [];
$newProductIds = array_map(static fn (array $item): int => (int) $item['id'], $recentProducts);
?>
<main class="store-main" id="contenido-tienda">
    <div class="container">
        <section class="store-intro" aria-labelledby="store-title">
            <div>
                <span class="store-eyebrow">TU TIENDA TECNO STAR</span>
                <h1 id="store-title"><?= $signedIn && $firstName !== '' ? 'Hola, ' . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . '.' : 'Encuentra tu próximo accesorio.' ?></h1>
                <p>Todo para conectar con tu día. Encuentra lo que necesitas y guárdalo para después.</p>
            </div>
            <?php if ($signedIn): ?>
                <a class="store-account-link" href="<?= app_url_html('/profile') ?>"><i class="bi bi-person-circle" aria-hidden="true"></i> Mi cuenta <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            <?php else: ?>
                <a class="store-account-link" href="<?= app_url_html('/login') ?>">Inicia sesión para guardar tus productos <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            <?php endif; ?>
        </section>

        <div class="store-layout <?= $catalogError ? 'store-layout--error' : '' ?>">
            <aside class="store-categories" aria-labelledby="store-categories-title" <?= $catalogError ? 'hidden' : '' ?>>
                <h2 id="store-categories-title">Explora por categoría</h2>
                <nav class="store-category-list" aria-label="Categorías de productos">
                    <button type="button" class="is-active" data-category-choice="" aria-pressed="true"><span><i class="bi bi-grid" aria-hidden="true"></i> Todos los productos</span><small><?= count($products) ?></small></button>
                    <?php foreach ($categoryCounts as $category => $count): ?>
                        <button type="button" data-category-choice="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>" aria-pressed="false"><span><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></span><small><?= $count ?></small></button>
                    <?php endforeach; ?>
                </nav>
                <div class="store-help"><i class="bi bi-bag-check" aria-hidden="true"></i><strong>A tu ritmo</strong><p>Añade productos a tu carrito o guarda tus favoritos mientras eliges.</p></div>
            </aside>

            <div class="store-catalog-content">
                <?php if ($recentProducts !== []): ?>
                    <section class="store-new-arrivals" aria-labelledby="store-new-title" data-novelty-section>
                        <div class="store-new-arrivals__heading"><span class="store-eyebrow">RECIÉN AÑADIDOS</span><h2 id="store-new-title">Lo más reciente</h2><p>Los últimos productos incorporados al catálogo.</p></div>
                        <div class="store-new-arrivals__items">
                            <?php foreach ($recentProducts as $recent): ?>
                                <a class="store-new-product" href="<?= app_url_html('/product?id=' . (int) $recent['id']) ?>">
                                    <span class="store-new-product__image"><?php if (!empty($recent['image'])): ?><img src="<?= htmlspecialchars((string) $recent['image'], ENT_QUOTES, 'UTF-8') ?>" alt="" width="64" height="64" loading="lazy" data-store-image><?php endif; ?><span class="store-image-fallback" <?= !empty($recent['image']) ? 'hidden' : '' ?>><i class="bi <?= htmlspecialchars((string) ($recent['icon'] ?? 'bi-box-seam'), ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span></span>
                                    <span><strong><?= htmlspecialchars((string) $recent['name'], ENT_QUOTES, 'UTF-8') ?></strong><small>$<?= number_format((float) $recent['price'], 2) ?></small></span><i class="bi bi-arrow-up-right" aria-hidden="true"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <section id="productos" class="store-products" aria-labelledby="catalog-title">
                    <div class="store-catalog-heading"><div><span class="store-eyebrow">ELIGE LO QUE VA CONTIGO</span><h2 id="catalog-title" tabindex="-1">Todos los productos</h2></div><span class="store-results" data-catalog-result-count role="status" aria-live="polite"><?= $catalogError ? 'Catálogo no disponible' : count($products) . (count($products) === 1 ? ' producto' : ' productos') ?></span></div>
                    <?php if (!$catalogError && $products !== []): ?>
                        <div class="store-controls" aria-label="Ordenar productos">
                            <div class="store-sort"><label for="catalog-sort">Ordenar</label><select id="catalog-sort" data-catalog-sort><option value="">Orden del catálogo</option><option value="newest">Más recientes</option><option value="price-asc">Menor precio</option><option value="price-desc">Mayor precio</option><option value="name">Nombre: A–Z</option></select></div>
                        </div>
                        <div class="store-filter-row">
                            <span class="store-active-category" data-active-category hidden></span>
                            <button class="store-clear-filters" type="button" data-clear-catalog hidden>Limpiar filtros <i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($catalogError): ?>
                        <div class="store-empty" role="alert"><span class="store-empty__icon"><i class="bi bi-wifi-off" aria-hidden="true"></i></span><h3>No pudimos cargar el catálogo</h3><p>Intenta nuevamente en un momento. Tus productos guardados siguen vinculados a tu cuenta.</p><a class="store-empty__action" href="<?= app_url_html('/store') ?>">Volver a cargar <i class="bi bi-arrow-clockwise" aria-hidden="true"></i></a></div>
                    <?php elseif ($products === []): ?>
                        <div class="store-empty"><span class="store-empty__icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><h3>No hay productos disponibles por ahora</h3><p>Vuelve más tarde para consultar las existencias o conoce nuestros servicios.</p><a class="store-empty__action" href="<?= app_url_html('/#servicios') ?>">Conocer servicios <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
                    <?php else: ?>
                        <div class="store-product-grid" data-catalog-grid>
                            <?php foreach ($products as $productIndex => $product): ?><?php require BASE_PATH . '/app/Views/store/partials/product-card.php'; ?><?php endforeach; ?>
                        </div>
                        <div class="store-empty" data-catalog-empty hidden><span class="store-empty__icon"><i class="bi bi-search" aria-hidden="true"></i></span><h3>No encontramos coincidencias</h3><p>Prueba con otra palabra, cambia de categoría o quita los filtros.</p><button class="store-empty__action" type="button" data-clear-catalog>Ver todos los productos <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div>
                    <?php endif; ?>
                </section>
                <aside class="store-service-note" aria-label="Otros servicios"><i class="bi bi-printer" aria-hidden="true"></i><p><strong>También estamos para tus ideas.</strong> Copias, impresiones y servicios de papelería en nuestra tienda.</p><a href="<?= app_url_html('/#servicios') ?>">Conocer servicios <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></aside>
            </div>
        </div>
    </div>
</main>
