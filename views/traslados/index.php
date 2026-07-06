<?php $titulo = 'Traslados en ambulancia'; ?>
<?php ob_start(); ?>

<div class="action-bar">
    <a href="/traslados/nuevo" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Nuevo traslado
    </a>
    <a href="/traslados/historial" class="btn btn-outline-secondary">
        <i class="bi bi-clock-history me-1"></i> Historial
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm stat-card stat-card-warning" data-filtro="pendiente">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold"><?= $pendientes ?></div>
                        <div class="text-muted small">Pendientes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm stat-card stat-card-primary" data-filtro="en_curso">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3">
                        <i class="bi bi-truck fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold"><?= $enCurso ?></div>
                        <div class="text-muted small">En curso</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm stat-card stat-card-success" data-filtro="completado_hoy">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold"><?= $completadosHoy ?></div>
                        <div class="text-muted small">Completados hoy</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm stat-card stat-card-info" data-filtro="total">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 rounded-3">
                        <i class="bi bi-list-check fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold"><?= $total ?></div>
                        <div class="text-muted small">Total activos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="filter-active-bar alert alert-info d-none py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
    <span><i class="bi bi-funnel me-1"></i> Filtrando: <strong class="filter-label"></strong></span>
    <button type="button" class="btn btn-sm btn-outline-secondary btn-clear-filter">Limpiar filtro</button>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom py-3">
        <h5 class="mb-0"><i class="bi bi-truck me-2"></i>Traslados activos</h5>
    </div>
    <?php if (empty($activos)): ?>
        <div class="card-body text-center py-4">
            <p class="text-muted mb-0">No hay traslados activos en este momento.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-elyra mb-0">
                <thead>
                    <tr>
                        <th>C&oacute;digo</th>
                        <th>Paciente</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Conductor</th>
                        <th>Estado</th>
                        <th style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activos as $t): ?>
                        <tr data-estado="<?= $t['estado'] ?>">
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary"><?= htmlspecialchars($t['codigo']) ?></span></td>
                            <td class="fw-semibold"><?= htmlspecialchars($t['paciente']) ?></td>
                            <td class="small"><?= htmlspecialchars($t['origen']) ?></td>
                            <td class="small"><?= htmlspecialchars($t['destino']) ?></td>
                            <td class="small"><?= htmlspecialchars($t['conductor']) ?></td>
                            <td>
                                <?php
                                $estados = [
                                    'pendiente' => ['label' => 'Pendiente', 'class' => 'warning'],
                                    'en_curso' => ['label' => 'En curso', 'class' => 'primary'],
                                    'en_destino' => ['label' => 'En destino', 'class' => 'info'],
                                    'en_retorno' => ['label' => 'En retorno', 'class' => 'secondary'],
                                    'completado' => ['label' => 'Completado', 'class' => 'success'],
                                    'cancelado' => ['label' => 'Cancelado', 'class' => 'danger'],
                                ];
                                $e = $estados[$t['estado']] ?? ['label' => $t['estado'], 'class' => 'secondary'];
                                ?>
                                <span class="badge bg-<?= $e['class'] ?> bg-opacity-10 text-<?= $e['class'] ?>">
                                    <?= $e['label'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="/traslados/ver?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary border-0" title="Ver detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if (in_array($t['estado'], ['pendiente', 'en_curso', 'en_destino', 'en_retorno'], true)): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary border-0" title="Actualizar estado" onclick="Elyra.actualizarEstado(<?= $t['id'] ?>, '<?= $t['estado'] ?>')">
                                            <i class="bi bi-arrow-right-circle"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card-list-view">
            <?php foreach ($activos as $t): ?>
                <div class="card-item" data-estado="<?= $t['estado'] ?>">
                    <div class="card-item-title d-flex align-items-center gap-2">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= htmlspecialchars($t['codigo']) ?></span>
                        <?= htmlspecialchars($t['paciente']) ?>
                    </div>
                    <div class="card-item-meta">
                        <span class="badge bg-<?= $estados[$t['estado']]['class'] ?? 'secondary' ?> bg-opacity-10 text-<?= $estados[$t['estado']]['class'] ?? 'secondary' ?> me-2">
                            <?= $estados[$t['estado']]['label'] ?? $t['estado'] ?>
                        </span>
                        <small class="text-muted"><?= htmlspecialchars($t['origen']) ?> &rarr; <?= htmlspecialchars($t['destino']) ?></small>
                    </div>
                    <div class="card-item-actions">
                        <a href="/traslados/ver?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        <?php if (in_array($t['estado'], ['pendiente', 'en_curso', 'en_destino', 'en_retorno'], true)): ?>
                            <button class="btn btn-sm btn-outline-primary" onclick="Elyra.actualizarEstado(<?= $t['id'] ?>, '<?= $t['estado'] ?>')"><i class="bi bi-arrow-right-circle"></i></button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
