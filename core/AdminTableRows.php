<?php

declare(strict_types=1);

final class AdminTableRows
{
    private static function escape(mixed $value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
    public static function quantity(mixed $value): string { return rtrim(rtrim(number_format((float)$value,3,'.',','),'0'),'.'); }
    public static function cells(string $kind, array $row): array
    {
        $e = static fn (mixed $value): string => self::escape($value);
        if(in_array($kind,['sales','purchases'],true)){
            $date=(new DateTimeImmutable($row['fecha_pago'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('America/El_Salvador'))->format('d/m/Y H:i');
            if($kind==='purchases')return ['<strong>'.$e($row['codigo_venta']).'</strong>',$e($row['codigo_pedido']),$date,'$'.number_format((float)$row['total'],2),'<span class="order-state '.($row['estado']==='anulada'?'order-state--cancelado':'order-state--entregado').'">'.($row['estado']==='anulada'?'Reembolsada':'Pagada').'</span>','<a class="order-link" href="'.app_url_html('/sales/detail?id='.(int)$row['id_venta']).'">Ver compra</a>'];
            return ['<strong>'.$e($row['codigo_venta']).'</strong>',$e($row['codigo_pedido']),$date,'$'.number_format((float)$row['total'],2),'$'.number_format((float)$row['recibido'],2),'$'.number_format((float)$row['cambio'],2),'<span class="order-state '.($row['estado']==='anulada'?'order-state--cancelado':'order-state--entregado').'">'.($row['estado']==='anulada'?'Anulada':'Pagada').'</span>','<a class="order-link" href="'.app_url_html('/sales/detail?id='.(int)$row['id_venta']).'">Ver comprobante</a>'];
        }
        if($kind==='users')return ['<strong>'.$e($row['nombres'].' '.$row['apellidos']).'</strong>',$e($row['correo']),$e($row['nombre_rol']),'<span class="order-state '.($row['estado']==='activo'?'order-state--entregado':'order-state--cancelado').'">'.($row['estado']==='activo'?'Activo':'Inactivo').'</span>',$row['nombre_rol']==='Administrador'?'Acceso completo':($row['nombre_rol']==='Cliente'?'—':(OrderModel::isStaff($row)?'Pedidos y caja':'Sin acceso a caja')),'<button class="order-secondary" type="button" data-user-edit="'.(int)$row['id_usuario'].'">Editar</button>'];
        if($kind==='orders') {
            $labels=['pendiente'=>'Pendiente','confirmado'=>'Confirmado','en_preparacion'=>'En preparación','listo'=>'Listo para retirar','entregado'=>'Entregado','cancelado'=>'Cancelado'];
            $date=(new DateTimeImmutable($row['created_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('America/El_Salvador'))->format('d/m/Y H:i');
            if(!empty($row['reserva_activa']))$date.='<small class="admin-table__subtext">Vence '.(new DateTimeImmutable($row['fecha_vencimiento'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('America/El_Salvador'))->format('d/m H:i').'</small>';
            $payment=isset($row['payment_state'])?($row['payment_state']==='anulada'?'Reembolsado':'Pagado'):($row['estado']==='cancelado'?'Sin cobro':($row['estado']==='entregado'?'Sin registro de caja':'Pendiente de cobro'));
            return ['<strong>'.$e($row['codigo_pedido']).'</strong>',$e($row['contacto_nombre']),$date,'$'.number_format((float)$row['total'],2),'<span class="order-state order-state--'.$e($row['estado']).'">'.$e($labels[$row['estado']]??$row['estado']).'</span><small class="order-payment-state">'.$payment.'</small>','<a class="order-link" href="'.app_url_html('/orders/detail?id='.(int)$row['id_pedido']).'">Ver pedido <i class="bi bi-arrow-right" aria-hidden="true"></i></a>'];
        }
        if ($kind === 'products') {
            $id = (int)$row['id_producto']; $name=$e($row['nombre_producto']); $active=$row['estado']==='activo';
            $photo = !empty($row['imagen']) ? '<img src="' . $e(product_image_url($row['imagen'])) . '" alt="" loading="lazy">' : '<i class="bi bi-box-seam" aria-hidden="true"></i>';
            $status = '<span class="admin-status-pill ' . ($active?'admin-status-pill--success':'admin-status-pill--muted') . '">' . ($active?'Activo':'Inactivo') . '</span>';
            if ($row['category_state'] !== 'activo' || $row['brand_state'] !== 'activo') $status .= '<small class="admin-table__subtext">Oculto: categoría o marca inactiva</small>';
            $action = $active?'Desactivar':'Activar'; $state=$active?'inactivo':'activo';
            return [
                '<div class="admin-product-cell"><div class="admin-product-cell__image">' . $photo . '</div><div><strong>' . $name . '</strong><small>' . $e($row['marca']) . '</small></div></div>',
                $e($row['codigo_producto']), $e($row['categoria']), '<strong>$' . number_format((float)$row['precio_venta'],2) . '</strong>', (string)(int)$row['stock'], $status,
                '<div class="catalog-row-actions"><a data-product-modal href="' . app_url_html('/admin/products/edit?id='.$id) . '" aria-label="Editar '.$name.'"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</a><form data-catalog-state method="post" action="'.app_url_html('/admin/products/state').'">'.Csrf::field().'<input type="hidden" name="id" value="'.$id.'"><input type="hidden" name="estado" value="'.$state.'"><button type="submit" aria-label="'.$action.' '.$name.'">'.$action.'</button></form></div>',
            ];
        }
        if ($kind === 'inventory') return [
            '<strong>'.$e($row['nombre_producto']).'</strong><small class="admin-table__subtext">'.$e($row['marca']).'</small>', $e($row['codigo_producto']),
            self::quantity($row['stock_actual']),self::quantity($row['stock_reservado']),'<strong>'.self::quantity($row['stock_disponible']).'</strong>',self::quantity($row['stock_minimo']),
            '<span class="admin-status-pill '.match ($row['stock_status']) { 'Agotado'=>'admin-status-pill--danger', 'Bajo'=>'admin-status-pill--warning', default=>'admin-status-pill--success' }.'">'.$e($row['stock_status']).'</span>',
            '<div class="inventory-actions"><button type="button" data-inventory-edit data-id="'.(int)$row['id_producto'].'" data-name="'.$e($row['nombre_producto']).'" data-stock="'.$e($row['stock_actual']).'" data-reserved="'.$e($row['stock_reservado']).'" data-minimum="'.$e($row['stock_minimo']).'">Registrar</button><button type="button" data-inventory-history="'.(int)$row['id_producto'].'">Historial</button></div>',
        ];
        if ($kind === 'movements') {
            $types = ['inicial'=>'Saldo inicial','entrada'=>'Entrada','salida'=>'Salida','ajuste'=>'Ajuste físico','minimo'=>'Mínimo','reserva'=>'Reserva','liberacion'=>'Liberación','entrega'=>'Entrega','devolucion'=>'Devolución'];
            $delta = (float)$row['cantidad'];
            $reserved=(isset($row['reservado_antes'])?self::quantity($row['reservado_antes']).' → ':'').self::quantity($row['reservado']);
            return [$e($row['created_at']),'<strong>'.$e($row['nombre_producto']).'</strong><small class="admin-table__subtext">'.$e($row['codigo_producto']).'</small>', $types[$row['tipo']] ?? $e($row['tipo']),($delta>0?'+':'').self::quantity($delta),self::quantity($row['stock_antes']),self::quantity($row['stock_despues']),$reserved,$e($row['responsable']),$e($row['motivo']).($row['tipo']==='minimo'?'<small class="admin-table__subtext">Mínimo: '.self::quantity($row['minimo_antes']).' → '.self::quantity($row['minimo_despues']).'</small>':'')];
        }
        $name=$e($row['name']); $id=(int)$row['id']; $active=$row['estado']==='activo'; $singular=$kind==='categories'?'categoría':'marca';
        return [
            '<div class="catalog-entity-name"><span class="catalog-entity-icon"><i class="bi '.($kind==='categories'?'bi-tags':'bi-bookmark').'" aria-hidden="true"></i></span><strong>'.$name.'</strong></div>',
            '<span class="admin-status-pill '.($active?'admin-status-pill--success':'admin-status-pill--muted').'">'.($active?'Activo':'Inactivo').'</span>',
            '<div class="catalog-row-actions"><button type="button" data-entity-modal data-id="'.$id.'" data-name="'.$name.'" data-state="'.$e($row['estado']).'" data-editor-title="Editar '.$singular.'" aria-label="Editar '.$name.'"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</button></div>',
        ];
    }
    public static function html(string $kind, array $rows): string
    {
        return implode('', array_map(static fn ($row) => '<tr><td>' . implode('</td><td>', self::cells($kind,$row)) . '</td></tr>', $rows));
    }
}
