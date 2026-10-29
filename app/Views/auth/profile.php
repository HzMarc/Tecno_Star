<?php

declare(strict_types=1);

$fullName = trim((string) $user['nombres'] . ' ' . (string) $user['apellidos']);
$isAdministrator = $user['nombre_rol']==='Administrador';
$initials = mb_strtoupper(
    mb_substr((string) ($user['nombres'] ?? ''), 0, 1)
    . mb_substr((string) ($user['apellidos'] ?? ''), 0, 1)
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/app.css?v='.filemtime(BASE_PATH.'/public/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/profile.css?v='.filemtime(BASE_PATH.'/public/assets/css/profile.css')) ?>">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/accounts.css?v='.filemtime(BASE_PATH.'/public/assets/css/accounts.css')) ?>"><script defer src="<?= app_url_html('/assets/js/commerce.js?v='.filemtime(BASE_PATH.'/public/assets/js/commerce.js')) ?>"></script>
</head>
<body>
    <header class="profile-header">
        <div class="profile-header__inner">
            <a class="profile-header__brand" href="<?= app_url_html($isAdministrator?'/admin':'/store') ?>" aria-label="<?= $isAdministrator?'Ir a administración':'Ir a la tienda' ?>">
                <img src="<?= app_url_html('/assets/images/logo-estrella.png') ?>" alt="">
                <span>Tecno Star</span>
            </a>
            <a class="profile-header__back" href="<?= app_url_html($user['nombre_rol']==='Administrador'?'/admin':'/store') ?>">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                <?= $user['nombre_rol']==='Administrador'?'Volver a administración':'Volver a la tienda' ?>
            </a>
        </div>
    </header>

    <main class="profile-page">
        <div class="profile-page__intro">
            <span class="profile-page__eyebrow">CUENTA TECNO STAR</span>
            <h1>Mi perfil</h1>
            <p><?= $isAdministrator?'Administra tus datos personales y la seguridad de tu cuenta.':'Consulta tus datos, pedidos y compras.' ?></p>
        </div>

        <section class="profile-layout" aria-label="Información de la cuenta">
            <article class="profile-hero">
                <div class="profile-hero__avatar" aria-hidden="true"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="profile-hero__content">
                    <span class="profile-hero__welcome">¡Hola!</span>
                    <h2><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars((string) $user['correo'], ENT_QUOTES, 'UTF-8') ?></p>
                    <?php if($user['nombre_rol']!=='Cliente'): ?><span class="profile-role"><i class="bi bi-shield-check" aria-hidden="true"></i><?= htmlspecialchars((string) $user['nombre_rol'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                </div>
            </article>

            <article class="profile-panel">
                <div class="profile-panel__heading">
                    <div>
                        <span class="profile-panel__eyebrow">INFORMACIÓN PERSONAL</span>
                        <h2>Datos de tu cuenta</h2>
                    </div>
                    <i class="bi bi-person-vcard" aria-hidden="true"></i>
                </div>

                <div class="profile-info-grid">
                    <div class="profile-info">
                        <span class="profile-info__label"><i class="bi bi-person" aria-hidden="true"></i>Nombre completo</span>
                        <strong><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="profile-info">
                        <span class="profile-info__label"><i class="bi bi-envelope" aria-hidden="true"></i>Correo electrónico</span>
                        <strong><?= htmlspecialchars((string) $user['correo'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="profile-info">
                        <span class="profile-info__label"><i class="bi bi-telephone" aria-hidden="true"></i>Teléfono</span>
                        <strong><?= htmlspecialchars(trim((string) ($user['telefono'] ?? '')) ?: 'No registrado', ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                </div>
                <?php if($accountsEnabled??false): ?><div class="account-edit-actions"><button class="order-primary" type="button" data-commerce-open="profile-edit-modal">Editar perfil</button><button class="order-secondary" type="button" data-commerce-open="profile-password-modal">Cambiar contraseña</button></div><?php endif; ?>
            </article>

            <aside class="profile-actions">
                <h2>Accesos rápidos</h2>
                <?php if($isAdministrator): ?>
                <?php foreach([['/admin/orders','bi-bag','Pedidos','Gestiona las reservas y los retiros'],['/admin/sales','bi-receipt','Ventas','Consulta cobros y comprobantes'],['/admin/products','bi-box-seam','Productos','Gestiona el catálogo de la tienda']] as [$path,$icon,$label,$description]): ?>
                <a class="profile-action" href="<?= app_url_html($path) ?>"><span class="profile-action__icon profile-action__icon--blue"><i class="bi <?= $icon ?>" aria-hidden="true"></i></span><span><strong><?= $label ?></strong><small><?= $description ?></small></span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                <?php endforeach; ?>
                <?php else: ?>
                <a class="profile-action" href="<?= app_url_html('/orders') ?>"><span class="profile-action__icon profile-action__icon--blue"><i class="bi bi-bag" aria-hidden="true"></i></span><span><strong>Mis pedidos</strong><small>Consulta el estado y el retiro</small></span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                <a class="profile-action" href="<?= app_url_html('/purchases') ?>"><span class="profile-action__icon profile-action__icon--blue"><i class="bi bi-receipt" aria-hidden="true"></i></span><span><strong>Mis compras</strong><small>Consulta tus comprobantes</small></span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                <a class="profile-action" href="<?= app_url_html('/store') ?>">
                    <span class="profile-action__icon profile-action__icon--blue"><i class="bi bi-grid" aria-hidden="true"></i></span>
                    <span><strong>Explorar productos</strong><small>Descubre nuestro catálogo</small></span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
                <a class="profile-action" href="<?= app_url_html('/store#favoritos') ?>">
                    <span class="profile-action__icon profile-action__icon--light"><i class="bi bi-heart" aria-hidden="true"></i></span>
                    <span><strong>Favoritos</strong><small>Consulta tus productos guardados</small></span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </a>
                <?php endif; ?>
                <form class="logout-form" method="post" action="<?= app_url_html('/logout') ?>"><?= Csrf::field() ?><button class="profile-action profile-action--logout" type="submit">
                    <span class="profile-action__icon profile-action__icon--danger"><i class="bi bi-box-arrow-right" aria-hidden="true"></i></span>
                    <span><strong>Cerrar sesión</strong><small>Salir de tu cuenta de forma segura</small></span>
                    <i class="bi bi-chevron-right" aria-hidden="true"></i>
                </button></form>
            </aside>
        </section>
    </main>
<?php require BASE_PATH.'/app/Views/auth/account-modals.php'; ?><div class="order-toasts" data-commerce-toasts role="status" aria-live="polite"></div></body>
</html>
