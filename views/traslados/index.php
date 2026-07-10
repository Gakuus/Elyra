<?php $titulo = 'Traslados en ambulancia'; ?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <div class="panel mb-2">
            <div class="panel-heading d-flex justify-content-between align-items-center">
                <span><i class="bi bi-truck me-1"></i> Traslados en ambulancia</span>
                <div class="d-flex gap-1">
                    <a href="/traslados/historial" class="btn py-0 px-3" style="font-size: 11px;">
                        <i class="bi bi-clock-history me-1"></i> Historial
                    </a>
                    <a href="/traslados/nuevo" class="btn btn-primary py-0 px-3" style="font-size: 11px;">
                        <i class="bi bi-plus-circle me-1"></i> Nuevo
                    </a>
                </div>
            </div>

            <div class="p-3">
                <div class="row g-2 mb-2">
                    <div class="col-md-3">
                        <div class="panel text-center p-2" style="cursor: pointer;" data-filtro="pendiente">
                            <div class="fw-bold" style="font-size: 20px;"><?= $pendientes ?></div>
                            <div class="small">Pendientes</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel text-center p-2" style="cursor: pointer;" data-filtro="en_curso">
                            <div class="fw-bold" style="font-size: 20px;"><?= $enCurso ?></div>
                            <div class="small">En curso</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel text-center p-2" style="cursor: pointer;" data-filtro="completado_hoy">
                            <div class="fw-bold" style="font-size: 20px;"><?= $completadosHoy ?></div>
                            <div class="small">Completados hoy</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="panel text-center p-2">
                            <div class="fw-bold" style="font-size: 20px;"><?= $totalHoy ?></div>
                            <div class="small">Total hoy</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-2">
                    <select id="filtroEstado" class="form-input" onchange="filtrarTraslados()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_curso">En curso</option>
                        <option value="en_destino">En destino</option>
                        <option value="en_retorno">En retorno</option>
                        <option value="completado">Completado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                    <input type="text" id="busquedaTraslado" class="form-input flex-grow-1" placeholder="Buscar por c\u00f3digo, conductor, destino..." oninput="filtrarTraslados()">
                </div>

                <div class="panel-inset">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>C\u00f3digo</th>
                                <th>Conductor</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Estado</th>
                                <th>Salida</th>
                                <th style="width: 100px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($traslados as $t): ?>
                                <tr class="fila-traslado" data-estado="<?= htmlspecialchars($t['estado']) ?>" data-busqueda="<?= htmlspecialchars(mb_strtolower($t['codigo'] . ' ' . $t['conductor'] . ' ' . $t['destino'])) ?>">
                                    <td class="fw-bold"><?= htmlspecialchars($t['codigo']) ?></td>
                                    <td class=""><?= htmlspecialchars($t['conductor']) ?></td>
                                    <td class=""><?= htmlspecialchars($t['origen']) ?></td>
                                    <td class=""><?= htmlspecialchars($t['destino']) ?></td>
                                    <td>
                                        <span class="fw-bold"><?= htmlspecialchars($t['estado_texto']) ?></span>
                                    </td>
                                    <td class=""><?= htmlspecialchars($t['salida']) ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="/traslados/ver?id=<?= $t['id'] ?>" class="btn btn-sm" title="Ver detalle">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($traslados)): ?>
                                <tr>
                                    <td colspan="7" class="text-center p-3">No hay traslados para mostrar.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php $scripts = <<<HTML
<script nonce="{$nonce}">
function filtrarTraslados() {
    var estado = document.getElementById('filtroEstado').value;
    var busqueda = document.getElementById('busquedaTraslado').value.toLowerCase();
    document.querySelectorAll('.fila-traslado').forEach(function(row) {
        var matchEstado = !estado || row.dataset.estado === estado;
        var matchBusqueda = !busqueda || row.dataset.busqueda.includes(busqueda);
        row.style.display = matchEstado && matchBusqueda ? '' : 'none';
    });
}

document.querySelectorAll('[data-filtro]').forEach(function(card) {
    card.addEventListener('click', function() {
        document.getElementById('filtroEstado').value = this.dataset.filtro;
        filtrarTraslados();
    });
});
</script>
HTML;
?>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
