<?php
$registrationValues=[];
foreach(['names','last_names','phone','email'] as $registrationField)$registrationValues[$registrationField]=htmlspecialchars(is_string($_POST[$registrationField]??null)?$_POST[$registrationField]:'',ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/app.css?v='.filemtime(BASE_PATH.'/public/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/auth.css?v='.filemtime(BASE_PATH.'/public/assets/css/auth.css')) ?>">
</head>
<body class="auth-page auth-page--register">
    <main class="auth-container container-fluid">
        <div class="auth-layout">
            <section class="auth-card auth-card--register" aria-labelledby="register-title">
                <img class="auth-card__logo" src="<?= app_url_html('/assets/images/logo-estrella.png') ?>" alt="">
                <h1 id="register-title">Crear cuenta</h1>
                <p class="auth-card__description">Regístrate para comenzar en Tecno Star.</p>

                <form class="auth-form" data-auth-form method="post" action="<?= app_url_html('/register') ?>" novalidate>
                    <?= Csrf::field() ?>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label class="form-label" for="register-names">Nombres</label>
                            <input class="form-control" id="register-names" name="names" type="text" autocomplete="given-name" maxlength="100" value="<?= $registrationValues['names'] ?>" required>
                            <div class="invalid-feedback">Ingresa tus nombres.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="register-last-names">Apellidos</label>
                            <input class="form-control" id="register-last-names" name="last_names" type="text" autocomplete="family-name" maxlength="100" value="<?= $registrationValues['last_names'] ?>" required>
                            <div class="invalid-feedback">Ingresa tus apellidos.</div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="register-phone">Teléfono</label>
                        <input class="form-control" id="register-phone" name="phone" type="tel" autocomplete="tel" maxlength="20" value="<?= $registrationValues['phone'] ?>" placeholder="7000-0000" pattern="(?:\+?503[ ]?)?[267][0-9]{3}[ \-]?[0-9]{4}" required>
                        <div class="form-text">8 dígitos, comenzando por 2, 6 o 7. Puedes añadir +503.</div>
                        <div class="invalid-feedback">Ingresa un teléfono de El Salvador, por ejemplo, 7000-0000.</div>
                    </div>

                    <div>
                        <label class="form-label" for="register-email">Correo electrónico</label>
                        <input class="form-control" id="register-email" name="email" type="email" autocomplete="email" maxlength="150" value="<?= $registrationValues['email'] ?>" required>
                        <div class="invalid-feedback">Ingresa un correo electrónico válido.</div>
                    </div>

                    <div>
                        <label class="form-label" for="register-password">Contraseña</label>
                        <div class="input-group">
                            <input class="form-control" id="register-password" name="password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle data-target="register-password" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="form-text">Usa una frase de al menos 12 caracteres.</div>
                        <div class="invalid-feedback">La contraseña debe tener al menos 12 caracteres.</div>
                    </div>

                    <div>
                        <label class="form-label" for="register-password-confirmation">Confirmar contraseña</label>
                        <div class="input-group">
                            <input class="form-control" id="register-password-confirmation" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" data-password-confirmation required>
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle data-target="register-password-confirmation" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Registrarse</button>
                </form>

                <?php require BASE_PATH . '/app/Views/auth/google-button.php'; ?>

                <p class="auth-card__footer">
                    ¿Ya tienes una cuenta?
                    <a href="<?= app_url_html('/login') ?>">Iniciar sesión</a>
                </p>
                <a class="auth-back-link" href="<?= app_url_html('/') ?>">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al inicio
                </a>
            </section>

            <aside class="auth-showcase" aria-label="Información de Tecno Star">
                <a class="auth-showcase__brand" href="<?= app_url_html('/') ?>" aria-label="Volver al inicio de Tecno Star">
                    <img src="<?= app_url_html('/assets/images/logo-tecno-star.png') ?>" alt="Tecno Star">
                </a>
                <div class="auth-showcase__art">
                    <div class="auth-art-card auth-art-card--one"><i class="bi bi-phone"></i></div>
                    <div class="auth-art-card auth-art-card--two"><i class="bi bi-headphones"></i></div>
                    <div class="auth-art-card auth-art-card--three"><i class="bi bi-printer"></i></div>
                    <img src="<?= app_url_html('/assets/images/logo-estrella.png') ?>" alt="" aria-hidden="true">
                </div>
                <h2>Forma parte de<br><strong>Tecno Star.</strong></h2>
                <p>Regístrate para consultar productos y preparar tus pedidos de forma sencilla.</p>
            </aside>
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
</body>
</html>

