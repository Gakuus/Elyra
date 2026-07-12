<?php $titulo = 'Conductores'; ?>
<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Conductores</h4>
    <a href="/conductores/crear" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nuevo conductor</a>
</div>

<?php if (isset($_GET['creado'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Conductor creado correctamente.</div>
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
                    <th>Usuario</th>
                    <th>Licencia</th>
                    <th>Teléfono</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($conductores as $c): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($c['apellido'] . ', ' . $c['nombre']) ?></td>
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
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
