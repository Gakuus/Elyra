<?php $titulo = 'Funcionarios'; ?>
<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Funcionarios</h4>
    <a href="/funcionarios/crear" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo funcionario</a>
</div>

<?php if (isset($_GET['creado'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Funcionario creado correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['actualizado'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Funcionario actualizado correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['desactivado'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Funcionario desactivado correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['reactivado'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Funcionario reactivado correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="msg msg-error mb-3"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars(urldecode($_GET['error'])) ?></div>
<?php endif; ?>

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
        <div class="panel-body text-center"><span class="fs-4 fw-bold text-muted"><?= $inactivos ?></span></div>
    </div>
</div>

<form method="get" class="d-flex gap-2 mb-3">
    <select name="activo" class="form-select" style="max-width:180px" onchange="this.form.submit()" aria-label="Filtrar por estado">
        <option value="">Todos</option>
        <option value="1"<?= $filtroActivo === '1' ? ' selected' : '' ?>>Activos</option>
        <option value="0"<?= $filtroActivo === '0' ? ' selected' : '' ?>>Inactivos</option>
    </select>
    <div class="position-relative flex-grow-1">
        <input type="text" name="buscar" class="form-input ps-4" placeholder="Buscar por nombre, usuario o email..." value="<?= htmlspecialchars($buscar) ?>" aria-label="Buscar funcionario">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small"></i>
    </div>
    <button type="submit" class="btn btn-sm btn-primary">Buscar</button>
</form>

<?php if (empty($funcionarios)): ?>
    <div class="text-center p-4 text-muted">No hay funcionarios que coincidan con la búsqueda.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Apellido y Nombre</th>
                    <th>Cédula</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($funcionarios as $f): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($f['apellido'] . ', ' . $f['nombre']) ?></td>
                    <td class="small"><?= htmlspecialchars($f['documento_identidad'] ?: '—') ?></td>
                    <td class="small"><?= htmlspecialchars($f['username'] ?: '—') ?></td>
                    <td>
                        <?php
                        $rolClass = match ($f['rol']) {
                            'superadmin' => 'bg-danger',
                            'admin' => 'bg-primary',
                            'medico' => 'bg-info',
                            'enfermero' => 'bg-success',
                            'tecnico' => 'bg-secondary',
                            'recepcionista' => 'bg-warning text-dark',
                            'farmaceutico' => 'bg-purple',
                            'conductor' => 'bg-info',
                            'copiloto' => 'bg-warning',
                            'paciente' => 'bg-success',
                            default => 'bg-secondary',
                        };
                        ?>
                        <span class="badge <?= $rolClass ?>"><?= htmlspecialchars($f['rol_label']) ?></span>
                    </td>
                    <td class="small"><?= htmlspecialchars($f['email'] ?: '—') ?></td>
                    <td class="small"><?= htmlspecialchars($f['telefono'] ?: '—') ?></td>
                    <td>
                        <?php if ($f['activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($f['tipo'] === 'funcionario'): ?>
                            <a href="/funcionarios/editar?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <?php else: ?>
                            <a href="/pacientes/editar?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <?php endif; ?>
                        <?php if ($f['activo']): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Desactivar"
                                onclick="abrirModalDesactivar(<?= $f['id'] ?>, '<?= htmlspecialchars($f['apellido'] . ', ' . $f['nombre'], ENT_QUOTES) ?>')">
                                <i class="bi bi-person-x"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-sm btn-outline-success" title="Reactivar"
                                onclick="abrirModalReactivar(<?= $f['id'] ?>, '<?= htmlspecialchars($f['apellido'] . ', ' . $f['nombre'], ENT_QUOTES) ?>')">
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
            <span>Desactivar funcionario</span>
            <span class="modal-close" onclick="cerrarModalDesactivar()">&times;</span>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro que deseas desactivar a <strong id="nombreFuncionario"></strong>?</p>
            <p class="small" style="color:#666">No podrá iniciar sesión hasta que sea reactivado.</p>
        </div>
        <div class="modal-footer">
            <form method="post" action="/funcionarios/desactivar">
                <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
                <input type="hidden" name="id" id="idFuncionario" value="">
                <button type="submit" class="btn btn-danger btn-sm">Desactivar</button>
                <button type="button" class="btn btn-sm" onclick="cerrarModalDesactivar()">Cancelar</button>
            </form>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalReactivar">
    <div class="modal-box">
        <div class="modal-header">
            <span>Reactivar funcionario</span>
            <span class="modal-close" onclick="cerrarModalReactivar()">&times;</span>
        </div>
        <div class="modal-body">
            <p>¿Deseas reactivar a <strong id="nombreFuncionarioReactivar"></strong>?</p>
        </div>
        <div class="modal-footer">
            <form method="post" action="/funcionarios/reactivar">
                <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
                <input type="hidden" name="id" id="idFuncionarioReactivar" value="">
                <button type="submit" class="btn btn-success btn-sm">Reactivar</button>
                <button type="button" class="btn btn-sm" onclick="cerrarModalReactivar()">Cancelar</button>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $nonce ?>">
function abrirModalDesactivar(id, nombre) {
    document.getElementById('idFuncionario').value = id;
    document.getElementById('nombreFuncionario').textContent = nombre;
    document.getElementById('modalDesactivar').classList.add('open');
}
function cerrarModalDesactivar() {
    document.getElementById('modalDesactivar').classList.remove('open');
}
function abrirModalReactivar(id, nombre) {
    document.getElementById('idFuncionarioReactivar').value = id;
    document.getElementById('nombreFuncionarioReactivar').textContent = nombre;
    document.getElementById('modalReactivar').classList.add('open');
}
function cerrarModalReactivar() {
    document.getElementById('modalReactivar').classList.remove('open');
}
</script>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
