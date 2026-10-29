<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Catálogo de productos y servicios de Tecno Star.">
    <title>Tienda | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Playfair+Display:wght@700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/app.css?v='.filemtime(BASE_PATH.'/public/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/store.css?v=20261016-9') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
</head>
<body>
    <?php require BASE_PATH . '/app/Views/store/partials/navbar.php'; ?>

    <?php require BASE_PATH . '/app/Views/store/partials/catalog-content.php'; ?>

    <?php require BASE_PATH . '/app/Views/store/partials/footer.php'; ?>

    <?php require BASE_PATH . '/app/Views/store/partials/shopping-drawers.php'; ?>

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

    <script src="<?= app_url_html('/assets/js/app.js?v='.filemtime(BASE_PATH.'/public/assets/js/app.js')) ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script defer src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script defer src="<?= app_url_html('/assets/js/animations.js?v='.filemtime(BASE_PATH.'/public/assets/js/animations.js')) ?>"></script>
</body>
</html>
