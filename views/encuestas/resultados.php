<?php $titulo = 'Resultados: ' . htmlspecialchars($encuesta['titulo']); ?>
<?php ob_start(); ?>

<div class="resultados-header">
    <a href="/encuestas" class="btn btn-sm mb-2"><i class="bi bi-arrow-left me-1"></i> Volver a encuestas</a>
    <h4 class="fw-semibold"><?= htmlspecialchars($encuesta['titulo']) ?></h4>
    <p class="text-muted small mb-0">
        <span class="badge badge-secondary text-secondary me-2"><?= count($encuesta['preguntas']) ?> preguntas</span>
        <span class="badge badge-info text-info"><?= $totalRespuestas ?> respuestas</span>
    </p>
</div>

<?php if ($totalRespuestas === 0): ?>
    <div class="text-center py-5">
        <div class="display-6 text-muted mb-3"><i class="bi bi-bar-chart"></i></div>
        <h5 class="fw-semibold">Sin respuestas</h5>
        <p class="text-muted mb-0">A&uacute;n no hay respuestas registradas para esta encuesta.</p>
    </div>
<?php else: ?>
    <div class="resultados-charts">
        <?php foreach ($stats as $i => $s): ?>
            <div class="panel mb-4">
                <div class="panel-body">
                    <h6 class="fw-semibold mb-3"><?= ($i + 1) ?>. <?= htmlspecialchars($s['texto']) ?></h6>

                    <?php if ($s['tipo'] === 'multiple_choice'): ?>
                        <div class="chart-container" style="position:relative; height:<?= max(200, count($s['datos']) * 50) ?>px;">
                            <canvas id="chart-<?= $i ?>"></canvas>
                        </div>

                    <?php elseif ($s['tipo'] === 'escala'): ?>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <div class="chart-container" style="position:relative; height:220px;">
                                    <canvas id="chart-<?= $i ?>"></canvas>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex flex-column justify-content-center">
                                <div class="text-center p-3 bg-light rounded-3">
                                    <div class="display-5 fw-bold text-primary"><?= $s['promedio'] ?></div>
                                    <div class="text-muted small">Promedio (1-5)</div>
                                    <div class="mt-2">
                                        <?php for ($e = 1; $e <= 5; $e++): ?>
                                            <i class="bi bi-star<?= $e <= round($s['promedio']) ? '-fill text-warning' : '-fill text-muted opacity-25' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="mt-2 text-center small text-muted">
                                    <?php foreach ($s['datos'] as $v => $c): ?>
                                        <span class="me-2"><?= htmlspecialchars((string) $v) ?>: <?= $c ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($s['tipo'] === 'texto'): ?>
                        <?php if (empty($s['datos'])): ?>
                            <p class="text-muted small mb-0">Sin respuestas de texto.</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($s['datos'] as $t): ?>
                                    <div class="list-group-item py-2 px-0">
                                        <i class="bi bi-quote text-muted me-1"></i>
                                        <?= htmlspecialchars($t) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script nonce="<?= $nonce ?>" src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script nonce="<?= $nonce ?>">
    document.addEventListener('DOMContentLoaded', function () {
        var colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14', '#0dcaf0', '#6610f2', '#d63384'];
        var stats = <?= json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        stats.forEach(function (s, i) {
            var canvas = document.getElementById('chart-' + i);
            if (!canvas) return;

            if (s.tipo === 'multiple_choice') {
                var labels = Object.keys(s.datos);
                var values = Object.values(s.datos);
                new Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors.slice(0, labels.length),
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, ticks: { stepSize: 1 } },
                            y: { ticks: { font: { size: 13 } } }
                        }
                    }
                });
            } else if (s.tipo === 'escala') {
                var labels = ['1', '2', '3', '4', '5'];
                var values = labels.map(function (l) { return s.datos[l] || 0; });
                var bg = values.map(function (v, idx) { return colors[idx] || '#ccc'; });
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: bg,
                            borderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { font: { size: 13 } } }
                        }
                    }
                });
            }
        });
    });
    </script>
<?php endif; ?>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
