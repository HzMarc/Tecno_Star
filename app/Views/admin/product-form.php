<?php $title = $id === null ? 'Nuevo producto' : 'Editar producto'; $adminSection='products'; $adminBackPath='/admin/products'; $adminBackLabel='Volver a productos'; require BASE_PATH . '/app/Views/admin/partials/catalog-header.php'; ?>
<div data-product-editor data-editor-title="<?= $escape($title) ?>">
<?php if ($errors): ?><div class="catalog-errors" role="alert"><strong>Revisa los campos indicados.</strong><ul><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form class="catalog-editor" method="post" enctype="multipart/form-data" action="<?= app_url_html('/admin/products/save') ?>">
    <?= Csrf::field() ?><input type="hidden" name="id" value="<?= $id ?? '' ?>">
    <div class="catalog-editor__fields">
        <section class="catalog-card"><h2>Información del producto</h2><p>Los campos con * son obligatorios.</p><div class="catalog-form-grid">
            <?php foreach (['codigo_producto'=>['Código *',50], 'nombre_producto'=>['Nombre *',150]] as $field=>[$label,$length]): ?>
                <label><?= $label ?><input name="<?= $field ?>" value="<?= $escape($values[$field] ?? '') ?>" maxlength="<?= $length ?>" required <?= isset($errors[$field])?'aria-invalid="true"':'' ?>></label>
            <?php endforeach; ?>
            <label>Categoría *<select name="id_categoria" required <?= isset($errors['id_categoria'])?'aria-invalid="true"':'' ?>><option value="">Selecciona una categoría</option><?php foreach ($categories as $option): ?><option value="<?= $option['id'] ?>" <?= (string)$option['id'] === (string)($values['id_categoria']??'')?'selected':'' ?>><?= $escape($option['name']) ?><?= $option['estado']!=='activo'?' (inactiva)':'' ?></option><?php endforeach; ?></select></label>
            <label>Marca<select name="id_marca"><option value="">Sin marca</option><?php foreach ($brands as $option): ?><option value="<?= $option['id'] ?>" <?= (string)$option['id'] === (string)($values['id_marca']??'')?'selected':'' ?>><?= $escape($option['name']) ?><?= $option['estado']!=='activo'?' (inactiva)':'' ?></option><?php endforeach; ?></select></label>
            <label class="catalog-form-wide">Descripción<textarea name="descripcion" rows="5" maxlength="10000"><?= $escape($values['descripcion']??'') ?></textarea><small>Incluye compatibilidad, medidas y características que conozcas del producto.</small></label>
        </div></section>
        <section class="catalog-card"><h2>Precios y visibilidad</h2><div class="catalog-form-grid">
            <?php foreach (['precio_compra'=>'Precio de compra ($) *', 'precio_venta'=>'Precio de venta ($) *'] as $field=>$label): ?><label><?= $label ?><input type="number" name="<?= $field ?>" value="<?= $escape($values[$field]??'') ?>" min="0" max="9999999999.99" step="0.01" required <?= isset($errors[$field])?'aria-invalid="true"':'' ?>></label><?php endforeach; ?>
            <label>Estado *<select name="estado"><option value="activo" <?= ($values['estado']??'')==='activo'?'selected':'' ?>>Activo</option><option value="inactivo" <?= ($values['estado']??'')==='inactivo'?'selected':'' ?>>Inactivo</option></select></label>
        </div><p class="catalog-help">Para aparecer en la tienda necesita stock disponible y categoría y marca activas. Gestiona las existencias desde Inventario.</p></section>
    </div>
    <aside class="catalog-card catalog-image-editor"><h2>Imagen</h2>
        <?php if (!empty($values['imagen'])): ?><img src="<?= $escape(product_image_url($values['imagen'])) ?>" alt="Imagen actual del producto"><label class="catalog-checkbox"><input type="checkbox" name="remove_image" value="1" <?= ($values['remove_image']??'')==='1'?'checked':'' ?>> Quitar imagen actual</label><?php else: ?><div class="catalog-image-placeholder"><i class="bi bi-image" aria-hidden="true"></i><span>Sin imagen</span></div><?php endif; ?>
        <label>URL de la imagen<input type="url" name="imagen_url" value="<?= $escape($values['imagen_url']??'') ?>" maxlength="2048" placeholder="https://ejemplo.com/imagen.jpg" autocomplete="url" <?= isset($errors['imagen_url'])?'aria-invalid="true"':'' ?>></label>
        <p class="catalog-help">También puedes pegar una URL http o https. Si indicas una URL, no selecciones un archivo.</p>
        <label><?= empty($values['imagen'])?'Subir imagen':'Reemplazar imagen' ?><input type="file" name="image" accept="image/jpeg,image/png,image/webp" <?= isset($errors['image'])?'aria-invalid="true"':'' ?>></label><p class="catalog-help">JPG, PNG o WebP. Máximo 4 MiB, 6000 px por lado y 20 megapíxeles. Si hay un error, vuelve a seleccionar el archivo.</p>
    </aside>
    <div class="catalog-save"><a href="<?= app_url_html('/admin/products') ?>">Cancelar</a><button class="admin-primary-button" type="submit"><?= $id === null ? 'Crear producto' : 'Guardar cambios' ?></button></div>
</form>
</div>
<?php require BASE_PATH . '/app/Views/admin/partials/catalog-footer.php'; ?>
