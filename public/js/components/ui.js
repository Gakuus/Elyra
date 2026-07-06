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
