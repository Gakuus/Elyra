<?php $titulo = htmlspecialchars($doc['titulo']); ?>
<?php ob_start(); ?>

<div class="public-doc">
    <div class="public-doc-header text-center">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
            <?php if (!empty($doc['especialidad'])): ?>
                <span class="badge bg-info bg-opacity-10 text-info"><?= htmlspecialchars($doc['especialidad']) ?></span>
            <?php endif; ?>
            <span class="badge bg-primary"><?= htmlspecialchars($doc['categoria']) ?></span>
        </div>
        <h4 class="mb-1"><?= htmlspecialchars($doc['titulo']) ?></h4>
        <small class="text-muted">Subido el <?= htmlspecialchars($doc['subido']) ?></small>
        <?php if (!empty($doc['descripcion'])): ?>
            <p class="text-muted mt-2 mb-0"><?= htmlspecialchars($doc['descripcion']) ?></p>
        <?php endif; ?>
    </div>

    <div class="text-center my-4">
        <button type="button" class="btn btn-lg btn-primary px-5" onclick="Elyra.verDocPublico(<?= $doc['id'] ?>, <?= htmlspecialchars(json_encode($doc['titulo']), ENT_QUOTES, 'UTF-8') ?>)">
            <i class="bi bi-eye me-2"></i> Ver documento
        </button>
    </div>

    <div class="public-doc-feedback mt-4 pt-3 border-top">
        <p class="fw-semibold mb-2 text-center">¿Te result&oacute; &uacute;til este documento?</p>
        <div class="d-flex gap-3 justify-content-center">
            <button class="btn btn-outline-success feedback-btn" data-voto="si" data-id="<?= $doc['id'] ?>">
                <i class="bi bi-emoji-smile me-1"></i> S&iacute;
            </button>
            <button class="btn btn-outline-warning feedback-btn" data-voto="no" data-id="<?= $doc['id'] ?>">
                <i class="bi bi-emoji-frown me-1"></i> No
            </button>
        </div>
        <div class="feedback-msg text-center small text-muted mt-2 d-none" id="feedbackMsg"></div>
    </div>
</div>

<div class="modal fade" id="docPublicoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="modal-content bg-body">
            <div class="modal-header border-bottom-0">
                <div class="me-3 overflow-hidden">
                    <h5 class="modal-title text-truncate" id="publicoPreviewTitle"></h5>
                </div>
                <div class="d-flex gap-2 flex-shrink-0">
                    <a id="publicoPreviewDownload" class="btn btn-sm btn-primary" title="Descargar PDF">
                        <i class="bi bi-download"></i>
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            </div>
            <div class="modal-body p-0 d-flex">
                <embed id="publicoPreviewEmbed" type="application/pdf" class="w-100" style="height: 90vh; border: none;">
            </div>
        </div>
    </div>
</div>

<script>
    window.Elyra = window.Elyra || {};

    window.Elyra.verDocPublico = function (id, titulo) {
        var titleEl = document.getElementById('publicoPreviewTitle');
        var embedEl = document.getElementById('publicoPreviewEmbed');
        var downloadEl = document.getElementById('publicoPreviewDownload');
        if (!embedEl || !titleEl || !downloadEl) return;
        titleEl.textContent = titulo;
        embedEl.src = '/publico/archivo?id=' + id;
        downloadEl.href = '/publico/archivo?id=' + id + '&descargar=1';
        new bootstrap.Modal(document.getElementById('docPublicoModal')).show();
    };

    document.addEventListener('hidden.bs.modal', function (e) {
        if (e.target && e.target.id === 'docPublicoModal') {
            var embed = document.getElementById('publicoPreviewEmbed');
            if (embed) embed.src = '';
        }
    });
</script>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
