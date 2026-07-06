(function () {
    'use strict';

    window.Elyra = window.Elyra || {};

    window.Elyra.toast = function (message, type) {
        type = type || 'success';
        var container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1060';
            document.body.appendChild(container);
        }
        var icons = {
            success: 'bi-check-circle-fill',
            danger: 'bi-exclamation-triangle-fill',
            warning: 'bi-exclamation-circle-fill',
            info: 'bi-info-circle-fill'
        };
        var html = '<div class="toast align-items-center text-bg-' + type + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">';
        html += '<div class="d-flex"><div class="toast-body"><i class="bi ' + (icons[type] || icons.info) + ' me-2"></i>' + message + '</div>';
        html += '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button></div></div>';
        container.insertAdjacentHTML('beforeend', html);
        var el = container.lastElementChild;
        var toast = new bootstrap.Toast(el, { delay: 4000 });
        toast.show();
        el.addEventListener('hidden.bs.toast', function () { el.remove(); });
    };

    window.Elyra.confirm = function (message, callback) {
        if (window.confirm(message)) {
            callback();
        }
    };

    window.Elyra.verQR = function (id) {
        var modal = document.getElementById('qrModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'qrModal';
            modal.className = 'modal fade';
            modal.setAttribute('tabindex', '-1');
            modal.setAttribute('aria-hidden', 'true');
            modal.innerHTML = '<div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h6 class="modal-title">C&oacute;digo QR</h6><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body text-center" id="qrModalBody"><p class="text-muted small mb-0">Cargando...</p></div></div></div>';
            document.body.appendChild(modal);
        }
        var body = document.getElementById('qrModalBody');
        body.innerHTML = '<div class="mb-3"><div id="qrcode"></div></div><p class="small text-muted mb-2">Escanear para ver el documento</p><button class="btn btn-sm btn-outline-primary me-1" onclick="Elyra.copiarEnlace(' + id + ', this)"><i class="bi bi-clipboard me-1"></i>Copiar enlace</button><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Imprimir</button>';
        var bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        if (typeof QRCode !== 'undefined') {
            document.getElementById('qrcode').innerHTML = '';
            new QRCode(document.getElementById('qrcode'), { text: window.location.origin + '/publico/doc?id=' + id, width: 180, height: 180 });
        } else {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
            script.onload = function () {
                document.getElementById('qrcode').innerHTML = '';
                new QRCode(document.getElementById('qrcode'), { text: window.location.origin + '/publico/doc?id=' + id, width: 180, height: 180 });
            };
            script.onerror = function () {
                body.innerHTML = '<p class="text-muted small">No se pudo cargar el generador QR</p>' + body.innerHTML;
            };
            document.head.appendChild(script);
        }
    };

    window.Elyra.copiarEnlace = function (id, btn) {
        var url = window.location.origin + '/publico/doc?id=' + id;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                var original = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check me-1"></i>Copiado';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-outline-success');
                setTimeout(function () {
                    btn.innerHTML = original;
                    btn.classList.remove('btn-outline-success');
                    btn.classList.add('btn-outline-secondary');
                }, 2000);
            });
        } else {
            var textarea = document.createElement('textarea');
            textarea.value = url;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            Elyra.toast('Enlace copiado al portapapeles');
        }
    };

    var theme = (function () {
        var stored = localStorage.getItem('elyra-theme');
        if (stored) return stored;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    })();

    function applyTheme(t) {
        document.documentElement.setAttribute('data-theme', t);
        localStorage.setItem('elyra-theme', t);
        var btn = document.getElementById('darkModeToggle');
        if (btn) {
            btn.innerHTML = t === 'dark'
                ? '<i class="bi bi-sun-fill"></i>'
                : '<i class="bi bi-moon-stars-fill"></i>';
        }
    }

    applyTheme(theme);

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('darkModeToggle');
        if (btn) {
            btn.addEventListener('click', function () {
                var current = document.documentElement.getAttribute('data-theme') || 'light';
                applyTheme(current === 'dark' ? 'light' : 'dark');
            });
        }
    });
})();
