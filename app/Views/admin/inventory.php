<?php

declare(strict_types=1);

$lowStock = (int)($inventorySummary['low_stock'] ?? count(array_filter($inventory, static fn (array $item): bool => (float) $item['stock_disponible'] <= (float) $item['stock_minimo'])));
$totalAvailable = (float)($inventorySummary['available'] ?? array_sum(array_map(static fn (array $item): float => (float) $item['stock_disponible'], $inventory)));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario | <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/admin.css?v=' . filemtime(BASE_PATH . '/public/assets/css/admin.css')) ?>">
    <?php require BASE_PATH . '/app/Views/admin/partials/shell-assets.php'; ?>
    <?php require BASE_PATH . '/app/Views/admin/partials/table-assets.php'; ?>
    <link rel="stylesheet" href="<?= app_url_html('/assets/css/admin-inventory.css?v='.filemtime(BASE_PATH.'/public/assets/css/admin-inventory.css')) ?>">
    <script defer src="<?= app_url_html('/assets/js/admin-inventory.js?v='.filemtime(BASE_PATH.'/public/assets/js/admin-inventory.js')) ?>"></script>
</head>
<body class="admin-page">
    <div class="admin-layout">
        <?php $adminSection = 'inventory'; ?>
        <?php require BASE_PATH . '/app/Views/admin/partials/sidebar.php'; ?>
        <main class="admin-main">
            <header class="admin-topbar">
                <div><?php require BASE_PATH.'/app/Views/admin/partials/back-link.php'; ?><h1>Inventario</h1><p class="admin-page-description">Controla existencias y registra entradas, salidas o ajustes.</p></div>
            </header>
            <section class="admin-metrics admin-metrics--inventory">
                <article class="admin-metric-card"><div class="admin-metric-card__icon admin-metric-card__icon--green"><i class="bi bi-stack"></i></div><div><span>Unidades disponibles</span><strong><?= AdminTableRows::quantity($totalAvailable) ?></strong><small>Stock actual menos reservado</small></div></article>
                <article class="admin-metric-card"><div class="admin-metric-card__icon admin-metric-card__icon--orange"><i class="bi bi-exclamation-triangle"></i></div><div><span>Stock bajo</span><strong><?= $lowStock ?></strong><small>Productos para revisar</small></div></article>
            </section>
            <section class="admin-table-panel" id="inventory-stock" aria-label="Existencias por producto">
                <div class="admin-table-toolbar">
                    <form class="catalog-filters inventory-filters"><label for="inventory-search">Buscar<input id="inventory-search" name="q" type="search" maxlength="150" placeholder="Producto o código"></label><label>Existencias<select name="low"><option value="">Todos los productos</option><option value="1">Solo stock bajo</option></select></label><button type="submit">Filtrar</button><button type="reset">Limpiar</button></form>
                    <button class="admin-primary-button" type="button" data-table-refresh><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Actualizar</button>
                </div>
                <div class="table-responsive">
                    <table id="admin-inventory-table" class="admin-table" data-admin-datatable data-table-kind="inventory" data-table-url="<?= app_url_html('/admin/tables/data') ?>">
                        <thead><tr><th>Producto</th><th>Código</th><th>Físico</th><th>Reservado</th><th>Disponible</th><th>Mínimo</th><th>Estado</th><th>Acciones</th></tr></thead>
                        <tbody>
                        <?= AdminTableRows::html('inventory', $inventory) ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($inventory === []): ?><div class="admin-empty-state"><i class="bi bi-clipboard2-data"></i><p>No hay registros de inventario.</p></div><?php endif; ?>
            </section>
            <details class="inventory-history" data-inventory-history-panel><summary><span><i class="bi bi-clock-history" aria-hidden="true"></i> Historial de movimientos</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <section class="admin-table-panel" aria-label="Historial de movimientos">
                <div class="inventory-history-toolbar"><a class="admin-back-link" href="#inventory-stock"><i class="bi bi-arrow-up" aria-hidden="true"></i> Volver a existencias</a></div>
                <form class="catalog-filters inventory-filters inventory-filters--history">
                    <label>Buscar<input name="q" type="search" maxlength="150" placeholder="Motivo o responsable"></label>
                    <label>Producto<select name="product"><option value="">Todos</option><?php foreach (($historyProducts ?? []) as $item): ?><option value="<?= (int)$item['id_producto'] ?>"><?= htmlspecialchars($item['nombre_producto'],ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label>Movimiento<select name="type"><option value="">Todos</option><option value="entrada">Entrada</option><option value="salida">Salida</option><option value="ajuste">Ajuste físico</option><option value="minimo">Mínimo</option><option value="inicial">Saldo inicial</option><option value="reserva">Reserva</option><option value="liberacion">Liberación</option><option value="entrega">Entrega</option><option value="devolucion">Devolución</option></select></label>
                    <label>Responsable<select name="actor"><option value="">Todos (incluye sistema)</option><?php foreach (($historyActors ?? []) as $item): ?><option value="<?= (int)$item['id_usuario'] ?>"><?= htmlspecialchars($item['responsable'],ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select></label>
                    <label>Desde<input name="from" type="date"></label><label>Hasta<input name="to" type="date"></label>
                    <button type="submit">Filtrar</button><button type="reset">Limpiar</button>
                </form>
                <div class="table-responsive"><table class="admin-table" id="admin-movements-table" data-admin-datatable data-table-kind="movements" data-table-url="<?= app_url_html('/admin/tables/data') ?>"><thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cambio</th><th>Físico antes</th><th>Físico después</th><th>Reservado</th><th>Responsable</th><th>Motivo</th></tr></thead><tbody></tbody></table></div>
            </section>
            </details>
        </main>
    </div>
    <dialog class="inventory-modal" data-inventory-modal aria-labelledby="inventory-modal-title">
        <header><div><small>Control de existencias</small><h2 id="inventory-modal-title">Registrar movimiento</h2></div><button type="button" data-inventory-close aria-label="Cerrar">×</button></header>
        <form method="post" action="<?= app_url_html('/admin/inventory/move') ?>" data-inventory-form>
            <?= Csrf::field() ?><input type="hidden" name="id_producto"><input type="hidden" name="solicitud"><input type="hidden" name="stock_esperado">
            <strong data-inventory-name></strong><p class="inventory-balance" data-inventory-balance></p>
            <p role="alert" class="inventory-error" data-inventory-error hidden></p>
            <label>Operación<select name="tipo" required><option value="entrada">Entrada de mercancía</option><option value="salida">Salida de mercancía</option><option value="ajuste">Ajustar a un conteo físico</option><option value="minimo">Configurar mínimo</option></select></label>
            <label><span data-quantity-label>Unidades que ingresan</span><input name="cantidad" type="number" min="0.001" max="999999999.999" step="0.001" required></label>
            <p class="inventory-help" data-inventory-help></p>
            <label>Motivo<textarea name="motivo" minlength="3" maxlength="500" rows="3" required placeholder="Ejemplo: recepción de mercancía, daño o revisión de conteo"></textarea></label>
            <footer><button type="button" data-inventory-close>Cancelar</button><button class="admin-primary-button" type="submit">Registrar movimiento</button></footer>
        </form>
    </dialog>
    <div class="inventory-toasts" data-inventory-toasts role="status" aria-live="polite"></div>
</body>
</html>
