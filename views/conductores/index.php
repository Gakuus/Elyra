<?php $titulo = "index"; ?>
<?php ob_start(); ?>
<h2>index</h2>
<p class="text-muted">En construcción.</p>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . "/../layout/base.php"; ?>
