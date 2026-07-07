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
    <link href="/css/classic.css" rel="stylesheet">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
</head>
<?php
$currentSess = \Elyra\Infrastructure\Service\SessionManager::class;
$isPaciente = $currentSess::isPaciente();
?>
<body<?= $currentSess::isAuthenticated() ? ' class="win-body"' : '' ?>>

<?php if ($currentSess::isAuthenticated()): ?>

<?php
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$breadcrumbs = [
    '/dashboard' => ['Inicio', null],
    '/documentos' => ['Documentación', '/dashboard'],
    '/documentos/generales' => ['Documentos generales', '/dashboard'],
    '/documentos/paciente' => ['Documentos por CI', '/dashboard'],
    '/documentos/subir' => ['Subir documento', '/documentos/generales'],
    '/documentos/editar' => ['Editar documento', '/documentos/generales'],
    '/documentos/ver' => ['Detalle', '/documentos/generales'],
    '/documentos/eliminar' => ['Eliminar', '/documentos/generales'],
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

<nav class="win-navbar navbar navbar-expand-lg">
    <div class="container-fluid px-4">
        <span class="fw-bold px-2" style="font-family: Tahoma, 'MS Sans Serif', sans-serif; font-size: 13px;">Elyra</span>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Abrir men\u00fa">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link<?= $currentUri === '/dashboard' ? ' active' : '' ?>" href="/dashboard">
                        <i class="bi bi-house-door me-1"></i> Inicio
                    </a>
                </li>

                <?php if ($isPaciente): ?>
                <li class="nav-item">
                    <a class="nav-link<?= str_starts_with($currentUri, '/documentos') ? ' active' : '' ?>" href="/documentos">
                        <i class="bi bi-file-text me-1"></i> Documentos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= str_starts_with($currentUri, '/encuestas') ? ' active' : '' ?>" href="/encuestas">
                        <i class="bi bi-bar-chart me-1"></i> Encuestas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link<?= str_starts_with($currentUri, '/traslados') ? ' active' : '' ?>" href="/traslados">
                        <i class="bi bi-truck me-1"></i> Traslados
                    </a>
                </li>
                <?php endif; ?>

                <?php if (!$isPaciente): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= str_starts_with($currentUri, '/documentos') || str_starts_with($currentUri, '/encuestas') ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-text me-1"></i> Documentaci&oacute;n
                    </a>
                    <ul class="dropdown-menu win-dropdown-menu">
                        <li><a class="win-dropdown-item" href="/documentos/generales"><i class="bi bi-globe me-2"></i>Documentos generales</a></li>
                        <li><a class="win-dropdown-item" href="/documentos/paciente"><i class="bi bi-person-search me-2"></i>Documentos por CI</a></li>
                        <li><a class="win-dropdown-item" href="/documentos/subir"><i class="bi bi-upload me-2"></i>Subir documento</a></li>
                        <li><div class="win-separator"></div></li>
                        <li><a class="win-dropdown-item" href="/encuestas"><i class="bi bi-bar-chart me-2"></i>Encuestas</a></li>
                        <li><a class="win-dropdown-item" href="/encuestas/crear"><i class="bi bi-plus-square me-2"></i>Crear encuesta</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle<?= str_starts_with($currentUri, '/traslados') || str_starts_with($currentUri, '/conductores') || str_starts_with($currentUri, '/rutas') ? ' active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-truck me-1"></i> Ambulancias
                    </a>
                    <ul class="dropdown-menu win-dropdown-menu">
                        <li><a class="win-dropdown-item" href="/traslados"><i class="bi bi-list-ul me-2"></i>Traslados activos</a></li>
                        <li><a class="win-dropdown-item" href="/traslados/nuevo"><i class="bi bi-plus-circle me-2"></i>Nuevo traslado</a></li>
                        <li><a class="win-dropdown-item" href="/traslados/historial"><i class="bi bi-clock-history me-2"></i>Historial</a></li>
                        <li><div class="win-separator"></div></li>
                        <li><a class="win-dropdown-item" href="/rutas"><i class="bi bi-map me-2"></i>Rutas</a></li>
                        <li><a class="win-dropdown-item" href="/conductores"><i class="bi bi-people me-2"></i>Conductores</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <span class="win-text small">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario') ?>
                </span>
                <?php if ($isPaciente): ?>
                <a href="/perfil" class="win-btn win-btn-sm">
                    <i class="bi bi-gear"></i> Perfil
                </a>
                <?php endif; ?>
                <a href="/logout" class="win-btn win-btn-sm">
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

    <footer class="win-statusbar text-center">
        &copy; 2026 Hospital de Clínicas &mdash; Elyra v1.0
    </footer>

<?php else: ?>

    <main>
        <?= $contenido ?? '' ?>
    </main>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/elyra.js?v=4" defer></script>
<script src="/js/components/ui.js?v=4" defer></script>
<?= $scripts ?? '' ?>
</body>
</html>
