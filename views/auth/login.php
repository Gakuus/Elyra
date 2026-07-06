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
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Contraseña" aria-label="Contraseña">
            </div>
            <button type="submit" class="btn btn-primary w-100 login-btn">Iniciar Sesión</button>
        </form>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
