<?php $titulo = 'Mapa en vivo'; ?>
<?php ob_start(); ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" nonce="<?= $nonce ?>" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" nonce="<?= $nonce ?>"></script>

<style>
    .mapa-container {
        display: flex;
        position: fixed;
        top: 60px;
        left: 240px;
        right: 0;
        bottom: 40px;
        z-index: 10;
    }
    .web20-content {
        padding: 0 !important;
        overflow: visible !important;
        max-width: 100% !important;
    }
    #map {
        flex: 1;
        height: 100%;
        min-height: 100%;
        z-index: 1;
    }
    .mapa-sidebar {
        width: 340px;
        background: var(--white);
        border-left: 1px solid var(--gray-light);
        overflow-y: auto;
        transition: transform 0.3s ease;
        z-index: 2;
        display: flex;
        flex-direction: column;
    }
    .mapa-sidebar.collapsed {
        transform: translateX(100%);
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
    }
    .mapa-sidebar-toggle {
        position: absolute;
        right: 346px;
        top: 10px;
        z-index: 3;
        background: var(--white);
        border: 1px solid var(--gray-light);
        border-radius: 4px 0 0 4px;
        padding: 8px 10px;
        cursor: pointer;
        transition: right 0.3s ease;
    }
    .mapa-sidebar.collapsed ~ .mapa-sidebar-toggle {
        right: 0;
        border-radius: 4px 0 0 4px;
    }
    .sidebar-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--gray-light);
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .sidebar-header .count {
        font-size: 0.8rem;
        color: var(--gray);
        font-weight: 400;
    }
    .traslado-card {
        padding: 12px 16px;
        border-bottom: 1px solid var(--gray-lightest);
        cursor: pointer;
        transition: background 0.15s;
    }
    .traslado-card:hover {
        background: var(--gray-lightest);
    }
    .traslado-card.active {
        background: #e8f0fe;
        border-left: 3px solid var(--blue);
    }
    .traslado-card .codigo {
        font-weight: 600;
        font-size: 0.95rem;
    }
    .traslado-card .conductor {
        font-size: 0.85rem;
        color: var(--gray-dark);
    }
    .traslado-card .route {
        font-size: 0.8rem;
        color: var(--gray);
        margin-top: 4px;
    }
    .traslado-card .route i {
        margin: 0 4px;
    }
    .mapa-empty {
        padding: 40px 16px;
        text-align: center;
        color: var(--gray);
    }
    .mapa-empty i {
        font-size: 2rem;
        display: block;
        margin-bottom: 8px;
    }
    .mapa-legend {
        position: absolute;
        bottom: 30px;
        left: 10px;
        background: var(--white);
        padding: 10px 14px;
        border-radius: 6px;
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
        z-index: 2;
        font-size: 0.8rem;
    }
    .mapa-legend div {
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
    }
    .mapa-status {
        position: absolute;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--white);
        padding: 6px 14px;
        border-radius: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,.15);
        z-index: 2;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .pulse-dot {
        width: 8px;
        height: 8px;
        background: var(--success);
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(76,175,80,.6); }
        70% { box-shadow: 0 0 0 8px rgba(76,175,80,0); }
        100% { box-shadow: 0 0 0 0 rgba(76,175,80,0); }
    }
    .leaflet-popup-content {
        font-family: inherit;
        margin: 10px 14px;
    }
    .popup-codigo {
        font-weight: 700;
        font-size: 1rem;
    }
    .popup-conductor {
        color: var(--gray-dark);
        font-size: 0.9rem;
    }
    .popup-route {
        font-size: 0.85rem;
        margin-top: 6px;
    }
    .popup-route i {
        color: var(--blue);
    }
</style>

<div class="mapa-container">
    <div id="map"></div>

    <div class="mapa-sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Traslados activos</span>
            <span class="count" id="countActivos">0 conductores</span>
        </div>
        <div id="listaTraslados">
            <div class="mapa-empty">
                <i class="bi bi-geo-alt"></i>
                Cargando ubicaciones...
            </div>
        </div>
    </div>

    <button class="mapa-sidebar-toggle" id="sidebarToggle" title="Panel">
        <i class="bi bi-list"></i>
    </button>

    <div class="mapa-legend">
        <div><span class="legend-dot" style="background:#4CAF50"></span> En curso</div>
        <div><span class="legend-dot" style="background:#2196F3"></span> En retorno</div>
        <div><span class="legend-dot" style="background:#FF9800"></span> En destino</div>
        <div><span class="legend-dot" style="background:#9E9E9E"></span> Sin traslado activo</div>
        <div><span class="legend-dot" style="background:#E91E63; width:14px; height:14px; border-radius:2px;"></span> Hospital</div>
    </div>

    <div class="mapa-status" id="mapaStatus">
        <div class="pulse-dot"></div>
        <span>Conectado</span>
    </div>
</div>

<script nonce="<?= $nonce ?>" src="js/mapa-traslados.js?v=1"></script>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
