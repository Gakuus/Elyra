<?php $titulo = 'Iniciar sesión'; ?>
<?php ob_start(); ?>
<div class="login-page">
    <div class="login-box">
        <div class="login-box-header">
            Hospital de Clínicas
        </div>
        <div class="login-box-body">
            <div class="login-box-logo">
                <img src="/img/elyralogo.png" alt="Elyra">
            </div>
            <div class="login-box-title">Sistema de Gestión Hospitalaria</div>
            <p class="text-muted small text-center mb-3">Ingresá tus credenciales para acceder</p>

            <?php if (isset($error)): ?>
                <div class="msg msg-error mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">

                <div class="form-group">
                    <label for="username" class="form-label">Usuario</label>
                    <input type="text" id="username" name="username" class="form-input w-100" required autocomplete="username" placeholder="Ingrese su usuario" autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="d-flex align-items-center gap-1">
                        <input type="password" id="password" name="password" class="form-input w-100" required autocomplete="current-password" placeholder="Ingrese su contraseña">
                        <button type="button" class="btn btn-sm" tabindex="-1" onclick="togglePassword()" style="flex-shrink:0;">
                            <i class="bi bi-eye-slash" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Iniciar Sesión</button>
            </form>

            <p class="text-center mt-3 mb-0 small">
                <a href="/registro">¿No tenés cuenta? Registrate</a>
            </p>
        </div>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php $scripts = <<<HTML
<script>
function togglePassword() {
    var pw = document.getElementById('password');
    var icon = document.getElementById('pwIcon');
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
