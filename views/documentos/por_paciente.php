<?php
$titulo = 'Documentos de Paciente';
$frontendLimit = 8;
?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <div class="win-panel mb-2">
            <div class="win-titlebar"><i class="bi bi-file-text me-1"></i> Documentos de Paciente</div>
            <div class="p-3">
                <form method="get" class="row justify-content-center">
                    <div class="col-md-8">
                        <label for="ci" class="win-label mb-1 fw-bold">C\u00e9dula de Identidad</label>
                        <div class="d-flex gap-1">
                            <input type="text" name="ci" id="ci" class="win-field flex-grow-1 text-center" placeholder="Ingrese los 8 d\u00edgitos" value="<?= htmlspecialchars($ci) ?>" aria-label="C\u00e9dula de identidad" inputmode="numeric" maxlength="8" pattern="\d{8}" title="Ingrese solo los 8 d\u00edgitos de la c\u00e9dula" autofocus>
                            <button type="submit" class="win-btn win-btn-primary px-3">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($ci) && !empty($ciError)): ?>
            <div class="win-msg win-msg-warning mb-2 text-center fw-bold">
                <i class="bi bi-exclamation-triangle me-1"></i> <?= $ciError ?>
            </div>

        <?php elseif (!empty($ci) && !empty($ciPaciente)):
            $iniciales = mb_strtoupper(mb_substr($ciPaciente->getNombre(), 0, 1) . mb_substr($ciPaciente->getApellido(), 0, 1));
        ?>
            <div class="win-panel mb-2">
                <div class="win-titlebar"><i class="bi bi-person me-1"></i> Ficha del paciente</div>
                <div class="p-3 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center text-white fw-bold mb-2 win-avatar" style="width: 80px; height: 80px; font-size: 30px;">
                        <?= htmlspecialchars($iniciales) ?>
                    </div>
                    <h5 class="fw-bold mb-1 win-text"><?= htmlspecialchars($ciPaciente->getNombreCompleto()) ?></h5>
                    <div class="d-flex justify-content-center gap-3 mb-2 win-text" style="font-size: 11px;">
                        <span><i class="bi bi-person-badge me-1"></i> CI: <?= htmlspecialchars($ci) ?></span>
                        <?php if ($ciPaciente->getEmail()): ?>
                            <span><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($ciPaciente->getEmail()) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="win-inset px-3 py-1 win-text" style="font-size: 11px;">
                            <i class="bi bi-file-text me-1"></i> <?= $total ?> documento(s)
                        </span>
                        <a href="/documentos/paciente" class="win-btn">Limpiar</a>
                        <a href="/documentos/subir" class="win-btn win-btn-primary">Subir</a>
                    </div>
                </div>
            </div>

            <?php if (!empty($documentos)): ?>
                <div class="win-panel mb-2">
                    <div class="win-titlebar-gray d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-list-ul me-1"></i> Documentos</span>
                        <form method="get" class="d-flex gap-1">
                            <input type="hidden" name="ci" value="<?= htmlspecialchars($ci) ?>">
                            <select name="categoria" class="win-field" onchange="this.form.submit()">
                                <option value="">Todos los tipos</option>
                                <?php foreach ($tiposDocumento as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"<?= $categoriaFiltro == $cat['id'] ? ' selected' : '' ?>>
                                        <?= $cat['nombre'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="q" class="win-field" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>" style="width: 160px;">
                        </form>
                    </div>
                    <div class="win-inset m-2">
                        <div id="docs-table-wrapper">
                        <?php $docs = $documentos; ?>
                        <?php $isPaciente = false; ?>
                        <?php require __DIR__ . '/_table.php'; ?>
                        </div>
                    </div>

                    <?php if ($totalPaginas > 1): ?>
                    <div class="win-statusbar d-flex justify-content-between align-items-center">
                        <span>P&aacute;gina <?= $pagina ?> de <?= $totalPaginas ?> (<?= $total ?> documentos)</span>
                        <div class="win-pagination">
                            <a class="win-page-btn <?= $pagina <= 1 ? 'disabled' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>">&laquo;</a>
                            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                <a class="win-page-btn <?= $i === $pagina ? 'active win-inset' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
                            <?php endfor; ?>
                            <a class="win-page-btn <?= $pagina >= $totalPaginas ? 'disabled' : '' ?>" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>">&raquo;</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="win-panel">
                    <div class="win-titlebar-gray"><i class="bi bi-list-ul me-1"></i> Documentos</div>
                    <div class="p-3 text-center win-text" style="font-size: 12px;">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <span>Este paciente no tiene documentos asignados.</span>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif (empty($ci)): ?>
            <div class="text-center py-3 win-text">
                <i class="bi bi-person-search display-3 d-block mb-3 text-secondary opacity-50"></i>
                <p>Ingres&aacute; una c&eacute;dula de identidad para buscar los documentos del paciente.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php $scripts = <<<HTML
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ciInput = document.getElementById('ci');
    if (ciInput) {
        ciInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 8);
        });
    }

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
    btn.className = 'win-statusbar text-center';
    btn.innerHTML = '<button class="win-btn" id="toggleDocsBtn">Ver los ' + (total - limit) + ' documentos restantes</button>';
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
