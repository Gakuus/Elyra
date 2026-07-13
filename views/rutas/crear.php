<?php $titulo = 'Nueva ruta'; ?>
<?php ob_start(); ?>
<h4 class="mb-3">Nueva ruta</h4>

<?php if (isset($error)): ?>
    <div class="msg msg-error mb-3"><i class="bi bi-exclamation-triangle-fill"></i> <span><?= htmlspecialchars($error) ?></span></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
    <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
    </div>

    <div class="form-group mb-3">
        <label for="nombre" class="form-label">Nombre de la ruta *</label>
        <input type="text" id="nombre" name="nombre" class="form-input w-100" required maxlength="100" placeholder="Ej: Ruta 1 - Hospital a Sanatorio">
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="origen" class="form-label">Origen *</label>
                <input type="text" id="origen" name="origen" class="form-input w-100" required maxlength="200" placeholder="Ej: Hospital de Clínicas">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="destino" class="form-label">Destino *</label>
                <input type="text" id="destino" name="destino" class="form-input w-100" required maxlength="200" placeholder="Ej: Sanatorio Español">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="distancia_km" class="form-label">Distancia (km)</label>
                <input type="number" id="distancia_km" name="distancia_km" class="form-input w-100" step="0.1" min="0" placeholder="Ej: 12.5">
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea id="descripcion" name="descripcion" class="form-input w-100" rows="3" placeholder="Descripción de la ruta (opcional)"></textarea>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Crear ruta</button>
        <a href="/rutas" class="btn">Cancelar</a>
    </div>
</form>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
