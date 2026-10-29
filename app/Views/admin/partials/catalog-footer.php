</main></div>
<dialog class="catalog-modal" aria-labelledby="catalog-modal-title" data-catalog-modal>
    <header class="catalog-modal__header"><div><span class="admin-eyebrow">Catálogo</span><h2 id="catalog-modal-title">Editar registro</h2></div><button type="button" class="catalog-modal__close" data-close-modal aria-label="Cerrar ventana">×</button></header>
    <div class="catalog-modal__body" data-modal-body></div>
</dialog>
<div class="catalog-toasts" aria-label="Notificaciones" data-catalog-toasts>
    <?php if ($notice): ?><div class="catalog-toast" role="status" data-catalog-notice><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span><?= $escape($notice) ?></span><button type="button" data-dismiss-toast aria-label="Cerrar notificación">×</button></div><?php endif; ?>
</div>
<script src="<?= app_url_html('/assets/js/admin-catalog.js?v=' . filemtime(BASE_PATH . '/public/assets/js/admin-catalog.js')) ?>" defer></script>
</body></html>
