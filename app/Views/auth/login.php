<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/app.css?v='.filemtime(BASE_PATH.'/public/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/auth.css?v='.filemtime(BASE_PATH.'/public/assets/css/auth.css')) ?>">
</head>
<body class="auth-page auth-page--login">
    <main class="auth-container container-fluid">
        <div class="auth-layout">
            <aside class="auth-showcase" aria-label="Información de Tecno Star">
                <a class="auth-showcase__brand" href="<?= app_url_html('/') ?>" aria-label="Volver a la tienda Tecno Star">
                    <img src="<?= app_url_html('/assets/images/logo-tecno-star.png') ?>" alt="Tecno Star">
                </a>
                <div class="auth-showcase__art">
                    <div class="auth-art-card auth-art-card--one"><i class="bi bi-phone"></i></div>
                    <div class="auth-art-card auth-art-card--two"><i class="bi bi-headphones"></i></div>
                    <div class="auth-art-card auth-art-card--three"><i class="bi bi-printer"></i></div>
                    <img src="<?= app_url_html('/assets/images/logo-estrella.png') ?>" alt="" aria-hidden="true">
                </div>
                <h2>Tu tecnología,<br><strong>más cerca.</strong></h2>
                <p>Accesorios tecnológicos y servicios de papelería para todos los días.</p>
            </aside>

            <section class="auth-card auth-card--login" aria-labelledby="login-title">
                <img class="auth-card__logo" src="<?= app_url_html('/assets/images/logo-estrella.png') ?>" alt="">
                <h1 id="login-title">Iniciar sesión</h1>
                <p class="auth-card__description">Accede a tu cuenta de Tecno Star.</p>

                <form class="auth-form" data-auth-form method="post" action="<?= app_url_html('/login') ?>" novalidate>
                    <?= Csrf::field() ?>
                    <div>
                        <label class="form-label" for="login-email">Correo electrónico</label>
                        <input class="form-control" id="login-email" name="email" type="email" autocomplete="email" required>
                        <div class="invalid-feedback">Ingresa un correo electrónico válido.</div>
                    </div>

                    <div>
                        <label class="form-label" for="login-password">Contraseña</label>
                        <div class="input-group">
                            <input class="form-control" id="login-password" name="password" type="password" autocomplete="current-password" minlength="8" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle data-target="login-password" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Ingresa tu contraseña.</div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Iniciar sesión</button>
                </form>

                <?php require BASE_PATH . '/app/Views/auth/google-button.php'; ?>

                <a class="auth-back-link" href="<?= app_url_html('/forgot-password') ?>">¿Olvidaste tu contraseña?</a>

                <p class="auth-card__footer">
                    ¿No tienes una cuenta?
                    <a href="<?= app_url_html('/register') ?>">Registrarse</a>
                </p>
                <a class="auth-back-link" href="<?= app_url_html('/') ?>">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al inicio
                </a>
            </section>
        </div>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="auth-toast" class="toast auth-toast border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i data-toast-icon class="bi bi-x-circle-fill me-2"></i>
                <strong class="me-auto" data-toast-title>Error</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="toast-body" data-toast-message></div>
        </div>
    </div>
    <?php if ($message !== null): ?>
        <div
            data-server-message="<?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>"
            data-message-type="<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>"
        ></div>
    <?php endif; ?>
    <script src="<?= app_url_html('/assets/js/app.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="<?= app_url_html('/assets/css/accounts.css') ?>"><script defer src="<?= app_url_html('/assets/js/commerce.js?v='.filemtime(BASE_PATH.'/public/assets/js/commerce.js')) ?>"></script><div class="order-toasts" data-commerce-toasts role="status" aria-live="polite"></div></body>
</html>

