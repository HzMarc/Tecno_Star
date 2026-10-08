<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tecno Star: accesorios tecnológicos, papelería, copias e impresiones.">
    <title><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> | Tecnología y papelería</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Tecno_Star/public/assets/css/app.css">
</head>
<body>
    <header class="store-header">
        <nav class="navbar navbar-expand-lg bg-white">
            <div class="container">
                <a class="navbar-brand store-brand" href="/Tecno_Star/public/" aria-label="Ir al inicio de Tecno Star">
                    <img src="/Tecno_Star/public/assets/images/logo-tecno-star.png" alt="Tecno Star">
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#store-navigation" aria-controls="store-navigation" aria-expanded="false" aria-label="Abrir menú">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="store-navigation">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" href="#inicio">Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="#categorias">Categorías</a></li>
                        <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                        <li class="nav-item"><a class="nav-link" href="#como-funciona">Cómo funciona</a></li>
                        <li class="nav-item"><a class="nav-link" href="#nosotros">Nosotros</a></li>
                    </ul>
                    <div class="d-flex gap-2">
                        <a class="btn btn-outline-primary btn-sm" href="/Tecno_Star/public/login">Iniciar sesión</a>
                        <a class="btn btn-primary btn-sm" href="/Tecno_Star/public/register">Crear cuenta</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section id="inicio" class="store-hero">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-7">
                        <span class="eyebrow"><i class="bi bi-stars"></i> Tecnología para todos los días</span>
                        <h1 class="store-hero__title">Encuentra lo que necesitas para conectar, crear y avanzar.</h1>
                        <p class="store-hero__text">
                            Accesorios para celulares, productos tecnológicos y servicios de papelería en un solo lugar.
                            Consulta nuestro catálogo y realiza tu pedido para retirarlo en tienda.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <a class="btn btn-primary btn-lg" href="/Tecno_Star/public/store">
                                Explorar catálogo <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                            <a class="btn btn-light btn-lg border" href="#servicios">Ver servicios</a>
                        </div>
                        <div class="hero-highlights">
                            <span><i class="bi bi-check-circle-fill"></i> Catálogo actualizado</span>
                            <span><i class="bi bi-check-circle-fill"></i> Atención cercana</span>
                            <span><i class="bi bi-check-circle-fill"></i> Retiro en tienda</span>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero-visual">
                            <div class="hero-visual__glow"></div>
                            <img src="/Tecno_Star/public/assets/images/logo-estrella.png" alt="" aria-hidden="true">
                            <span class="hero-visual__caption">Tu tecnología,<br><strong>más cerca.</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="categorias" class="store-section">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Lo que buscas está aquí</span>
                    <h2>Soluciones para tu día a día</h2>
                    <p>Consulta productos organizados por categorías y encuentra el accesorio ideal para tu dispositivo.</p>
                </div>
                <div class="row g-4">
                    <div class="col-sm-6 col-lg-3">
                        <article class="category-card">
                            <div class="category-card__icon"><i class="bi bi-phone"></i></div>
                            <h3>Accesorios móviles</h3>
                            <p>Fundas, protectores, cargadores y cables compatibles con tu celular.</p>
                            <a href="#inicio">Ver categoría <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <article class="category-card">
                            <div class="category-card__icon"><i class="bi bi-headphones"></i></div>
                            <h3>Audio y conectividad</h3>
                            <p>Audífonos, adaptadores y soluciones para mantenerte conectado.</p>
                            <a href="#inicio">Ver categoría <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <article class="category-card">
                            <div class="category-card__icon"><i class="bi bi-keyboard"></i></div>
                            <h3>Accesorios tecnológicos</h3>
                            <p>Mouse, teclados, bocinas y productos para estudiar o trabajar.</p>
                            <a href="#inicio">Ver categoría <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <article class="category-card">
                            <div class="category-card__icon"><i class="bi bi-printer"></i></div>
                            <h3>Papelería</h3>
                            <p>Materiales y servicios para tus tareas, proyectos y documentos.</p>
                            <a href="#servicios">Ver servicios <i class="bi bi-arrow-right"></i></a>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section id="servicios" class="store-section store-section--soft">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-5">
                        <span class="eyebrow">Más que una tienda</span>
                        <h2>Todo lo que necesitas, en un mismo lugar.</h2>
                        <p class="section-lead">Tecno Star combina tecnología, atención personalizada y servicios de papelería para resolver tus necesidades de forma práctica.</p>
                    </div>
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="service-item"><i class="bi bi-file-earmark-text"></i><div><h3>Copias e impresiones</h3><p>Imprime tus documentos con facilidad.</p></div></div>
                            </div>
                            <div class="col-md-6">
                                <div class="service-item"><i class="bi bi-camera"></i><div><h3>Fotografías y stickers</h3><p>Materializa tus ideas y recuerdos.</p></div></div>
                            </div>
                            <div class="col-md-6">
                                <div class="service-item"><i class="bi bi-box-seam"></i><div><h3>Productos disponibles</h3><p>Consulta existencias antes de visitar la tienda.</p></div></div>
                            </div>
                            <div class="col-md-6">
                                <div class="service-item"><i class="bi bi-person-check"></i><div><h3>Atención cercana</h3><p>Recibe apoyo para elegir lo que necesitas.</p></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="como-funciona" class="store-section">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Compra de forma sencilla</span>
                    <h2>Así funciona Tecno Star</h2>
                </div>
                <div class="row g-4 process-row">
                    <div class="col-md-4"><div class="process-step"><span>01</span><h3>Consulta</h3><p>Busca productos por categoría, marca o modelo compatible.</p></div></div>
                    <div class="col-md-4"><div class="process-step"><span>02</span><h3>Realiza tu pedido</h3><p>Selecciona lo que necesitas y revisa la información de tu pedido.</p></div></div>
                    <div class="col-md-4"><div class="process-step"><span>03</span><h3>Retira en tienda</h3><p>Espera la confirmación, paga presencialmente y recoge tus productos.</p></div></div>
                </div>
            </div>
        </section>

        <section id="nosotros" class="store-section store-section--soft">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-5">
                        <span class="eyebrow">Conócenos</span>
                        <h2>Acerca de nosotros</h2>
                        <p class="section-lead">
                            Tecno Star es tu tienda de tecnología de confianza. Ofrecemos accesorios
                            tecnológicos y servicios de papelería para ayudarte en tus actividades
                            diarias.
                        </p>
                        <div class="about-contact-list">
                            <a href="mailto:soporte@tecnostar.com">
                                <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                                <span>soporte@tecnostar.com</span>
                            </a>
                            <a href="tel:+50322223333">
                                <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                                <span>+503 2222-3333</span>
                            </a>
                            <span>
                                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                                <span>Ilobasco, Cabañas, El Salvador</span>
                            </span>
                        </div>
                        <a
                            class="btn btn-primary"
                            href="https://maps.app.goo.gl/crjYXr6XTuPhvwLe7"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Abrir en Google Maps <i class="bi bi-box-arrow-up-right ms-1"></i>
                        </a>
                    </div>
                    <div class="col-lg-7">
                        <div class="location-map">
                            <iframe
                                src="https://www.google.com/maps?q=13.8419191,-88.8523793&z=17&output=embed"
                                title="Ubicación de Tecno Star en Ilobasco, Cabañas"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                            ></iframe>
                            <p class="location-map__fallback">
                                <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                                <a href="https://maps.app.goo.gl/crjYXr6XTuPhvwLe7" target="_blank" rel="noopener noreferrer">
                                    Ver ubicación en Google Maps
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="store-cta">
            <div class="container">
                <div class="store-cta__content">
                    <div>
                        <span class="eyebrow eyebrow--light">Empieza hoy</span>
                        <h2>Conoce Tecno Star y encuentra tu próxima solución.</h2>
                    </div>
                    <a class="btn btn-light btn-lg" href="/Tecno_Star/public/register">Crear mi cuenta <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </section>
    </main>

    <footer class="store-footer">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-md-4">
                    <img class="store-footer__logo" src="/Tecno_Star/public/assets/images/logo-tecno-star.png" alt="Tecno Star">
                    <p class="store-footer__description">Accesorios tecnológicos y servicios de papelería.</p>
                </div>
                <div class="col-md-4">
                    <p class="store-footer__description">Tecnología, accesorios y papelería para todos los días.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="store-footer__copyright">
                        &copy; <?= date('Y') ?> Tecno Star.<br>
                        Todos los derechos reservados.
                    </p>
                    <p class="store-footer__developer">
                        Desarrollado por <strong>Black Rose Systems</strong>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="/Tecno_Star/public/assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
