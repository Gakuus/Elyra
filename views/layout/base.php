<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Elyra' ?> — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/elyra.css" rel="stylesheet">
    <link href="/css/components/login.css" rel="stylesheet">
    <link href="/css/components/admin.css" rel="stylesheet">
    <link href="/css/components/tables.css" rel="stylesheet">
    <link href="/css/components/stats.css" rel="stylesheet">
</head>
<body>

<?php if (isset($_SESSION['user'])): ?>

<?php $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>

<nav class="navbar navbar-expand-lg navbar-dark" style="background: #1a1f36;">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="/dashboard">Elyra</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link<?= $currentUri === '/dashboard' ? ' active' : '' ?>" href="/dashboard">
                        <i class="bi bi-house-door me-1"></i> Inicio
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= str_starts_with($currentUri, '/documentos') || str_starts_with($currentUri, '/encuestas') ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-text me-1"></i> Documentaci&oacute;n
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/documentos"><i class="bi bi-list-ul me-2"></i>Documentos</a></li>
                        <li><a class="dropdown-item" href="/documentos/subir"><i class="bi bi-upload me-2"></i>Subir documento</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/encuestas"><i class="bi bi-bar-chart me-2"></i>Encuestas</a></li>
                        <li><a class="dropdown-item" href="/encuestas/crear"><i class="bi bi-plus-square me-2"></i>Crear encuesta</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= str_starts_with($currentUri, '/traslados') || str_starts_with($currentUri, '/conductores') || str_starts_with($currentUri, '/rutas') ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-truck me-1"></i> Ambulancias
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/traslados"><i class="bi bi-list-ul me-2"></i>Traslados activos</a></li>
                        <li><a class="dropdown-item" href="/traslados/nuevo"><i class="bi bi-plus-circle me-2"></i>Nuevo traslado</a></li>
                        <li><a class="dropdown-item" href="/traslados/historial"><i class="bi bi-clock-history me-2"></i>Historial</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/rutas"><i class="bi bi-map me-2"></i>Rutas</a></li>
                        <li><a class="dropdown-item" href="/conductores"><i class="bi bi-people me-2"></i>Conductores</a></li>
                    </ul>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light-emphasis small">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user']['nombre']) ?>
                </span>
                <a href="/logout" class="btn btn-outline-light btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="main-content">
    <?= $contenido ?? '' ?>
</main>

<footer class="footer">
    &copy; 2026 Hospital de Clínicas &mdash; Elyra v1.0
</footer>

<?php else: ?>

    <main>
        <?= $contenido ?? '' ?>
    </main>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/elyra.js" defer></script>
<script src="/js/components/ui.js" defer></script>
</body>
</html>
