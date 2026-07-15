<?php $titulo = 'Conductores'; ?>
<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Conductores</h4>
    <a href="conductores/crear" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo conductor</a>
</div>

<?php if (isset($_GET['creado'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Conductor creado correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['actualizado'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Conductor actualizado correctamente.</div>
<?php endif; ?>

<form method="get" class="d-flex gap-2 mb-3">
    <select name="activo" class="form-select" style="max-width:180px" onchange="this.form.submit()" aria-label="Filtrar por estado">
        <option value="">Todos</option>
        <option value="1"<?= $filtroActivo === '1' ? ' selected' : '' ?>>Activos</option>
        <option value="0"<?= $filtroActivo === '0' ? ' selected' : '' ?>>Inactivos</option>
    </select>
    <div class="position-relative flex-grow-1">
        <input type="text" name="buscar" class="form-input ps-4" placeholder="Buscar por nombre, usuario o cédula..." value="<?= htmlspecialchars($buscar) ?>" aria-label="Buscar conductor">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small"></i>
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Buscar</button>
</form>

<div class="d-flex gap-3 mb-3">
    <div class="panel" style="flex:1">
        <div class="panel-heading">Total</div>
        <div class="panel-body text-center"><span class="fs-4 fw-bold"><?= $total ?></span></div>
    </div>
    <div class="panel" style="flex:1">
        <div class="panel-heading">Activos</div>
        <div class="panel-body text-center"><span class="fs-4 fw-bold text-success"><?= $activos ?></span></div>
    </div>
    <div class="panel" style="flex:1">
        <div class="panel-heading">Inactivos</div>
        <div class="panel-body text-center"><span class="fs-4 fw-bold text-muted"><?= $total - $activos ?></span></div>
    </div>
</div>

<?php if (empty($conductores)): ?>
    <div class="text-center p-4 text-muted">No hay conductores registrados.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Apellido y Nombre</th>
                    <th>Rol</th>
                    <th>Usuario</th>
                    <th>Licencia</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($conductores as $c): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre']) ?></td>
                    <td>
                        <?php if ($c['rol'] === 'copiloto'): ?>
                            <span class="badge bg-warning text-dark">Copiloto</span>
                        <?php else: ?>
                            <span class="badge bg-info text-white">Conductor</span>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= htmlspecialchars($c['username']) ?></td>
                    <td class="small"><?= htmlspecialchars($c['licencia'] ?: '—') ?></td>
                    <td class="small"><?= htmlspecialchars($c['telefono'] ?: '—') ?></td>
                    <td>
                        <?php if ($c['activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="conductores/editar?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <?php if ($c['activo']): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Desactivar"
                                onclick="abrirModalDesactivar(<?= $c['id'] ?>, '<?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre'], ENT_QUOTES) ?>')">
                                <i class="bi bi-person-x"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-outline-success" title="Reactivar"
                                onclick="abrirModalReactivar(<?= $c['id'] ?>, '<?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre'], ENT_QUOTES) ?>')">
                                <i class="bi bi-person-check"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div class="modal-overlay" id="modalDesactivar">
    <div class="modal-box">
        <div class="modal-header">
            <span>Desactivar conductor / copiloto</span>
            <span class="modal-close" onclick="cerrarModalDesactivar()">&times;</span>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro que deseas desactivar a <strong id="nombreConductor"></strong>?</p>
            <p class="small" style="color:#666">No podrá ser asignado a traslados hasta que sea reactivado.</p>
        </div>
        <div class="modal-footer">
            <form method="post" id="formDesactivar" action="conductores/desactivar">
                <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
                <input type="hidden" name="id" id="idConductor" value="">
                <button type="submit" class="btn btn-danger btn-sm">Desactivar</button>
                <button type="button" class="btn btn-sm" onclick="cerrarModalDesactivar()">Cancelar</button>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalReactivar">
    <div class="modal-box">
        <div class="modal-header">
            <span>Reactivar conductor / copiloto</span>
            <span class="modal-close" onclick="cerrarModalReactivar()">&times;</span>
        </div>
        <div class="modal-body">
            <p>¿Deseas reactivar a <strong id="nombreConductorReactivar"></strong>?</p>
        </div>
        <div class="modal-footer">
            <form method="post" id="formReactivar" action="conductores/reactivar">
                <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
                <input type="hidden" name="id" id="idConductorReactivar" value="">
                <button type="submit" class="btn btn-success btn-sm">Reactivar</button>
                <button type="button" class="btn btn-sm" onclick="cerrarModalReactivar()">Cancelar</button>
            </form>
        </div>
    </div>
</div>

<?php if (isset($_GET['desactivado'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Conductor desactivado correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['reactivado'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Conductor reactivado correctamente.</div>
<?php endif; ?>

<script nonce="<?= $nonce ?>">
function abrirModalDesactivar(id, nombre) {
    document.getElementById('idConductor').value = id;
    document.getElementById('nombreConductor').textContent = nombre;
    document.getElementById('modalDesactivar').classList.add('open');
}
function cerrarModalDesactivar() {
    document.getElementById('modalDesactivar').classList.remove('open');
}
function abrirModalReactivar(id, nombre) {
    document.getElementById('idConductorReactivar').value = id;
    document.getElementById('nombreConductorReactivar').textContent = nombre;
    document.getElementById('modalReactivar').classList.add('open');
}
function cerrarModalReactivar() {
    document.getElementById('modalReactivar').classList.remove('open');
}
</script>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
