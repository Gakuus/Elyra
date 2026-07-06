<?php $titulo = 'Documentos'; ?>
<?php ob_start(); ?>

<div class="action-bar">
    <a href="/documentos/subir" class="btn btn-primary">
        <i class="bi bi-upload me-1"></i> Subir documento
    </a>
    <form method="get" class="d-flex gap-2 flex-wrap" id="filterForm">
        <select name="categoria" class="form-select" onchange="this.form.submit()">
            <option value="">Todas las categor&iacute;as</option>
            <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>"<?= $categoriaFiltro == $cat['id'] ? ' selected' : '' ?>>
                    <?= $cat['nombre'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="position-relative">
            <input type="text" name="q" class="form-control ps-4" placeholder="Buscar t&iacute;tulo..." value="<?= htmlspecialchars($search) ?>" aria-label="Buscar documento">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted small"></i>
        </div>
    </form>
</div>

<?php if (empty($documentos)): ?>

<div class="text-center py-5">
    <div class="display-6 text-muted mb-3"><i class="bi bi-file-earmark-x"></i></div>
    <h5 class="fw-semibold">No hay documentos</h5>
    <p class="text-muted mb-4">
        <?= $search || $categoriaFiltro ? 'No se encontraron documentos con los filtros seleccionados.' : 'A&uacute;n no hay documentos subidos al sistema.' ?>
    </p>
    <?php if ($search || $categoriaFiltro): ?>
        <a href="/documentos" class="btn btn-outline-secondary">Limpiar filtros</a>
    <?php else: ?>
        <a href="/documentos/subir" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Subir primer documento</a>
    <?php endif; ?>
</div>

<?php else: ?>

<div class="table-responsive">
    <table class="table table-elyra mb-0">
        <thead>
            <tr>
                <th style="width: 50px;">QR</th>
                <th>T&iacute;tulo</th>
                <th>Categor&iacute;a</th>
                <th>Subido</th>
                <th style="width: 140px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($documentos as $doc): ?>
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
                    <td><span class="badge bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($doc['categoria']) ?></span></td>
                    <td class="text-muted small"><?= htmlspecialchars($doc['subido']) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="/documentos/ver?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary border-0" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="/documentos/editar?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary border-0" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary border-0" title="Copiar enlace" onclick="Elyra.copiarEnlace(<?= $doc['id'] ?>, this)">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger border-0" title="Eliminar" onclick="Elyra.confirm(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['titulo'], ENT_QUOTES) ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card-list-view">
    <?php foreach ($documentos as $doc): ?>
        <div class="card-item">
            <div class="card-item-title"><?= htmlspecialchars($doc['titulo']) ?></div>
            <div class="card-item-meta">
                <span class="badge bg-primary bg-opacity-10 text-primary me-2"><?= htmlspecialchars($doc['categoria']) ?></span>
                <?= htmlspecialchars($doc['subido']) ?>
                <?php if (!$doc['activo']): ?>
                    <span class="badge bg-secondary ms-2">Inactivo</span>
                <?php endif; ?>
            </div>
            <div class="card-item-actions">
                <button class="btn btn-sm btn-outline-secondary" onclick="Elyra.verQR(<?= $doc['id'] ?>)"><i class="bi bi-qr-code"></i></button>
                <a href="/documentos/ver?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                <a href="/documentos/editar?id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                <button class="btn btn-sm btn-outline-secondary" onclick="Elyra.copiarEnlace(<?= $doc['id'] ?>, this)"><i class="bi bi-link-45deg"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="Elyra.confirm(<?= $doc['id'] ?>, '<?= htmlspecialchars($doc['titulo'], ENT_QUOTES) ?>')"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($totalPaginas > 1): ?>
<nav class="mt-3 d-flex justify-content-between align-items-center" aria-label="Paginaci&oacute;n">
    <p class="text-muted small mb-0">Mostrando <?= count($documentos) ?> de <?= $total ?> documentos</p>
    <ul class="pagination pagination-sm mb-0">
        <li class="page-item<?= $pagina <= 1 ? ' disabled' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>" aria-label="Anterior">&laquo;</a>
        </li>
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="page-item<?= $i === $pagina ? ' active' : '' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item<?= $pagina >= $totalPaginas ? ' disabled' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>" aria-label="Siguiente">&raquo;</a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php endif; ?>

<div class="mt-3">
    <a href="/encuestas" class="btn btn-outline-primary btn-sm"><i class="bi bi-bar-chart me-1"></i> Ir a Encuestas</a>
</div>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
