<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Elyra — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/elyra.css" rel="stylesheet">
    <link href="/css/components/homepage.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white shadow-sm border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="/">
            <img src="/img/elyralogo.png" alt="Elyra" height="32" class="me-2">
            Elyra
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="/">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="#noticias">Noticias</a></li>
                <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-outline-primary btn-sm" href="/login">
                        <i class="bi bi-person"></i> Acceso interno
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main>

<section class="hero-section d-flex align-items-center">
    <div class="hero-overlay"></div>
    <div class="container position-relative text-center text-white">
        <img src="/img/elyralogo.png" alt="Elyra" height="80" class="mb-4">
        <h1 class="display-4 fw-bold mb-3">Hospital de Clínicas</h1>
        <p class="lead mb-4 fs-5">Sistema de gesti&oacute;n hospitalaria &mdash; Elyra</p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/login" class="btn btn-light btn-lg px-4">
                <i class="bi bi-person-badge"></i> Acceso al sistema
            </a>
            <a href="#noticias" class="btn btn-outline-light btn-lg px-4">
                <i class="bi bi-newspaper"></i> &Uacute;ltimas noticias
            </a>
        </div>
    </div>
</section>

<section id="noticias" class="py-5">
    <div class="container">
        <h2 class="fw-bold mb-2">&Uacute;ltimas noticias</h2>
        <p class="text-muted mb-4">Novedades del Hospital de Cl&iacute;nicas</p>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 news-card">
                    <div class="card-body">
                        <p class="text-muted small mb-1"><i class="bi bi-calendar3"></i> 26 de junio de 2026</p>
                        <h5 class="card-title">Cirug&iacute;a de t&oacute;rax recibe premio internacional</h5>
                        <p class="card-text text-muted">La Dra. Macarena Muto fue galardonada por su contribuci&oacute;n a la cirug&iacute;a tor&aacute;cica a nivel internacional.</p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="#" class="btn btn-sm btn-outline-primary">Leer m&aacute;s</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 news-card">
                    <div class="card-body">
                        <p class="text-muted small mb-1"><i class="bi bi-calendar3"></i> 25 de junio de 2026</p>
                        <h5 class="card-title">Nuevo motor para cirug&iacute;a de pie</h5>
                        <p class="card-text text-muted">El Hospital incorpor&oacute; un moderno motor quir&uacute;rgico para cirug&iacute;as de pie y tobillo.</p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="#" class="btn btn-sm btn-outline-primary">Leer m&aacute;s</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 news-card">
                    <div class="card-body">
                        <p class="text-muted small mb-1"><i class="bi bi-calendar3"></i> 25 de junio de 2026</p>
                        <h5 class="card-title">Las paredes del piso 10 tienen nuevas historias</h5>
                        <p class="card-text text-muted">Nueva exposici&oacute;n art&iacute;stica en el Hospital de Cl&iacute;nicas que transforma los espacios.</p>
                    </div>
                    <div class="card-footer bg-transparent border-top-0">
                        <a href="#" class="btn btn-sm btn-outline-primary">Leer m&aacute;s</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="servicios" class="py-5 bg-light">
    <div class="container">
        <h2 class="fw-bold mb-2">Servicios del sistema</h2>
        <p class="text-muted mb-4">M&oacute;dulos disponibles en Elyra</p>
        <div class="row g-4">
            <div class="col-md-3 col-6">
                <div class="card text-center h-100 border-0 shadow-sm service-card">
                    <div class="card-body py-4">
                        <div class="display-6 text-primary mb-3"><i class="bi bi-file-text"></i></div>
                        <h6 class="fw-semibold">Documentos</h6>
                        <p class="small text-muted mb-0">Gesti&oacute;n y archivo de documentaci&oacute;n cl&iacute;nica</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card text-center h-100 border-0 shadow-sm service-card">
                    <div class="card-body py-4">
                        <div class="display-6 text-primary mb-3"><i class="bi bi-bar-chart"></i></div>
                        <h6 class="fw-semibold">Encuestas</h6>
                        <p class="small text-muted mb-0">Creaci&oacute;n y an&aacute;lisis de encuestas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card text-center h-100 border-0 shadow-sm service-card">
                    <div class="card-body py-4">
                        <div class="display-6 text-primary mb-3"><i class="bi bi-truck"></i></div>
                        <h6 class="fw-semibold">Traslados</h6>
                        <p class="small text-muted mb-0">Gesti&oacute;n de ambulancias y traslados</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card text-center h-100 border-0 shadow-sm service-card">
                    <div class="card-body py-4">
                        <div class="display-6 text-primary mb-3"><i class="bi bi-people"></i></div>
                        <h6 class="fw-semibold">Conductores</h6>
                        <p class="small text-muted mb-0">Registro y control de conductores</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contacto" class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-6">
                <h4 class="fw-bold mb-3">Contacto</h4>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i> Av. Italia s/n - Montevideo, Uruguay</li>
                    <li class="mb-2"><i class="bi bi-telephone text-primary me-2"></i> 1953 / 0800 1953</li>
                    <li class="mb-2"><i class="bi bi-envelope text-primary me-2"></i> atencionalusuario@hc.edu.uy</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h4 class="fw-bold mb-3">Accesos directos</h4>
                <div class="row g-2">
                    <div class="col-6"><a href="/login" class="btn btn-outline-primary w-100 btn-sm"><i class="bi bi-box-arrow-in-right"></i> Acceso interno</a></div>
                    <div class="col-6"><a href="/publico/doc" class="btn btn-outline-primary w-100 btn-sm"><i class="bi bi-file-earmark"></i> Documento por QR</a></div>
                    <div class="col-6"><a href="/publico/encuesta" class="btn btn-outline-primary w-100 btn-sm"><i class="bi bi-chat-square-text"></i> Encuesta p&uacute;blica</a></div>
                </div>
            </div>
        </div>
    </div>
</section>

</main>

<footer class="bg-dark text-white py-4">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 small">&copy; 2026 Hospital de Cl&iacute;nicas &mdash; Elyra v1.0</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0 small text-muted">Universidad de la Rep&uacute;blica &mdash; Facultad de Medicina</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
