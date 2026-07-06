<?php $titulo = 'Página no encontrada'; ?>
<?php ob_start(); ?>
<div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: var(--page-bg);">
    <div class="text-center px-3">
        <div class="display-1 fw-bold text-primary mb-2" style="font-size: 6rem; line-height: 1;">404</div>
        <div class="display-6 fw-semibold text-dark mb-3">P&aacute;gina no encontrada</div>
        <p class="text-muted mb-4" style="max-width: 400px; margin-left: auto; margin-right: auto;">
            La p&aacute;gina que buscas no existe o fue movida.
        </p>
        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="/dashboard" class="btn btn-primary px-4">
                <i class="bi bi-house-door me-2"></i>Volver al inicio
            </a>
            <button onclick="history.back()" class="btn btn-outline-secondary px-4">
                <i class="bi bi-arrow-left me-2"></i>Regresar
            </button>
        </div>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
