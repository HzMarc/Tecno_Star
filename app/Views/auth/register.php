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
    <link rel="stylesheet" href="/Tecno_Star/public/assets/css/app.css">
</head>
<body class="auth-page auth-page--register">
    <main class="auth-container container-fluid">
        <div class="auth-layout">
            <section class="auth-card auth-card--register" aria-labelledby="register-title">
                <img class="auth-card__logo" src="/Tecno_Star/public/assets/images/logo-estrella.png" alt="">
                <h1 id="register-title">Crear cuenta</h1>
                <p class="auth-card__description">Regístrate para comenzar en Tecno Star.</p>

                <form class="auth-form" data-auth-form method="post" action="/Tecno_Star/public/register" novalidate>
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <label class="form-label" for="register-names">Nombres</label>
                            <input class="form-control" id="register-names" name="names" type="text" autocomplete="given-name" required>
                            <div class="invalid-feedback">Ingresa tus nombres.</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="register-last-names">Apellidos</label>
                            <input class="form-control" id="register-last-names" name="last_names" type="text" autocomplete="family-name" required>
                            <div class="invalid-feedback">Ingresa tus apellidos.</div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label" for="register-phone">Teléfono</label>
                        <input class="form-control" id="register-phone" name="phone" type="tel" autocomplete="tel" required>
                        <div class="invalid-feedback">Ingresa un número de teléfono.</div>
                    </div>

                    <div>
                        <label class="form-label" for="register-email">Correo electrónico</label>
                        <input class="form-control" id="register-email" name="email" type="email" autocomplete="email" required>
                        <div class="invalid-feedback">Ingresa un correo electrónico válido.</div>
                    </div>

                    <div>
                        <label class="form-label" for="register-password">Contraseña</label>
                        <div class="input-group">
                            <input class="form-control" id="register-password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle data-target="register-password" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="form-text">Usa al menos 8 caracteres.</div>
                        <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres.</div>
                    </div>

                    <div>
                        <label class="form-label" for="register-password-confirmation">Confirmar contraseña</label>
                        <div class="input-group">
                            <input class="form-control" id="register-password-confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" data-password-confirmation required>
                            <button class="btn btn-outline-secondary password-toggle" type="button" data-password-toggle data-target="register-password-confirmation" aria-label="Mostrar contraseña">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">Las contraseñas deben coincidir.</div>
                    </div>

                    <button class="btn btn-primary w-100" type="submit">Registrarse</button>
                </form>

                <div class="auth-divider"><span>o continúa con</span></div>
                <a class="google-button btn btn-outline-secondary" href="/Tecno_Star/public/auth/google">
                    <svg class="google-button__icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M21.35 12.23c0-.72-.06-1.42-.18-2.09H12v3.96h5.24a4.48 4.48 0 0 1-1.94 2.94v2.45h3.14c1.84-1.69 2.91-4.18 2.91-7.26Z"/>
                        <path fill="#34A853" d="M12 21.5c2.63 0 4.84-.87 6.45-2.36l-3.14-2.45c-.87.58-1.98.92-3.31.92-2.54 0-4.7-1.72-5.47-4.03H3.29v2.53A9.74 9.74 0 0 0 12 21.5Z"/>
                        <path fill="#FBBC05" d="M6.53 13.58a5.85 5.85 0 0 1 0-3.16V7.89H3.29a9.5 9.5 0 0 0 0 8.22l3.24-2.53Z"/>
                        <path fill="#EA4335" d="M12 6.39c1.43 0 2.71.49 3.72 1.45l2.79-2.79C16.84 3.48 14.63 2.5 12 2.5a9.74 9.74 0 0 0-8.71 5.39l3.24 2.53C7.3 8.11 9.46 6.39 12 6.39Z"/>
                    </svg>
                    Registrarse con Google
                </a>

                <p class="auth-card__footer">
                    ¿Ya tienes una cuenta?
                    <a href="/Tecno_Star/public/login">Iniciar sesión</a>
                </p>
                <a class="auth-back-link" href="/Tecno_Star/public/">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al inicio
                </a>
            </section>

            <aside class="auth-showcase" aria-label="Información de Tecno Star">
                <a class="auth-showcase__brand" href="/Tecno_Star/public/" aria-label="Volver al inicio de Tecno Star">
                    <img src="/Tecno_Star/public/assets/images/logo-tecno-star.png" alt="Tecno Star">
                </a>
                <div class="auth-showcase__art">
                    <div class="auth-art-card auth-art-card--one"><i class="bi bi-phone"></i></div>
                    <div class="auth-art-card auth-art-card--two"><i class="bi bi-headphones"></i></div>
                    <div class="auth-art-card auth-art-card--three"><i class="bi bi-printer"></i></div>
                    <img src="/Tecno_Star/public/assets/images/logo-estrella.png" alt="" aria-hidden="true">
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
    <script src="/Tecno_Star/public/assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
