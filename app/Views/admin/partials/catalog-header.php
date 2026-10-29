<?php
$escape = static fn (mixed $value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$notice ??= null;
?>
<!DOCTYPE html>
<html lang="es"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $escape($title) ?> | <?= $escape(APP_NAME) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/admin.css?v=' . filemtime(BASE_PATH . '/public/assets/css/admin.css')) ?>">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/admin-catalog.css?v=' . filemtime(BASE_PATH . '/public/assets/css/admin-catalog.css')) ?>">
    <?php require BASE_PATH . '/app/Views/admin/partials/shell-assets.php'; ?>
    <?php require BASE_PATH . '/app/Views/admin/partials/table-assets.php'; ?>
</head><body class="admin-page"><div class="admin-layout">
<?php require BASE_PATH . '/app/Views/admin/partials/sidebar.php'; ?>
<main class="admin-main">
    <header class="admin-topbar"><div><?php require BASE_PATH.'/app/Views/admin/partials/back-link.php'; ?><h1><?= $escape($title) ?></h1><?php if(!empty($pageDescription)): ?><p class="admin-page-description"><?= $escape($pageDescription) ?></p><?php endif; ?></div>
    <?php if(isset($pageAction)): ?><?php if(isset($pageAction['url'])): ?><a class="admin-primary-button" data-product-modal href="<?= app_url_html($pageAction['url']) ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> <?= $escape($pageAction['label']) ?></a><?php else: ?><button class="admin-primary-button" type="button" data-entity-modal data-editor-title="<?= $escape($pageAction['label']) ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> <?= $escape($pageAction['label']) ?></button><?php endif; ?><?php endif; ?></header>
    <?php if ($errors && !isset($values)): ?><div class="catalog-errors" role="alert"><strong>Revisa la información indicada.</strong><ul><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
