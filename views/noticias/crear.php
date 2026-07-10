<?php $titulo = 'Nueva noticia'; ?>
<?php ob_start(); ?>
<h4 class="mb-3">Nueva noticia</h4>

<?php if (isset($error)): ?>
    <div class="msg msg-error mb-3"><i class="bi bi-exclamation-triangle-fill"></i> <span><?= htmlspecialchars($error) ?></span></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
    <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
    </div>

    <div class="form-group mb-3">
        <label for="titulo" class="form-label">Título</label>
        <input type="text" id="titulo" name="titulo" class="form-input w-100" required maxlength="200" placeholder="Título de la noticia">
    </div>

    <div class="form-group mb-3">
        <label for="contenido" class="form-label">Contenido</label>
        <textarea id="contenido" name="contenido" class="form-input w-100" rows="6" required placeholder="Contenido de la noticia"></textarea>
    </div>

    <div class="form-group mb-3">
        <label for="imagen" class="form-label">Imagen (opcional)</label>
        <input type="file" id="imagen" name="imagen" class="form-input w-100" accept="image/jpeg,image/png,image/webp,image/gif">
        <p class="small text-muted mt-1">JPG, PNG, WebP o GIF. Máximo 5 MB.</p>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Publicar</button>
        <a href="/noticias" class="btn">Cancelar</a>
    </div>
</form>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
