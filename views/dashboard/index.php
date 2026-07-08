<?php $titulo = 'Inicio'; ?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <div class="panel mb-2">
            <div class="panel-heading"><i class="bi bi-house-door me-1"></i> Panel de Control</div>
            <div class="panel-body text-muted">
                Bienvenido al sistema de gesti&oacute;n hospitalaria Elyra.
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6 col-lg-3">
                <div class="panel text-center">
                    <div class="panel-body">
                        <div class="stat-box-number"><?= $totalDocs ?></div>
                        <div class="stat-box-label">Documentos</div>
                        <div class="stat-box-label">
                            <a href="/documentos/generales"><?= $totalGenerales ?> generales</a>
                            &middot;
                            <a href="/documentos/paciente"><?= $totalDocs - $totalGenerales ?> de pacientes</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="panel text-center">
                    <div class="panel-body">
                        <div class="stat-box-number"><?= $totalEncuestas ?></div>
                        <div class="stat-box-label">Encuestas</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="panel text-center">
                    <div class="panel-body">
                        <div class="stat-box-number"><?= $totalTraslados ?></div>
                        <div class="stat-box-label">Traslados activos</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="panel text-center">
                    <div class="panel-body">
                        <div class="stat-box-number"><?= $totalConductores ?></div>
                        <div class="stat-box-label">Conductores</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-md-8">
                <div class="panel">
                    <div class="panel-heading-gray"><i class="bi bi-clock-history me-1"></i> Actividad reciente</div>
                    <div class="panel-inset m-2">
                        <?php if (empty($recientes)): ?>
                            <div class="p-3 text-center text-muted">No hay actividad reciente para mostrar.</div>
                        <?php else: ?>
                            <?php foreach ($recientes as $r): ?>
                                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                                    <i class="bi bi-file-earmark-text" style="color: #000080;"></i>
                                    <div class="flex-grow-1">
                                        <a href="/documentos/editar?id=<?= $r['id'] ?>" class="fw-semibold text-truncate d-block" style="text-decoration: none;"><?= htmlspecialchars($r['titulo']) ?></a>
                                        <small class="text-muted"><?= htmlspecialchars($r['categoria']) ?> &middot; Subido el <?= htmlspecialchars($r['subido']) ?></small>
                                    </div>
                                    <a href="/documentos/editar?id=<?= $r['id'] ?>" class="btn btn-sm">Editar</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel">
                    <div class="panel-heading-gray"><i class="bi bi-lightning me-1"></i> Accesos r&aacute;pidos</div>
                    <div class="p-2 d-grid gap-1">
                        <a href="/documentos/generales" class="btn btn-sm text-start px-3">
                            <i class="bi bi-globe me-2"></i>Documentos generales
                        </a>
                        <a href="/documentos/paciente" class="btn btn-sm text-start px-3">
                            <i class="bi bi-person-search me-2"></i>Documentos por CI
                        </a>
                        <a href="/documentos/subir" class="btn btn-sm text-start px-3">
                            <i class="bi bi-upload me-2"></i>Subir documento
                        </a>
                        <a href="/traslados/nuevo" class="btn btn-sm text-start px-3">
                            <i class="bi bi-plus-circle me-2"></i>Nuevo traslado
                        </a>
                        <a href="/encuestas/crear" class="btn btn-sm text-start px-3">
                            <i class="bi bi-plus-square me-2"></i>Crear encuesta
                        </a>
                        <a href="/traslados" class="btn btn-sm text-start px-3">
                            <i class="bi bi-eye me-2"></i>Ver traslados activos
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
