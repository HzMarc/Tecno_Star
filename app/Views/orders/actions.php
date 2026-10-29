<?php
$next=$staff?array_values(array_diff(OrderModel::NEXT[$order['estado']],['entregado'])):(in_array($order['estado'],['pendiente','confirmado'],true)?['cancelado']:[]);
$cashActions=($kind??'')==='cash';if($cashActions)$next=array_values(array_diff($next,['cancelado']));
$actionLabels=['confirmado'=>'Aceptar pedido','en_preparacion'=>'Iniciar preparación','listo'=>'Marcar listo para retirar','cancelado'=>'Cancelar pedido'];
if($next): ?>
<div class="order-next-actions"><?php foreach($next as $state): ?><button class="<?= $state==='cancelado'?'order-secondary order-danger':'order-primary' ?>" type="button" data-order-open="<?= $state ?>" data-action-label="<?= $actionLabels[$state] ?>"><?= $actionLabels[$state] ?></button><?php endforeach; ?></div>
<dialog class="order-modal" data-order-modal aria-labelledby="order-modal-title"><header><h2 id="order-modal-title">Actualizar pedido</h2><button type="button" data-order-close aria-label="Cerrar">×</button></header><form method="post" action="<?= app_url_html('/orders/state') ?>" data-order-form><?= Csrf::field() ?><input type="hidden" name="target_user_id" value="<?= (int)$user['id_usuario'] ?>"><input type="hidden" name="id" value="<?= (int)$order['id_pedido'] ?>"><input type="hidden" name="esperado" value="<?= $e($order['estado']) ?>"><input type="hidden" name="estado" value="">
<?php if($cashActions): ?><input type="hidden" name="return_to" value="cash"><?php endif; ?>
<p data-order-action-description>Confirma el siguiente paso del pedido.</p><label>Motivo o comentario<textarea name="motivo" minlength="3" maxlength="500" required rows="3"></textarea></label><p class="order-error" data-order-error role="alert" hidden></p><footer><button class="order-secondary" type="button" data-order-close>Volver</button><button class="order-primary" type="submit">Confirmar</button></footer></form></dialog>
<?php endif; ?>
