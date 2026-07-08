<?php $titulo = 'Registrarse'; ?>
<?php ob_start(); ?>
<div class="login-page">
    <div class="login-box" style="max-width: 500px;">
        <div class="login-box-header">
            Hospital de Clínicas
        </div>
        <div class="login-box-body">
            <div class="login-box-logo">
                <img src="/img/elyralogo.png" alt="Elyra">
            </div>
            <div class="login-box-title">Crear cuenta de usuario</div>
            <p class="text-muted small text-center mb-3">Completá tus datos para registrarte</p>

            <?php if (isset($error)): ?>
                <div class="msg msg-error mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">

                <div class="d-flex gap-2">
                    <div class="form-group w-100">
                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" id="nombre" name="nombre" class="form-input w-100" required maxlength="100" placeholder="Nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                    </div>
                    <div class="form-group w-100">
                        <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                        <input type="text" id="apellido" name="apellido" class="form-input w-100" required maxlength="100" placeholder="Apellido" value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-input w-100" required maxlength="150" placeholder="correo@gmail.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="documento" class="form-label">Cédula <span class="text-danger">*</span></label>
                    <input type="text" id="documento" name="documento" class="form-input w-100" required maxlength="8" pattern="[0-9]{8}" placeholder="Número de cédula (8 dígitos)" value="<?= htmlspecialchars($_POST['documento'] ?? '') ?>">
                </div>

                <div class="d-flex gap-2">
                    <div class="form-group w-100">
                        <label for="username" class="form-label">Usuario <span class="text-danger">*</span></label>
                        <input type="text" id="username" name="username" class="form-input w-100" required minlength="3" maxlength="50" placeholder="Usuario" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                    </div>
                    <div class="form-group w-100">
                        <label for="telefono" class="form-label">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" class="form-input w-100" maxlength="9" pattern="[0-9]{8,9}" placeholder="098765432" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class="form-group w-100">
                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-1">
                            <input type="password" id="password" name="password" class="form-input w-100" required minlength="6" placeholder="Mín. 6 caracteres">
                            <button type="button" class="btn btn-sm btn-toggle-pw" tabindex="-1" onclick="togglePw('password', 'pwIcon')" style="flex-shrink:0;">
                                <i class="bi bi-eye-slash" id="pwIcon"></i>
                            </button>
                        </div>
                    </div>
                    <div class="form-group w-100">
                        <label for="password2" class="form-label">Repetir contraseña <span class="text-danger">*</span></label>
                        <div class="d-flex align-items-center gap-1">
                            <input type="password" id="password2" name="password2" class="form-input w-100" required minlength="6" placeholder="Repetir contraseña">
                            <button type="button" class="btn btn-sm btn-toggle-pw" tabindex="-1" onclick="togglePw('password2', 'pwIcon2')" style="flex-shrink:0;">
                                <i class="bi bi-eye-slash" id="pwIcon2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Crear cuenta</button>
            </form>

            <p class="text-center mt-3 mb-0 small">
                <a href="/login">¿Ya tenés cuenta? Iniciá sesión</a>
            </p>
        </div>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php $scripts = <<<HTML
<script>
function togglePw(id, iconId) {
    var pw = document.getElementById(id);
    var icon = document.getElementById(iconId);
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.className = 'bi bi-eye';
    } else {
        pw.type = 'password';
        icon.className = 'bi bi-eye-slash';
    }
}
</script>
HTML;
?>
<?php require __DIR__ . '/../layout/base.php'; ?>
