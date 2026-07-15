<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="<?= rtrim((string)(parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?: ''), '/') ?>/">
    <title>Elyra — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/web20.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#3B5998">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Elyra">
    <link rel="apple-touch-icon" href="img/icon-192.png">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
    <script>window.BASE_PATH = '<?= rtrim((string)(parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?: ''), '/') ?>';</script>
    <style>
        body { background: #fff; }
        .public-nav { background:#3B5998; border-bottom:2px solid #2A4780; }
        .public-nav-inner { display:flex; align-items:center; justify-content:space-between; padding:20px 0; }
        .public-nav-brand { display:flex; align-items:center; gap:12px; font-weight:bold; color:#fff; text-decoration:none; font-size:22px; }
        .public-nav-brand:hover { text-decoration:none; }
        .public-nav-links { display:flex; align-items:center; gap:26px; list-style:none; margin:0; padding:0; }
        .public-nav-links a { color:#CCD9F0; text-decoration:none; font-size:16px; }
        .public-nav-links a:hover { color:#fff; text-decoration:underline; }
        .hero-section { background:url('img/hospital-de-clinicas.jpg') center/cover no-repeat; padding:140px 0; text-align:center; color:#fff; border-bottom:2px solid #2A4780; position:relative; }
        .hero-section::before { content:''; position:absolute; inset:0; background:rgba(0,0,0,0.45); }
        .hero-section .container { position:relative; z-index:1; }
        .hero-section h1 { font-size:44px; font-weight:bold; margin:14px 0; }
        .hero-section .lead { font-size:18px; margin-bottom:20px; color:#CCD9F0; }
        .section { padding:70px 0; }
        .section-title { font-size:28px; font-weight:bold; color:#3B5998; margin-bottom:8px; }
        .section-subtitle { font-size:15px; color:#777; margin-bottom:32px; }

        .service-card { border:1px solid #ddd; background:#fff; text-align:center; padding:32px 20px; }
        .service-card:hover { border-color:#3B5998; }
        .service-card h6 { font-size:15px !important; }
        .service-card p { font-size:13px !important; }
        .service-icon { font-size:40px; color:#3B5998; margin-bottom:12px; }
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
        <a class="public-nav-brand" href="">
            <img src="img/elyralogo.png" alt="Elyra" height="28">
            Elyra
        </a>
        <ul class="public-nav-links">
            <li><a href="">Inicio</a></li>
            <li><a href="#noticias">Noticias</a></li>
            <li><a href="#servicios">Servicios</a></li>
            <li><a href="#acerca">Acerca</a></li>
            <li><a href="#contacto">Contacto</a></li>
            <li>
                <a class="btn btn-primary btn-sm" href="login">
                    <i class="bi bi-person"></i> Acceso interno
                </a>
            </li>
        </ul>
    </div>
</div>

<div class="hero-section">
    <div class="container">
        <img src="img/elyralogo.png" alt="Elyra" height="90">
        <h1>Hospital de Clínicas</h1>
        <p class="lead">Sistema de gestión hospitalaria — Elyra</p>
        <div class="d-flex justify-content-center gap-3" style="margin-top:16px;">
            <a href="login" class="btn btn-primary btn-lg" style="font-size:14px;padding:8px 24px;">
                <i class="bi bi-person-badge"></i> Acceso al sistema
            </a>
            <a href="#noticias-semana" class="btn btn-lg" style="font-size:14px;padding:8px 24px;background:rgba(255,255,255,0.12);color:#fff;border-color:rgba(255,255,255,0.3);">
                <i class="bi bi-newspaper"></i> Noticias
            </a>
        </div>
    </div>
</div>

<?php if (!empty($noticiasSemanaArr)): ?>
<style>
.carousel-item { display:none; }
.carousel-item.active { display:block; }
.car-slide { position:relative; height:320px; background-size:cover; background-position:center; border-radius:12px; overflow:hidden; display:flex; flex-direction:column; justify-content:flex-end; }
.car-slide::before { content:''; position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.05) 100%); }
.car-slide .container { position:relative; z-index:2; padding-bottom:60px; }
.car-slide h2 { font-size:24px; font-weight:700; color:#fff; margin:0 0 6px 0; max-width:600px; line-height:1.25; }
.car-slide p { font-size:14px; color:rgba(255,255,255,0.8); margin:0 0 12px 0; max-width:520px; line-height:1.5; }
.car-slide .meta { font-size:12px; color:rgba(255,255,255,0.5); margin-bottom:10px; }
.car-slide .btn-outline-light { font-size:13px; padding:6px 20px; border-radius:6px; }
.c-prev { position:absolute; top:50%; transform:translateY(-50%); left:12px; z-index:3; background:rgba(0,0,0,0.35); border:none; color:#fff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:16px; }
.c-next { position:absolute; top:50%; transform:translateY(-50%); right:12px; z-index:3; background:rgba(0,0,0,0.35); border:none; color:#fff; width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:16px; }
.c-next:hover,
.c-prev:hover { background:rgba(0,0,0,0.6); }
.c-indic { position:absolute; bottom:14px; left:0; right:0; z-index:3; display:flex; align-items:center; justify-content:center; gap:6px; }
.c-indic button { width:8px; height:8px; border-radius:50%; border:0; background:rgba(255,255,255,0.4); cursor:pointer; padding:0; transition:all 0.2s; }
.c-indic button.active { background:#fff; width:20px; border-radius:4px; }
@media (max-width:768px) {
    .car-slide { height:220px; border-radius:8px; }
    .car-slide h2 { font-size:18px; }
    .car-slide p { font-size:13px; }
}
</style>

<section id="noticias-semana" class="section" style="background:#f8f9fb;padding-top:0;">
    <div class="container">
        <div class="section-title">Noticias de la semana</div>
        <div class="section-subtitle">Lo más destacado de los últimos días</div>
        <div id="noticiasCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000" style="position:relative;">
            <div class="carousel-inner">
                <?php foreach ($noticiasSemanaArr as $i => $n): 
                    $imgBg = $n['imagen'] ? 'background-image:url(\'uploads/noticias/' . rawurlencode($n['imagen']) . '\')' : 'background:linear-gradient(135deg,#1a1a2e,#16213e)';
                ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <div class="car-slide" style="<?= $imgBg ?>">
                        <div class="container">
                            <h2><?= htmlspecialchars($n['titulo']) ?></h2>
                            <p><?= htmlspecialchars(mb_substr($n['contenido'], 0, 120)) ?><?= mb_strlen($n['contenido']) > 120 ? '…' : '' ?></p>
                            <p class="meta"><i class="bi bi-calendar3"></i> <?= htmlspecialchars($n['creada']) ?></p>
                            <a href="#" class="btn btn-outline-light btn-sm">Leer más <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <button class="c-ctrl c-prev" type="button" data-bs-target="#noticiasCarousel" data-bs-slide="prev">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button class="c-ctrl c-next" type="button" data-bs-target="#noticiasCarousel" data-bs-slide="next">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="c-indic">
                <?php foreach ($noticiasSemanaArr as $i => $n): ?>
                <button type="button" data-bs-target="#noticiasCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<script nonce="<?= \Elyra\Infrastructure\Service\SessionManager::getNonce() ?>">
document.addEventListener('DOMContentLoaded', function(){
    var carousel = document.getElementById('noticiasCarousel');
    if (carousel) {
        carousel.addEventListener('slid.bs.carousel', function(e){
            var dots = carousel.querySelectorAll('.c-indic button');
            dots.forEach(function(d, i){
                d.classList.toggle('active', i === e.to);
            });
        });
    }
});
</script>
<?php endif; ?>

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
                            <img src="img/elyralogo.png" alt="Elyra" height="48">
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
                            ITSP. Nos especializamos en aplicaciones web con
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
                    <li class="mb-2"><i class="bi bi-envelope" style="color:#3B5998;margin-right:6px;"></i> lainsmes@gmail.com</li>
                </ul>
            </div>
            <div class="col-md-6">
                <div class="section-title" style="margin-bottom:12px;">Accesos directos</div>
                <div class="row g-2">
                    <div class="col-6"><a href="login" class="quick-link"><i class="bi bi-box-arrow-in-right"></i> Acceso interno</a></div>
                    <div class="col-6"><a href="publico/doc" class="quick-link"><i class="bi bi-file-earmark"></i> Documento por QR</a></div>
                    <div class="col-6"><a href="publico/encuesta" class="quick-link"><i class="bi bi-chat-square-text"></i> Encuesta pública</a></div>
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

<script nonce="<?= \Elyra\Infrastructure\Service\SessionManager::getNonce() ?>" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
<script nonce="<?= \Elyra\Infrastructure\Service\SessionManager::getNonce() ?>" src="js/elyra.js" defer></script>
<script nonce="<?= \Elyra\Infrastructure\Service\SessionManager::getNonce() ?>" src="js/components/ui.js" defer></script>
</body>
</html>
