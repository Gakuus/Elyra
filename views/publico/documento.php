<?php $titulo = 'Documento'; ?>
<?php ob_start(); ?>
<div class="card">
    <div class="card-body">
        <h2>Documento</h2>
        <p>Documento asociado al código: <strong><?= $codigo ?></strong></p>
        <p class="text-muted">Vista pública — acceso sin autenticación.</p>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
