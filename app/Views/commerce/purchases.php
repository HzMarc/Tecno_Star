<?php
require BASE_PATH.'/app/Views/commerce/head.php';
$filters=$cards['filters'];
$date=static fn($value)=>(new DateTimeImmutable($value,new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('America/El_Salvador'))->format('d/m/Y · H:i');
$pageUrl=static fn(int $page)=>app_url_html('/purchases?'.http_build_query(array_filter($filters,static fn($value)=>$value!=='')+['page'=>$page]));
?>
<div class="commerce-summary commerce-summary--purchases">
    <article><span>Compras pagadas</span><strong>$<?= $e(number_format((float)$cards['summary']['paid'],2)) ?></strong></article>
    <article><span>Importe reembolsado</span><strong>$<?= $e(number_format((float)$cards['summary']['voided'],2)) ?></strong></article>
</div>
<p class="commerce-help">Los importes corresponden a los filtros seleccionados.</p>
<section class="orders-panel purchase-filter-panel" aria-label="Filtrar mis compras">
    <form class="orders-filters purchase-filters" method="get" action="<?= app_url_html('/purchases') ?>">
        <label>Buscar compra<input name="q" type="search" maxlength="150" placeholder="Comprobante o pedido" value="<?= $e($filters['q']) ?>"></label>
        <label>Estado<select name="status"><option value="">Todas</option><option value="pagada" <?= $filters['status']==='pagada'?'selected':'' ?>>Pagada</option><option value="anulada" <?= $filters['status']==='anulada'?'selected':'' ?>>Reembolsada</option></select></label>
        <label>Desde<input type="date" name="from" value="<?= $e($filters['from']) ?>"></label>
        <label>Hasta<input type="date" name="to" value="<?= $e($filters['to']) ?>"></label>
        <button class="order-primary" type="submit">Filtrar</button><a class="order-secondary" href="<?= app_url_html('/purchases') ?>">Limpiar</a>
    </form>
</section>
<?php if(!$cards['items']): ?>
<section class="orders-panel purchase-empty"><i class="bi bi-bag-check" aria-hidden="true"></i><h2><?= $cards['all']===0?'Todavía no tienes compras':'No encontramos compras con estos filtros' ?></h2><p><?= $cards['all']===0?'Aquí aparecerán tus compras cuando completes el pago en la tienda.':'Prueba con otro comprobante, estado o rango de fechas.' ?></p><a class="order-primary" href="<?= app_url_html($cards['all']===0?'/store':'/purchases') ?>"><?= $cards['all']===0?'Ver catálogo':'Ver todas mis compras' ?></a></section>
<?php else: ?>
<div class="purchase-grid" aria-label="Mis compras">
<?php foreach($cards['items'] as $card): $refunded=$card['estado']==='anulada'; ?>
    <article class="orders-panel purchase-card">
        <header><span class="purchase-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span><span class="purchase-state <?= $refunded?'purchase-state--refunded':'' ?>"><?= $refunded?'Reembolsada':'Pagada' ?></span></header>
        <div><h2><?= $e($card['codigo_venta']) ?></h2><p class="purchase-date"><i class="bi bi-calendar3" aria-hidden="true"></i> <?= $date($card['fecha_pago']) ?></p></div>
        <dl class="purchase-details"><dt>Pedido</dt><dd><?= $e($card['codigo_pedido']) ?></dd><dt>Método de pago</dt><dd>Efectivo</dd></dl>
        <footer><div><span><?= $refunded?'Total reembolsado':'Total de la compra' ?></span><strong>$<?= $e(number_format((float)$card['total'],2)) ?></strong></div><a class="order-primary" aria-label="Ver comprobante <?= $e($card['codigo_venta']) ?>" href="<?= app_url_html('/sales/detail?id='.(int)$card['id_venta']) ?>">Ver comprobante <i class="bi bi-arrow-right" aria-hidden="true"></i></a></footer>
    </article>
<?php endforeach; ?>
</div>
<nav class="purchase-pagination" aria-label="Páginas de mis compras">
    <?php if($cards['page']>1): ?><a class="order-secondary" rel="prev" href="<?= $pageUrl($cards['page']-1) ?>">Anterior</a><?php endif; ?>
    <span>Página <?= $cards['page'] ?> de <?= $cards['pages'] ?> · <?= $cards['total'] ?> <?= $cards['total']===1?'compra':'compras' ?></span>
    <?php if($cards['page']<$cards['pages']): ?><a class="order-secondary" rel="next" href="<?= $pageUrl($cards['page']+1) ?>">Siguiente</a><?php endif; ?>
</nav>
<?php endif;require BASE_PATH.'/app/Views/commerce/foot.php'; ?>
