<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/Tecno_Star/public/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <a class="brand" href="/Tecno_Star/public/" aria-label="Ir al inicio de Tecno Star">
            <img
                class="brand__image"
                src="/Tecno_Star/public/assets/images/logo-tecno-star.png"
                alt="Tecno Star"
            >
        </a>
    </header>

    <main class="container">
        <img
            class="hero-logo"
            src="/Tecno_Star/public/assets/images/logo-estrella.png"
            alt=""
            aria-hidden="true"
        >
        <h1>Bienvenido a <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></h1>
        <p>La estructura MVC está lista para comenzar el desarrollo.</p>
    </main>
    <script src="/Tecno_Star/public/assets/js/app.js"></script>
</body>
</html>
