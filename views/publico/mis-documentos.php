<?php

use Elyra\Infrastructure\Service\SessionManager;

$titulo = 'Mis documentos';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="<?= rtrim((string)(parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?: ''), '/') ?>/">
    <title><?= htmlspecialchars($titulo) ?> — Elyra</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/web20.css">
</head>
<body style="background:#f2f2f2;">

<div class="container py-5">
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1"><i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($paciente['nombre']) ?></h4>
        <p class="text-muted small">Tus documentos asociados</p>
    </div>

    <?php if (empty($documentos)): ?>
    <div class="text-center py-5">
        <div style="font-size:32px;color:var(--text-muted);margin-bottom:10px;"><i class="bi bi-file-earmark-x"></i></div>
        <h5 class="fw-semibold">No hay documentos</h5>
        <p class="text-muted mb-0">A&uacute;n no ten&eacute;s documentos asignados.</p>
    </div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($documentos as $doc): ?>
        <?php if (!$doc['activo']) continue; ?>
        <div class="col-md-6 col-lg-4">
            <div class="panel h-100">
                <div class="panel-body d-flex flex-column">
                    <h6 class="fw-semibold mb-1"><?= htmlspecialchars($doc['titulo']) ?></h6>
                    <div class="mb-2">
                        <span class="badge" style="background:#D9EDF7;color:#31708F;border:1px solid #BCE8F1;"><?= htmlspecialchars($doc['categoria']) ?></span>
                        <?php if ($doc['especialidad']): ?>
                        <span class="badge" style="background:#DFF0D8;color:#3C763D;border:1px solid #C1E2B3;"><?= htmlspecialchars($doc['especialidad']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($doc['descripcion']): ?>
                    <p class="text-muted small mb-2 flex-grow-1"><?= htmlspecialchars($doc['descripcion']) ?></p>
                    <?php endif; ?>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <small class="text-muted"><?= htmlspecialchars($doc['subido']) ?></small>
                        <a href="publico/doc?id=<?= $doc['id'] ?>" class="btn btn-primary btn-sm">
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

</body>
</html>
