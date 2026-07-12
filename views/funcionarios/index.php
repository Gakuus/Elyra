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
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Email</th>
                    <th>Licencia conducir</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($funcionarios as $f): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($f['apellido'] . ', ' . $f['nombre']) ?></td>
                    <td class="small"><?= htmlspecialchars($f['username']) ?></td>
                    <td>
                        <?php
                        $rolClass = match ($f['rol']) {
                            'superadmin' => 'bg-danger',
                            'admin' => 'bg-primary',
                            'conductor' => 'bg-info',
                            'copiloto' => 'bg-warning',
                            default => 'bg-secondary',
                        };
                        ?>
                        <span class="badge <?= $rolClass ?>"><?= htmlspecialchars($f['rol_label']) ?></span>
                    </td>
                    <td class="small"><?= htmlspecialchars($f['email'] ?: '—') ?></td>
                    <td class="small"><?= htmlspecialchars($f['licencia_conducir'] ?: '—') ?></td>
                    <td class="small"><?= htmlspecialchars($f['telefono'] ?: '—') ?></td>
                    <td>
                        <?php if ($f['activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="/funcionarios/editar?id=<?= $f['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <?php if ($f['activo']): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Desactivar"
                                onclick="abrirModalDesactivar(<?= $f['id'] ?>, '<?= htmlspecialchars($f['apellido'] . ', ' . $f['nombre'], ENT_QUOTES) ?>')">
                                <i class="bi bi-person-x"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/_modal_desactivar.php'; ?>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
