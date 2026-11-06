</main></div>
<dialog class="catalog-modal" aria-labelledby="catalog-modal-title" data-catalog-modal>
    <header class="catalog-modal__header"><div><span class="admin-eyebrow">Catálogo</span><h2 id="catalog-modal-title">Editar registro</h2></div><button type="button" class="catalog-modal__close" data-close-modal aria-label="Cerrar ventana">×</button></header>
    <div class="catalog-modal__body" data-modal-body></div>
</dialog>
<dialog class="catalog-quick-create" aria-labelledby="quick-entity-title" data-quick-entity-dialog>
    <header><h2 id="quick-entity-title">Nueva opción</h2><button type="button" data-quick-entity-close aria-label="Cerrar y volver al producto">×</button></header>
    <p>Se guardará la nueva opción sin cerrar ni borrar los datos del producto.</p>
    <form action="<?= app_url_html('/admin/catalog/save') ?>" method="post" data-quick-entity-form>
        <?= Csrf::field() ?><input type="hidden" name="kind"><input type="hidden" name="estado" value="activo">
        <label for="quick-entity-name">Nombre *<input id="quick-entity-name" name="name" maxlength="100" required autocomplete="off"></label>
        <p class="catalog-quick-create__error" data-quick-entity-error role="alert" hidden></p>
        <footer><button type="button" data-quick-entity-close>Cancelar</button><button type="submit">Crear y seleccionar</button></footer>
    </form>
</dialog>
<div class="catalog-toasts" aria-label="Notificaciones" data-catalog-toasts>
    <?php if ($notice): ?><div class="catalog-toast" role="status" data-catalog-notice><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span><?= $escape($notice) ?></span><button type="button" data-dismiss-toast aria-label="Cerrar notificación">×</button></div><?php endif; ?>
</div>
<script src="<?= app_url_html('/assets/js/admin-catalog.js?v=' . filemtime(BASE_PATH . '/public/assets/js/admin-catalog.js')) ?>" defer></script>
</body></html>
