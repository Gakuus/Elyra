<?php $titulo = 'Encuestas'; ?>
<?php ob_start(); ?>
<h2>Encuestas</h2>
<a href="/encuestas/crear" class="btn btn-primary mb-3">+ Nueva encuesta</a>
<p class="text-muted">Gestión de encuestas de satisfacción.</p>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
