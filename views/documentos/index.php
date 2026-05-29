<?php $titulo = 'Documentos'; ?>
<?php ob_start(); ?>
<h2>Documentos</h2>
<a href="/documentos/subir" class="btn btn-primary mb-3">+ Subir documento</a>
<p class="text-muted">Módulo de gestión de documentación para pacientes.</p>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
