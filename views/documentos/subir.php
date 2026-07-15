<?php $titulo = 'Subir documento'; ?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <?php if (isset($error)): ?>
            <div class="msg msg-error d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="panel">
            <div class="panel-body">
                <h5 class="fw-bold mb-3"><i class="bi bi-upload me-2 text-primary"></i>Subir nuevo documento</h5>

                <form method="post" enctype="multipart/form-data" id="uploadForm">
                    <div style="position:absolute;left:-9999px" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                    </div>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">T&iacute;tulo del documento <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-input" required minlength="3" maxlength="200" placeholder="Ej: Indicaciones pre-operatorias" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="especialidad" class="form-label">Especialidad</label>
                            <select name="especialidad" id="especialidad" class="form-select">
                                <option value="">Sin especialidad...</option>
                                <?php foreach ($especialidades as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"<?= ($_POST['especialidad'] ?? '') == $cat['id'] ? ' selected' : '' ?>>
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
                                    <option value="<?= $cat['id'] ?>"<?= ($_POST['categoria'] ?? '') == $cat['id'] ? ' selected' : '' ?>>
                                        <?= htmlspecialchars($cat['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipo de documento <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_doc" id="tipoGeneral" value="general"<?= (empty($_POST['paciente']) ? ' checked' : '') ?>>
                                <label class="form-check-label fw-semibold" for="tipoGeneral">
                                    <i class="bi bi-globe text-secondary me-1"></i> General
                                </label>
                                <div class="text-muted small ms-4">Protocolos, gu&iacute;as, formularios administrativos</div>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_doc" id="tipoPaciente" value="paciente"<?= (!empty($_POST['paciente']) ? ' checked' : '') ?>>
                                <label class="form-check-label fw-semibold" for="tipoPaciente">
                                    <i class="bi bi-person text-success me-1"></i> Espec&iacute;fico del paciente
                                </label>
                                <div class="text-muted small ms-4">Historia cl&iacute;nica, an&aacute;lisis, recetas</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="pacienteField" style="<?= empty($_POST['paciente']) ? 'display:none' : '' ?>">
                        <label for="paciente" class="form-label">Paciente <span class="text-danger">*</span></label>
                        <select name="paciente" id="paciente" class="form-select">
                            <option value="">Seleccionar paciente...</option>
                            <?php foreach ($pacientes as $pac): ?>
                                <option value="<?= $pac['id'] ?>"<?= ($_POST['paciente'] ?? '') == $pac['id'] ? ' selected' : '' ?>>
                                    <?= htmlspecialchars($pac['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="archivo" class="form-label">Archivo PDF <span class="text-danger">*</span></label>
                        <div id="dropZone" class="drop-zone">
                            <div class="drop-zone-content" id="dropContent">
                                <i class="bi bi-cloud-upload display-6 text-muted"></i>
                                <p class="mb-1 fw-semibold">Arrastr&aacute; tu PDF ac&aacute; o hac&eacute; clic para seleccionar</p>
                                <p class="text-muted small mb-0">Solo PDF — M&aacute;x. 10 MB</p>
                            </div>
                            <div class="drop-zone-preview d-none" id="dropPreview">
                                <i class="bi bi-filetype-pdf display-6 text-primary"></i>
                                <p class="mb-0 fw-semibold" id="fileName"></p>
                                <p class="text-muted small mb-0" id="fileSize"></p>
                                <button type="button" class="btn btn-sm btn-danger mt-2" id="removeFile">
                                    <i class="bi bi-x"></i> Quitar archivo
                                </button>
                            </div>
                            <input type="file" name="archivo" id="archivo" class="drop-zone-input" accept=".pdf,application/pdf" required>
                        </div>
                        <div class="invalid-feedback" id="fileError"></div>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label">Descripci&oacute;n (opcional)</label>
                        <textarea name="descripcion" id="descripcion" class="form-textarea" rows="3" maxlength="500" placeholder="Breve descripci&oacute;n del documento..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        <div class="form-hint">M&aacute;ximo 500 caracteres.</div>
                    </div>

                    <div class="progress mb-3 d-none" id="progressContainer">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%">0%</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-cloud-upload me-1"></i> Subir documento
                        </button>
                        <a href="documentos" class="btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
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
