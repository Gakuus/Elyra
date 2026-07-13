<?php $titulo = 'Recuperar contraseña'; ?>
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
            <div class="login-box-title">Recuperar contraseña</div>
            <p class="text-muted small text-center mb-3">Ingresá tu email y te enviaremos un enlace para restablecer tu contraseña</p>

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
            <?php endif; ?>

            <?php if (!isset($exito)): ?>
            <form method="post">
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                </div>
                <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">

                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input w-100" required autocomplete="email" placeholder="tu@email.com" autofocus>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-2">Enviar enlace</button>
            </form>
            <?php endif; ?>

            <p class="text-center mt-3 mb-0 small">
                <a href="/login">Volver al inicio de sesión</a>
            </p>
        </div>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
