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

        <a href="/traslados" class="btn btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i> Volver a traslados</a>

        <div class="panel mb-4">
            <div class="panel-body">
                <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                    <div>
                        <h4 class="fw-semibold mb-1"><?= htmlspecialchars($t['codigo']) ?></h4>
                        <p class="text-muted mb-0"><?= htmlspecialchars($t['fecha'] ?? '') ?> &middot; <?= htmlspecialchars($t['hora'] ?? '') ?></p>
                    </div>
                    <span class="badge badge-<?= htmlspecialchars($t['estado']) ?> fs-6 px-3 py-2"><?= htmlspecialchars($e['label']) ?></span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="panel h-100">
                    <div class="panel-body">
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
                <div class="panel h-100">
                    <div class="panel-body">
                        <h6 class="text-muted mb-3"><i class="bi bi-box me-1"></i> Elemento</h6>
                        <div class="mb-2">
                            <small class="text-muted d-block">Elemento</small>
                            <span class="fw-semibold"><?= htmlspecialchars($t['elemento_descripcion'] ?? '-') ?></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Tipo</small>
                            <span class="badge"><?= htmlspecialchars($t['elemento_tipo'] ?? 'paciente') ?></span>
                        </div>
                        <?php if (!empty($t['observaciones'])): ?>
                            <div class="mt-2">
                                <small class="text-muted d-block">Observaciones</small>
                                <span class="small"><?= nl2br(htmlspecialchars($t['observaciones'])) ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($t['motivo_cancelacion'])): ?>
                            <div class="mt-2">
                                <small class="text-muted d-block">Motivo cancelación</small>
                                <span class="small text-danger"><?= nl2br(htmlspecialchars($t['motivo_cancelacion'])) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="panel h-100">
                    <div class="panel-body">
                        <h6 class="text-muted mb-3"><i class="bi bi-geo-alt me-1"></i> Recorrido</h6>
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-center">
                                <div class="p-2 rounded-circle d-inline-flex">
                                    <i class="bi bi-circle-fill small"></i>
                                </div>
                                <div class="small fw-semibold mt-1"><?= htmlspecialchars($t['origen'] ?? '-') ?></div>
                            </div>
                            <div class="flex-grow-1 border-top border-2 border-primary opacity-50"></div>
                            <div class="text-center">
                                <div class="p-2 rounded-circle d-inline-flex">
                                    <i class="bi bi-circle-fill small"></i>
                                </div>
                                <div class="small fw-semibold mt-1"><?= htmlspecialchars($t['destino'] ?? '-') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel h-100">
                    <div class="panel-body">
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

        <div class="panel">
            <div class="panel-body">
                <h6 class="text-muted mb-4"><i class="bi bi-clock-history me-1"></i> Timeline del traslado</h6>
                <div class="timeline">
                    <?php foreach ($timeline as $step): ?>
                        <?php
                        $info = $estados[$step['estado']] ?? ['label' => $step['estado'], 'class' => 'secondary'];
                        ?>
                        <div class="timeline-step <?= $step['completado'] ? 'completed' : '' ?> <?= $step['activo'] ? 'active' : '' ?>">
                            <div class="timeline-dot bg-<?= $info['class'] ?>"></div>
                            <div class="timeline-content">
                                <span class="badge badge-<?= htmlspecialchars($step['estado']) ?>"><?= htmlspecialchars($info['label']) ?></span>
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
                <a href="/traslados/actualizar-estado?id=<?= $t['id'] ?>" class="btn btn-primary">
                    <i class="bi bi-arrow-right-circle me-1"></i> Actualizar estado
                </a>
            <?php endif; ?>
            <a href="/traslados" class="btn">Volver</a>
        </div>

    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
