<?php $titulo = $modo === 'crear' ? 'Nuevo funcionario' : 'Editar funcionario'; ?>
<?php ob_start(); ?>
<h4 class="mb-3"><?= $modo === 'crear' ? 'Nuevo funcionario' : 'Editar funcionario' ?></h4>

<?php if (isset($error)): ?>
    <div class="msg msg-error mb-3"><i class="bi bi-exclamation-triangle-fill"></i> <span><?= htmlspecialchars($error) ?></span></div>
<?php endif; ?>

<?php
$f = $funcionario ?? [];
$f['nombre'] = $f['nombre'] ?? ($form['nombre'] ?? '');
$f['apellido'] = $f['apellido'] ?? ($form['apellido'] ?? '');
$f['username'] = $f['username'] ?? ($form['username'] ?? '');
$f['email'] = $f['email'] ?? ($form['email'] ?? '');
$f['rol'] = $f['rol'] ?? ($form['rol'] ?? 'admin');
$f['licencia'] = $f['licencia'] ?? ($form['licencia'] ?? '');
$f['licencia_conducir'] = $f['licencia_conducir'] ?? ($form['licencia_conducir'] ?? '');
$f['telefono'] = $f['telefono'] ?? ($form['telefono'] ?? '');
$f['documento_identidad'] = $f['documento_identidad'] ?? ($form['documento_identidad'] ?? '');
?>

<form method="post">
    <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
    <div style="position:absolute;left:-9999px" aria-hidden="true">
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="nombre" class="form-label">Nombre *</label>
                <input type="text" id="nombre" name="nombre" class="form-input w-100" required maxlength="100" value="<?= htmlspecialchars($f['nombre']) ?>">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="apellido" class="form-label">Apellido *</label>
                <input type="text" id="apellido" name="apellido" class="form-input w-100" required maxlength="100" value="<?= htmlspecialchars($f['apellido']) ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="username" class="form-label">Usuario *</label>
                <input type="text" id="username" name="username" class="form-input w-100" required maxlength="50" value="<?= htmlspecialchars($f['username']) ?>">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="password" class="form-label"><?= $modo === 'crear' ? 'Contraseña' : 'Nueva contraseña (dejar vacío para mantener)' ?></label>
                <input type="password" id="password" name="password" class="form-input w-100" <?= $modo === 'crear' ? '' : '' ?> minlength="6" <?= $modo === 'crear' ? 'placeholder="Cédula por defecto (si no es admin)"' : 'placeholder="Dejar vacío para mantener actual"' ?>>
                <?php if ($modo === 'crear'): ?>
                    <small class="text-muted">Obligatoria para admin/superadmin. Para otros roles se usa la cédula por defecto.</small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="rol" class="form-label">Rol *</label>
                <select id="rol" name="rol" class="form-select w-100" required>
                    <?php
                    $rolLabels = [
                        'superadmin' => 'Super Administrador',
                        'admin' => 'Administrador',
                        'medico' => 'Médico',
                        'enfermero' => 'Enfermero/a',
                        'tecnico' => 'Técnico',
                        'recepcionista' => 'Recepcionista',
                        'farmaceutico' => 'Farmacéutico',
                    ];
                    foreach ($roles as $r): ?>
                        <option value="<?= $r ?>"<?= $f['rol'] === $r ? ' selected' : '' ?>><?= $rolLabels[$r] ?? ucfirst($r) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-input w-100" maxlength="150" value="<?= htmlspecialchars($f['email']) ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="documento_identidad" class="form-label">Cédula de identidad</label>
                <input type="text" id="documento_identidad" name="documento_identidad" class="form-input w-100" required maxlength="8" data-numeric placeholder="8 dígitos" value="<?= htmlspecialchars($f['documento_identidad']) ?>">
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" id="telefono" name="telefono" class="form-input w-100" maxlength="9" data-numeric placeholder="8-9 dígitos" value="<?= htmlspecialchars($f['telefono']) ?>">
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="licencia" class="form-label">Licencia profesional</label>
                <select id="licencia" name="licencia" class="form-select w-100">
                    <option value="">Sin licencia</option>
                    <?php foreach ($licencias as $codigo => $descripcion): ?>
                        <option value="<?= $codigo ?>"<?= $f['licencia'] === $codigo ? ' selected' : '' ?>><?= htmlspecialchars($codigo . ' — ' . $descripcion) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="form-group">
                <label for="licencia_conducir" class="form-label">Licencia de conducir</label>
                <select id="licencia_conducir" name="licencia_conducir" class="form-select w-100">
                    <option value="">Sin licencia</option>
                    <?php foreach ($categoriasLicenciaConducir as $codigo => $descripcion): ?>
                        <option value="<?= $codigo ?>"<?= $f['licencia_conducir'] === $codigo ? ' selected' : '' ?>><?= htmlspecialchars($codigo . ' — ' . $descripcion) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= $modo === 'crear' ? 'Crear funcionario' : 'Guardar cambios' ?></button>
        <a href="funcionarios" class="btn">Cancelar</a>
    </div>
</form>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
