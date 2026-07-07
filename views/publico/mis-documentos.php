<?php

use Elyra\Infrastructure\Service\SessionManager;

$titulo = 'Mis documentos';
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo) ?> — Elyra</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/css/elyra.css?v=7">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1"><i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($paciente['nombre']) ?></h4>
        <p class="text-muted small">Tus documentos asociados</p>
    </div>

    <?php if (empty($documentos)): ?>
    <div class="text-center py-5">
        <div class="display-6 text-muted mb-3"><i class="bi bi-file-earmark-x"></i></div>
        <h5 class="fw-semibold">No hay documentos</h5>
        <p class="text-muted mb-0">A&uacute;n no ten&eacute;s documentos asignados.</p>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($documentos as $doc): ?>
        <?php if (!$doc['activo']) continue; ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title fw-semibold mb-1"><?= htmlspecialchars($doc['titulo']) ?></h6>
                    <div class="mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($doc['categoria']) ?></span>
                        <?php if ($doc['especialidad']): ?>
                        <span class="badge bg-info bg-opacity-10 text-info"><?= htmlspecialchars($doc['especialidad']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($doc['descripcion']): ?>
                    <p class="text-muted small mb-2 flex-grow-1"><?= htmlspecialchars($doc['descripcion']) ?></p>
                    <?php endif; ?>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <small class="text-muted"><?= htmlspecialchars($doc['subido']) ?></small>
                        <a href="/publico/doc?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye me-1"></i> Ver
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>