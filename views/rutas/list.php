<?php $titulo = 'Rutas'; ?>
<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Rutas</h4>
    <a href="rutas/crear" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nueva ruta</a>
</div>

<?php if (isset($_GET['creada'])): ?>
    <div class="msg msg-success mb-3"><i class="bi bi-check-circle-fill"></i> Ruta creada correctamente.</div>
<?php endif; ?>

<div class="d-flex gap-3 mb-3">
    <div class="panel" style="flex:1">
        <div class="panel-heading">Total rutas</div>
        <div class="panel-body text-center"><span class="fs-4 fw-bold"><?= $total ?></span></div>
    </div>
</div>

<?php if (empty($rutas)): ?>
    <div class="text-center p-4 text-muted">No hay rutas registradas.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Distancia</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rutas as $r): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($r['nombre']) ?></td>
                    <td class="small"><?= htmlspecialchars($r['origen']) ?></td>
                    <td class="small"><?= htmlspecialchars($r['destino']) ?></td>
                    <td class="small"><?= $r['distancia_km'] !== null ? htmlspecialchars((string) $r['distancia_km']) . ' km' : '—' ?></td>
                    <td class="small text-muted"><?= htmlspecialchars($r['descripcion'] ?: '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
