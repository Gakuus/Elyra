<?php $titulo = 'Inicio'; ?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <div class="win-panel mb-2">
            <div class="win-titlebar"><i class="bi bi-house-door me-1"></i> Panel de Control</div>
            <div class="p-3 win-text" style="font-size: 12px;">
                Bienvenido al sistema de gesti&oacute;n hospitalaria Elyra.
            </div>
        </div>

        <div class="row g-2 mb-2">
            <div class="col-md-6 col-lg-3">
                <div class="win-panel text-center">
                    <div class="p-3">
                        <div class="fw-bold" style="font-size: 24px; font-family: Tahoma, 'MS Sans Serif', sans-serif;"><?= $totalDocs ?></div>
                        <div class="win-text small">Documentos</div>
                        <div class="win-text small">
                            <a href="/documentos/generales" class="win-text"><?= $totalGenerales ?> generales</a>
                            &middot;
                            <a href="/documentos/paciente" class="win-text"><?= $totalDocs - $totalGenerales ?> de pacientes</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="win-panel text-center">
                    <div class="p-3">
                        <div class="fw-bold" style="font-size: 24px; font-family: Tahoma, 'MS Sans Serif', sans-serif;"><?= $totalEncuestas ?></div>
                        <div class="win-text small">Encuestas</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="win-panel text-center">
                    <div class="p-3">
                        <div class="fw-bold" style="font-size: 24px; font-family: Tahoma, 'MS Sans Serif', sans-serif;"><?= $totalTraslados ?></div>
                        <div class="win-text small">Traslados activos</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="win-panel text-center">
                    <div class="p-3">
                        <div class="fw-bold" style="font-size: 24px; font-family: Tahoma, 'MS Sans Serif', sans-serif;"><?= $totalConductores ?></div>
                        <div class="win-text small">Conductores</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-2">
            <div class="col-md-8">
                <div class="win-panel">
                    <div class="win-titlebar-gray"><i class="bi bi-clock-history me-1"></i> Actividad reciente</div>
                    <div class="win-inset m-2">
                        <?php if (empty($recientes)): ?>
                            <div class="p-3 win-text text-center" style="font-size: 12px;">No hay actividad reciente para mostrar.</div>
                        <?php else: ?>
                            <?php foreach ($recientes as $r): ?>
                                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom border-secondary" style="border-color: #808080 !important;">
                                    <i class="bi bi-file-earmark-text" style="color: #000080;"></i>
                                    <div class="flex-grow-1">
                                        <a href="/documentos/editar?id=<?= $r['id'] ?>" class="win-text fw-semibold text-truncate d-block" style="text-decoration: none; font-size: 12px;"><?= htmlspecialchars($r['titulo']) ?></a>
                                        <small class="win-text"><?= htmlspecialchars($r['categoria']) ?> &middot; Subido el <?= htmlspecialchars($r['subido']) ?></small>
                                    </div>
                                    <a href="/documentos/editar?id=<?= $r['id'] ?>" class="win-btn win-btn-sm">Editar</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="win-panel">
                    <div class="win-titlebar-gray"><i class="bi bi-lightning me-1"></i> Accesos r&aacute;pidos</div>
                    <div class="p-2 d-grid gap-1">
                        <a href="/documentos/generales" class="win-btn win-btn-sm text-start px-3">
                            <i class="bi bi-globe me-2"></i>Documentos generales
                        </a>
                        <a href="/documentos/paciente" class="win-btn win-btn-sm text-start px-3">
                            <i class="bi bi-person-search me-2"></i>Documentos por CI
                        </a>
                        <a href="/documentos/subir" class="win-btn win-btn-sm text-start px-3">
                            <i class="bi bi-upload me-2"></i>Subir documento
                        </a>
                        <a href="/traslados/nuevo" class="win-btn win-btn-sm text-start px-3">
                            <i class="bi bi-plus-circle me-2"></i>Nuevo traslado
                        </a>
                        <a href="/encuestas/crear" class="win-btn win-btn-sm text-start px-3">
                            <i class="bi bi-plus-square me-2"></i>Crear encuesta
                        </a>
                        <a href="/traslados" class="win-btn win-btn-sm text-start px-3">
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
