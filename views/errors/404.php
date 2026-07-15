<?php $titulo = 'Página no encontrada'; ?>
<?php ob_start(); ?>
<div class="text-center p-4" style="margin-top: 60px;">
    <div class="error-code">404</div>
    <h3 class="fw-bold mt-2">Página no encontrada</h3>
    <p class="text-muted mb-4">La página que buscas no existe o fue movida.</p>
    <div class="d-flex justify-content-center gap-2">
        <a href="dashboard" class="btn btn-primary"><i class="bi bi-house-door me-1"></i> Volver al inicio</a>
        <button onclick="history.back()" class="btn"><i class="bi bi-arrow-left me-1"></i> Regresar</button>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
