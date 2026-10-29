<?php if(OrderModel::cashEnabled(Database::connection())):
$saleQuery=Database::connection()->prepare('SELECT id_venta,estado FROM tbventas WHERE id_pedido=? AND cobro_solicitud IS NOT NULL');$saleQuery->execute([$order['id_pedido']]);$receipt=$saleQuery->fetch(); ?>
<?php if($receipt): ?><a class="order-secondary" href="<?= app_url_html('/sales/detail?id='.(int)$receipt['id_venta']) ?>">Ver comprobante <?= $receipt['estado']==='anulada'?'anulado':'de pago' ?></a>
<?php elseif($staff && $order['estado']==='listo' && $order['reserva_activa']): ?><p class="order-payment-note">Pendiente de cobro · efectivo al retirar</p><a class="order-primary" href="<?= app_url_html('/admin/cash?id='.(int)$order['id_pedido']) ?>"><i class="bi bi-cash-coin" aria-hidden="true"></i> Ir a Caja</a>
<?php endif;endif; ?>
