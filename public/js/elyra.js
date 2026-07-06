(function () {
    'use strict';

    var meta = document.querySelector('meta[name="csrf-token"]');
    var token = meta ? meta.getAttribute('content') : '';

    window.Elyra = { csrfToken: token };

    if (token) {
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('submit', function (e) {
                var form = e.target;
                if ((form.method || 'get').toLowerCase() === 'post') {
                    if (!form.querySelector('[name="_csrf_token"]')) {
                        var input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = '_csrf_token';
                        input.value = token;
                        form.appendChild(input);
                    }
                }
            });
        });
    }
})();
