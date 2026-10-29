<?php if ($pages > 1): ?>
<nav class="catalog-pagination" aria-label="Páginas del catálogo">
    <?php if ($page > 1): ?><a href="<?= $escape(app_url($paginationPath . '?' . http_build_query(array_merge($paginationFilters, ['page'=>$page-1])))) ?>">← Anterior</a><?php endif; ?>
    <span>Página <?= $page ?> de <?= $pages ?> · <?= $total ?> resultados</span>
    <?php if ($page < $pages): ?><a href="<?= $escape(app_url($paginationPath . '?' . http_build_query(array_merge($paginationFilters, ['page'=>$page+1])))) ?>">Siguiente →</a><?php endif; ?>
</nav>
<?php endif; ?>
