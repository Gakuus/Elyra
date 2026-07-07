<?php
/** @var array $docs Lista de documentos */
/** @var bool $isPaciente */
$isPaciente = $isPaciente ?? \Elyra\Infrastructure\Service\SessionManager::isPaciente();
?>
<div class="table-responsive">
    <table class="table table-elyra mb-0">
        <thead>
            <tr>
                <th style="width: 50px;">QR</th>
                <th>T&iacute;tulo</th>
                <th>Categor&iacute;a</th>
                <th>Paciente</th>
                <th>Subido</th>
                <th style="width: 140px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($docs as $doc): ?>
                <tr>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-secondary border-0" onclick="Elyra.verQR(<?= $doc['id'] ?>)" title="Ver QR">
                            <i class="bi bi-qr-code"></i>
                        </button>
                    </td>
                    <td class="fw-semibold">
                        <?= htmlspecialchars($doc['titulo']) ?>
                        <?php if (!$doc['activo']): ?>
                            <span class="badge bg-secondary ms-2">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($doc['especialidad']): ?>
                            <span class="badge bg-info bg-opacity-10 text-info me-1"><?= htmlspecialchars($doc['especialidad']) ?></span>
                        <?php endif; ?>
                        <span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($doc['categoria']) ?></span>
                    </td>
                    <td class="text-muted small">
                        <?php if ($doc['paciente']): ?>
                            <span class="badge bg-success bg-opacity-10 text-success"><i class="bi bi-person"></i> <?= htmlspecialchars($doc['paciente']) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-globe"></i> General</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($doc['subido']) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/documentos/ver?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary border-0" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <?php if (!$isPaciente): ?>
                            <a href="/documentos/editar?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary border-0" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary border-0" title="Copiar enlace" onclick="Elyra.copiarEnlace(<?= $doc['id'] ?>, this)">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                            <?php if (!$isPaciente): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0" title="Eliminar" onclick="Elyra.confirm(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['titulo'], ENT_QUOTES) ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card-list-view">
    <?php foreach ($docs as $doc): ?>
        <div class="card-item">
            <div class="card-item-title"><?= htmlspecialchars($doc['titulo']) ?></div>
            <div class="card-item-meta">
                <?php if ($doc['especialidad']): ?>
                    <span class="badge bg-info bg-opacity-10 text-info me-1"><?= htmlspecialchars($doc['especialidad']) ?></span>
                <?php endif; ?>
                <span class="badge bg-primary bg-opacity-10 text-primary me-2"><?= htmlspecialchars($doc['categoria']) ?></span>
                <?php if ($doc['paciente']): ?>
                    <span class="badge bg-success bg-opacity-10 text-success me-2"><i class="bi bi-person"></i> <?= htmlspecialchars($doc['paciente']) ?></span>
                <?php else: ?>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary me-2"><i class="bi bi-globe"></i> General</span>
                <?php endif; ?>
                <?= htmlspecialchars($doc['subido']) ?>
                <?php if (!$doc['activo']): ?>
                    <span class="badge bg-secondary ms-2">Inactivo</span>
                <?php endif; ?>
            </div>
            <div class="card-item-actions">
                <button class="btn btn-sm btn-outline-secondary" onclick="Elyra.verQR(<?= $doc['id'] ?>)"><i class="bi bi-qr-code"></i></button>
                <a href="/documentos/ver?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                <?php if (!$isPaciente): ?>
                <a href="/documentos/editar?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <?php endif; ?>
                <button class="btn btn-sm btn-outline-secondary" onclick="Elyra.copiarEnlace(<?= $doc['id'] ?>, this)"><i class="bi bi-link-45deg"></i></button>
                <?php if (!$isPaciente): ?>
                <button class="btn btn-sm btn-outline-danger" onclick="Elyra.confirm(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['titulo'], ENT_QUOTES) ?>')"><i class="bi bi-trash"></i></button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
