<?php $titulo = 'Noticias'; ?>
<?php ob_start(); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="m-0">Noticias</h4>
    <a href="/noticias/crear" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nueva noticia</a>
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
                        <a href="/noticias/toggle?id=<?= $n['id'] ?>" class="text-decoration-none">
                            <?php if ($n['activo']): ?>
                                <span class="badge bg-success">Sí</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </a>
                    </td>
                    <td>
                        <a href="/noticias/editar?id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
                        <a href="/noticias/eliminar?id=<?= $n['id'] ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar esta noticia?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
