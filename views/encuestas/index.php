<?php $titulo = 'Encuestas'; ?>
<?php ob_start(); ?>

<div class="action-bar">
    <a href="/encuestas/crear" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Nueva encuesta
    </a>
</div>

<?php if (empty($encuestas)): ?>
    <div class="text-center py-5">
        <div class="display-6 text-muted mb-3"><i class="bi bi-bar-chart"></i></div>
        <h5 class="fw-semibold">No hay encuestas</h5>
        <p class="text-muted mb-4">A&uacute;n no se ha creado ninguna encuesta.</p>
        <a href="/encuestas/crear" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Crear primera encuesta</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-elyra mb-0">
            <thead>
                <tr>
                    <th>T&iacute;tulo</th>
                    <th>Preguntas</th>
                    <th>Estado</th>
                    <th>Creada</th>
                    <th style="width: 160px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($encuestas as $e): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= htmlspecialchars($e['titulo']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($e['descripcion']) ?></small>
                        </td>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary"><?= $e['preguntas'] ?></span></td>
                        <td>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="toggle-<?= $e['id'] ?>"<?= $e['activa'] ? ' checked' : '' ?> data-encuesta-id="<?= $e['id'] ?>">
                                <label class="form-check-label small" for="toggle-<?= $e['id'] ?>"><?= $e['activa'] ? 'Activa' : 'Inactiva' ?></label>
                            </div>
                        </td>
                        <td class="text-muted small"><?= htmlspecialchars($e['creada']) ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="/encuestas/resultados?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-secondary border-0" title="Ver resultados">
                                    <i class="bi bi-bar-chart"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-secondary border-0" title="Copiar enlace" onclick="Elyra.copiarEnlaceEncuesta(<?= $e['id'] ?>, this)">
                                    <i class="bi bi-link-45deg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-list-view mt-3">
        <?php foreach ($encuestas as $e): ?>
            <div class="card-item">
                <div class="card-item-title"><?= htmlspecialchars($e['titulo']) ?></div>
                <div class="card-item-meta">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary me-2"><?= $e['preguntas'] ?> preg.</span>
                    <span class="badge bg-<?= $e['activa'] ? 'success' : 'secondary' ?> bg-opacity-10 text-<?= $e['activa'] ? 'success' : 'secondary' ?> me-2"><?= $e['activa'] ? 'Activa' : 'Inactiva' ?></span>
                    <?= htmlspecialchars($e['creada']) ?>
                </div>
                <div class="card-item-actions">
                    <a href="/encuestas/resultados?id=<?= $e['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-bar-chart"></i></a>
                    <button class="btn btn-sm btn-outline-secondary" onclick="Elyra.copiarEnlaceEncuesta(<?= $e['id'] ?>, this)"><i class="bi bi-link-45deg"></i></button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="mt-3">
    <a href="/documentos" class="btn btn-outline-primary btn-sm"><i class="bi bi-file-text me-1"></i> Ir a Documentos</a>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
