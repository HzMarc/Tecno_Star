<?php $has=static fn(string $section):bool=>in_array($section,$sections,true); ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 28px 36px 48px; }
        * { box-sizing: border-box; }
        body { color:#202d42; font-family:"DejaVu Sans",sans-serif; font-size:9px; line-height:1.45; }
        .masthead { width:100%; margin-bottom:20px; padding:14px 16px; border-radius:10px; background:#14213d; color:#fff; }
        .masthead td { vertical-align:middle; }
        .logo-wrap { width:58px; height:58px; border-radius:9px; background:#fff; text-align:center; vertical-align:middle; }
        .brand-logo { width:52px; height:52px; object-fit:contain; }
        .brand-name { color:#fff; font-size:17px; font-weight:bold; letter-spacing:1.4px; }
        .brand-subtitle { margin-top:4px; color:#c3cee1; font-size:7px; letter-spacing:1.2px; text-transform:uppercase; }
        .masthead-period { text-align:right; color:#c3cee1; font-size:8px; }
        .masthead-period strong { display:block; margin-top:5px; color:#fff; font-size:10px; }
        .eyebrow { margin:0 0 4px; color:#5472ad; font-size:7px; font-weight:bold; letter-spacing:1.4px; text-transform:uppercase; }
        h1 { margin:0; color:#15233a; font-size:21px; letter-spacing:-.5px; }
        .intro { margin:5px 0 16px; color:#77849a; font-size:8px; }
        .section { margin:0 0 18px; page-break-inside:auto; }
        .section.keep { page-break-inside:avoid; }
        .section-head { width:100%; margin:0 0 8px; padding-bottom:6px; border-bottom:1px solid #dfe6f0; }
        .section-head td { vertical-align:bottom; }
        .section-index { width:28px; color:#2457d6; font-size:9px; font-weight:bold; }
        h2 { margin:0; color:#20324f; font-size:12px; }
        .section-description { margin:3px 0 0; color:#7d899c; font-size:7px; }
        .metrics { width:100%; border-collapse:separate; border-spacing:6px 0; margin:0 -6px; }
        .metrics td { width:33.333%; padding:10px 11px; border:1px solid #e6ebf2; border-radius:8px; background:#f7f9fc; vertical-align:top; }
        .metric-label { display:block; color:#718099; font-size:7px; }
        .metric-value { display:block; margin-top:4px; color:#1e4eaa; font-size:15px; font-weight:bold; }
        .metric-detail { display:block; margin-top:2px; color:#8a95a6; font-size:7px; }
        table.data { width:100%; border-collapse:collapse; }
        .data thead { display:table-header-group; }
        .data th { padding:7px 8px; background:#edf2fa; color:#566782; font-size:7px; font-weight:bold; text-align:left; }
        .data td { padding:7px 8px; border-bottom:1px solid #edf0f5; color:#35455f; font-size:8px; vertical-align:middle; }
        .data tr { page-break-inside:avoid; }
        .data tbody tr:nth-child(even) td { background:#fbfcfe; }
        .numeric { text-align:right!important; white-space:nowrap; }
        .total-row td { background:#f2f6ff!important; color:#1c418d!important; font-weight:bold; }
        .monthly { width:100%; border-collapse:collapse; }
        .monthly td { padding:5px 7px; border-bottom:1px solid #eff2f7; font-size:8px; }
        .monthly .month { width:76px; color:#617089; }
        .monthly .amount { width:83px; color:#30425f; text-align:right; white-space:nowrap; }
        .bar-track { height:10px; background:#edf2fa; }
        .bar { height:10px; border-radius:2px; background:#3471e8; }
        .empty { padding:12px; border:1px solid #e6ebf3; border-radius:7px; background:#fafbfd; color:#7d899b; font-size:8px; text-align:center; }
        .status { font-size:7px; font-weight:bold; }
        .status--out { color:#b7334b; }
        .status--low { color:#946412; }
        .reinvestment { width:100%; border-collapse:separate; border-spacing:6px 0; margin:0 -6px; }
        .reinvestment td { width:33.333%; padding:10px 11px; border:1px solid #e4eaf3; border-radius:8px; background:#f7f9fd; }
        .reinvestment strong { display:block; margin-top:4px; color:#1c4eae; font-size:14px; }
        .note { margin:8px 0 0; color:#7b8799; font-size:7px; }
        .end-mark { margin-top:15px; padding-top:7px; border-top:1px solid #e5eaf1; color:#8691a2; font-size:7px; text-align:center; }
    </style>
</head>
<body>
    <table class="masthead"><tr>
        <td style="width:70px"><div class="logo-wrap"><img class="brand-logo" src="<?= $logoDataUri ?>" alt="Tecno Star"></div></td>
        <td><div class="brand-name">TECNO STAR</div><div class="brand-subtitle">Tecnología · impresión · servicio</div></td>
        <td class="masthead-period">PERÍODO DEL REPORTE<strong><?= $escape($report['from']) ?> al <?= $escape($report['to']) ?></strong><span>Generado <?= $escape($generatedAt) ?></span></td>
    </tr></table>
    <p class="eyebrow">Informe administrativo</p>
    <h1>Reporte del negocio</h1>
    <p class="intro">Información de las secciones seleccionadas, consolidada para el período indicado.</p>

    <?php if ($has('sales')): ?>
    <section class="section keep">
        <table class="section-head"><tr><td class="section-index"><?= $escape($sectionNumbers['sales']) ?></td><td><h2>Resumen de ventas</h2><p class="section-description">Cobros registrados en caja durante el período.</p></td></tr></table>
        <table class="metrics"><tr>
            <td><span class="metric-label">Ventas cobradas</span><span class="metric-value"><?= $money($report['summary']['paid']) ?></span><span class="metric-detail"><?= $number($report['summary']['paid_count']) ?> ventas</span></td>
            <td><span class="metric-label">Ticket promedio</span><span class="metric-value"><?= $money($report['summary']['average']) ?></span><span class="metric-detail">Por venta cobrada</span></td>
            <td><span class="metric-label">Ventas de productos</span><span class="metric-value"><?= $money($report['summary']['product_sales']) ?></span><span class="metric-detail">Venta de mercancía</span></td>
        </tr></table>
    </section>
    <?php endif; ?>

    <?php if ($has('services')): ?>
    <section class="section keep">
        <table class="section-head"><tr><td class="section-index"><?= $escape($sectionNumbers['services']) ?></td><td><h2>Servicios</h2><p class="section-description">Cobros de servicios registrados durante el período.</p></td></tr></table>
        <table class="metrics"><tr><td><span class="metric-label">Ingresos por servicios</span><span class="metric-value"><?= $money($report['summary']['service_sales']) ?></span><span class="metric-detail"><?= $number($report['summary']['service_count']) ?> servicios cobrados</span></td></tr></table>
    </section>
    <?php endif; ?>

    <?php if ($has('monthly')): ?>
    <section class="section keep">
        <table class="section-head"><tr><td class="section-index"><?= $escape($sectionNumbers['monthly']) ?></td><td><h2>Ingresos por mes</h2><p class="section-description">Comparativo de ventas cobradas en el rango seleccionado.</p></td></tr></table>
        <?php if ($report['months'] === []): ?><div class="empty">No hay meses para mostrar.</div><?php else: ?>
        <table class="monthly"><tbody>
            <?php foreach ($report['months'] as $month): $barWidth=$maxMonthlyIncome>0?max(0,min(100,((float)$month['paid']/$maxMonthlyIncome)*100)):0; ?>
            <tr><td class="month"><?= $escape($month['label']) ?></td><td><div class="bar-track"><div class="bar" style="width:<?= number_format($barWidth,2,'.','') ?>%"></div></div></td><td class="amount"><?= $money((float)$month['paid']) ?></td></tr>
            <?php endforeach; ?>
        </tbody></table><?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($has('products')): ?>
    <section class="section">
        <table class="section-head"><tr><td class="section-index"><?= $escape($sectionNumbers['products']) ?></td><td><h2>Productos más vendidos</h2><p class="section-description">Primeros diez productos por unidades vendidas.</p></td></tr></table>
        <?php if ($report['best_sellers'] === []): ?><div class="empty">No hay ventas de productos en este período.</div><?php else: ?>
        <table class="data"><thead><tr><th>Código</th><th>Producto</th><th class="numeric">Unidades</th><th class="numeric">Ingresos</th></tr></thead><tbody>
            <?php foreach ($report['best_sellers'] as $product): ?><tr><td><?= $escape($product['code']) ?></td><td><?= $escape($product['name']) ?></td><td class="numeric"><?= $number((float)$product['units']) ?></td><td class="numeric"><?= $money((float)$product['revenue']) ?></td></tr><?php endforeach; ?>
        </tbody></table><?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($has('inventory')): ?>
    <section class="section">
        <table class="section-head"><tr><td class="section-index"><?= $escape($sectionNumbers['inventory']) ?></td><td><h2>Inventario</h2><p class="section-description">Existencias disponibles al generar este documento.</p></td></tr></table>
        <table class="metrics"><tr>
            <td><span class="metric-label">Unidades disponibles</span><span class="metric-value"><?= $number($report['stock']['units']) ?></span><span class="metric-detail"><?= $number($report['stock']['products']) ?> productos</span></td>
            <td><span class="metric-label">Productos agotados</span><span class="metric-value"><?= $number($report['stock']['out_count']) ?></span><span class="metric-detail">Sin unidades disponibles</span></td>
            <td><span class="metric-label">Pocas unidades</span><span class="metric-value"><?= $number($report['stock']['low_count']) ?></span><span class="metric-detail">En o bajo el mínimo</span></td>
        </tr></table>
        <p class="section-description" style="margin:10px 0 7px">Valor de existencias a precio de compra: <strong><?= $money($report['stock']['cost_value']) ?></strong></p>
        <?php if ($stockRows === []): ?><div class="empty">No hay productos agotados ni con pocas unidades.</div><?php else: ?>
        <table class="data"><thead><tr><th>Código</th><th>Producto</th><th>Estado</th><th class="numeric">Disponible</th><th class="numeric">Mínimo</th></tr></thead><tbody>
            <?php foreach ($stockRows as $stock): ?><tr><td><?= $escape($stock['code']) ?></td><td><?= $escape($stock['name']) ?></td><td class="status <?= $stock['status']==='Agotado'?'status--out':'status--low' ?>"><?= $escape($stock['status']) ?></td><td class="numeric"><?= $number((float)$stock['available']) ?></td><td class="numeric"><?= $number((float)$stock['minimum']) ?></td></tr><?php endforeach; ?>
        </tbody></table><?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($has('reinvestment')): ?>
    <section class="section keep">
        <table class="section-head"><tr><td class="section-index"><?= $escape($sectionNumbers['reinvestment']) ?></td><td><h2>Reinversión</h2><p class="section-description">Distribución registrada para ventas cobradas.</p></td></tr></table>
        <table class="reinvestment"><tr>
            <td><span class="metric-label">Reposición</span><strong><?= $money($report['reinvestment']['replacement']) ?></strong></td>
            <td><span class="metric-label">Crecimiento</span><strong><?= $money($report['reinvestment']['growth']) ?></strong></td>
            <td><span class="metric-label">Total asignado</span><strong><?= $money($report['reinvestment']['total']) ?></strong></td>
        </tr></table>
        <?php if ($report['reinvestment']['missing'] > 0): ?><p class="note"><?= $number($report['reinvestment']['missing']) ?> ventas no tienen distribución de reinversión registrada.</p><?php endif; ?>
        <p class="note">Los importes muestran la distribución guardada por venta y no representan la ganancia neta.</p>
    </section>
    <?php endif; ?>

    <div class="end-mark">Tecno Star · Reporte administrativo · <?= $escape($generatedAt) ?></div>
</body>
</html>
