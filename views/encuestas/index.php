<?php
$titulo = 'Encuestas';
$isPaciente = \Elyra\Infrastructure\Service\SessionManager::isPaciente();
?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <div class="win-panel mb-3">
            <div class="win-titlebar d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart me-1"></i> Encuestas</span>
                <?php if (!$isPaciente): ?>
                <a href="/encuestas/crear" class="win-btn win-btn-primary py-0 px-3" style="font-size: 11px;">
                    <i class="bi bi-plus-lg me-1"></i> Nueva
                </a>
                <?php endif; ?>
            </div>

            <?php if (empty($encuestas)): ?>
                <div class="p-4 text-center win-text" style="font-size: 12px;">
                    <i class="bi bi-bar-chart fs-1 d-block mb-2"></i>
                    <p class="mb-2">No hay encuestas.</p>
                    <?php if (!$isPaciente): ?>
                    <a href="/encuestas/crear" class="win-btn win-btn-primary">Crear primera encuesta</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="win-inset m-2">
                    <table class="win-table">
                        <thead>
                            <tr>
                                <th>T&iacute;tulo</th>
                                <th>Preg.</th>
                                <th>Estado</th>
                                <th>Creada</th>
                                <th style="width: 120px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($encuestas as $e): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold win-text"><?= htmlspecialchars($e['titulo']) ?></div>
                                        <small class="win-text"><?= htmlspecialchars($e['descripcion']) ?></small>
                                    </td>
                                    <td class="win-text"><?= $e['preguntas'] ?></td>
                                    <td>
                                        <?php if ($isPaciente): ?>
                                            <span class="win-text"><?= $e['activa'] ? 'Activa' : 'Inactiva' ?></span>
                                        <?php else: ?>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="toggle-<?= $e['id'] ?>"<?= $e['activa'] ? ' checked' : '' ?> data-encuesta-id="<?= $e['id'] ?>">
                                            <label class="form-check-label small win-text" for="toggle-<?= $e['id'] ?>"><?= $e['activa'] ? 'Activa' : 'Inactiva' ?></label>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="win-text"><?= htmlspecialchars($e['creada']) ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="/encuestas/resultados?id=<?= $e['id'] ?>" class="win-btn win-btn-sm" title="Ver resultados">
                                                <i class="bi bi-bar-chart"></i>
                                            </a>
                                            <button type="button" class="win-btn win-btn-sm" title="Copiar enlace" onclick="Elyra.copiarEnlaceEncuesta(<?= $e['id'] ?>, this)">
                                                <i class="bi bi-link-45deg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
