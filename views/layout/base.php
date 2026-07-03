<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Elyra' ?> — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/elyra.css" rel="stylesheet">
</head>
<body>

<?php if (isset($_SESSION['user'])): ?>

<div id="wrapper">
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <h5>Elyra</h5>
        </div>
        <nav class="sidebar-nav">
            <?php
            $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $sidebarItems = [
                '/documentos' => ['icon' => 'bi-file-text', 'label' => 'Documentos'],
                '/encuestas'  => ['icon' => 'bi-bar-chart',  'label' => 'Encuestas'],
                '/traslados'  => ['icon' => 'bi-truck',      'label' => 'Ambulancias'],
                '/conductores'=> ['icon' => 'bi-people',     'label' => 'Conductores'],
                '/rutas'      => ['icon' => 'bi-map',        'label' => 'Rutas'],
            ];
            foreach ($sidebarItems as $url => $item):
                $active = str_starts_with($currentUri, $url) ? ' active' : '';
            ?>
                <a href="<?= $url ?>" class="sidebar-item<?= $active ?>">
                    <i class="bi <?= $item['icon'] ?>"></i>
                    <span><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <div id="page-content-wrapper">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Abrir menú" aria-expanded="false">
                    <i class="bi bi-list"></i>
                </button>
                <span class="topbar-title">Hospital de Clínicas</span>
            </div>
            <div class="topbar-right">
                <span class="topbar-user">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user']['nombre']) ?>
                </span>
                <a href="/logout" class="btn-topbar-logout">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </div>
        </header>

        <main class="main-content">
            <?= $contenido ?? '' ?>
        </main>

        <footer class="footer">
            &copy; 2026 Hospital de Clínicas &mdash; Elyra v1.0
        </footer>
    </div>
</div>

<script>
(function() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var toggleBtn = document.querySelector('.sidebar-toggle');

    window.toggleSidebar = function() {
        var isOpen = sidebar.classList.toggle('open');
        overlay.classList.toggle('open');
        toggleBtn.setAttribute('aria-label', isOpen ? 'Cerrar menú' : 'Abrir menú');
        toggleBtn.setAttribute('aria-expanded', isOpen);
    };
})();
</script>

<?php else: ?>

    <main>
        <?= $contenido ?? '' ?>
    </main>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
