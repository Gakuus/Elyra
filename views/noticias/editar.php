<?php $titulo = 'Editar noticia'; ?>
<?php ob_start(); ?>
<h4 class="mb-3">Editar noticia</h4>

<?php if (isset($error)): ?>
    <div class="msg msg-error mb-3"><i class="bi bi-exclamation-triangle-fill"></i> <span><?= htmlspecialchars($error) ?></span></div>
<?php endif; ?>

<?php if ($noticia): ?>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $noticia['id'] ?>">
    <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
    <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
    </div>

    <div class="form-group mb-3">
        <label for="titulo" class="form-label">Título</label>
        <input type="text" id="titulo" name="titulo" class="form-input w-100" required maxlength="200" value="<?= htmlspecialchars($noticia['titulo']) ?>">
    </div>

    <div class="form-group mb-3">
        <label for="contenido" class="form-label">Contenido</label>
        <textarea id="contenido" name="contenido" class="form-input w-100" rows="6" required><?= htmlspecialchars($noticia['contenido']) ?></textarea>
    </div>

    <div class="form-group mb-3">
        <label class="form-label">Imagen actual</label>
        <?php if ($noticia['imagen']): ?>
            <div class="mb-2">
                <img src="/uploads/noticias/<?= htmlspecialchars($noticia['imagen']) ?>" alt="" style="max-height:120px;border:1px solid #ddd;border-radius:4px;object-fit:cover;">
            </div>
        <?php else: ?>
            <p class="small text-muted">Sin imagen</p>
        <?php endif; ?>
        <label for="imagen" class="form-label">Reemplazar imagen (opcional)</label>
        <input type="file" id="imagen" name="imagen" class="form-input w-100" accept="image/jpeg,image/png,image/webp,image/gif">
        <p class="small text-muted mt-1">JPG, PNG, WebP o GIF. Máximo 5 MB.</p>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="/noticias" class="btn">Cancelar</a>
    </div>
</form>
<?php endif; ?>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
