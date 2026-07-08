<?php $titulo = 'Inicio'; ?>
<?php ob_start(); ?>
<div class="py-4">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="panel text-center h-100">
                <div class="panel-body">
                    <i class="bi bi-file-text" style="font-size: 2rem; color: #0d6efd;"></i>
                    <div class="stat-box-number"><?= $totalDocs ?></div>
                    <div class="stat-box-label">Documentos</div>
                    <a href="/documentos" class="btn btn-sm btn-primary mt-2">Ver documentos</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel text-center h-100">
                <div class="panel-body">
                    <i class="bi bi-bar-chart" style="font-size: 2rem; color: #198754;"></i>
                    <div class="stat-box-number">Encuestas</div>
                    <div class="stat-box-label">Participá dando tu opinión</div>
                    <a href="/encuestas" class="btn btn-sm btn-primary mt-2">Ver encuestas</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel text-center h-100">
                <div class="panel-body">
                    <i class="bi bi-truck" style="font-size: 2rem; color: #ffc107;"></i>
                    <div class="stat-box-number">Traslados</div>
                    <div class="stat-box-label">Tus traslados activos</div>
                    <a href="/traslados" class="btn btn-sm btn-primary mt-2">Ver traslados</a>
                </div>
            </div>
        </div>
    </div>

    <div class="panel mt-4">
        <div class="panel-heading d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Documentos recientes</span>
            <a href="/documentos" class="btn btn-sm btn-primary">Ver todos</a>
        </div>
        <div class="panel-body p-0">
            <?php if (empty($recientes)): ?>
                <p class="text-muted text-center py-4 mb-0">No tenés documentos asignados.</p>
            <?php else: ?>
                <?php foreach ($recientes as $doc): ?>
                    <a href="/documentos/ver?id=<?= $doc['id'] ?>" class="d-flex justify-content-between align-items-center border-bottom px-3 py-2 text-decoration-none">
                        <span><i class="bi bi-file-earmark-pdf me-2 text-danger"></i> <?= htmlspecialchars($doc['titulo']) ?></span>
                        <small class="text-muted"><?= $doc['subido'] ?> &middot; <?= htmlspecialchars($doc['categoria']) ?></small>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
