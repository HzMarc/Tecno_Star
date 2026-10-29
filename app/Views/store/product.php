<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?> | Tecno Star">
    <title><?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Playfair+Display:wght@700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/app.css?v='.filemtime(BASE_PATH.'/public/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/product.css?v=20261017-1') ?>">
</head>
<body class="product-page">
    <?php require BASE_PATH . '/app/Views/store/partials/navbar.php'; ?>

    <main class="product-detail-page">
        <div class="container">
            <a class="product-detail-page__back" href="<?= app_url_html('/store') ?>">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al catálogo
            </a>

            <div class="product-detail-page__layout">
                <div class="product-detail-page__image">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?= htmlspecialchars((string) $product['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?>" data-store-image>
                    <?php endif; ?>
                    <span class="store-image-fallback" <?= !empty($product['image']) ? 'hidden' : '' ?>><i class="bi bi-box-seam" aria-hidden="true"></i><span>Imagen no disponible</span></span>
                </div>

                <article class="product-detail-page__content">
                    <div class="product-detail-page__eyebrow">
                        <span class="product-detail-page__brand"><?= htmlspecialchars((string) $product['brand'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="product-detail-page__category"><?= htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <h1><?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                    <span class="product-detail-page__price-label">Precio por unidad</span>
                    <strong class="product-detail-page__price">$<?= number_format((float) $product['price'], 2) ?></strong>
                    <div class="product-detail-page__stock <?= (int) $product['stock'] > 0 ? 'is-available' : 'is-unavailable' ?>">
                        <i class="bi <?= (int) $product['stock'] > 0 ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>" aria-hidden="true"></i>
                        <strong><?= (int) $product['stock'] > 0 ? 'Disponible' : 'Agotado' ?></strong>
                        <?php if ((int) $product['stock'] > 0): ?>
                            <span><?= (int) $product['stock'] ?> <?= (int) $product['stock'] === 1 ? 'unidad disponible' : 'unidades disponibles' ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ((int) $product['stock'] > 0): ?>
                        <div class="product-detail-page__quantity" data-product-quantity data-max-stock="<?= (int) $product['stock'] ?>">
                            <span>Cantidad</span>
                            <button type="button" data-quantity-decrease aria-label="Disminuir cantidad" disabled>−</button>
                            <strong data-quantity-value aria-live="polite">1</strong>
                            <button type="button" data-quantity-increase aria-label="Aumentar cantidad" <?= (int) $product['stock'] === 1 ? 'disabled' : '' ?>>+</button>
                        </div>
                    <?php endif; ?>
                    <div class="product-detail-page__actions">
                        <button class="btn btn-primary" type="button" data-detail-add-cart data-product-id="<?= (int) $product['id'] ?>" data-product-name="<?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?>" data-product-brand="<?= htmlspecialchars((string) $product['brand'], ENT_QUOTES, 'UTF-8') ?>" data-product-category="<?= htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8') ?>" data-product-description="<?= htmlspecialchars((string) $product['description'], ENT_QUOTES, 'UTF-8') ?>" data-product-price="<?= htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8') ?>" data-product-image="<?= htmlspecialchars((string) ($product['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-product-stock="<?= (int) $product['stock'] ?>" <?= (int) $product['stock'] < 1 ? 'disabled' : '' ?>>
                            <i class="bi bi-cart-plus me-1" aria-hidden="true"></i> Agregar al carrito
                        </button>
                        <button class="btn btn-outline-primary product-detail-page__wishlist" type="button" data-detail-add-wishlist data-product-id="<?= (int) $product['id'] ?>" data-product-name="<?= htmlspecialchars((string) $product['name'], ENT_QUOTES, 'UTF-8') ?>" data-product-brand="<?= htmlspecialchars((string) $product['brand'], ENT_QUOTES, 'UTF-8') ?>" data-product-category="<?= htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8') ?>" data-product-description="<?= htmlspecialchars((string) $product['description'], ENT_QUOTES, 'UTF-8') ?>" data-product-price="<?= htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8') ?>" data-product-image="<?= htmlspecialchars((string) ($product['image'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-product-stock="<?= (int) $product['stock'] ?>" aria-label="Agregar a favoritos" title="Agregar a favoritos">
                            <i class="bi bi-heart" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="product-detail-page__pickup"><i class="bi bi-shop" aria-hidden="true"></i><div><strong>Retiro en tienda</strong><span>Ilobasco, Cabañas</span></div><a href="<?= app_url_html('/#nosotros') ?>">Ver ubicación <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></div>
                </article>
            </div>

            <section class="product-detail-page__info" aria-labelledby="product-info-title">
                <div class="product-detail-page__about">
                    <h2 id="product-info-title">Sobre este producto</h2>
                    <p class="product-detail-page__description"><?= htmlspecialchars(trim((string) ($product['description'] ?? '')) ?: 'Aún no hay una descripción para este producto.', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div>
                <h2>Ficha del producto</h2>
                <dl class="product-detail-page__specs">
                    <div><dt>Marca</dt><dd><?= htmlspecialchars((string) $product['brand'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Categoría</dt><dd><?= htmlspecialchars((string) $product['category'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                </dl>
                </div>
            </section>
        </div>
    </main>

    <?php require BASE_PATH . '/app/Views/store/partials/footer.php'; ?>
    <?php require BASE_PATH . '/app/Views/store/partials/shopping-drawers.php'; ?>
    <script src="<?= app_url_html('/assets/js/app.js?v='.filemtime(BASE_PATH.'/public/assets/js/app.js')) ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
