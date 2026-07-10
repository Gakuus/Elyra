<?php $titulo = htmlspecialchars($encuesta['titulo'] ?? 'Encuesta'); ?>
<?php ob_start(); ?>

<?php if (isset($_GET['ok'])): ?>
<div class="public-encuesta-wrapper">
    <div class="panel text-center py-5 px-4">
        <div style="font-size:36px;color:var(--success);margin-bottom:10px;"><i class="bi bi-check-circle-fill"></i></div>
        <h4 class="fw-semibold">Gracias por tu opinión</h4>
        <p class="text-muted mb-0">Tu respuesta ha sido registrada correctamente.</p>
    </div>
</div>
<?php else: ?>

<div class="public-encuesta-wrapper">

    <?php if (isset($error)): ?>
        <div class="msg msg-error d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><?= $error ?></span>
        </div>
    <?php endif; ?>

    <div class="panel">
        <div class="panel-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-bar-chart" style="font-size:32px;color:var(--blue);"></i>
                <h4 class="fw-semibold mt-2"><?= htmlspecialchars($encuesta['titulo']) ?></h4>
                <?php if (!empty($encuesta['descripcion'])): ?>
                    <p class="text-muted mb-0"><?= htmlspecialchars($encuesta['descripcion']) ?></p>
                <?php endif; ?>
            </div>

            <form method="post" id="encuestaRespForm" novalidate>
                <div style="position:absolute;left:-9999px" aria-hidden="true">
                    <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                </div>
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(\Elyra\Infrastructure\Service\SessionManager::getCsrfToken()) ?>">
                <input type="hidden" name="encuesta_id" value="<?= $encuesta['id'] ?>">

                <?php foreach ($encuesta['preguntas'] as $i => $p): ?>
                    <div class="public-pregunta mb-4">
                        <p class="fw-semibold mb-2"><?= ($i + 1) ?>. <?= htmlspecialchars($p['texto']) ?></p>

                        <?php if ($p['tipo'] === 'escala'): ?>
                            <div class="escala-group d-flex gap-2 flex-wrap" role="group" aria-label="Escala de 1 a 5">
                                <?php for ($v = 1; $v <= 5; $v++): ?>
                                    <label class="escala-label">
                                        <input type="radio" name="respuestas[<?= $i ?>]" value="<?= $v ?>" required>
                                        <span><?= $v ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted">1 = Muy malo &middot; 5 = Excelente</small>

                        <?php elseif ($p['tipo'] === 'multiple_choice' && !empty($p['opciones'])): ?>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($p['opciones'] as $o): ?>
                                    <label class="mc-label d-flex align-items-center gap-2 p-2 rounded border">
                                        <input type="radio" name="respuestas[<?= $i ?>]" value="<?= htmlspecialchars($o) ?>" required>
                                        <span><?= htmlspecialchars($o) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($p['tipo'] === 'texto'): ?>
                            <textarea name="respuestas[<?= $i ?>]" class="form-textarea" rows="3" maxlength="500" placeholder="Escrib&iacute; tu respuesta..." required style="width:100%;"></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" class="btn btn-primary btn-lg w-100 mt-3">
                    <i class="bi bi-send me-1"></i> Enviar respuestas
                </button>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $nonce ?>">
(function () {
    var form = document.getElementById('encuestaRespForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var radios = form.querySelectorAll('input[type="radio"][required]');
        var groups = {};
        radios.forEach(function (r) {
            groups[r.name] = groups[r.name] || { checked: false, group: form.querySelectorAll('input[name="' + r.name + '"]') };
        });
        Object.keys(groups).forEach(function (name) {
            var g = groups[name];
            g.checked = form.querySelector('input[name="' + name + '"]:checked') !== null;
        });
        var invalid = Object.keys(groups).filter(function (name) { return !groups[name].checked; });
        if (invalid.length > 0) {
            e.preventDefault();
            var firstInvalid = form.querySelector('input[name="' + invalid[0] + '"]');
            if (firstInvalid) {
                firstInvalid.closest('.public-pregunta').scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.closest('.public-pregunta').style.borderLeft = '3px solid var(--danger)';
            }
        }
    });
})();
</script>

<?php endif; ?>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
