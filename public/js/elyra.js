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

    window.Elyra.fetch = function (url, options) {
        options = options || {};
        options.headers = options.headers || {};
        if (token) {
            options.headers['X-CSRF-Token'] = token;
        }
        if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
            options.headers['Content-Type'] = options.headers['Content-Type'] || 'application/json';
            options.body = JSON.stringify(options.body);
        }
        return fetch(url, options);
    };

    window.Elyra.setInputFilter = function (textbox, inputFilter, errMsg) {
        ['input', 'keydown', 'keyup', 'mousedown', 'mouseup', 'select', 'contextmenu', 'drop', 'focusout'].forEach(function (event) {
            textbox.addEventListener(event, function (e) {
                if (inputFilter(this.value)) {
                    if (['keydown', 'mousedown', 'focusout'].indexOf(e.type) >= 0) {
                        this.classList.remove('input-error');
                        this.setCustomValidity('');
                    }
                    this.oldValue = this.value;
                    this.oldSelectionStart = this.selectionStart;
                    this.oldSelectionEnd = this.selectionEnd;
                } else if (this.hasOwnProperty('oldValue')) {
                    this.classList.add('input-error');
                    this.setCustomValidity(errMsg);
                    this.reportValidity();
                    this.value = this.oldValue;
                    this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
                } else {
                    this.value = '';
                }
            });
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-numeric]').forEach(function (el) {
            window.Elyra.setInputFilter(el, function (value) {
                return /^\d*$/.test(value);
            }, 'Solo se permiten números');
        });
    });
})();
