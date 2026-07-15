<?php $titulo = 'Crear encuesta'; ?>
<?php ob_start(); ?>

<div class="row justify-content-center">
    <div class="col-lg-10">

        <?php if (isset($error)): ?>
            <div class="msg msg-error d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="panel">
            <div class="panel-body">
                <h5 class="fw-bold mb-3"><i class="bi bi-plus-square me-2 text-primary"></i>Nueva encuesta</h5>

                <form method="post" id="encuestaForm">
                    <div style="position:absolute;left:-9999px" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                    </div>
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['_csrf_token'] ?? '') ?>">

                    <div class="mb-3">
                        <label for="titulo" class="form-label">T&iacute;tulo de la encuesta <span class="text-danger">*</span></label>
                        <input type="text" name="titulo" id="titulo" class="form-input" required minlength="3" maxlength="200" placeholder="Ej: Satisfacci&oacute;n general del paciente" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label">Descripci&oacute;n (opcional)</label>
                        <textarea name="descripcion" id="descripcion" class="form-input" rows="2" maxlength="500" placeholder="Breve descripci&oacute;n de la encuesta..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0"><i class="bi bi-question-circle me-1"></i>Preguntas</h6>
                        <button type="button" class="btn btn-sm btn-primary" id="addQuestion"><i class="bi bi-plus-lg me-1"></i>Agregar pregunta</button>
                    </div>

                    <div id="questionsContainer">
                        <p class="text-muted small" id="noQuestions">No hay preguntas. Hac&eacute; clic en "Agregar pregunta" para empezar.</p>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn"><i class="bi bi-check-lg me-1"></i> Guardar encuesta</button>
                        <a href="encuestas" class="btn">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script nonce="<?= $nonce ?>">
(function () {
    var qIndex = 0;
    var container = document.getElementById('questionsContainer');
    var addBtn = document.getElementById('addQuestion');
    var noMsg = document.getElementById('noQuestions');
    var form = document.getElementById('encuestaForm');

    var tipos = [
        { value: 'multiple_choice', label: 'Opci&oacute;n m&uacute;ltiple' },
        { value: 'escala', label: 'Escala (1-5)' },
        { value: 'texto', label: 'Texto libre' },
    ];

    function addQuestion(data) {
        data = data || {};
        var idx = qIndex++;
        var div = document.createElement('div');
        div.className = 'question-item card border mb-3';
        div.dataset.index = idx;

        var tipoOptions = tipos.map(function (t) {
            var sel = (data.tipo || 'multiple_choice') === t.value ? ' selected' : '';
            return '<option value="' + t.value + '"' + sel + '>' + t.label + '</option>';
        }).join('');

        var html = '<div class="card-body">';
        html += '<div class="d-flex align-items-start gap-2">';
        html += '<div class="flex-grow-1">';
        html += '<input type="text" name="preguntas[' + idx + '][texto]" class="form-input mb-2" placeholder="Escrib&iacute; la pregunta..." required value="' + (data.texto || '') + '">';
        html += '<div class="row g-2 align-items-center">';
        html += '<div class="col-auto"><select name="preguntas[' + idx + '][tipo]" class="form-select tipo-select" data-idx="' + idx + '">' + tipoOptions + '</select></div>';
        html += '<div class="col"><div class="opciones-container" id="opciones-' + idx + '"></div></div>';
        html += '<div class="col-auto"><button type="button" class="btn btn-sm btn-outline-danger remove-question" title="Quitar pregunta"><i class="bi bi-trash"></i></button></div>';
        html += '</div></div></div></div>';

        div.innerHTML = html;
        container.appendChild(div);
        noMsg.style.display = 'none';
        updateOpciones(idx, data.tipo || 'multiple_choice', data.opciones || []);
    }

    function updateOpciones(idx, tipo, opciones) {
        var cont = document.getElementById('opciones-' + idx);
        if (!cont) return;
        cont.innerHTML = '';
        if (tipo !== 'multiple_choice') return;

        var wrapper = document.createElement('div');
        wrapper.className = 'd-flex flex-wrap align-items-center gap-1 opciones-wrapper';

        function addOption(val) {
            var optIdx = wrapper.querySelectorAll('.opcion-item').length;
            var item = document.createElement('span');
            item.className = 'opcion-item input-group input-group-sm';
            item.style.width = 'auto';
            item.innerHTML = '<input type="text" name="preguntas[' + idx + '][opciones][]" class="form-input" placeholder="Opci&oacute;n" value="' + (val || '') + '" style="width:130px" required> <button type="button" class="btn btn-sm opcion-remove"><i class="bi bi-x"></i></button>';
            item.querySelector('.opcion-remove').addEventListener('click', function () {
                item.remove();
            });
            wrapper.insertBefore(item, wrapper.lastElementChild);
        }

        if (opciones.length === 0) {
            addOption('');
            addOption('');
        } else {
            opciones.forEach(function (o) { addOption(o); });
        }

        var addOptBtn = document.createElement('button');
        addOptBtn.type = 'button';
        addOptBtn.className = 'btn btn-outline-primary btn-sm';
        addOptBtn.innerHTML = '<i class="bi bi-plus"></i>';
        addOptBtn.title = 'Agregar opci&oacute;n';
        addOptBtn.addEventListener('click', function () { addOption(''); });
        wrapper.appendChild(addOptBtn);

        cont.appendChild(wrapper);
    }

    container.addEventListener('change', function (e) {
        if (e.target.classList.contains('tipo-select')) {
            var idx = parseInt(e.target.dataset.idx);
            updateOpciones(idx, e.target.value, []);
        }
    });

    container.addEventListener('click', function (e) {
        if (e.target.closest('.remove-question')) {
            var item = e.target.closest('.question-item');
            item.remove();
            if (!container.querySelector('.question-item')) {
                noMsg.style.display = '';
            }
        }
    });

    addBtn.addEventListener('click', function () { addQuestion({}); });
})();
</script>
<?php $scripts = ob_get_clean(); ?>

<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
