<?php $titulo = 'Iniciar sesión'; ?>
<?php ob_start(); ?>
<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <h3 class="text-center mb-4">Hospital de Clínicas</h3>
        <h4 class="text-center mb-4">Elyra — Iniciar sesión</h4>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
