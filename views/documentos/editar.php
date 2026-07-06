<?php $titulo = 'Editar documento'; ?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <?php if (isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="card-title mb-3"><i class="bi bi-pencil me-2 text-primary"></i>Editar documento</h5>

                <form method="post" id="editForm">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">
                    <input type="hidden" name="id" value="<?= $doc['id'] ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">T&iacute;tulo del documento <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-control" required minlength="3" maxlength="200" value="<?= htmlspecialchars($doc['titulo']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categor&iacute;a <span class="text-danger">*</span></label>
                    <select name="categoria" id="categoria" class="form-select" required>
                        <option value="">Seleccionar categor&iacute;a...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"<?= ($doc['categoria_id'] ?? '') == $cat['id'] ? ' selected' : '' ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label">Descripci&oacute;n (opcional)</label>
                        <textarea name="descripcion" id="descripcion" class="form-control" rows="3" maxlength="500" placeholder="Breve descripci&oacute;n del documento..."><?= htmlspecialchars($doc['descripcion'] ?? '') ?></textarea>
                        <div class="form-text text-muted small">M&aacute;ximo 500 caracteres.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Guardar cambios
                        </button>
                        <a href="/documentos" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($doc['filename'])): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body p-4">
                <h6 class="card-title"><i class="bi bi-paperclip me-2 text-muted"></i>Archivo adjunto</h6>
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

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
