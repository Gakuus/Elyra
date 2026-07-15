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

                <form method="post" id="formTraslado">
                    <div style="position:absolute;left:-9999px" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                    </div>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(\Elyra\Infrastructure\Service\SessionManager::getCsrfToken()) ?>">

                    <h6 class="text-muted mb-3"><i class="bi bi-person-badge me-1"></i> Personal</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="conductor_id" class="form-label">Conductor <span class="text-danger">*</span></label>
                            <select name="conductor_id" id="conductor_id" class="form-select" required>
                                <option value="">Seleccionar conductor...</option>
                                <?php foreach ($conductores as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>"<?= (int)($_POST['conductor_id'] ?? 0) === (int)$c['id'] ? ' selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="copiloto_id" class="form-label">Copiloto</label>
                            <select name="copiloto_id" id="copiloto_id" class="form-select">
                                <option value="">Sin copiloto</option>
                                <?php foreach ($copilotos as $cp): ?>
                                    <option value="<?= (int)$cp['id'] ?>"<?= (int)($_POST['copiloto_id'] ?? 0) === (int)$cp['id'] ? ' selected' : '' ?>><?= htmlspecialchars($cp['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="ruta_id" class="form-label">Ruta</label>
                            <select name="ruta_id" id="ruta_id" class="form-select">
                                <option value="">Sin ruta asignada</option>
                                <?php foreach ($rutas as $r): ?>
                                    <option value="<?= (int)$r['id'] ?>" data-distancia="<?= htmlspecialchars((string)($r['distancia_km'] ?? '')) ?>"<?= (int)($_POST['ruta_id'] ?? 0) === (int)$r['id'] ? ' selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <h6 class="text-muted mb-3"><i class="bi bi-box me-1"></i> Elemento a trasladar</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="paciente"<?= ($_POST['tipo'] ?? 'paciente') === 'paciente' ? ' selected' : '' ?>>Paciente</option>
                                <option value="equipamiento"<?= ($_POST['tipo'] ?? '') === 'equipamiento' ? ' selected' : '' ?>>Equipamiento</option>
                                <option value="insumo"<?= ($_POST['tipo'] ?? '') === 'insumo' ? ' selected' : '' ?>>Insumo</option>
                                <option value="organo"<?= ($_POST['tipo'] ?? '') === 'organo' ? ' selected' : '' ?>>Órgano</option>
                            </select>
                        </div>

                        <div class="col-md-8" id="campo-paciente">
                            <label for="paciente_id" class="form-label">Paciente <span class="text-danger">*</span></label>
                            <select name="paciente_id" id="paciente_id" class="form-select">
                                <option value="">Seleccionar paciente...</option>
                                <?php foreach ($pacientes as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>"<?= (int)($_POST['paciente_id'] ?? 0) === (int)$p['id'] ? ' selected' : '' ?>><?= htmlspecialchars($p['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-8 d-none" id="campo-catalogo">
                            <label for="catalogo_elemento_id" class="form-label" id="label-catalogo">Elemento <span class="text-danger">*</span></label>
                            <select name="catalogo_elemento_id" id="catalogo_elemento_id" class="form-select">
                                <option value="" id="opt-default-catalogo">Seleccionar elemento...</option>
                                <?php foreach ($insumos as $i): ?>
                                    <option value="<?= (int)$i['id'] ?>" data-tipo="insumo"<?= (int)($_POST['catalogo_elemento_id'] ?? 0) === (int)$i['id'] ? ' selected' : '' ?>><?= htmlspecialchars($i['nombre']) ?></option>
                                <?php endforeach; ?>
                                <?php foreach ($equipamiento as $e): ?>
                                    <option value="<?= (int)$e['id'] ?>" data-tipo="equipamiento"<?= (int)($_POST['catalogo_elemento_id'] ?? 0) === (int)$e['id'] ? ' selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
                                <?php endforeach; ?>
                                <?php foreach ($organos as $o): ?>
                                    <option value="<?= (int)$o['id'] ?>" data-tipo="organo"<?= (int)($_POST['catalogo_elemento_id'] ?? 0) === (int)$o['id'] ? ' selected' : '' ?>><?= htmlspecialchars($o['nombre']) ?></option>
                                <?php endforeach; ?>
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

                    <h6 class="text-muted mb-3"><i class="bi bi-clock me-1"></i> Tiempos</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="fecha_salida" class="form-label">Fecha de salida <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_salida" id="fecha_salida" class="form-control" required value="<?= htmlspecialchars($_POST['fecha_salida'] ?? date('Y-m-d')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="hora_salida" class="form-label">Hora de salida <span class="text-danger">*</span></label>
                            <input type="time" name="hora_salida" id="hora_salida" class="form-control" required value="<?= htmlspecialchars($_POST['hora_salida'] ?? date('H:i')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="hora_llegada" class="form-label">Hora llegada estimada</label>
                            <input type="time" name="hora_llegada" id="hora_llegada" class="form-control" readonly value="<?= htmlspecialchars($_POST['hora_llegada'] ?? '') ?>">
                            <small class="text-muted" id="llegada-info"></small>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-recalcular" title="Recalcular llegada">
                                <i class="bi bi-arrow-clockwise me-1"></i> Recalcular
                            </button>
                        </div>
                    </div>

                    <h6 class="text-muted mb-3"><i class="bi bi-chat-left-text me-1"></i> Observaciones</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <textarea name="observaciones" class="form-input" rows="2" placeholder="Notas adicionales sobre el traslado (opcional)" maxlength="500"><?= htmlspecialchars($_POST['observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Registrar traslado</button>
                        <a href="traslados" class="btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php ob_start(); ?>
<script nonce="<?= $nonce ?>" src="js/nuevo-traslado.js" defer></script>
<?php $scripts = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
