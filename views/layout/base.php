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
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
</head>
<body>

<?php if (isset($_SESSION['user'])): ?>

<?php
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$breadcrumbs = [
    '/dashboard' => ['Inicio', null],
    '/documentos' => ['Documentación', '/dashboard'],
    '/documentos/subir' => ['Subir documento', '/documentos'],
    '/documentos/editar' => ['Editar documento', '/documentos'],
    '/documentos/ver' => ['Detalle', '/documentos'],
    '/documentos/eliminar' => ['Eliminar', '/documentos'],
    '/encuestas' => ['Encuestas', '/dashboard'],
    '/encuestas/crear' => ['Crear encuesta', '/encuestas'],
    '/encuestas/resultados' => ['Resultados', '/encuestas'],
    '/encuestas/editar' => ['Editar encuesta', '/encuestas'],
    '/traslados' => ['Traslados', '/dashboard'],
    '/traslados/nuevo' => ['Nuevo traslado', '/traslados'],
    '/traslados/ver' => ['Detalle traslado', '/traslados'],
    '/traslados/historial' => ['Historial', '/traslados'],
    '/conductores' => ['Conductores', '/dashboard'],
    '/conductores/crear' => ['Crear conductor', '/conductores'],
    '/rutas' => ['Rutas', '/dashboard'],
    '/rutas/crear' => ['Crear ruta', '/rutas'],
];

function renderBreadcrumbs(string $uri, array $map): void {
    if (!isset($map[$uri])) return;
    [$label, $parent] = $map[$uri];
    echo '<nav aria-label="breadcrumb" class="breadcrumb-custom">';
    echo '<ol class="breadcrumb mb-0">';
    if ($parent) {
        echo '<li class="breadcrumb-item"><a href="' . $parent . '">' . $map[$parent][0] . '</a></li>';
    }
    echo '<li class="breadcrumb-item active" aria-current="page">' . $label . '</li>';
    echo '</ol></nav>';
}
?>

<nav class="navbar navbar-expand-lg navbar-dark" style="background: var(--navbar-bg); transition: background 0.3s;">
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
                <button id="darkModeToggle" class="btn btn-outline-light btn-sm" aria-label="Alternar modo oscuro">
                    <i class="bi bi-moon-stars-fill"></i>
                </button>
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
    <?php if ($currentUri !== '/dashboard'): ?>
        <?php renderBreadcrumbs($currentUri, $breadcrumbs); ?>
    <?php endif; ?>
    <?= $contenido ?? '' ?>
</main>

    <?php require __DIR__ . '/../documentos/_modal_qr.php'; ?>
    <?php require __DIR__ . '/../documentos/_modal_eliminar.php'; ?>
    <div class="toast-container"></div>

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
