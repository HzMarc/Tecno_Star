<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar reportes | Tecno Star</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/admin.css?v=' . filemtime(BASE_PATH . '/public/assets/css/admin.css')) ?>">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/admin-reports.css?v=' . filemtime(BASE_PATH . '/public/assets/css/admin-reports.css')) ?>">
    <script defer src="<?= app_url_html('/assets/js/admin-reports.js?v=' . filemtime(BASE_PATH . '/public/assets/js/admin-reports.js')) ?>"></script>
    <?php require BASE_PATH . '/app/Views/admin/partials/shell-assets.php'; ?>
</head>
<body class="admin-page">
<div class="admin-layout">
    <?php $adminSection = 'reports'; require BASE_PATH . '/app/Views/admin/partials/sidebar.php'; ?>
    <main class="admin-main reports-main">
        <header class="reports-heading">
            <div><span class="admin-eyebrow">Tecno Star · Administración</span><h1>Generar reportes</h1><p>Elige qué información necesitas y descárgala en PDF o Excel.</p></div>
        </header>

        <form class="reports-form" method="get" action="<?= app_url_html('/admin/reports/generate') ?>" data-report-form>
            <section class="reports-panel reports-panel--selection" aria-labelledby="report-form-title">
                <div class="reports-panel__heading">
                    <div><span>Reporte personalizado</span><h2 id="report-form-title">Período y contenido</h2></div>
                    <i class="bi bi-sliders2" aria-hidden="true"></i>
                </div>
                <div class="reports-date-fields">
                    <label>Desde<input type="date" name="from" value="<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?>" required></label>
                    <label>Hasta<input type="date" name="to" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>" required></label>
                </div>
                <div class="reports-selection-heading">
                    <div><h3>Secciones del sistema</h3><p>Selecciona una o varias secciones para el archivo.</p></div>
                    <label class="reports-select-all"><input type="checkbox" data-report-select-all checked><span>Reporte general</span></label>
                </div>
                <div class="reports-section-grid" data-report-sections>
                    <label class="reports-section-card"><input type="checkbox" name="sections[]" value="sales" checked><span class="reports-section-card__icon is-blue"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><span class="reports-section-card__copy"><strong>Ventas</strong><small>Ingresos cobrados, cantidad de ventas y ticket promedio.</small></span><i class="bi bi-check-circle-fill reports-section-card__check" aria-hidden="true"></i></label>
                    <?php if ($servicesEnabled): ?><label class="reports-section-card"><input type="checkbox" name="sections[]" value="services" checked><span class="reports-section-card__icon is-violet"><i class="bi bi-printer" aria-hidden="true"></i></span><span class="reports-section-card__copy"><strong>Servicios</strong><small>Ingresos y cobros del área de servicios.</small></span><i class="bi bi-check-circle-fill reports-section-card__check" aria-hidden="true"></i></label><?php endif; ?>
                    <label class="reports-section-card"><input type="checkbox" name="sections[]" value="monthly" checked><span class="reports-section-card__icon is-green"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span><span class="reports-section-card__copy"><strong>Ingresos por mes</strong><small>Comparativo mensual de ventas cobradas.</small></span><i class="bi bi-check-circle-fill reports-section-card__check" aria-hidden="true"></i></label>
                    <label class="reports-section-card"><input type="checkbox" name="sections[]" value="products" checked><span class="reports-section-card__icon is-orange"><i class="bi bi-trophy" aria-hidden="true"></i></span><span class="reports-section-card__copy"><strong>Productos más vendidos</strong><small>Unidades e ingresos por producto.</small></span><i class="bi bi-check-circle-fill reports-section-card__check" aria-hidden="true"></i></label>
                    <label class="reports-section-card"><input type="checkbox" name="sections[]" value="inventory" checked><span class="reports-section-card__icon is-red"><i class="bi bi-box-seam" aria-hidden="true"></i></span><span class="reports-section-card__copy"><strong>Inventario</strong><small>Existencias, agotados, alertas y valor a costo.</small></span><i class="bi bi-check-circle-fill reports-section-card__check" aria-hidden="true"></i></label>
                    <?php if ($reinvestmentEnabled): ?><label class="reports-section-card"><input type="checkbox" name="sections[]" value="reinvestment" checked><span class="reports-section-card__icon is-indigo"><i class="bi bi-arrow-repeat" aria-hidden="true"></i></span><span class="reports-section-card__copy"><strong>Reinversión</strong><small>Reposición y crecimiento asignados a ventas.</small></span><i class="bi bi-check-circle-fill reports-section-card__check" aria-hidden="true"></i></label><?php endif; ?>
                </div>
                <p class="reports-validation" data-report-error role="alert" hidden>Selecciona al menos una sección para continuar.</p>
                <div class="reports-actions">
                    <span class="reports-actions__hint" data-report-count></span>
                    <div>
                        <button class="reports-button reports-button--pdf" type="submit" name="format" value="pdf"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> Generar PDF</button>
                        <button class="reports-button reports-button--excel" type="submit" name="format" value="xlsx"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Generar Excel</button>
                    </div>
                </div>
            </section>
        </form>

        <aside class="reports-tip"><i class="bi bi-lightbulb" aria-hidden="true"></i><p><strong>Reporte general</strong> incluye todas las secciones disponibles. Si desmarcas esa opción, puedes armar un archivo más específico. Las existencias reflejan el inventario al momento de generar el archivo.</p></aside>
    </main>
</div>
</body>
</html>
