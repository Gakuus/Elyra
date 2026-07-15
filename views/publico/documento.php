<?php $titulo = htmlspecialchars($doc['titulo']); ?>
<?php ob_start(); ?>

<style>
    .public-doc-card { cursor: pointer; transition: border-color 0.2s, transform 0.2s; }
    .public-doc-card:hover { border-color: var(--blue-light); transform: translateY(-2px); }
    .public-doc-card .panel-body { overflow-wrap: break-word; word-break: break-word; }
</style>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">

        <div class="panel public-doc-card" role="button" onclick="Elyra.verDocPublico(<?= $doc['id'] ?>, <?= htmlspecialchars(json_encode($doc['titulo']), ENT_QUOTES, 'UTF-8') ?>)">
            <div class="panel-body text-center py-5">
                <div style="font-size:48px;color:var(--blue);margin-bottom:10px;">
                    <i class="bi bi-file-earmark-pdf"></i>
                </div>
                <h5 class="fw-semibold mb-1"><?= htmlspecialchars($doc['titulo']) ?></h5>
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <?php if (!empty($doc['especialidad'])): ?>
                        <span class="badge" style="background:#D9EDF7;color:#31708F;border:1px solid #BCE8F1;"><?= htmlspecialchars($doc['especialidad']) ?></span>
                    <?php endif; ?>
                    <span class="badge" style="background:#4F74B8;color:#fff;border:1px solid #3B5998;"><?= htmlspecialchars($doc['categoria']) ?></span>
                </div>
                <p class="text-muted small mb-0">Subido el <?= htmlspecialchars($doc['subido']) ?></p>
                <?php if (!empty($doc['descripcion'])): ?>
                    <p class="text-muted mt-2"><?= htmlspecialchars($doc['descripcion']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="public-doc-feedback mt-4 pt-3 border-top">
            <p class="fw-semibold mb-2 text-center">¿Te result&oacute; &uacute;til este documento?</p>
            <div class="d-flex gap-3 justify-content-center">
                <button class="btn btn-success feedback-btn" data-voto="si" data-id="<?= $doc['id'] ?>">
                    <i class="bi bi-emoji-smile me-1"></i> S&iacute;
                </button>
                <button class="btn btn-warning feedback-btn" data-voto="no" data-id="<?= $doc['id'] ?>">
                    <i class="bi bi-emoji-frown me-1"></i> No
                </button>
            </div>
            <div class="feedback-msg text-center small text-muted mt-2 d-none" id="feedbackMsg"></div>
        </div>

        <div class="d-flex gap-2 justify-content-center mt-3 flex-wrap">
            <a href="publico/archivo?id=<?= $doc['id'] ?>&descargar=1" class="btn btn-primary">
                <i class="bi bi-download me-1"></i> Descargar PDF
            </a>
            <a href="publico/encuesta?id=<?= $doc['encuesta_id'] ?? 3 ?>" class="btn btn-info">
                <i class="bi bi-chat-square-text me-1"></i> Encuesta de satisfacci&oacute;n
            </a>
        </div>

    </div>
</div>

<div class="modal-overlay" id="docPublicoModal">
    <div class="modal-box" style="max-width:90%;width:1200px;">
        <div class="modal-header">
            <span class="text-truncate" id="publicoPreviewTitle"></span>
            <span class="modal-close" onclick="document.getElementById('docPublicoModal').classList.remove('open')">&times;</span>
        </div>
        <div class="modal-body p-0">
            <embed id="publicoPreviewEmbed" type="application/pdf" class="w-100" style="min-height:80vh;border:none;">
        </div>
    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
