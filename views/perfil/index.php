<?php $titulo = 'Mi Perfil'; ?>
<?php ob_start(); ?>
<div class="container py-4" style="max-width: 600px;">
    <h4 class="fw-semibold mb-3"><i class="bi bi-person-circle me-2"></i> Mi Perfil</h4>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($paciente): ?>
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small text-muted">Nombre</label>
                    <p class="fw-semibold mb-0"><?= htmlspecialchars($paciente->getNombreCompleto()) ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Cédula</label>
                    <p class="mb-0"><?= htmlspecialchars($paciente->getDocumentoIdentidad() ?? '—') ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">Usuario</label>
                    <p class="mb-0"><?= htmlspecialchars($paciente->getUsername() ?? '—') ?></p>
                </div>

                <hr>

                <form method="post">
                    <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label small">Email</label>
                        <input type="email" id="email" name="email" class="form-control" maxlength="150" value="<?= htmlspecialchars($paciente->getEmail() ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="telefono" class="form-label small">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" maxlength="9" pattern="[0-9]{8,9}" value="<?= htmlspecialchars($paciente->getTelefono() ?? '') ?>">
                    </div>

                    <hr>
                    <h6 class="fw-semibold">Cambiar contraseña</h6>
                    <p class="small text-muted">Dejá los campos vacíos si no querés cambiar.</p>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label small">Nueva contraseña</label>
                            <input type="password" id="password" name="password" class="form-control" minlength="6" placeholder="Mín. 6 caracteres">
                        </div>
                        <div class="col-md-6">
                            <label for="password2" class="form-label small">Repetir contraseña</label>
                            <input type="password" id="password2" name="password2" class="form-control" minlength="6" placeholder="Repetir">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
