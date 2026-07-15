<?php $titulo = 'Noticias'; ?>
<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Noticias</h4>
    <a href="noticias/crear" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nueva noticia</a>
</div>

<?php if (empty($noticias)): ?>
    <div class="text-center p-4 text-muted">No hay noticias registradas.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Fecha</th>
                    <th>Autor</th>
                    <th>Imagen</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($noticias as $n): ?>
                <tr>
                    <td class="fw-semibold"><?= htmlspecialchars($n['titulo']) ?></td>
                    <td class="small"><?= htmlspecialchars($n['creada']) ?></td>
                    <td class="small"><?= htmlspecialchars($n['autor']) ?></td>
                    <td>
                        <?php if ($n['imagen']): ?>
                            <i class="bi bi-check-lg text-success"></i>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="post" action="noticias/toggle" class="d-inline">
                            <input type="hidden" name="id" value="<?= $n['id'] ?>">
                            <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent text-decoration-none">
                            <?php if ($n['activo']): ?>
                                <span class="badge bg-success">S&iacute;</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="noticias/editar?id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="noticias/eliminar" class="d-inline" onsubmit="return confirm('¿Eliminar esta noticia?')">
                            <input type="hidden" name="id" value="<?= $n['id'] ?>">
                            <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
