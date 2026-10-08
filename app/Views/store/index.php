<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Catálogo de productos y servicios de Tecno Star.">
    <title>Tienda | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Tecno_Star/public/assets/css/app.css">
</head>
<body>
    <?php require BASE_PATH . '/app/Views/store/partials/navbar.php'; ?>

    <main>
        <section class="catalog-hero">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="eyebrow"><i class="bi bi-grid"></i> Catálogo Tecno Star</span>
                        <h1>Encuentra lo que necesitas</h1>
                        <p>Explora accesorios tecnológicos y productos para acompañarte en tus actividades diarias.</p>
                    </div>
                    <div class="col-lg-5">
                        <article class="weekly-offer-card">
                            <div class="weekly-offer-card__icon">
                                <i class="bi bi-laptop" aria-hidden="true"></i>
                            </div>
                            <div>
                                <span class="weekly-offer-card__eyebrow">Oferta de la semana</span>
                                <h2>Laptops gamer</h2>
                                <p>Hasta el <strong>30% de descuento</strong></p>
                                <a class="btn btn-light btn-sm" href="#productos">Ver oferta <i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section id="productos" class="catalog-section">
            <div class="container">
                <div class="catalog-toolbar">
                    <div>
                        <span class="eyebrow">Productos disponibles</span>
                        <h2>Elige tu próximo accesorio</h2>
                    </div>
                    <div class="catalog-toolbar__filters">
                        <label class="catalog-filter-label" for="catalog-category">Categoría</label>
                        <select class="form-select catalog-filter" id="catalog-category" data-catalog-filter aria-label="Filtrar por categoría">
                            <option value="">Todas las categorías</option>
                            <option value="Accesorios móviles">Accesorios móviles</option>
                            <option value="Audio y conectividad">Audio y conectividad</option>
                            <option value="Tecnología">Tecnología</option>
                        </select>
                        <label class="catalog-filter-label" for="catalog-sort">Ordenar por</label>
                        <select class="form-select catalog-filter" id="catalog-sort" data-catalog-sort aria-label="Ordenar productos por precio">
                            <option value="">Relevancia</option>
                            <option value="price-asc">Precio: menor a mayor</option>
                            <option value="price-desc">Precio: mayor a menor</option>
                        </select>
                    </div>
                </div>

                <div class="row g-4" data-catalog-grid>
                    <?php foreach ($products as $product): ?>
                        <div class="col-sm-6 col-lg-3 catalog-product" data-product-card data-category="<?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8') ?>" data-price="<?= htmlspecialchars((string) $product['price'], ENT_QUOTES, 'UTF-8') ?>" data-search="<?= htmlspecialchars(strtolower($product['name'] . ' ' . $product['category']), ENT_QUOTES, 'UTF-8') ?>">
                            <article class="product-card">
                                <div class="product-card__image">
                                    <?php if ($product['is_offer']): ?>
                                        <span class="product-card__badge">Oferta</span>
                                    <?php endif; ?>
                                    <i class="bi <?= htmlspecialchars($product['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i>
                                </div>
                                <span class="product-card__brand"><?= htmlspecialchars($product['brand'], ENT_QUOTES, 'UTF-8') ?></span>
                                <h3><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <div class="product-card__pricing">
                                    <?php if ($product['old_price'] !== null): ?>
                                        <del>$<?= number_format((float) $product['old_price'], 2) ?></del>
                                    <?php endif; ?>
                                    <strong class="product-card__price">$<?= number_format((float) $product['price'], 2) ?></strong>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="catalog-empty d-none" data-catalog-empty>
                    <i class="bi bi-search"></i>
                    <h3>No encontramos productos</h3>
                    <p>Prueba con otra palabra o categoría.</p>
                </div>
            </div>
        </section>

        <section class="catalog-services">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-7">
                        <span class="eyebrow eyebrow--light">También te ayudamos con</span>
                        <h2>Copias, impresiones y servicios de papelería</h2>
                        <p>Visítanos para solicitar servicios de papelería y retirar tus pedidos en tienda.</p>
                    </div>
                    <div class="col-lg-5 text-lg-end">
                        <a class="btn btn-light btn-lg" href="/Tecno_Star/public/#servicios">Conocer servicios <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="store-footer">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <img class="store-footer__logo" src="/Tecno_Star/public/assets/images/logo-tecno-star.png" alt="Tecno Star">
            <p class="store-footer__description">Accesorios tecnológicos y servicios de papelería.</p>
            <small>&copy; <?= date('Y') ?> Tecno Star</small>
        </div>
    </footer>

    <?php if ($message !== null): ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div id="auth-toast" class="toast auth-toast border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i data-toast-icon class="bi bi-check-circle-fill me-2"></i>
                    <strong class="me-auto" data-toast-title>Éxito</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
                <div class="toast-body" data-toast-message></div>
            </div>
        </div>
        <div
            data-server-message="<?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>"
            data-message-type="<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>"
        ></div>
    <?php endif; ?>

    <script src="/Tecno_Star/public/assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
