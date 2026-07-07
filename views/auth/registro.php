<?php $titulo = 'Registrarse'; ?>
<?php ob_start(); ?>
<div class="login-wrapper">
    <div class="login-card" style="max-width: 500px;">
        <div class="login-logo">
            <img src="/img/elyralogo.png" alt="Elyra">
        </div>
        <h2 class="login-title">Hospital de Cl&iacute;nicas</h2>
        <p class="login-subtitle">Crear cuenta de usuario</p>

        <?php if (isset($error)): ?>
            <div class="login-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nombre" class="form-label small">Nombre <span class="text-danger">*</span></label>
                    <div class="login-input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="nombre" name="nombre" class="form-control" required maxlength="100" placeholder="Nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="apellido" class="form-label small">Apellido <span class="text-danger">*</span></label>
                    <div class="login-input-group">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="apellido" name="apellido" class="form-control" required maxlength="100" placeholder="Apellido" value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label small">Email <span class="text-danger">*</span></label>
                <div class="login-input-group">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control" required maxlength="150" placeholder="correo@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="documento" class="form-label small">C&eacute;dula <span class="text-danger">*</span></label>
                <div class="login-input-group">
                    <i class="bi bi-card-text input-icon"></i>
                    <input type="text" id="documento" name="documento" class="form-control" required maxlength="20" placeholder="N&uacute;mero de c&eacute;dula" value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="username" class="form-label small">Usuario <span class="text-danger">*</span></label>
                    <div class="login-input-group">
                        <i class="bi bi-person-badge input-icon"></i>
                        <input type="text" id="username" name="username" class="form-control" required minlength="3" maxlength="50" placeholder="Usuario" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label small">Tel&eacute;fono</label>
                    <div class="login-input-group">
                        <i class="bi bi-telephone input-icon"></i>
                        <input type="tel" id="telefono" name="telefono" class="form-control" maxlength="20" placeholder="Tel&eacute;fono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="password" class="form-label small">Contrase&ntilde;a <span class="text-danger">*</span></label>
                    <div class="login-input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="M&iacute;n. 6 caracteres">
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="password2" class="form-label small">Repetir contrase&ntilde;a <span class="text-danger">*</span></label>
                    <div class="login-input-group">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="password2" name="password2" class="form-control" required minlength="6" placeholder="Repetir contrase&ntilde;a">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 login-btn">Crear cuenta</button>
        </form>

        <p class="text-center mt-3 mb-0 small">
            <a href="/login" class="text-decoration-none">¿Ya ten&eacute;s cuenta? Inici&aacute; sesi&oacute;n</a>
        </p>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
