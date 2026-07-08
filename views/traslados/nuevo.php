<?php $titulo = 'Nuevo traslado'; ?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10">

        <?php if (isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="panel">
            <div class="panel-body">
                <h5 class="card-title mb-3"><i class="bi bi-plus-circle me-2 text-primary"></i>Registrar nuevo traslado</h5>

                <form method="post">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

                    <h6 class="text-muted mb-3"><i class="bi bi-person-badge me-1"></i> Personal</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="conductor" class="form-label">Conductor <span class="text-danger">*</span></label>
                            <select name="conductor" id="conductor" class="form-select" required>
                                <option value="">Seleccionar conductor...</option>
                                <?php foreach ($conductores as $c): ?>
                                    <option value="<?= htmlspecialchars($c) ?>"<?= ($_POST['conductor'] ?? '') === $c ? ' selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="copiloto" class="form-label">Copiloto</label>
                            <input type="text" name="copiloto" id="copiloto" class="form-control" placeholder="Nombre del copiloto" value="<?= htmlspecialchars($_POST['copiloto'] ?? '') ?>">
                        </div>
                    </div>

                    <h6 class="text-muted mb-3"><i class="bi bi-box me-1"></i> Elemento</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label for="elemento" class="form-label">Elemento a trasladar <span class="text-danger">*</span></label>
                            <input type="text" name="elemento" id="elemento" class="form-control" placeholder="Ej: Paciente Juan Pérez" required value="<?= htmlspecialchars($_POST['elemento'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="paciente"<?= ($_POST['tipo'] ?? 'paciente') === 'paciente' ? ' selected' : '' ?>>Paciente</option>
                                <option value="equipamiento"<?= ($_POST['tipo'] ?? '') === 'equipamiento' ? ' selected' : '' ?>>Equipamiento</option>
                                <option value="insumo"<?= ($_POST['tipo'] ?? '') === 'insumo' ? ' selected' : '' ?>>Insumo</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="text-muted mb-3"><i class="bi bi-geo-alt me-1"></i> Recorrido</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="origen" class="form-label">Origen <span class="text-danger">*</span></label>
                            <select name="origen" id="origen" class="form-select" required>
                                <option value="">Seleccionar origen...</option>
                                <?php foreach ($ubicaciones as $u): ?>
                                    <option value="<?= htmlspecialchars($u) ?>"<?= ($_POST['origen'] ?? '') === $u ? ' selected' : '' ?>><?= htmlspecialchars($u) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="destino" class="form-label">Destino <span class="text-danger">*</span></label>
                            <select name="destino" id="destino" class="form-select" required>
                                <option value="">Seleccionar destino...</option>
                                <?php foreach ($ubicaciones as $u): ?>
                                    <option value="<?= htmlspecialchars($u) ?>"<?= ($_POST['destino'] ?? '') === $u ? ' selected' : '' ?>><?= htmlspecialchars($u) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="ruta" class="form-label">Ruta</label>
                        <select name="ruta" id="ruta" class="form-select">
                            <option value="">Sin ruta asignada</option>
                            <?php foreach ($rutas as $r): ?>
                                <option value="<?= htmlspecialchars($r) ?>"<?= ($_POST['ruta'] ?? '') === $r ? ' selected' : '' ?>><?= htmlspecialchars($r) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h6 class="text-muted mb-3"><i class="bi bi-clock me-1"></i> Tiempos estimados</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="fecha_salida" class="form-label">Fecha de salida <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_salida" id="fecha_salida" class="form-control" required value="<?= htmlspecialchars($_POST['fecha_salida'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="hora_salida" class="form-label">Hora de salida <span class="text-danger">*</span></label>
                            <input type="time" name="hora_salida" id="hora_salida" class="form-control" required value="<?= htmlspecialchars($_POST['hora_salida'] ?? date('H:i')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="hora_llegada" class="form-label">Hora llegada estimada</label>
                            <input type="time" name="hora_llegada" id="hora_llegada" class="form-control" value="<?= htmlspecialchars($_POST['hora_llegada'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Registrar traslado</button>
                        <a href="/traslados" class="btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
