<?php

declare(strict_types=1);

$formatMoney = static fn (float $amount): string => '$' . number_format($amount, 2);
$formatNumber = static fn (float|int $amount): string => number_format($amount);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/admin.css?v=' . filemtime(BASE_PATH . '/public/assets/css/admin.css')) ?>">
    <?php require BASE_PATH . '/app/Views/admin/partials/shell-assets.php'; ?>
</head>
<body class="admin-page">
    <div class="admin-layout">
        <?php $adminSection = 'dashboard'; require BASE_PATH . '/app/Views/admin/partials/sidebar.php'; ?>

        <main class="admin-main">
            <header class="admin-topbar">
                <div>
                    <span class="admin-eyebrow">Panel de control</span>
                    <h1>Resumen general</h1>
                </div>
                <a class="admin-topbar__store" href="<?= app_url_html('/store') ?>"><i class="bi bi-shop"></i> Ver tienda</a>
            </header>

            <section class="admin-metrics" aria-label="Métricas principales">
                <a class="admin-metric-card" href="<?= app_url_html('/admin/products?status=activo') ?>">
                    <div class="admin-metric-card__icon admin-metric-card__icon--blue"><i class="bi bi-box-seam"></i></div>
                    <div><span>Productos activos</span><strong><?= $formatNumber($metrics['products']) ?></strong><small>En el catálogo</small></div>
                </a>
                <a class="admin-metric-card" href="<?= app_url_html('/admin/inventory') ?>">
                    <div class="admin-metric-card__icon admin-metric-card__icon--green"><i class="bi bi-stack"></i></div>
                    <div><span>Stock disponible</span><strong><?= rtrim(rtrim(number_format($metrics['stock'],3,'.',','),'0'),'.') ?></strong><small>Stock físico menos reservado</small></div>
                </a>
                <a class="admin-metric-card" href="<?= app_url_html('/admin/orders?status=pendiente') ?>">
                    <div class="admin-metric-card__icon admin-metric-card__icon--orange"><i class="bi bi-hourglass-split"></i></div>
                    <div><span>Pedidos pendientes</span><strong><?= $formatNumber($metrics['pending_orders']) ?></strong><small>Requieren atención</small></div>
                </a>
                <a class="admin-metric-card" href="<?= app_url_html('/admin/sales?status=pagada') ?>">
                    <div class="admin-metric-card__icon admin-metric-card__icon--purple"><i class="bi bi-currency-dollar"></i></div>
                    <div><span>Ventas pagadas</span><strong><?= $formatMoney($metrics['sales_total']) ?></strong><small>Acumulado registrado</small></div>
                </a>
            </section>

            <section class="admin-dashboard-grid">
                <article class="admin-panel admin-panel--wide">
                    <div class="admin-panel__heading">
                        <div><span class="admin-eyebrow">Catálogo</span><h2>Principales categorías</h2></div>
                        <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                    </div>
                    <div id="category-chart" class="admin-chart" aria-label="Productos por categoría"></div>
                    <a class="admin-panel__link" href="<?= app_url_html('/admin/products') ?>">Ver productos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </article>

                <article class="admin-panel">
                    <div class="admin-panel__heading">
                        <div><span class="admin-eyebrow">Operación</span><h2>Estado de pedidos</h2></div>
                        <i class="bi bi-pie-chart" aria-hidden="true"></i>
                    </div>
                    <div id="orders-chart" class="admin-chart" aria-label="Estado de pedidos"></div>
                    <a class="admin-panel__link" href="<?= app_url_html('/admin/orders') ?>">Gestionar pedidos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </article>

                <article class="admin-panel">
                    <div class="admin-panel__heading">
                        <div><span class="admin-eyebrow">Comunidad</span><h2>Usuarios activos</h2></div>
                        <i class="bi bi-people" aria-hidden="true"></i>
                    </div>
                    <div class="admin-highlight">
                        <strong><?= $formatNumber($metrics['users']) ?></strong>
                        <span>cuentas activas en Tecno Star</span>
                    </div>
                    <a class="admin-panel__link" href="<?= app_url_html('/admin/users') ?>"><span>Gestionar usuarios</span><i class="bi bi-arrow-right"></i></a>
                </article>
            </section>
        </main>
    </div>
    <script>
        const categoryData = <?= json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const orderData = <?= json_encode($ordersByStatus, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const orderLabels = <?= json_encode($orderLabels ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const chartColors = ['#1a56db', '#0d6efd', '#6c757d', '#7c3aed', '#16844b', '#c36b00'];

        if (window.ApexCharts) {
            new ApexCharts(document.querySelector('#category-chart'), {
                chart: { type: 'bar', height: 290, toolbar: { show: false }, fontFamily: 'Open Sans, sans-serif' },
                series: [{ name: 'Productos', data: categoryData.map(item => Number(item.total)) }],
                xaxis: { categories: categoryData.map(item => item.name), labels: { rotate: -35 } },
                colors: ['#1a56db'],
                plotOptions: { bar: { borderRadius: 5, distributed: true, columnWidth: '52%' } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#e9ecef' },
                legend: { show: false },
                noData: { text: 'Aún no hay productos categorizados' }
            }).render();

            new ApexCharts(document.querySelector('#orders-chart'), {
                chart: { type: 'donut', height: 290, fontFamily: 'Open Sans, sans-serif' },
                series: orderData.map(item => Number(item.total)),
                labels: orderData.map(item => orderLabels[item.name] || String(item.name).replaceAll('_',' ')),
                colors: chartColors,
                legend: { position: 'bottom', fontSize: '12px' },
                dataLabels: { enabled: true },
                noData: { text: 'No hay pedidos registrados' }
            }).render();
        }
    </script>
</body>
</html>
