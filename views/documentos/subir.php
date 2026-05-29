<?php $titulo = 'Subir documento'; ?>
<?php ob_start(); ?>
<h2>Subir documento</h2>
<form>
    <div class="mb-3">
        <label class="form-label">Título</label>
        <input type="text" name="titulo" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Categoría</label>
        <select name="categoria" class="form-select"></select>
    </div>
    <div class="mb-3">
        <label class="form-label">Archivo PDF</label>
        <input type="file" name="archivo" class="form-control" accept=".pdf" required>
    </div>
    <button type="submit" class="btn btn-primary">Subir</button>
    <a href="/documentos" class="btn btn-secondary">Cancelar</a>
</form>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
