<?php
$titulo = 'Documentos Generales';
$frontendLimit = 8;
?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <div class="panel mb-2">
            <div class="panel-heading d-flex justify-content-between align-items-center">
                <span><i class="bi bi-globe me-1"></i> Documentos Generales</span>
                <a href="documentos/subir" class="btn btn-primary py-0 px-3" style="font-size: 11px;">
                    <i class="bi bi-upload me-1"></i> Subir
                </a>
            </div>
            <div class="p-3">
                <form method="get" class="d-flex gap-2">
                    <select name="categoria" class="form-select" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tiposDocumento as $cat): ?>
                            <option value="<?= $cat['id'] ?>"<?= $categoriaFiltro == $cat['id'] ? ' selected' : '' ?>>
                                <?= $cat['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="q" class="form-input flex-grow-1" placeholder="Buscar por t&iacute;tulo..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn"><i class="bi bi-search me-1"></i> Buscar</button>
                </form>
            </div>
        </div>

        <?php if (empty($documentos)): ?>
            <div class="panel">
                <div class="p-3 text-center" style="font-size: 12px;">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    <span>No hay documentos generales.</span>
                </div>
            </div>
        <?php else: ?>
            <?php $docs = $documentos; ?>
            <?php $isPaciente = false; ?>
            <div class="panel mb-2">
                <div class="panel-inset m-2">
                    <div id="docs-table-wrapper">
                    <?php require __DIR__ . '/_table.php'; ?>
                    </div>
                </div>

                <?php if ($totalPaginas > 1): ?>
                <div class="d-flex justify-content-between align-items-center border-top p-2 small text-muted">
                    <span>P&aacute;gina <?= $pagina ?> de <?= $totalPaginas ?> (<?= $total ?> documentos)</span>
                    <div class="pagination">
                        <a class="page-link <?= $pagina <= 1 ? 'disabled' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>">&laquo;</a>
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                            <a class="page-link <?= $i === $pagina ? 'active panel-inset' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                        <?php endfor; ?>
                        <a class="page-link <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>">&raquo;</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php $scripts = <<<HTML
<script nonce="{$nonce}">
document.addEventListener('DOMContentLoaded', function() {
    var wrapper = document.getElementById('docs-table-wrapper');
    if (!wrapper) return;
    var rows = wrapper.querySelectorAll('table tbody tr');
    var cards = wrapper.querySelectorAll('.card-list-view .card-item');
    var limit = {$frontendLimit};
    var total = Math.max(rows.length, cards.length);

    function applyLimit() {
        rows.forEach(function(r, i) { r.style.display = i < limit ? '' : 'none'; });
        cards.forEach(function(c, i) { c.style.display = i < limit ? '' : 'none'; });
    }

    if (total <= limit) return;

    applyLimit();

    var btn = document.createElement('div');
    btn.className = 'd-flex justify-content-center border-top p-2 small text-muted';
    btn.innerHTML = '<button class="btn" id="toggleDocsBtn">Ver los ' + (total - limit) + ' documentos restantes</button>';
    wrapper.parentNode.insertBefore(btn, wrapper.nextSibling);

    document.getElementById('toggleDocsBtn').addEventListener('click', function() {
        var showingAll = wrapper.dataset.showingAll === 'true';
        if (showingAll) {
            applyLimit();
            wrapper.dataset.showingAll = 'false';
            this.textContent = 'Ver los ' + (total - limit) + ' documentos restantes';
        } else {
            rows.forEach(function(r) { r.style.display = ''; });
            cards.forEach(function(c) { c.style.display = ''; });
            wrapper.dataset.showingAll = 'true';
            this.textContent = 'Mostrar menos';
        }
    });
});
</script>
HTML;
?>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
