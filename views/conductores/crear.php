<?php $titulo = 'Nuevo conductor'; ?>
<?php ob_start(); ?>
<h4 class="mb-3">Nuevo conductor</h4>

<?php if (isset($error)): ?>
    <div class="msg msg-error mb-3"><i class="bi bi-exclamation-triangle-fill"></i> <span><?= htmlspecialchars($error) ?></span></div>
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
                <input type="text" id="nombre" name="nombre" class="form-input w-100" required maxlength="100" placeholder="Nombre">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="apellido" class="form-label">Apellido *</label>
                <input type="text" id="apellido" name="apellido" class="form-input w-100" required maxlength="100" placeholder="Apellido">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="username" class="form-label">Usuario *</label>
                <input type="text" id="username" name="username" class="form-input w-100" required maxlength="50" placeholder="Nombre de usuario">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="password" class="form-label">Contraseña *</label>
                <input type="password" id="password" name="password" class="form-input w-100" required minlength="6" placeholder="Mínimo 6 caracteres">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="form-group">
                <label for="rol" class="form-label">Rol *</label>
                <select id="rol" name="rol" class="form-select w-100" required>
                    <option value="conductor" selected>Conductor</option>
                    <option value="copiloto">Copiloto</option>
                </select>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input w-100" maxlength="150" placeholder="email@ejemplo.com">
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-group">
                <label for="documento_identidad" class="form-label">Cédula de identidad</label>
                <input type="text" id="documento_identidad" name="documento_identidad" class="form-input w-100" maxlength="8" data-numeric placeholder="8 dígitos">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="form-group">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" id="telefono" name="telefono" class="form-input w-100" maxlength="9" data-numeric placeholder="099123456">
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-group">
                <label for="licencia" class="form-label">Licencia profesional</label>
                <select id="licencia" name="licencia" class="form-select w-100">
                    <option value="">Sin licencia</option>
                    <?php foreach ($licencias as $codigo => $descripcion): ?>
                        <option value="<?= $codigo ?>"><?= htmlspecialchars($codigo . ' — ' . $descripcion) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="form-group">
                <label for="licencia_conducir" class="form-label">Licencia de conducir</label>
                <select id="licencia_conducir" name="licencia_conducir" class="form-select w-100">
                    <option value="">Sin licencia</option>
                    <?php foreach ($categoriasLicenciaConducir as $codigo => $descripcion): ?>
                        <option value="<?= $codigo ?>"><?= htmlspecialchars($codigo . ' — ' . $descripcion) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Crear</button>
        <a href="conductores" class="btn">Cancelar</a>
    </div>
</form>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
