<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elyra — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/web20.css" rel="stylesheet">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
    <style>
        body { background: #fff; }
        .public-nav { background:#3B5998; border-bottom:2px solid #2A4780; }
        .public-nav-inner { display:flex; align-items:center; justify-content:space-between; padding:20px 0; }
        .public-nav-brand { display:flex; align-items:center; gap:12px; font-weight:bold; color:#fff; text-decoration:none; font-size:22px; }
        .public-nav-brand:hover { text-decoration:none; }
        .public-nav-links { display:flex; align-items:center; gap:26px; list-style:none; margin:0; padding:0; }
        .public-nav-links a { color:#CCD9F0; text-decoration:none; font-size:16px; }
        .public-nav-links a:hover { color:#fff; text-decoration:underline; }
        .hero-section { background:url('/img/hospital-de-clinicas.jpg') center/cover no-repeat; padding:140px 0; text-align:center; color:#fff; border-bottom:2px solid #2A4780; position:relative; }
        .hero-section::before { content:''; position:absolute; inset:0; background:rgba(0,0,0,0.45); }
        .hero-section .container { position:relative; z-index:1; }
        .hero-section h1 { font-size:44px; font-weight:bold; margin:14px 0; }
        .hero-section .lead { font-size:18px; margin-bottom:20px; color:#CCD9F0; }
        .section { padding:70px 0; }
        .section-title { font-size:22px; font-weight:bold; color:#3B5998; margin-bottom:6px; }
        .section-subtitle { font-size:13px; color:#777; margin-bottom:28px; }
        .news-card { border:1px solid #ddd; background:#fff; }
        .news-card .card-footer { background:#f6f6f6; border-top:1px solid #ddd; padding:6px 10px; }
        .service-card { border:1px solid #ddd; background:#fff; text-align:center; padding:20px 10px; }
        .service-card:hover { border-color:#3B5998; }
        .service-icon { font-size:32px; color:#3B5998; margin-bottom:8px; }
        .public-footer { background:#2A4780; color:#CCD9F0; padding:20px 0; font-size:11px; text-align:center; border-top:2px solid #1A3560; }
        .public-footer .text-muted { color:#99AACC; }
        .contact-section { background:#f6f6f6; border-top:1px solid #ddd; }
        .quick-link { display:block; padding:4px 8px; border:1px solid #ddd; background:#fff; font-size:11px; color:#3B5998; text-decoration:none; }
        .quick-link:hover { border-color:#3B5998; background:#E8EDF5; text-decoration:none; }
    </style>
</head>
<body>

<div class="public-nav">
    <div class="container public-nav-inner">
        <a class="public-nav-brand" href="/">
            <img src="/img/elyralogo.png" alt="Elyra" height="28">
            Elyra
        </a>
        <ul class="public-nav-links">
            <li><a href="/">Inicio</a></li>
            <li><a href="#noticias">Noticias</a></li>
            <li><a href="#servicios">Servicios</a></li>
            <li><a href="#acerca">Acerca</a></li>
            <li><a href="#contacto">Contacto</a></li>
            <li>
                <a class="btn btn-primary btn-sm" href="/login">
                    <i class="bi bi-person"></i> Acceso interno
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="hero-section">
    <div class="container">
        <img src="/img/elyralogo.png" alt="Elyra" height="90">
        <h1>Hospital de Clínicas</h1>
        <p class="lead">Sistema de gestión hospitalaria — Elyra</p>
        <div class="d-flex justify-content-center gap-3" style="margin-top:16px;">
            <a href="/login" class="btn btn-primary btn-lg" style="font-size:14px;padding:8px 24px;">
                <i class="bi bi-person-badge"></i> Acceso al sistema
            </a>
            <a href="#noticias" class="btn btn-lg" style="font-size:14px;padding:8px 24px;background:rgba(255,255,255,0.12);color:#fff;border-color:rgba(255,255,255,0.3);">
                <i class="bi bi-newspaper"></i> Últimas noticias
            </a>
        </div>
    </div>
</div>

<section id="noticias" class="section">
    <div class="container">
        <div class="section-title">Últimas noticias</div>
        <div class="section-subtitle">Novedades del Hospital de Clínicas</div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="news-card">
                    <div class="p-3">
                        <p class="small text-muted mb-1"><i class="bi bi-calendar3"></i> 26 de junio de 2026</p>
                        <h5 class="fw-semibold" style="font-size:13px;margin:0 0 6px 0;">Cirugía de tórax recibe premio internacional</h5>
                        <p class="small text-muted mb-0">La Dra. Macarena Muto fue galardonada por su contribución a la cirugía torácica a nivel internacional.</p>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-sm btn-primary">Leer más</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="news-card">
                    <div class="p-3">
                        <p class="small text-muted mb-1"><i class="bi bi-calendar3"></i> 25 de junio de 2026</p>
                        <h5 class="fw-semibold" style="font-size:13px;margin:0 0 6px 0;">Nuevo motor para cirugía de pie</h5>
                        <p class="small text-muted mb-0">El Hospital incorporó un moderno motor quirúrgico para cirugías de pie y tobillo.</p>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-sm btn-primary">Leer más</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="news-card">
                    <div class="p-3">
                        <p class="small text-muted mb-1"><i class="bi bi-calendar3"></i> 25 de junio de 2026</p>
                        <h5 class="fw-semibold" style="font-size:13px;margin:0 0 6px 0;">Las paredes del piso 10 tienen nuevas historias</h5>
                        <p class="small text-muted mb-0">Nueva exposición artística en el Hospital de Clínicas que transforma los espacios.</p>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-sm btn-primary">Leer más</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <section id="servicios" class="section" style="background:#f2f2f2;border-top:1px solid #ddd;">
        <div class="container">
            <div class="section-title">Servicios del sistema</div>
            <div class="section-subtitle">Módulos disponibles en Elyra</div>
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-file-text"></i></div>
                        <h6 class="fw-semibold" style="font-size:12px;margin:0 0 4px 0;">Documentos</h6>
                        <p class="small text-muted mb-0">Gestión y archivo de documentación clínica</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-bar-chart"></i></div>
                        <h6 class="fw-semibold" style="font-size:12px;margin:0 0 4px 0;">Encuestas</h6>
                        <p class="small text-muted mb-0">Creación y análisis de encuestas</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-truck"></i></div>
                        <h6 class="fw-semibold" style="font-size:12px;margin:0 0 4px 0;">Traslados</h6>
                        <p class="small text-muted mb-0">Gestión de ambulancias y traslados</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-people"></i></div>
                        <h6 class="fw-semibold" style="font-size:12px;margin:0 0 4px 0;">Conductores</h6>
                        <p class="small text-muted mb-0">Registro y control de conductores</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="acerca" class="section" style="border-top:1px solid #ddd;">
        <div class="container">
            <div class="section-title">Sobre nosotros</div>
            <div class="section-subtitle">Elyra, Lain y el equipo de desarrollo</div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card" style="text-align:left;padding:16px;">
                        <div style="text-align:center;margin-bottom:10px;">
                            <img src="/img/elyralogo.png" alt="Elyra" height="48">
                        </div>
                        <h6 class="fw-semibold" style="font-size:13px;margin:0 0 6px 0;color:#3B5998;">Elyra</h6>
                        <p class="small text-muted mb-2" style="font-size:11px;">
                            Sistema web modular para la gesti&oacute;n documental y trazabilidad de ambulancias del
                            Hospital de Cl&iacute;nicas. Desarrollado con PHP, MySQL y JavaScript, permite
                            administrar documentos cl&iacute;nicos con acceso v&iacute;a QR, encuestas de satisfacci&oacute;n
                            digitales y seguimiento en tiempo real de traslados en ambulancia a nivel nacional.
                        </p>
                        <p class="small text-muted mb-0" style="font-size:11px;">
                            <i class="bi bi-boxes"></i> Arquitectura hexagonal &bull;
                            Seguridad primero &bull; C&oacute;digo abierto
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card" style="text-align:left;padding:16px;">
                        <div style="text-align:center;margin-bottom:10px;">
                            <div class="icon-box-sm" style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;background:#3B5998;border-radius:4px;color:#fff;font-weight:bold;font-size:18px;">L</div>
                        </div>
                        <h6 class="fw-semibold" style="font-size:13px;margin:0 0 6px 0;color:#3B5998;">Lain</h6>
                        <p class="small text-muted mb-2" style="font-size:11px;">
                            Colectivo de desarrollo de software conformado por estudiantes de la
                            Facultad de Ingenier&iacute;a. Nos especializamos en aplicaciones web con
                            impacto social, combinando tecnolog&iacute;as modernas con dise&ntilde;o funcional
                            y accesible.
                        </p>
                        <p class="small text-muted mb-0" style="font-size:11px;">
                            <i class="bi bi-code-slash"></i> C&oacute;digo limpio &bull;
                            UX minimalista &bull; Innovaci&oacute;n
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card" style="text-align:left;padding:16px;">
                        <div style="text-align:center;margin-bottom:10px;">
                            <div class="icon-box-sm" style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;background:#E9EAED;border-radius:50%;color:#3B5998;font-weight:bold;font-size:16px;">A</div>
                        </div>
                        <h6 class="fw-semibold" style="font-size:13px;margin:0 0 6px 0;color:#3B5998;">Equipo</h6>
                        <p class="small text-muted mb-2" style="font-size:11px;">
                            Desarrolladores multidisciplinarios con experiencia en ingenier&iacute;a de
                            software, bases de datos y experiencia de usuario. Trabajamos bajo
                            metodolog&iacute;as &aacute;giles (Scrum) con entregas incrementales y revisi&oacute;n
                            continua de c&oacute;digo.
                        </p>
                        <p class="small text-muted mb-0" style="font-size:11px;">
                            <i class="bi bi-people"></i> Alan, Kevin, Tom &bull;
                            Scrum &bull; Pair programming
                        </p>
                    </div>
                </div>
            </div>

            <div class="row g-4 mt-2">
                <div class="col-12">
                    <div class="service-card" style="text-align:left;padding:16px;">
                        <h6 class="fw-semibold" style="font-size:13px;margin:0 0 10px 0;color:#3B5998;">
                            <i class="bi bi-tools"></i> Herramientas empleadas
                        </h6>
                        <div class="row g-2">
                            <div class="col-6 col-md-3"><span class="quick-link" style="display:inline-block;padding:4px 10px;margin:2px;"><i class="bi bi-chevron-right" style="font-size:9px;"></i> PHP 8.5</span></div>
                            <div class="col-6 col-md-3"><span class="quick-link" style="display:inline-block;padding:4px 10px;margin:2px;"><i class="bi bi-chevron-right" style="font-size:9px;"></i> MySQL 8</span></div>
                            <div class="col-6 col-md-3"><span class="quick-link" style="display:inline-block;padding:4px 10px;margin:2px;"><i class="bi bi-chevron-right" style="font-size:9px;"></i> JavaScript ES6+</span></div>
                            <div class="col-6 col-md-3"><span class="quick-link" style="display:inline-block;padding:4px 10px;margin:2px;"><i class="bi bi-chevron-right" style="font-size:9px;"></i> Bootstrap 5</span></div>
                            <div class="col-6 col-md-3"><span class="quick-link" style="display:inline-block;padding:4px 10px;margin:2px;"><i class="bi bi-chevron-right" style="font-size:9px;"></i> HTML5 / CSS3</span></div>
                            <div class="col-6 col-md-3"><span class="quick-link" style="display:inline-block;padding:4px 10px;margin:2px;"><i class="bi bi-chevron-right" style="font-size:9px;"></i> Apache / Nginx</span></div>
                            <div class="col-6 col-md-3"><span class="quick-link" style="display:inline-block;padding:4px 10px;margin:2px;"><i class="bi bi-chevron-right" style="font-size:9px;"></i> Git / GitHub</span></div>
                            <div class="col-6 col-md-3"><span class="quick-link" style="display:inline-block;padding:4px 10px;margin:2px;"><i class="bi bi-chevron-right" style="font-size:9px;"></i> Chart.js / Leaflet</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<section id="contacto" class="section contact-section">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="section-title" style="margin-bottom:12px;">Contacto</div>
                <ul class="list-unstyled" style="font-size:12px;">
                    <li class="mb-2"><i class="bi bi-geo-alt" style="color:#3B5998;margin-right:6px;"></i> Av. Italia s/n - Montevideo, Uruguay</li>
                    <li class="mb-2"><i class="bi bi-telephone" style="color:#3B5998;margin-right:6px;"></i> 1953 / 0800 1953</li>
                    <li class="mb-2"><i class="bi bi-envelope" style="color:#3B5998;margin-right:6px;"></i> atencionalusuario@hc.edu.uy</li>
                </ul>
            </div>
            <div class="col-md-6">
                <div class="section-title" style="margin-bottom:12px;">Accesos directos</div>
                <div class="row g-2">
                    <div class="col-6"><a href="/login" class="quick-link"><i class="bi bi-box-arrow-in-right"></i> Acceso interno</a></div>
                    <div class="col-6"><a href="/publico/doc" class="quick-link"><i class="bi bi-file-earmark"></i> Documento por QR</a></div>
                    <div class="col-6"><a href="/publico/encuesta" class="quick-link"><i class="bi bi-chat-square-text"></i> Encuesta pública</a></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="public-footer">
    <div class="container">
        &copy; 2026 Hospital de Cl&iacute;nicas &mdash; Elyra v1.0
    </div>
</div>

<script src="/js/elyra.js" defer></script>
<script src="/js/components/ui.js" defer></script>
</body>
</html>
