<?php $titulo = 'Tracking - Conductor'; ?>
<?php ob_start(); ?>

<style>
    * { box-sizing: border-box; }
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        margin: 0;
        padding: 0;
        background: #f5f5f5;
        min-height: 100vh;
    }
    .tracking-header {
        background: var(--blue-dark);
        color: white;
        padding: 16px 20px;
        text-align: center;
    }
    .tracking-header h4 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    .tracking-header small {
        opacity: 0.8;
        font-size: 0.8rem;
    }
    .tracking-status {
        padding: 20px;
        text-align: center;
    }
    .status-indicator {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        margin: 0 auto 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        transition: all 0.3s;
    }
    .status-indicator.inactive {
        background: #ffebee;
        color: #c62828;
    }
    .status-indicator.active {
        background: #e8f5e9;
        color: #2e7d32;
        animation: pulse-green 2s infinite;
    }
    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(46,125,50,.4); }
        70% { box-shadow: 0 0 0 15px rgba(46,125,50,0); }
        100% { box-shadow: 0 0 0 0 rgba(46,125,50,0); }
    }
    .status-text {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .status-sub {
        color: var(--gray);
        font-size: 0.85rem;
    }
    .tracking-btn {
        display: block;
        width: calc(100% - 40px);
        margin: 16px 20px;
        padding: 16px;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    .tracking-btn.start {
        background: #4CAF50;
        color: white;
    }
    .tracking-btn.start:hover {
        background: #43a047;
    }
    .tracking-btn.stop {
        background: #f44336;
        color: white;
    }
    .tracking-btn.stop:hover {
        background: #e53935;
    }
    .tracking-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .tracking-info {
        margin: 0 20px;
        padding: 16px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
    }
    .tracking-info .row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.9rem;
    }
    .tracking-info .row:last-child {
        border-bottom: none;
    }
    .tracking-info .label {
        color: var(--gray);
    }
    .tracking-coords {
        margin: 16px 20px;
        padding: 12px;
        background: #e3f2fd;
        border-radius: 8px;
        font-size: 0.8rem;
        color: #1565c0;
        text-align: center;
        font-family: monospace;
    }
    .tracking-error {
        margin: 12px 20px;
        padding: 12px;
        background: #ffebee;
        border-radius: 8px;
        color: #c62828;
        font-size: 0.85rem;
        text-align: center;
    }
    .tracking-success {
        margin: 12px 20px;
        padding: 12px;
        background: #e8f5e9;
        border-radius: 8px;
        color: #2e7d32;
        font-size: 0.85rem;
        text-align: center;
    }
    .hidden { display: none; }
</style>

<div class="tracking-header">
    <h4>Tracking de ubicaci&oacute;n</h4>
    <small>Comparte tu ubicaci&oacute;n con el hospital</small>
</div>

<div class="tracking-status">
    <div class="status-indicator inactive" id="statusIcon">
        <i class="bi bi-geo-alt"></i>
    </div>
    <div class="status-text" id="statusText">Inactivo</div>
    <div class="status-sub" id="statusSub">Presiona el bot&oacute;n para comenzar</div>
</div>

<button class="tracking-btn start" id="toggleBtn" onclick="toggleTracking()">
    <i class="bi bi-play-fill"></i> Compartir ubicaci&oacute;n
</button>

<div class="tracking-coords hidden" id="coordsDisplay"></div>
<div class="tracking-error hidden" id="errorDisplay"></div>
<div class="tracking-success hidden" id="successDisplay"></div>

<div class="tracking-info" id="infoPanel">
    <div class="row">
        <span class="label">Conductor ID</span>
        <span><?= htmlspecialchars((string) $conductor_id) ?></span>
    </div>
    <div class="row">
        <span class="label">Estado GPS</span>
        <span id="gpsStatus">Esperando</span>
    </div>
    <div class="row">
        <span class="label">&Uacute;ltima actualizaci&oacute;n</span>
        <span id="lastUpdate">--</span>
    </div>
    <div class="row">
        <span class="label">Env&iacute;os exitosos</span>
        <span id="sendCount">0</span>
    </div>
</div>

<script nonce="<?= $nonce ?>">
var TRACKING_CONFIG = {
    conductorId: <?= (int) $conductor_id ?>,
    csrfToken: '<?= htmlspecialchars(\Elyra\Infrastructure\Service\SessionManager::getCsrfToken()) ?>',
    intervalMs: 5000,
    ubicacionActual: <?= json_encode($ubicacion_actual, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
};
</script>
<script nonce="<?= $nonce ?>" src="/js/tracking-conductor.js?v=1"></script>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
