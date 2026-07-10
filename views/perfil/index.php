<?php $titulo = 'Mi Perfil'; ?>
<?php ob_start(); ?>
<div class="row justify-content-center">
    <div class="col-lg-8 col-xl-7">

        <div class="panel mb-2">
            <div class="panel-heading"><i class="bi bi-person-circle me-1"></i> Mi Perfil</div>

            <?php if (isset($error)): ?>
                <div class="msg msg-warning m-2 text-center fw-bold" style="font-size: 11px;">
                    <i class="bi bi-exclamation-triangle me-1"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="msg m-2 text-center fw-bold" style="font-size: 11px; background: #dfd; border: 2px solid #080;">
                    <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($user): ?>
                <div class="p-3">
                    <div class="text-center mb-3">
                        <?php $fotoBase64 = $user->getFotoBase64(); ?>
                        <?php if ($fotoBase64): ?>
                            <img src="<?= $fotoBase64 ?>" alt="Foto de perfil" class="avatar" style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="d-inline-flex align-items-center justify-content-center text-white fw-bold avatar" style="width: 100px; height: 100px; font-size: 36px;">
                                <?= htmlspecialchars(mb_strtoupper(mb_substr($user->getNombre(), 0, 1) . mb_substr($user->getApellido(), 0, 1))) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <form method="post" enctype="multipart/form-data">
                        <div style="position:absolute;left:-9999px" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <div class="form-label mb-1">Nombre</div>
                                <div class="form-input w-100" style="background: #f0f0f0;" readonly><?= htmlspecialchars($user->getNombreCompleto()) ?></div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-label mb-1">Cédula de Identidad</div>
                                <?php $ci = $user->getDocumentoIdentidad(); ?>
                                <?php if ($ci): ?>
                                    <div class="form-input w-100" style="background: #f0f0f0;" readonly><?= htmlspecialchars($ci) ?></div>
                                <?php else: ?>
                                    <input type="text" name="documento_identidad" class="form-input w-100" maxlength="8" pattern="\d{8}" inputmode="numeric" placeholder="Ingrese 8 dígitos" value="">
                                    <div class="small" style="font-size: 10px; margin-top: 2px;">Solo se puede establecer una vez.</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="border-top mb-2"></div>
                        <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">

                        <div class="mb-2">
                            <label for="foto" class="form-label mb-1">Foto de perfil</label>
                            <input type="file" id="foto" name="foto" class="form-input w-100" accept="image/jpeg,image/png,image/gif,image/webp">
                            <div class="small" style="font-size: 10px; margin-top: 2px;">JPEG, PNG, GIF o WebP. Máx. 2MB.</div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <label for="email" class="form-label mb-1">Email</label>
                                <input type="email" id="email" name="email" class="form-input w-100" maxlength="150" value="<?= htmlspecialchars($user->getEmail() ?? '') ?>">
                            </div>
                            <div class="col-sm-6">
                                <label for="telefono" class="form-label mb-1">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" class="form-input w-100" maxlength="9" pattern="[0-9]{8,9}" value="<?= htmlspecialchars(method_exists($user, 'getTelefono') ? ($user->getTelefono() ?? '') : '') ?>" placeholder="8 o 9 dígitos">
                            </div>
                        </div>

                        <div class="border-top mb-2"></div>

                        <div class="form-label fw-bold mb-1">Cambiar contraseña</div>
                        <div class="small mb-2" style="font-size: 10px;">Dejá los campos vacíos si no querés cambiar.</div>

                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <label for="password" class="form-label mb-1">Nueva contraseña</label>
                                <input type="password" id="password" name="password" class="form-input w-100" minlength="6" placeholder="Mín. 6 caracteres">
                            </div>
                            <div class="col-sm-6">
                                <label for="password2" class="form-label mb-1">Repetir contraseña</label>
                                <input type="password" id="password2" name="password2" class="form-input w-100" minlength="6" placeholder="Repetir">
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
