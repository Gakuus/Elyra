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
        .public-nav-inner { display:flex; align-items:center; justify-content:space-between; padding:6px 0; }
        .public-nav-brand { display:flex; align-items:center; gap:8px; font-weight:bold; color:#fff; text-decoration:none; font-size:14px; }
        .public-nav-brand:hover { text-decoration:none; }
        .public-nav-links { display:flex; align-items:center; gap:14px; list-style:none; margin:0; padding:0; }
        .public-nav-links a { color:#CCD9F0; text-decoration:none; font-size:12px; }
        .public-nav-links a:hover { color:#fff; text-decoration:underline; }
        .hero-section { background:linear-gradient(135deg,#1A3560 0%,#3B5998 100%); padding:60px 0; text-align:center; color:#fff; border-bottom:2px solid #2A4780; }
        .hero-section h1 { font-size:38px; font-weight:bold; margin:14px 0; }
        .hero-section .lead { font-size:17px; margin-bottom:20px; color:#CCD9F0; }
        .section-title { font-size:18px; font-weight:bold; color:#3B5998; margin-bottom:4px; }
        .section-subtitle { font-size:11px; color:#777; margin-bottom:16px; }
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

<section id="noticias" class="py-5">
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

<section id="servicios" class="py-5" style="background:#f2f2f2;border-top:1px solid #ddd;">
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

<section id="contacto" class="py-5 contact-section">
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
