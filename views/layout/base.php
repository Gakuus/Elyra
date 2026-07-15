<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="<?= rtrim((string)(parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?: ''), '/') ?>/">
    <title><?= htmlspecialchars($titulo ?? 'Elyra') ?> — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/web20.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#3B5998">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Elyra">
    <link rel="apple-touch-icon" href="img/icon-192.png">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
    <script nonce="<?= $nonce ?>">window.BASE_PATH = '<?= rtrim((string)(parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?: ''), '/') ?>';</script>
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
    '/traslados/mapa' => ['Mapa en vivo', '/traslados'],
    '/traslados/tracking' => ['Tracking', '/traslados'],
    '/conductores' => ['Conductores', '/dashboard'],
    '/conductores/crear' => ['Crear conductor', '/conductores'],
    '/rutas' => ['Rutas', '/dashboard'],
    '/rutas/crear' => ['Crear ruta', '/rutas'],
    '/funcionarios' => ['Funcionarios', '/dashboard'],
    '/funcionarios/crear' => ['Crear funcionario', '/funcionarios'],
    '/funcionarios/editar' => ['Editar funcionario', '/funcionarios'],
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
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Abrir menú" type="button">&#9776;</button>
        <a href="" class="web20-header-logo">
            <img src="img/elyralogo.png" alt="Elyra">
            <span>Elyra</span>
        </a>
    </div>
    <div class="web20-header-user">
        <a href="perfil"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario') ?></a>
        <form method="post" action="logout" style="display:inline">
            <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
            <button type="submit" class="btn btn-sm btn-link" style="text-decoration:none" aria-label="Cerrar sesión"><i class="bi bi-box-arrow-right"></i> Salir</button>
        </form>
    </div>
</div>

<div class="web20-sidebar-overlay" id="sidebarOverlay"></div>

<div class="web20-wrapper">
    <div class="web20-sidebar" id="mainSidebar" role="navigation" aria-label="Menú principal">
        <?php if (!$isPaciente): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Gestión</div>
            <a href="dashboard" class="sidebar-link <?= $currentUri === '/dashboard' ? 'active' : '' ?>"><img src="img/silk/house.png" width="16" height="16" alt=""> Panel</a>
            <a href="perfil" class="sidebar-link <?= $currentUri === '/perfil' ? 'active' : '' ?>"><img src="img/silk/user.png" width="16" height="16" alt=""> Mi Perfil</a>
            <a href="noticias" class="sidebar-link <?= str_starts_with($currentUri, '/noticias') ? 'active' : '' ?>"><img src="img/silk/newspaper.png" width="16" height="16" alt=""> Noticias</a>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Documentación</div>
            <a href="documentos/generales" class="sidebar-link <?= $currentUri === '/documentos/generales' ? 'active' : '' ?>"><img src="img/silk/world.png" width="16" height="16" alt=""> Generales</a>
            <a href="documentos/paciente" class="sidebar-link <?= $currentUri === '/documentos/paciente' ? 'active' : '' ?>"><img src="img/silk/magnifier.png" width="16" height="16" alt=""> Por CI</a>
            <a href="documentos/subir" class="sidebar-link <?= $currentUri === '/documentos/subir' ? 'active' : '' ?>"><img src="img/silk/arrow_up.png" width="16" height="16" alt=""> Subir</a>
            <a href="encuestas" class="sidebar-link <?= $currentUri === '/encuestas' ? 'active' : '' ?>"><img src="img/silk/chart_bar.png" width="16" height="16" alt=""> Encuestas</a>
            <a href="encuestas/crear" class="sidebar-link <?= $currentUri === '/encuestas/crear' ? 'active' : '' ?>"><img src="img/silk/add.png" width="16" height="16" alt=""> Nueva encuesta</a>
        </div>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Ambulancias</div>
            <a href="traslados" class="sidebar-link <?= $currentUri === '/traslados' ? 'active' : '' ?>"><img src="img/silk/lorry.png" width="16" height="16" alt=""> Traslados</a>
            <a href="traslados/mapa" class="sidebar-link <?= $currentUri === '/traslados/mapa' ? 'active' : '' ?>"><img src="img/silk/map.png" width="16" height="16" alt=""> Mapa en vivo</a>
            <a href="traslados/nuevo" class="sidebar-link <?= $currentUri === '/traslados/nuevo' ? 'active' : '' ?>"><img src="img/silk/add.png" width="16" height="16" alt=""> Nuevo</a>
            <a href="traslados/historial" class="sidebar-link <?= $currentUri === '/traslados/historial' ? 'active' : '' ?>"><img src="img/silk/clock.png" width="16" height="16" alt=""> Historial</a>
            <a href="rutas" class="sidebar-link <?= $currentUri === '/rutas' ? 'active' : '' ?>"><img src="img/silk/map.png" width="16" height="16" alt=""> Rutas</a>
            <a href="conductores" class="sidebar-link <?= $currentUri === '/conductores' ? 'active' : '' ?>"><img src="img/silk/group.png" width="16" height="16" alt=""> Conductores</a>
            <a href="funcionarios" class="sidebar-link <?= str_starts_with($currentUri, '/funcionarios') ? 'active' : '' ?>"><img src="img/silk/user_edit.png" width="16" height="16" alt=""> Funcionarios</a>
        </div>
        <?php else: ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Menú</div>
            <a href="dashboard" class="sidebar-link <?= $currentUri === '/dashboard' ? 'active' : '' ?>"><img src="img/silk/house.png" width="16" height="16" alt=""> Panel</a>
            <a href="documentos" class="sidebar-link <?= str_starts_with($currentUri, '/documentos') ? 'active' : '' ?>"><img src="img/silk/page_white_text.png" width="16" height="16" alt=""> Documentos</a>
            <a href="encuestas" class="sidebar-link <?= str_starts_with($currentUri, '/encuestas') ? 'active' : '' ?>"><img src="img/silk/chart_bar.png" width="16" height="16" alt=""> Encuestas</a>
            <a href="traslados" class="sidebar-link <?= str_starts_with($currentUri, '/traslados') ? 'active' : '' ?>"><img src="img/silk/lorry.png" width="16" height="16" alt=""> Traslados</a>
            <a href="perfil" class="sidebar-link <?= $currentUri === '/perfil' ? 'active' : '' ?>"><img src="img/silk/user.png" width="16" height="16" alt=""> Mi Perfil</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="web20-content" id="main-content">
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

<script nonce="<?= $nonce ?>" src="js/elyra.js?v=6" defer></script>
<script nonce="<?= $nonce ?>" src="js/components/ui.js?v=5" defer></script>
<script nonce="<?= $nonce ?>">
(function() {
    var timeout = 1800000; // 30 min
    var timer;
    function resetTimer() {
        clearTimeout(timer);
        timer = setTimeout(function () {
            var f = document.createElement('form');
            f.method = 'POST';
            f.action = 'logout';
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = '_csrf_token';
            inp.value = '<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>';
            f.appendChild(inp);
            document.body.appendChild(f);
            f.submit();
        }, timeout);
    }
    document.addEventListener('mousemove', resetTimer);
    document.addEventListener('keydown', resetTimer);
    document.addEventListener('click', resetTimer);
    document.addEventListener('scroll', resetTimer);
    resetTimer();
})();
</script>
<script nonce="<?= $nonce ?>">
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('sw.js');
    });
}
</script>
<script nonce="<?= $nonce ?>">
(function () {
    var startX = 0, startY = 0;
    document.addEventListener('touchstart', function (e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    }, { passive: true });
    document.addEventListener('touchend', function (e) {
        var dx = e.changedTouches[0].clientX - startX;
        var dy = e.changedTouches[0].clientY - startY;
        if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 60) {
            var ev = new CustomEvent('swipeleft', { detail: { dx: dx } });
            document.dispatchEvent(ev);
        }
    }, { passive: true });
})();
</script>
<script nonce="<?= $nonce ?>">
(function () {
    var btn = document.getElementById('hamburgerBtn');
    var sidebar = document.getElementById('mainSidebar');
    var overlay = document.getElementById('sidebarOverlay');
    if (!btn || !sidebar || !overlay) return;

    function open() {
        sidebar.classList.add('open');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        sidebar.classList.remove('open');
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        if (sidebar.classList.contains('open')) { close(); } else { open(); }
    });

    overlay.addEventListener('click', close);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('open')) close();
    });

    document.addEventListener('swipeleft', close);
})();
</script>
<div id="pwa-install-prompt" class="pwa-install-prompt">
    <div class="pwa-install-content">
        <div class="pwa-install-info">
            <i class="bi bi-download"></i>
            <span>Instalá Elyra en tu dispositivo</span>
        </div>
        <div class="pwa-install-actions">
            <button id="pwa-install-btn" class="btn btn-sm btn-success">Instalar</button>
            <button id="pwa-dismiss-btn" class="btn btn-sm btn-link" style="text-decoration:none">Ahora no</button>
        </div>
    </div>
</div>
<script nonce="<?= $nonce ?>">
(function () {
    var promptEl = document.getElementById('pwa-install-prompt');
    var installBtn = document.getElementById('pwa-install-btn');
    var dismissBtn = document.getElementById('pwa-dismiss-btn');
    var deferredPrompt = null;

    if (!promptEl) return;

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        promptEl.classList.add('show');
    });

    installBtn.addEventListener('click', function () {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function (choice) {
            promptEl.classList.remove('show');
            deferredPrompt = null;
        });
    });

    dismissBtn.addEventListener('click', function () {
        promptEl.classList.remove('show');
        deferredPrompt = null;
    });

    window.addEventListener('appinstalled', function () {
        promptEl.classList.remove('show');
        deferredPrompt = null;
    });
})();
</script>
<?= $scripts ?? '' ?>
</body>
</html>
