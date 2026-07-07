<?php $titulo = $doc ? htmlspecialchars($doc['titulo']) : 'Documento no encontrado'; ?>
<?php ob_start(); ?>

<?php if (!$doc): ?>

<div class="text-center py-5">
    <div class="display-6 text-muted mb-3"><i class="bi bi-file-earmark-x"></i></div>
    <h5 class="fw-semibold">Documento no encontrado</h5>
    <p class="text-muted mb-4">El documento que buscas no existe o fue eliminado.</p>
    <a href="/documentos" class="btn btn-primary">Volver a documentos</a>
</div>

<?php else: ?>

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1"><?= htmlspecialchars($doc['titulo']) ?></h4>
        <div class="d-flex align-items-center gap-2">
            <?php if ($doc['especialidad']): ?>
                <span class="badge bg-info bg-opacity-10 text-info"><?= htmlspecialchars($doc['especialidad']) ?></span>
            <?php endif; ?>
            <span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($doc['categoria']) ?></span>
            <?php if ($doc['paciente']): ?>
                <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-person"></i> <?= htmlspecialchars($doc['paciente']) ?></span>
            <?php else: ?>
                <span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-globe"></i> Documento general</span>
            <?php endif; ?>
            <small class="text-muted">Subido el <?= htmlspecialchars($doc['subido']) ?></small>
            <?php if (!$doc['activo']): ?>
                <span class="badge bg-secondary">Inactivo</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="/documentos/archivo?id=<?= $doc['id'] ?>&descargar=1" class="btn btn-primary">
            <i class="bi bi-download me-1"></i> Descargar PDF
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="Elyra.verQR(<?= $doc['id'] ?>)" title="Ver QR">
            <i class="bi bi-qr-code"></i>
        </button>
        <a href="/documentos/editar?id=<?= $doc['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i>
        </a>
        <a href="/documentos" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
</div>

<?php if ($doc['descripcion']): ?>
    <p class="text-muted mb-3"><?= htmlspecialchars($doc['descripcion']) ?></p>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-0">
        <embed src="/documentos/archivo?id=<?= $doc['id'] ?>" type="application/pdf" class="w-100" style="height: 75vh; border: none; border-radius: 0.5rem;">
    </div>
</div>

<?php if (isset($doc['encuesta_id']) && $doc['encuesta_id']): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body d-flex align-items-center justify-content-between">
        <div>
            <i class="bi bi-bar-chart me-2 text-primary"></i>
            <span class="fw-semibold">Encuesta asociada</span>
            <span class="text-muted ms-2 small">Este documento tiene una encuesta de satisfacción.</span>
        </div>
        <a href="/encuestas/resultados?id=<?= $doc['encuesta_id'] ?>" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-eye me-1"></i> Ver resultados
        </a>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
