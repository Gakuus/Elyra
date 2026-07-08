<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Elyra' ?> — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/web20.css" rel="stylesheet">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
</head>
<?php
$currentSess = \Elyra\Infrastructure\Service\SessionManager::class;
$isPaciente = $currentSess::isPaciente();
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<body>

<?php if ($currentSess::isAuthenticated()): ?>

<?php
$breadcrumbMap = [
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
    '/traslados' => ['Traslados', '/dashboard'],
    '/traslados/nuevo' => ['Nuevo traslado', '/traslados'],
    '/traslados/ver' => ['Detalle traslado', '/traslados'],
    '/traslados/historial' => ['Historial', '/traslados'],
    '/conductores' => ['Conductores', '/dashboard'],
    '/conductores/crear' => ['Crear conductor', '/conductores'],
    '/rutas' => ['Rutas', '/dashboard'],
    '/rutas/crear' => ['Crear ruta', '/rutas'],
    '/perfil' => ['Mi Perfil', '/dashboard'],
];

function renderBreadcrumbs(string $uri, array $map): void {
    if (!isset($map[$uri])) return;
    [$label, $parent] = $map[$uri];
    echo '<div class="breadcrumb">';
    if ($parent) {
        echo '<a href="' . $parent . '">' . $map[$parent][0] . '</a><span class="separator"> &rsaquo; </span>';
    }
    echo $label;
    echo '</div>';
}
?>

<div class="web20-header">
    <div class="web20-header-brand">
        <a href="/" class="web20-header-logo">
            <img src="/img/elyralogo.png" alt="Elyra">
            <span>Elyra</span>
        </a>
    </div>
    <div class="web20-header-user">
        <a href="/perfil"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario') ?></a>
        <a href="/logout"><i class="bi bi-box-arrow-right"></i> Salir</a>
    </div>
</div>

<div class="web20-wrapper">
    <div class="web20-sidebar">
        <?php if (!$isPaciente): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Gestión</div>
            <a href="/dashboard" class="sidebar-link <?= $currentUri === '/dashboard' ? 'active' : '' ?>"><img src="/img/silk/house.png" width="16" height="16" alt=""> Panel</a>
            <a href="/perfil" class="sidebar-link <?= $currentUri === '/perfil' ? 'active' : '' ?>"><img src="/img/silk/user.png" width="16" height="16" alt=""> Mi Perfil</a>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Documentación</div>
            <a href="/documentos/generales" class="sidebar-link <?= $currentUri === '/documentos/generales' ? 'active' : '' ?>"><img src="/img/silk/world.png" width="16" height="16" alt=""> Generales</a>
            <a href="/documentos/paciente" class="sidebar-link <?= $currentUri === '/documentos/paciente' ? 'active' : '' ?>"><img src="/img/silk/magnifier.png" width="16" height="16" alt=""> Por CI</a>
            <a href="/documentos/subir" class="sidebar-link <?= $currentUri === '/documentos/subir' ? 'active' : '' ?>"><img src="/img/silk/arrow_up.png" width="16" height="16" alt=""> Subir</a>
            <a href="/encuestas" class="sidebar-link <?= $currentUri === '/encuestas' ? 'active' : '' ?>"><img src="/img/silk/chart_bar.png" width="16" height="16" alt=""> Encuestas</a>
            <a href="/encuestas/crear" class="sidebar-link <?= $currentUri === '/encuestas/crear' ? 'active' : '' ?>"><img src="/img/silk/add.png" width="16" height="16" alt=""> Nueva encuesta</a>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Ambulancias</div>
            <a href="/traslados" class="sidebar-link <?= $currentUri === '/traslados' ? 'active' : '' ?>"><img src="/img/silk/lorry.png" width="16" height="16" alt=""> Traslados</a>
            <a href="/traslados/nuevo" class="sidebar-link <?= $currentUri === '/traslados/nuevo' ? 'active' : '' ?>"><img src="/img/silk/add.png" width="16" height="16" alt=""> Nuevo</a>
            <a href="/traslados/historial" class="sidebar-link <?= $currentUri === '/traslados/historial' ? 'active' : '' ?>"><img src="/img/silk/clock.png" width="16" height="16" alt=""> Historial</a>
            <a href="/rutas" class="sidebar-link <?= $currentUri === '/rutas' ? 'active' : '' ?>"><img src="/img/silk/map.png" width="16" height="16" alt=""> Rutas</a>
            <a href="/conductores" class="sidebar-link <?= $currentUri === '/conductores' ? 'active' : '' ?>"><img src="/img/silk/group.png" width="16" height="16" alt=""> Conductores</a>
        </div>
        <?php else: ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Menú</div>
            <a href="/dashboard" class="sidebar-link <?= $currentUri === '/dashboard' ? 'active' : '' ?>"><img src="/img/silk/house.png" width="16" height="16" alt=""> Panel</a>
            <a href="/documentos" class="sidebar-link <?= str_starts_with($currentUri, '/documentos') ? 'active' : '' ?>"><img src="/img/silk/page_white_text.png" width="16" height="16" alt=""> Documentos</a>
            <a href="/encuestas" class="sidebar-link <?= str_starts_with($currentUri, '/encuestas') ? 'active' : '' ?>"><img src="/img/silk/chart_bar.png" width="16" height="16" alt=""> Encuestas</a>
            <a href="/traslados" class="sidebar-link <?= str_starts_with($currentUri, '/traslados') ? 'active' : '' ?>"><img src="/img/silk/lorry.png" width="16" height="16" alt=""> Traslados</a>
            <a href="/perfil" class="sidebar-link <?= $currentUri === '/perfil' ? 'active' : '' ?>"><img src="/img/silk/user.png" width="16" height="16" alt=""> Mi Perfil</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="web20-content">
        <?php if ($currentUri !== '/dashboard'): ?>
            <?php renderBreadcrumbs($currentUri, $breadcrumbMap); ?>
        <?php endif; ?>
        <?= $contenido ?? '' ?>
    </div>
</div>

<div class="web20-footer">
    &copy; 2026 Hospital de Clínicas &mdash; Elyra v1.0
</div>

<?php require __DIR__ . '/../documentos/_modal_qr.php'; ?>
<?php require __DIR__ . '/../documentos/_modal_eliminar.php'; ?>
<div class="toast-container"></div>

<?php else: ?>

    <main>
        <?= $contenido ?? '' ?>
    </main>

<?php endif; ?>

<script src="/js/elyra.js?v=5" defer></script>
<script src="/js/components/ui.js?v=5" defer></script>
<?= $scripts ?? '' ?>
</body>
</html>
