<?php $title = 'Productos'; $adminSection = 'products'; $pageDescription='Gestiona precios, imágenes y visibilidad en la tienda.'; $pageAction=['url'=>'/admin/products/edit','label'=>'Nuevo producto']; require BASE_PATH . '/app/Views/admin/partials/catalog-header.php'; ?>
<section class="admin-table-panel" aria-label="Productos registrados">
    <form class="catalog-filters" method="get" action="<?= app_url_html('/admin/products') ?>">
        <label>Buscar por nombre o código<input name="q" type="search" maxlength="150" placeholder="Buscar producto…" value="<?= $escape($filters['q']) ?>"></label>
        <label>Categoría<select name="category"><option value="">Todas las categorías</option><?php foreach ($categories as $option): ?><option value="<?= $option['id'] ?>" <?= (string)$option['id'] === $filters['category'] ? 'selected' : '' ?>><?= $escape($option['name']) ?><?= $option['estado'] !== 'activo' ? ' (inactiva)' : '' ?></option><?php endforeach; ?></select></label>
        <label>Marca<select name="brand"><option value="">Todas las marcas</option><?php foreach ($brands as $option): ?><option value="<?= $option['id'] ?>" <?= (string)$option['id'] === $filters['brand'] ? 'selected' : '' ?>><?= $escape($option['name']) ?><?= $option['estado'] !== 'activo' ? ' (inactiva)' : '' ?></option><?php endforeach; ?></select></label>
        <label>Estado<select name="status"><option value="">Todos</option><option value="activo" <?= $filters['status']==='activo'?'selected':'' ?>>Activo</option><option value="inactivo" <?= $filters['status']==='inactivo'?'selected':'' ?>>Inactivo</option></select></label>
        <button class="admin-primary-button" type="submit">Filtrar</button><a href="<?= app_url_html('/admin/products') ?>">Limpiar</a>
    </form>
    <p class="catalog-results"><?= $total ?> <?= $total === 1 ? 'producto encontrado' : 'productos encontrados' ?></p>
    <div class="table-responsive"><table id="admin-products-table" class="admin-table" data-admin-datatable data-table-kind="products" data-table-start="<?= ($page-1)*12 ?>" data-table-url="<?= app_url_html('/admin/tables/data') ?>"><thead><tr><th>Producto</th><th>Código</th><th>Categoría</th><th>Precio venta</th><th>Stock</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
    <?= AdminTableRows::html('products', $products) ?>
    </tbody></table></div>
    <?php if (!$products): ?><div class="admin-empty-state"><p>No hay productos que coincidan con estos filtros.</p><a data-product-modal href="<?= app_url_html('/admin/products/edit') ?>">Crear un producto</a></div><?php endif; ?>
    <?php $paginationPath='/admin/products'; $paginationFilters=$filters; require BASE_PATH . '/app/Views/admin/partials/catalog-pagination.php'; ?>
</section>
<?php require BASE_PATH . '/app/Views/admin/partials/catalog-footer.php'; ?>
