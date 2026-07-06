<?php $titulo = htmlspecialchars($doc['titulo']); ?>
<?php ob_start(); ?>

<div class="public-doc">
    <div class="public-doc-header">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary"><?= htmlspecialchars($doc['categoria']) ?></span>
            <small class="text-muted">Subido el <?= htmlspecialchars($doc['subido']) ?></small>
        </div>
        <h3 class="mb-0"><?= htmlspecialchars($doc['titulo']) ?></h3>
        <?php if (!empty($doc['descripcion'])): ?>
            <p class="text-muted mt-2 mb-0"><?= htmlspecialchars($doc['descripcion']) ?></p>
        <?php endif; ?>
    </div>

    <?php if (!empty($doc['filename'])): ?>
    <div class="public-doc-viewer">
        <embed src="/storage/uploads/documents/<?= rawurlencode($doc['filename']) ?>" type="application/pdf" class="public-doc-embed">
    </div>
    <?php else: ?>
    <div class="public-doc-viewer">
        <div class="public-doc-placeholder">
            <i class="bi bi-filetype-pdf display-3 text-muted"></i>
            <p class="text-muted mb-0">Vista previa no disponible</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="public-doc-feedback">
        <p class="fw-semibold mb-2">¿Te result&oacute; &uacute;til este documento?</p>
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

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
