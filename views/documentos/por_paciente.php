<?php
$titulo = 'Documentos de Paciente';
$frontendLimit = 8;
?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <div class="panel mb-2">
            <div class="panel-heading"><i class="bi bi-file-text me-1"></i> Documentos de Paciente</div>
            <div class="p-3">
                <form method="get" class="row justify-content-center">
                    <div class="col-md-8">
                        <label for="ci" class="form-label mb-1 fw-bold">Cedula de Identidad</label>
                        <div class="d-flex gap-1">
                            <input type="text" name="ci" id="ci" class="form-input flex-grow-1 text-center" placeholder="Ingrese los 8 digitos sin guiones" value="<?= htmlspecialchars($ci) ?>" aria-label="Cedula de identidad" inputmode="numeric" maxlength="8" pattern="\d{8}" title="Ingrese solo los 8 digitos de la cedula" autofocus>
                            <button type="submit" class="btn btn-primary px-3">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if (!empty($ci) && !empty($ciError)): ?>
            <div class="msg msg-warning mb-2 text-center fw-bold">
                <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($ciError) ?>
            </div>

        <?php elseif (!empty($ci) && !empty($ciPaciente)):
            $iniciales = mb_strtoupper(mb_substr($ciPaciente->getNombre(), 0, 1) . mb_substr($ciPaciente->getApellido(), 0, 1));
        ?>
            <div class="panel mb-2">
                <div class="panel-heading"><i class="bi bi-person me-1"></i> Ficha del paciente</div>
                <div class="p-3 text-center">
                    <?php $pfoto = $ciPaciente->getFotoBase64(); ?>
                    <?php if ($pfoto): ?>
                        <img src="<?= $pfoto ?>" alt="Foto de <?= htmlspecialchars($ciPaciente->getNombreCompleto()) ?>" class="avatar mb-2" style="width: 80px; height: 80px; object-fit: cover;">
                    <?php else: ?>
                        <div class="d-inline-flex align-items-center justify-content-center text-white fw-bold mb-2 avatar" style="width: 80px; height: 80px; font-size: 30px;">
                            <?= htmlspecialchars($iniciales) ?>
                        </div>
                    <?php endif; ?>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($ciPaciente->getNombreCompleto()) ?></h5>
                    <div class="d-flex justify-content-center gap-3 mb-2" style="font-size: 11px;">
                        <span><i class="bi bi-person-badge me-1"></i> CI: <?= htmlspecialchars($ci) ?></span>
                        <?php if ($ciPaciente->getEmail()): ?>
                            <span><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($ciPaciente->getEmail()) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex justify-content-center gap-2">
                        <span class="panel-inset px-3 py-1" style="font-size: 11px;">
                            <i class="bi bi-file-text me-1"></i> <?= $total ?> documento(s)
                        </span>
                        <a href="/documentos/paciente" class="btn">Limpiar</a>
                        <a href="/documentos/subir" class="btn btn-primary">Subir</a>
                    </div>
                </div>
            </div>

            <?php if (!empty($documentos)): ?>
                <div class="panel mb-2">
                    <div class="panel-heading-gray d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-list-ul me-1"></i> Documentos</span>
                        <form method="get" class="d-flex gap-1">
                            <input type="hidden" name="ci" value="<?= htmlspecialchars($ci) ?>">
                            <select name="categoria" class="form-select" onchange="this.form.submit()">
                                <option value="">Todos los tipos</option>
                                <?php foreach ($tiposDocumento as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"<?= $categoriaFiltro == $cat['id'] ? ' selected' : '' ?>>
                                         <?= htmlspecialchars((string) $cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="q" class="form-input" placeholder="Buscar..." value="<?= htmlspecialchars($search) ?>" style="width: 160px;">
                        </form>
                    </div>
                    <div class="panel-inset m-2">
                        <div id="docs-table-wrapper">
                        <?php $docs = $documentos; ?>
                        <?php $isPaciente = false; ?>
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
            <?php else: ?>
                <div class="panel">
                    <div class="panel-heading-gray"><i class="bi bi-list-ul me-1"></i> Documentos</div>
                    <div class="p-3 text-center" style="font-size: 12px;">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        <span>Este paciente no tiene documentos asignados.</span>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif (empty($ci)): ?>
            <div class="text-center py-3">
                <i class="bi bi-person-search display-3 d-block mb-3 text-secondary opacity-50"></i>
                <p>Ingres&aacute; una c&eacute;dula de identidad para buscar los documentos del paciente.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php $scripts = <<<HTML
<script nonce="{$nonce}">
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
