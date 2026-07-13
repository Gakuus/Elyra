(function () {
    'use strict';

    var tipo = document.getElementById('tipo');
    var campoPaciente = document.getElementById('campo-paciente');
    var campoCatalogo = document.getElementById('campo-catalogo');
    var catalogoSelect = document.getElementById('catalogo_elemento_id');
    var labelCatalogo = document.getElementById('label-catalogo');
    var rutaSelect = document.getElementById('ruta_id');
    var origenSelect = document.getElementById('origen');
    var destinoSelect = document.getElementById('destino');
    var horaSalida = document.getElementById('hora_salida');
    var fechaSalida = document.getElementById('fecha_salida');
    var horaLlegada = document.getElementById('hora_llegada');
    var llegadaInfo = document.getElementById('llegada-info');
    var btnRecalcular = document.getElementById('btn-recalcular');

    var VELOCIDAD_PROMEDIO = 45;

    function toggleCampos() {
        var val = tipo.value;

        if (val === 'paciente') {
            campoPaciente.classList.remove('d-none');
            campoCatalogo.classList.add('d-none');
        } else if (val === 'organo') {
            campoPaciente.classList.remove('d-none');
            campoCatalogo.classList.remove('d-none');
            labelCatalogo.textContent = 'Órgano *';
            filtrarCatalogo('organo');
        } else if (val === 'insumo') {
            campoPaciente.classList.add('d-none');
            campoCatalogo.classList.remove('d-none');
            labelCatalogo.textContent = 'Insumo *';
            filtrarCatalogo('insumo');
        } else if (val === 'equipamiento') {
            campoPaciente.classList.add('d-none');
            campoCatalogo.classList.remove('d-none');
            labelCatalogo.textContent = 'Equipamiento *';
            filtrarCatalogo('equipamiento');
        }
    }

    function filtrarCatalogo(tipoCatalogo) {
        var opts = catalogoSelect.options;
        var defaultOpt = document.getElementById('opt-default-catalogo');
        for (var i = 0; i < opts.length; i++) {
            var opt = opts[i];
            if (opt === defaultOpt) {
                opt.style.display = '';
                continue;
            }
            var tipoOpt = opt.getAttribute('data-tipo');
            opt.style.display = (tipoOpt === tipoCatalogo) ? '' : 'none';
        }
        catalogoSelect.value = '';
    }

    function calcularHoraLlegada() {
        var rutaOpt = rutaSelect.options[rutaSelect.selectedIndex];
        var distancia = rutaOpt ? rutaOpt.getAttribute('data-distancia') : '';

        if (!distancia || distancia === '' || distancia === '0') {
            var origenVal = origenSelect.value;
            var destinoVal = destinoSelect.value;
            if (origenVal && destinoVal && origenVal !== destinoVal) {
                llegadaInfo.textContent = '';
                horaLlegada.value = '';
                horaLlegada.removeAttribute('readonly');
                return;
            }
            horaLlegada.value = '';
            llegadaInfo.textContent = 'Seleccioná una ruta con distancia para auto-calcular';
            return;
        }

        var distNum = parseFloat(distancia);
        if (isNaN(distNum) || distNum <= 0) {
            horaLlegada.value = '';
            return;
        }

        var horas = distNum / VELOCIDAD_PROMEDIO;
        var minutosTotales = Math.ceil(horas * 60);

        var salida = horaSalida.value;
        if (!salida) {
            horaLlegada.value = '';
            llegadaInfo.textContent = '';
            return;
        }

        var parts = salida.split(':');
        var h = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10);
        var totalMin = h * 60 + m + minutosTotales;
        var llegH = Math.floor(totalMin / 60) % 24;
        var llegM = totalMin % 60;

        var strH = llegH < 10 ? '0' + llegH : '' + llegH;
        var strM = llegM < 10 ? '0' + llegM : '' + llegM;

        horaLlegada.value = strH + ':' + strM;
        horaLlegada.setAttribute('readonly', 'readonly');

        var hLabel = horas < 1 ? minutosTotales + ' min' : numberFormat(horas) + ' h (' + minutosTotales + ' min)';
        llegadaInfo.textContent = distNum + ' km → ~' + hLabel + ' a ' + VELOCIDAD_PROMEDIO + ' km/h';
    }

    function numberFormat(n) {
        return Math.round(n * 10) / 10;
    }

    tipo.addEventListener('change', toggleCampos);
    rutaSelect.addEventListener('change', calcularHoraLlegada);
    horaSalida.addEventListener('change', calcularHoraLlegada);
    fechaSalida.addEventListener('change', calcularHoraLlegada);
    if (btnRecalcular) {
        btnRecalcular.addEventListener('click', calcularHoraLlegada);
    }

    toggleCampos();
    if (horaSalida.value && rutaSelect.value) {
        calcularHoraLlegada();
    }
})();
