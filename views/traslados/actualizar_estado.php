<?php
$titulo = 'Actualizar estado - ' . htmlspecialchars($t['codigo']);
$eActual = $estados[$t['estado']];
?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <a href="traslados/ver?id=<?= $t['id'] ?>" class="btn btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i> Volver al detalle</a>

        <div class="panel">
            <div class="panel-body">
                <h4 class="fw-semibold mb-3">Actualizar estado</h4>

                <div class="mb-4">
                    <span class="text-muted">Traslado</span>
                    <span class="fw-semibold"><?= htmlspecialchars($t['codigo']) ?></span>
                    &middot;
                    <?= htmlspecialchars($t['elemento_descripcion'] ?? '-') ?>
                    &middot;
                    <span class="badge badge-<?= htmlspecialchars($t['estado']) ?>"><?= $eActual['label'] ?></span>
                </div>

                <?php if (empty($allowed)): ?>
                    <div class="alert alert-info mb-0">Este traslado ya está en su estado final. No hay acciones disponibles.</div>
                <?php else: ?>
                    <form method="POST" action="traslados/actualizar-estado" class="row g-3">
                        <div style="position:absolute;left:-9999px" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                        </div>
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(\Elyra\Infrastructure\Service\SessionManager::getCsrfToken()) ?>">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">

                        <div class="col-12">
                            <label class="form-label fw-semibold">Seleccionar nuevo estado</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($allowed as $est): ?>
                                    <?php $info = $estados[$est]; ?>
                                    <button type="submit" name="estado" value="<?= $est ?>" class="btn px-4 py-2"
                                        data-estado="<?= htmlspecialchars($est) ?>"
                                        onclick="return confirmarCambioEstado(this)">
                                        <i class="bi bi-arrow-right-circle me-1"></i> <?= $info['label'] ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-12 d-none" id="campo-motivo">
                            <label for="motivo" class="form-label fw-semibold">Motivo de cancelación <span class="text-danger">*</span></label>
                            <textarea name="motivo" id="motivo" class="form-input" rows="3" placeholder="Motivo requerido para cancelar..." maxlength="500"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="observacion" class="form-label fw-semibold">Observaciones (opcional)</label>
                            <textarea name="observacion" id="observacion" class="form-input" rows="2" placeholder="Nota sobre este cambio de estado..." maxlength="500"></textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="btn-confirmar">
                                <i class="bi bi-check-lg me-1"></i> Confirmar cambio
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php ob_start(); ?>
<script nonce="<?= $nonce ?>">
function confirmarCambioEstado(btn) {
    var estado = btn.getAttribute('data-estado');
    var campoMotivo = document.getElementById('campo-motivo');
    var motivoInput = document.getElementById('motivo');
    if (estado === 'cancelado') {
        campoMotivo.classList.remove('d-none');
        motivoInput.required = true;
        motivoInput.focus();
        return false;
    }
    motivoInput.required = false;
    return confirm('¿Estás seguro de cambiar a «' + btn.textContent.trim() + '»?');
}
</script>
<?php $scripts = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
