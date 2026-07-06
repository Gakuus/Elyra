<?php
$titulo = 'Traslado ' . htmlspecialchars($t['codigo']);
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
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10">

        <a href="/traslados" class="btn btn-outline-secondary btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i> Volver a traslados</a>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="fw-semibold mb-1"><?= htmlspecialchars($t['codigo']) ?></h4>
                        <p class="text-muted mb-0"><?= htmlspecialchars($t['fecha'] ?? '') ?> &middot; <?= htmlspecialchars($t['hora'] ?? '') ?></p>
                    </div>
                    <span class="badge bg-<?= $e['class'] ?> bg-opacity-10 text-<?= $e['class'] ?> fs-6 px-3 py-2"><?= $e['label'] ?></span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-3"><i class="bi bi-person-badge me-1"></i> Personal</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Conductor</small>
                            <span class="fw-semibold"><?= htmlspecialchars($t['conductor'] ?? '-') ?></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Copiloto</small>
                            <span class="fw-semibold"><?= htmlspecialchars($t['copiloto'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-3"><i class="bi bi-box me-1"></i> Elemento</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Elemento</small>
                            <span class="fw-semibold"><?= htmlspecialchars($t['paciente'] ?? $t['elemento'] ?? '-') ?></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Tipo</small>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= htmlspecialchars($t['tipo'] ?? 'paciente') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-3"><i class="bi bi-geo-alt me-1"></i> Recorrido</h6>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-center">
                                <div class="bg-primary bg-opacity-10 p-2 rounded-circle d-inline-flex">
                                    <i class="bi bi-circle-fill text-primary small"></i>
                                </div>
                                <div class="small fw-semibold mt-1"><?= htmlspecialchars($t['origen'] ?? '-') ?></div>
                            </div>
                            <div class="flex-grow-1 border-top border-2 border-primary opacity-50"></div>
                            <div class="text-center">
                                <div class="bg-success bg-opacity-10 p-2 rounded-circle d-inline-flex">
                                    <i class="bi bi-circle-fill text-success small"></i>
                                </div>
                                <div class="small fw-semibold mt-1"><?= htmlspecialchars($t['destino'] ?? '-') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-3"><i class="bi bi-clock me-1"></i> Tiempos</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Salida</small>
                            <span class="fw-semibold"><?= htmlspecialchars($t['fecha'] ?? '') ?> <?= htmlspecialchars($t['hora'] ?? '') ?></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Llegada estimada</small>
                            <span class="fw-semibold"><?= htmlspecialchars($t['hora_llegada'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h6 class="text-muted mb-4"><i class="bi bi-clock-history me-1"></i> Timeline del traslado</h6>
                <div class="timeline">
                    <?php foreach ($timeline as $step): ?>
                        <?php
                        $info = $estados[$step['estado']] ?? ['label' => $step['estado'], 'class' => 'secondary'];
                        ?>
                        <div class="timeline-step <?= $step['completado'] ? 'completed' : '' ?> <?= $step['activo'] ? 'active' : '' ?>">
                            <div class="timeline-dot bg-<?= $info['class'] ?>"></div>
                            <div class="timeline-content">
                                <span class="badge bg-<?= $info['class'] ?> bg-opacity-10 text-<?= $info['class'] ?>"><?= $info['label'] ?></span>
                                <?php if ($step['fecha']): ?>
                                    <small class="text-muted d-block mt-1"><?= htmlspecialchars($step['fecha']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <?php if (in_array($t['estado'], ['pendiente', 'en_curso', 'en_destino', 'en_retorno'], true)): ?>
                <a href="/traslados/actualizar-estado?id=<?= $t['id'] ?>&estado=<?= $t['estado'] ?>" class="btn btn-primary">
                    <i class="bi bi-arrow-right-circle me-1"></i> Actualizar estado
                </a>
            <?php endif; ?>
            <a href="/traslados" class="btn btn-outline-secondary">Volver</a>
        </div>

    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
