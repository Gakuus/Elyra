<?php $titulo = 'Nueva contraseña'; ?>
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
            <div class="login-box-title">Establecer nueva contraseña</div>
            <p class="text-muted small text-center mb-3">Ingresá tu nueva contraseña</p>

            <?php if (isset($error)): ?>
                <div class="msg msg-error mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if (isset($exito)): ?>
                <div class="msg msg-success mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><?= htmlspecialchars($exito) ?></span>
                </div>
                <p class="text-center mt-3 mb-0 small">
                    <a href="/login">Ir al inicio de sesión</a>
                </p>
            <?php else: ?>
            <form method="post">
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                </div>
                <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">

                <div class="form-group">
                    <label for="password" class="form-label">Nueva contraseña</label>
                    <div class="d-flex align-items-center gap-1">
                        <input type="password" id="password" name="password" class="form-input w-100" required minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres" autofocus>
                        <button type="button" class="btn btn-sm" tabindex="-1" onclick="togglePassword()" style="flex-shrink:0;">
                            <i class="bi bi-eye-slash" id="pwIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password2" class="form-label">Repetir contraseña</label>
                    <input type="password" id="password2" name="password2" class="form-input w-100" required minlength="6" autocomplete="new-password" placeholder="Repetí tu contraseña">
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Guardar contraseña</button>
            </form>
            <?php endif; ?>

            <p class="text-center mt-3 mb-0 small">
                <a href="/login">Volver al inicio de sesión</a>
            </p>
        </div>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php $scripts = <<<HTML
<script nonce="{$nonce}">
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
