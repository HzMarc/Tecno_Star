<?php
$escapeGoogle = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$googleValue = static function (string $key) use ($identity, $escapeGoogle): string {
    return $escapeGoogle(is_string($_POST[$key] ?? null) ? $_POST[$key] : ($identity[$key] ?? ''));
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Continuar con Google | <?= $escapeGoogle(APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/app.css?v=' . filemtime(BASE_PATH . '/public/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/auth.css?v=' . filemtime(BASE_PATH . '/public/assets/css/auth.css')) ?>">
</head>
<body class="auth-page auth-page--google">
<main class="auth-container container-fluid">
    <section class="auth-card auth-google-complete" aria-labelledby="google-title">
        <img class="auth-card__logo" src="<?= app_url_html('/assets/images/logo-estrella.png') ?>" alt="Tecno Star">
        <h1 id="google-title"><?= $existingAccount ? 'Vincular tu cuenta' : 'Completa tu cuenta' ?></h1>
        <p class="auth-card__description">Google verificó tu correo: <strong><?= $escapeGoogle($identity['email']) ?></strong></p>
        <?php if ($message !== null): ?><div class="alert alert-danger" role="alert"><?= $escapeGoogle($message) ?></div><?php endif; ?>
        <form class="auth-form" method="post" action="<?= app_url_html('/auth/google/complete') ?>">
            <?= Csrf::field() ?>
            <?php if ($existingAccount): ?>
                <p>Ya tienes una cuenta con este correo. Confirma tu contraseña de Tecno Star para vincularla. La próxima vez podrás entrar con Google.</p>
                <input type="hidden" name="username" autocomplete="username" value="<?= $escapeGoogle($identity['email']) ?>">
                <div>
                    <label class="form-label" for="google-password">Contraseña de Tecno Star</label>
                    <input class="form-control" id="google-password" name="password" type="password" autocomplete="current-password" maxlength="72" required>
                </div>
                <a href="<?= app_url_html('/forgot-password') ?>">¿Olvidaste tu contraseña?</a>
            <?php else: ?>
                <p>Confirma tus datos de contacto para preparar y retirar tus pedidos.</p>
                <div>
                    <label class="form-label" for="google-names">Nombres</label>
                    <input class="form-control" id="google-names" name="names" autocomplete="given-name" maxlength="100" value="<?= $googleValue('names') ?>" required>
                </div>
                <div>
                    <label class="form-label" for="google-last-names">Apellidos</label>
                    <input class="form-control" id="google-last-names" name="last_names" autocomplete="family-name" maxlength="100" value="<?= $googleValue('last_names') ?>" required>
                </div>
                <div>
                    <label class="form-label" for="google-phone">Teléfono</label>
                    <input class="form-control" id="google-phone" name="phone" type="tel" autocomplete="tel" maxlength="20" value="<?= $googleValue('phone') ?>" placeholder="7000-0000" pattern="(?:\+?503[ ]?)?[267][0-9]{3}[ \-]?[0-9]{4}" required>
                    <div class="form-text">Teléfono de El Salvador de 8 dígitos; puedes añadir +503.</div>
                </div>
            <?php endif; ?>
            <button class="btn btn-primary w-100" type="submit"><?= $existingAccount ? 'Vincular y entrar' : 'Crear cuenta y entrar' ?></button>
        </form>
        <a class="auth-back-link" href="<?= app_url_html('/login') ?>">Volver al inicio de sesión</a>
    </section>
</main>
</body>
</html>
