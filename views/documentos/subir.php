<?php $titulo = 'Subir documento'; ?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <?php if (isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="card-title mb-3"><i class="bi bi-upload me-2 text-primary"></i>Subir nuevo documento</h5>

                <form method="post" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">T&iacute;tulo del documento <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-control" required minlength="3" maxlength="200" placeholder="Ej: Indicaciones pre-operatorias" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
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
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeFile">
                                    <i class="bi bi-x"></i> Quitar archivo
                                </button>
                            </div>
                            <input type="file" name="archivo" id="archivo" class="drop-zone-input" accept=".pdf,application/pdf" required>
                        </div>
                        <div class="invalid-feedback" id="fileError"></div>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label">Descripci&oacute;n (opcional)</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3" maxlength="500" placeholder="Breve descripci&oacute;n del documento..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        <div class="form-text text-muted small">M&aacute;ximo 500 caracteres.</div>
                    </div>

                    <div class="progress mb-3 d-none" id="progressContainer">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%">0%</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-cloud-upload me-1"></i> Subir documento
                        </button>
                        <a href="/documentos" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
