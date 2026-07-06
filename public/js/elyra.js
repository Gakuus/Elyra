(function () {
    'use strict';

    var csrfToken = document.querySelector('meta[name="csrf-token"]');
    var token = csrfToken ? csrfToken.getAttribute('content') : '';

    document.addEventListener('DOMContentLoaded', function () {
        var toastEl = document.querySelector('.toast-container');
        if (toastEl) {
            var toasts = bootstrap.Toast.initAll();
        }
    });

    window.Elyra = {
        csrfToken: token,

        toast: function (message, type) {
            type = type || 'success';
            var container = document.querySelector('.toast-container');
            if (!container) {
                container = document.createElement('div');
                container.className = 'toast-container position-fixed top-0 end-0 p-3';
                container.style.zIndex = '9999';
                document.body.appendChild(container);
            }
            var icons = { success: 'bi-check-circle-fill', danger: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
            var html = '<div class="toast align-items-center text-bg-' + type + ' border-0" role="alert">';
            html += '<div class="d-flex"><div class="toast-body"><i class="bi ' + (icons[type] || icons.info) + ' me-2"></i> ' + message + '</div>';
            html += '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>';
            container.insertAdjacentHTML('beforeend', html);
            var el = container.lastElementChild;
            var toast = new bootstrap.Toast(el, { delay: 4000 });
            toast.show();
            el.addEventListener('hidden.bs.toast', function () { el.remove(); });
        },

        fetch: function (url, options) {
            options = options || {};
            options.headers = options.headers || {};
            if (token) {
                options.headers['X-CSRF-Token'] = token;
            }
            return fetch(url, options);
        },

        confirm: function (message, callback) {
            if (confirm(message)) {
                callback();
            }
        }
    };
})();
