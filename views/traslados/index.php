<?php $titulo = 'Ambulancias'; ?>
<?php ob_start(); ?>
<h2>Traslados en ambulancia</h2>
<a href="/traslados/nuevo" class="btn btn-primary mb-3">+ Nuevo traslado</a>
<a href="/traslados/historial" class="btn btn-outline-secondary mb-3">Historial</a>
<p class="text-muted">Módulo de trazabilidad de ambulancias.</p>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
