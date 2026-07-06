<?php $titulo = 'Historial de traslados'; ?>
<?php ob_start(); ?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <h4 class="fw-semibold mb-0">Historial de traslados</h4>
    <a href="/traslados/nuevo" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Nuevo traslado</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Buscar</label>
                <input type="text" name="buscar" class="form-control form-control-sm" placeholder="Código, paciente, origen..." value="<?= htmlspecialchars($filtros['buscar']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($estadosList as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $filtros['estado'] === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Conductor</label>
                <select name="conductor" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <?php foreach ($conductores as $c): ?>
                        <?php if ($c === '') continue; ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= $filtros['conductor'] === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Fecha</label>
                <input type="text" name="fecha" class="form-control form-control-sm" placeholder="dd/mm/aaaa" value="<?= htmlspecialchars($filtros['fecha']) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="bi bi-funnel me-1"></i> Filtrar</button>
                <a href="/traslados/historial" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-circle"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Código</th>
                        <th>Paciente / Elemento</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Conductor</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-end">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($traslados)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No se encontraron traslados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($traslados as $t): ?>
                            <?php
                            $estadoClasses = [
                                'pendiente' => 'warning',
                                'en_curso' => 'primary',
                                'en_destino' => 'info',
                                'en_retorno' => 'secondary',
                                'completado' => 'success',
                                'cancelado' => 'danger',
                            ];
                            $cls = $estadoClasses[$t['estado']] ?? 'secondary';
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($t['codigo'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['paciente'] ?? $t['elemento'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['origen'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['destino'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($t['conductor'] ?? '-') ?></td>
                                <td class="text-nowrap"><?= htmlspecialchars($t['fecha'] ?? '') ?></td>
                                <td><span class="badge bg-<?= $cls ?> bg-opacity-10 text-<?= $cls ?>"><?= $estadosList[$t['estado']] ?? $t['estado'] ?></span></td>
                                <td class="text-end"><a href="/traslados/ver?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
