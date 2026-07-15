<?php $titulo = 'Editar paciente'; ?>
<?php ob_start(); ?>
<h4 class="mb-3">Editar paciente</h4>

<?php if (isset($_GET['error'])): ?>
    <div class="msg msg-error mb-3"><i class="bi bi-exclamation-triangle-fill"></i> <span><?= htmlspecialchars(urldecode($_GET['error'])) ?></span></div>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
    <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre *</label>
                <input type="text" id="nombre" name="nombre" class="form-input w-100" required maxlength="100" value="<?= htmlspecialchars($paciente['nombre']) ?>">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="apellido" class="form-label">Apellido *</label>
                <input type="text" id="apellido" name="apellido" class="form-input w-100" required maxlength="100" value="<?= htmlspecialchars($paciente['apellido']) ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="username" class="form-label">Usuario *</label>
                <input type="text" id="username" name="username" class="form-input w-100" required maxlength="50" value="<?= htmlspecialchars($paciente['username']) ?>">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" class="form-input w-100" minlength="6" placeholder="Dejar vacío para no cambiar">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input w-100" maxlength="150" value="<?= htmlspecialchars($paciente['email']) ?>">
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-group">
                <label for="documento_identidad" class="form-label">Cédula de identidad</label>
                <input type="text" id="documento_identidad" name="documento_identidad" class="form-input w-100" maxlength="8" data-numeric value="<?= htmlspecialchars($paciente['documento_identidad']) ?>">
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-group">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" id="telefono" name="telefono" class="form-input w-100" maxlength="9" data-numeric value="<?= htmlspecialchars($paciente['telefono']) ?>">
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
        <a href="funcionarios" class="btn">Cancelar</a>
    </div>
</form>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
