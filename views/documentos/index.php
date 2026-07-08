<?php
$titulo = 'Mis Documentos';
?>
<?php ob_start(); ?>

<div class="d-flex justify-content-between align-items-center mb-2">
    <h4 class="mb-0">Mis Documentos</h4>
</div>

<form method="get" class="d-flex gap-2 mb-2" id="filterForm">
    <select name="categoria" class="form-select" onchange="this.form.submit()" aria-label="Tipo de documento">
        <option value="">Todos los tipos</option>
        <?php foreach ($tiposDocumento as $cat): ?>
            <option value="<?= $cat['id'] ?>"<?= $categoriaFiltro == $cat['id'] ? ' selected' : '' ?>>
                <?= $cat['nombre'] ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="position-relative flex-grow-1">
        <input type="text" name="q" class="form-input ps-4" placeholder="Buscar por t&iacute;tulo..." value="<?= htmlspecialchars($search) ?>" aria-label="Buscar documento">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small"></i>
    </div>
</form>

<?php if (empty($documentos)): ?>
    <div class="text-muted small py-3">
        <i class="bi bi-inbox me-1"></i> No ten&eacute;s documentos asignados.
    </div>
<?php else: ?>
    <?php $docs = $documentos; ?>
    <?php require __DIR__ . '/_table.php'; ?>

    <?php if ($totalPaginas > 1): ?>
    <nav class="d-flex justify-content-between align-items-center border-top pt-3" aria-label="Paginaci&oacute;n">
        <p class="text-muted small mb-0">P&aacute;gina <?= $pagina ?> de <?= $totalPaginas ?> (<?= $total ?> documentos)</p>
        <ul class="pagination mb-0">
            <li class="page-item<?= $pagina <= 1 ? ' disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>" aria-label="Anterior">&laquo;</a>
            </li>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <li class="page-item<?= $i === $pagina ? ' active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item<?= $pagina >= $totalPaginas ? ' disabled' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>" aria-label="Siguiente">&raquo;</a>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
<?php endif; ?>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
