<?php $titulo = 'Inicio'; ?>
<?php ob_start(); ?>
<h2>Panel de Control</h2>
<p class="text-muted mb-4">Bienvenido al sistema de gesti&oacute;n hospitalaria Elyra.</p>

<div class="row g-4">
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-file-text fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold"><?= $totalDocs ?></div>
                        <div class="text-muted small">Documentos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-bar-chart fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold"><?= $totalEncuestas ?></div>
                        <div class="text-muted small">Encuestas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-truck fs-4 text-info"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold"><?= $totalTraslados ?></div>
                        <div class="text-muted small">Traslados activos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                        <i class="bi bi-people fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="fs-3 fw-bold"><?= $totalConductores ?></div>
                        <div class="text-muted small">Conductores</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-2">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom py-3">
                <h5 class="mb-0">Actividad reciente</h5>
            </div>
            <div class="card-body p-0 list-group list-group-flush">
                <?php if (empty($recientes)): ?>
                    <p class="text-muted small py-4 px-3 mb-0">No hay actividad reciente para mostrar.</p>
                <?php else: ?>
                    <?php foreach ($recientes as $r): ?>
                        <div class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3 px-3">
                            <i class="bi bi-file-earmark-text text-primary fs-5"></i>
                            <div class="flex-grow-1 min-w-0">
                                <a href="/documentos/editar?id=<?= $r['id'] ?>" class="text-decoration-none fw-semibold text-truncate d-block"><?= htmlspecialchars($r['titulo']) ?></a>
                                <small class="text-muted"><?= htmlspecialchars($r['categoria']) ?> &middot; Subido el <?= htmlspecialchars($r['subido']) ?></small>
                            </div>
                            <a href="/documentos/editar?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom py-3">
                <h5 class="mb-0">Accesos r&aacute;pidos</h5>
            </div>
            <div class="card-body p-3">
                <div class="d-grid gap-2">
                    <a href="/documentos/subir" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-upload me-2"></i>Subir documento
                    </a>
                    <a href="/traslados/nuevo" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-plus-circle me-2"></i>Nuevo traslado
                    </a>
                    <a href="/encuestas/crear" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-plus-square me-2"></i>Crear encuesta
                    </a>
                    <a href="/traslados" class="btn btn-outline-primary btn-sm text-start">
                        <i class="bi bi-eye me-2"></i>Ver traslados activos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>