<footer class="store-footer store-footer--organized">
    <div class="container">
        <div class="store-footer__grid">
            <div class="store-footer__identity">
                <a href="<?= app_url_html('/') ?>" aria-label="Inicio de Tecno Star"><img class="store-footer__logo" src="<?= app_url_html('/assets/images/logo-tecno-star.png') ?>" alt="Tecno Star" loading="lazy" width="128" height="48"></a>
                <p class="store-footer__description">Tecnología, accesorios y papelería para tu día a día.</p>
                <span class="store-footer__pickup"><i class="bi bi-shop" aria-hidden="true"></i> Retiro en tienda</span>
            </div>
            <nav aria-label="Explorar Tecno Star">
                <h2>Explora</h2>
                <a href="<?= app_url_html('/store') ?>">Catálogo de productos</a>
                <a href="<?= app_url_html('/#servicios') ?>">Papelería y servicios</a>
                <a href="<?= app_url_html('/#como-funciona') ?>">Cómo funciona</a>
            </nav>
            <nav aria-label="Ayuda y cuenta">
                <h2>Estamos cerca</h2>
                <a href="<?= app_url_html('/#nosotros') ?>">Contacto y ubicación</a>
                <a href="<?= app_url_html(isset($_SESSION['user']) ? '/profile' : '/login') ?>"><?= isset($_SESSION['user']) ? 'Mi perfil' : 'Iniciar sesión' ?></a>
                <p>Ilobasco, Cabañas,<br>El Salvador</p>
            </nav>
        </div>
        <div class="store-footer__bottom">
            <small>&copy; <?= date('Y') ?> Tecno Star. Todos los derechos reservados.</small>
            <div class="store-footer__credit">
                <small>Desarrollado por</small>
                <button class="rose-signature" type="button" data-rose-signature aria-label="Black Rose Systems. Animar rosas" title="Haz florecer las rosas">
                    <canvas aria-hidden="true"></canvas>
                    <span>Black Rose Systems</span>
                </button>
            </div>
        </div>
    </div>
</footer>
<script src="<?= app_url_html('/assets/js/rose-signature.js?v=20261016-2') ?>" defer></script>
