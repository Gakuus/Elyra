<?php $titulo = 'Editar documento'; ?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <?php if (isset($error)): ?>
            <div class="msg msg-error d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="msg msg-success d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <div class="panel">
            <div class="panel-body">
                <h5 class="fw-bold mb-3"><i class="bi bi-pencil me-2 text-primary"></i>Editar documento</h5>

                <form method="post" id="editForm">
                    <div style="position:absolute;left:-9999px" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                    </div>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">T&iacute;tulo del documento <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-input" required minlength="3" maxlength="200" value="<?= htmlspecialchars($doc['titulo']) ?>">
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="especialidad" class="form-label">Especialidad</label>
                            <select name="especialidad" id="especialidad" class="form-select">
                                <option value="">Sin especialidad...</option>
                                <?php foreach ($especialidades as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"<?= ($doc['especialidad_id'] ?? '') == $cat['id'] ? ' selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col">
                            <label for="categoria" class="form-label">Tipo de documento <span class="text-danger">*</span></label>
                            <select name="categoria" id="categoria" class="form-select" required>
                                <option value="">Seleccionar tipo...</option>
                                <?php foreach ($tiposDocumento as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"<?= ($doc['categoria_id'] ?? '') == $cat['id'] ? ' selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php $esPaciente = !empty($doc['paciente_id']); ?>
                    <div class="mb-3">
                        <label class="form-label">Tipo de documento</label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_doc" id="tipoGeneral" value="general"<?= !$esPaciente ? ' checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="tipoGeneral">
                                    <i class="bi bi-globe text-secondary me-1"></i> General
                                </label>
                                <div class="text-muted small ms-4">Protocolos, gu&iacute;as, formularios administrativos</div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_doc" id="tipoPaciente" value="paciente"<?= $esPaciente ? ' checked' : '' ?>>
                                <label class="form-check-label fw-semibold" for="tipoPaciente">
                                    <i class="bi bi-person text-success me-1"></i> Espec&iacute;fico del paciente
                                </label>
                                <div class="text-muted small ms-4">Historia cl&iacute;nica, an&aacute;lisis, recetas</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="pacienteField" style="<?= !$esPaciente ? 'display:none' : '' ?>">
                        <label for="paciente" class="form-label">Paciente <span class="text-danger">*</span></label>
                        <select name="paciente" id="paciente" class="form-select">
                            <option value="">Seleccionar paciente...</option>
                            <?php foreach ($pacientes as $pac): ?>
                                <option value="<?= $pac['id'] ?>"<?= ($doc['paciente_id'] ?? '') == $pac['id'] ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($pac['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label">Descripci&oacute;n (opcional)</label>
                        <textarea name="descripcion" id="descripcion" class="form-textarea" rows="3" maxlength="500" placeholder="Breve descripci&oacute;n del documento..."><?= htmlspecialchars($doc['descripcion'] ?? '') ?></textarea>
                        <div class="form-hint">M&aacute;ximo 500 caracteres.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Guardar cambios
                        </button>
                        <a href="documentos" class="btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($doc['filename'])): ?>
        <div class="panel mt-3">
            <div class="panel-body">
                <h6 class="fw-bold"><i class="bi bi-paperclip me-2 text-muted"></i>Archivo adjunto</h6>
                <p class="mb-0 small text-muted">
                    <i class="bi bi-filetype-pdf me-1"></i>
                    <?= htmlspecialchars($doc['filename']) ?>
                    <span class="mx-2">&middot;</span>
                    Subido el <?= htmlspecialchars($doc['subido']) ?>
                </p>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php $scripts = <<<HTML
<script nonce="{$nonce}">
document.querySelectorAll('input[name="tipo_doc"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const field = document.getElementById('pacienteField');
        const select = document.getElementById('paciente');
        if (document.getElementById('tipoPaciente').checked) {
            field.style.display = '';
            select.required = true;
        } else {
            field.style.display = 'none';
            select.required = false;
            select.value = '';
        }
    });
});
</script>
HTML;
?>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
