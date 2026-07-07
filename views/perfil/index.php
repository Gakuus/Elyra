<?php $titulo = 'Mi Perfil'; ?>
<?php ob_start(); ?>
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <div class="win-panel mb-2">
            <div class="win-titlebar"><i class="bi bi-person-circle me-1"></i> Mi Perfil</div>

            <?php if (isset($error)): ?>
                <div class="win-msg win-msg-warning m-2 text-center fw-bold" style="font-size: 11px;">
                    <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="win-msg m-2 text-center fw-bold" style="font-size: 11px; background: #dfd; border: 2px solid #080;">
                    <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($user): ?>
                <div class="p-3">
                    <div class="row g-2 mb-2">
                        <div class="col-sm-6">
                            <div class="win-label mb-1">Nombre</div>
                            <div class="win-field w-100" style="background: #f0f0f0;" readonly><?= htmlspecialchars($user->getNombreCompleto()) ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="win-label mb-1">Cédula</div>
                            <div class="win-field w-100" style="background: #f0f0f0;" readonly><?= htmlspecialchars($user->getDocumentoIdentidad() ?? '—') ?></div>
                        </div>
                    </div>

                    <div class="win-separator mb-2"></div>

                    <form method="post">
                        <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">

                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <label for="email" class="win-label mb-1">Email</label>
                                <input type="email" id="email" name="email" class="win-field w-100" maxlength="150" value="<?= htmlspecialchars($user->getEmail() ?? '') ?>">
                            </div>
                            <div class="col-sm-6">
                                <label for="telefono" class="win-label mb-1">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" class="win-field w-100" maxlength="9" pattern="[0-9]{8,9}" value="<?= htmlspecialchars(method_exists($user, 'getTelefono') ? ($user->getTelefono() ?? '') : '') ?>">
                            </div>
                        </div>

                        <div class="win-separator mb-2"></div>

                        <div class="win-label fw-bold mb-1">Cambiar contraseña</div>
                        <div class="win-text small mb-2" style="font-size: 10px;">Dejá los campos vacíos si no querés cambiar.</div>

                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <label for="password" class="win-label mb-1">Nueva contraseña</label>
                                <input type="password" id="password" name="password" class="win-field w-100" minlength="6" placeholder="Mín. 6 caracteres">
                            </div>
                            <div class="col-sm-6">
                                <label for="password2" class="win-label mb-1">Repetir contraseña</label>
                                <input type="password" id="password2" name="password2" class="win-field w-100" minlength="6" placeholder="Repetir">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="win-btn win-btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
