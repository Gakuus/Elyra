<?php $titulo = 'Iniciar sesión'; ?>
<?php ob_start(); ?>
<div class="login-wrapper">
    <div class="login-card">
        <div class="login-logo">
            <img src="/img/elyralogo.png" alt="Elyra">
        </div>
        <h2 class="login-title">Hospital de Clínicas</h2>
        <p class="login-subtitle">Sistema de Gestión Hospitalaria</p>

        <?php if (isset($error)): ?>
            <div class="login-alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
            <div class="login-input-group">
                <i class="bi bi-person input-icon"></i>
                <input type="text" id="username" name="username" class="form-control" required autocomplete="username" placeholder="Usuario" aria-label="Usuario" autofocus>
            </div>
            <div class="login-input-group">
                <i class="bi bi-lock input-icon"></i>
                <input type="password" id="password" name="password" class="form-control input-icon-end-padding" required autocomplete="current-password" placeholder="Contraseña" aria-label="Contraseña">
                <button type="button" class="btn btn-link input-icon-end toggle-password" tabindex="-1" data-target="password">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
            <button type="submit" class="btn btn-primary w-100 login-btn">Iniciar Sesión</button>
        </form>
        <p class="text-center mt-3 mb-0 small">
            <a href="/registro" class="text-decoration-none">¿No tenés cuenta? Registrate</a>
        </p>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php $scripts = <<<HTML
<script>
document.querySelectorAll('.toggle-password').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var target = document.getElementById(this.dataset.target);
        if (!target) return;
        var icon = this.querySelector('i');
        if (target.type === 'password') {
            target.type = 'text';
            icon.className = 'bi bi-eye';
        } else {
            target.type = 'password';
            icon.className = 'bi bi-eye-slash';
        }
    });
});
</script>
HTML;
?>
<?php require __DIR__ . '/../layout/base.php'; ?>
