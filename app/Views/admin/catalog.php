<?php $adminSection=$kind; $singular=$kind==='categories'?'categoría':'marca'; $pageDescription=$kind==='categories'?'Organiza los productos por tipo.':'Administra las marcas de tus productos.'; $pageAction=['label'=>'Nueva '.$singular]; require BASE_PATH . '/app/Views/admin/partials/catalog-header.php'; ?>
<section class="admin-table-panel" aria-label="Registros del catálogo">
    <form class="catalog-filters" method="get" action="<?= app_url_html('/admin/catalog') ?>"><input type="hidden" name="kind" value="<?= $kind ?>"><label>Buscar por nombre<input type="search" name="q" maxlength="100" placeholder="Buscar <?= $kind==='categories'?'categoría':'marca' ?>…" value="<?= $escape($q) ?>"></label><label>Estado<select name="status"><option value="">Todos</option><option value="activo" <?= $state==='activo'?'selected':'' ?>>Activo</option><option value="inactivo" <?= $state==='inactivo'?'selected':'' ?>>Inactivo</option></select></label><button class="admin-primary-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button><a href="<?= app_url_html('/admin/catalog?kind='.$kind) ?>">Limpiar</a></form>
    <p class="catalog-results"><?= $total ?> registros encontrados</p>
    <div class="table-responsive"><table id="admin-<?= $kind ?>-table" class="admin-table catalog-entity-table" data-admin-datatable data-table-kind="<?= $kind ?>" data-table-start="<?= ($page-1)*12 ?>" data-table-url="<?= app_url_html('/admin/tables/data') ?>"><thead><tr><th>Nombre</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
    <?= AdminTableRows::html($kind, $entities) ?>
    </tbody></table></div>
    <?php if (!$entities): ?><div class="admin-empty-state"><i class="bi bi-search" aria-hidden="true"></i><p>No se encontraron registros.</p></div><?php endif; ?>
    <?php $paginationPath='/admin/catalog'; $paginationFilters=['kind'=>$kind,'q'=>$q,'status'=>$state]; require BASE_PATH . '/app/Views/admin/partials/catalog-pagination.php'; ?>
</section>
<template data-entity-editor>
    <div data-entity-editor-content data-editor-title="<?= !empty($values['id'])?'Editar':'Nueva' ?> <?= $singular ?>">
        <?php if ($errors): ?><div class="catalog-errors" role="alert"><strong>Revisa los campos indicados.</strong><ul><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form class="catalog-entity-form" method="post" action="<?= app_url_html('/admin/catalog/save') ?>">
            <?= Csrf::field() ?><input type="hidden" name="kind" value="<?= $kind ?>"><input type="hidden" name="id" value="<?= $escape($values['id']??'') ?>">
            <label>Nombre *<input name="name" maxlength="100" required value="<?= $escape($values['name']??'') ?>" placeholder="Nombre de la <?= $singular ?>" <?= isset($errors['name'])?'aria-invalid="true"':'' ?>></label>
            <label>Estado<select name="estado"><option value="activo">Activo</option><option value="inactivo" <?= ($values['estado']??'')==='inactivo'?'selected':'' ?>>Inactivo</option></select></label>
            <p class="catalog-help"><i class="bi bi-info-circle" aria-hidden="true"></i> Un registro inactivo oculta sus productos de la tienda. Puedes volver a activarlo cuando lo necesites.</p>
            <div class="catalog-save"><button type="button" class="catalog-secondary-button" data-close-modal>Cancelar</button><button class="admin-primary-button" type="submit">Guardar <?= $singular ?></button></div>
        </form>
    </div>
</template>
<?php require BASE_PATH . '/app/Views/admin/partials/catalog-footer.php'; ?>
