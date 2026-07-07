<?php $titulo = htmlspecialchars($doc['titulo']); ?>
<?php ob_start(); ?>

<style>
    .public-doc-card { cursor: pointer; transition: box-shadow 0.2s, transform 0.2s; }
    .public-doc-card:hover { box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important; transform: translateY(-2px); }
</style>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">

        <div class="card border-0 shadow-sm public-doc-card" role="button" onclick="Elyra.verDocPublico(<?= $doc['id'] ?>, <?= htmlspecialchars(json_encode($doc['titulo']), ENT_QUOTES, 'UTF-8') ?>)">
            <div class="card-body text-center py-5">
                <div class="display-1 text-primary mb-3">
                    <i class="bi bi-file-earmark-pdf"></i>
                </div>
                <h5 class="fw-semibold mb-1"><?= htmlspecialchars($doc['titulo']) ?></h5>
                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                    <?php if (!empty($doc['especialidad'])): ?>
                        <span class="badge bg-info bg-opacity-10 text-info"><?= htmlspecialchars($doc['especialidad']) ?></span>
                    <?php endif; ?>
                    <span class="badge bg-primary"><?= htmlspecialchars($doc['categoria']) ?></span>
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
                <button class="btn btn-outline-success feedback-btn" data-voto="si" data-id="<?= $doc['id'] ?>">
                    <i class="bi bi-emoji-smile me-1"></i> S&iacute;
                </button>
                <button class="btn btn-outline-warning feedback-btn" data-voto="no" data-id="<?= $doc['id'] ?>">
                    <i class="bi bi-emoji-frown me-1"></i> No
                </button>
            </div>
            <div class="feedback-msg text-center small text-muted mt-2 d-none" id="feedbackMsg"></div>
        </div>

        <div class="text-center mt-3">
            <a href="/publico/archivo?id=<?= $doc['id'] ?>&descargar=1" class="btn btn-outline-primary">
                <i class="bi bi-download me-1"></i> Descargar PDF
            </a>
        </div>

    </div>
</div>

<div class="modal fade" id="docPublicoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content bg-body">
                <div class="modal-header">
                    <h5 class="modal-title text-truncate" id="publicoPreviewTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
            <div class="modal-body p-0">
                <embed id="publicoPreviewEmbed" type="application/pdf" class="w-100" style="min-height: 80vh; border: none;">
            </div>
        </div>
    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
