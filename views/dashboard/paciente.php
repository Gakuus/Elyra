<?php $titulo = 'Inicio'; ?>
<?php ob_start(); ?>
<div class="container py-4">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card text-center p-4 h-100">
                <div class="display-6 text-primary mb-2"><i class="bi bi-file-text"></i></div>
                <h5 class="fw-semibold"><?= $totalDocs ?></h5>
                <p class="text-muted small mb-0">Documentos</p>
                <a href="/documentos" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-4 h-100">
                <div class="display-6 text-success mb-2"><i class="bi bi-bar-chart"></i></div>
                <h5 class="fw-semibold">Encuestas</h5>
                <p class="text-muted small mb-0">Participá dando tu opinión</p>
                <a href="/encuestas" class="stretched-link"></a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center p-4 h-100">
                <div class="display-6 text-warning mb-2"><i class="bi bi-truck"></i></div>
                <h5 class="fw-semibold">Traslados</h5>
                <p class="text-muted small mb-0">Tus traslados activos</p>
                <a href="/traslados" class="stretched-link"></a>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Documentos recientes</h5>
            <a href="/documentos" class="btn btn-sm btn-outline-primary">Ver todos</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recientes)): ?>
                <p class="text-muted text-center py-4 mb-0">No tenés documentos asignados.</p>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($recientes as $doc): ?>
                        <a href="/documentos/ver?id=<?= $doc['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-file-earmark-pdf me-2 text-danger"></i> <?= htmlspecialchars($doc['titulo']) ?></span>
                            <small class="text-muted"><?= $doc['subido'] ?> &middot; <?= htmlspecialchars($doc['categoria']) ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
