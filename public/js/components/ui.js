(function () {
    'use strict';

    window.Elyra = window.Elyra || {};

    window.Elyra.toast = function (message, type) {
        type = type || 'success';
        var container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        var icons = {
            success: 'bi-check-circle-fill',
            danger: 'bi-exclamation-triangle-fill',
            warning: 'bi-exclamation-circle-fill',
            info: 'bi-info-circle-fill'
        };
        var colors = {
            success: '#DFF0D8',
            danger: '#F2DEDE',
            warning: '#FCF8E3',
            info: '#D9EDF7'
        };
        var borderColors = {
            success: '#C1E2B3',
            danger: '#EED3D7',
            warning: '#FBEED4',
            info: '#BCE8F1'
        };
        var textColors = {
            success: '#3C763D',
            danger: '#A94442',
            warning: '#8A6D3B',
            info: '#31708F'
        };
        var el = document.createElement('div');
        el.className = 'toast';
        el.style.background = colors[type] || colors.info;
        el.style.borderColor = borderColors[type] || borderColors.info;
        el.style.color = textColors[type] || textColors.info;
        el.innerHTML = '<i class="bi ' + (icons[type] || icons.info) + ' me-2"></i>' + message;
        container.appendChild(el);
        setTimeout(function () {
            el.style.opacity = '0';
            el.style.transition = 'opacity 0.3s';
            setTimeout(function () { el.remove(); }, 300);
        }, 4000);
    };

    window.Elyra.confirm = function (id, titulo) {
        var modal = document.getElementById('eliminarModal');
        if (!modal) return;
        document.getElementById('eliminarMensaje').textContent = 'Eliminar "' + titulo + '"?';
        document.getElementById('eliminarConfirmar').href = '/documentos/eliminar?id=' + id;
        modal.classList.add('open');
    };

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal-close') || e.target.closest('.modal-close')) {
            var modal = e.target.closest('.modal-overlay');
            if (modal) modal.classList.remove('open');
        }
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('open');
            var embed = e.target.querySelector('iframe');
            if (embed) embed.src = '';
        }
        var qrBtn = e.target.closest('[data-qr-copy]');
        if (qrBtn) {
            window.Elyra.copiarEnlace(parseInt(qrBtn.getAttribute('data-qr-copy')), qrBtn);
        }
    });

    window.Elyra.verQR = function (id) {
        var modal = document.getElementById('qrModal');
        if (!modal) return;
        var body = document.getElementById('qrModalBody');
        var url = window.location.origin + '/publico/doc?id=' + id;
        body.innerHTML = '<div class="mb-3"><div id="qrcode"></div></div><p class="small text-muted mb-2">Escanear para ver el documento</p><button class="btn btn-sm btn-primary me-1" data-qr-copy="' + id + '"><i class="bi bi-clipboard me-1"></i>Copiar enlace</button><button class="btn btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Imprimir</button>';
        modal.classList.add('open');
        if (typeof window.QRCode !== 'undefined') {
            document.getElementById('qrcode').innerHTML = '';
            new window.QRCode(document.getElementById('qrcode'), { text: url, width: 180, height: 180 });
        } else {
            var script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
            script.onload = function () {
                document.getElementById('qrcode').innerHTML = '';
                new window.QRCode(document.getElementById('qrcode'), { text: url, width: 180, height: 180 });
            };
            script.onerror = function () {
                body.innerHTML = '<p class="text-muted small">No se pudo cargar el generador QR</p>' + body.innerHTML;
            };
            document.head.appendChild(script);
        }
    };

    function initUploadForm() {
        var form = document.getElementById('uploadForm');
        var zone = document.getElementById('dropZone');
        var input = document.getElementById('archivo');
        var nameEl = document.getElementById('fileName');
        var sizeEl = document.getElementById('fileSize');
        var removeBtn = document.getElementById('removeFile');
        var errorEl = document.getElementById('fileError');
        var progressContainer = document.getElementById('progressContainer');
        var progressBar = document.getElementById('progressBar');
        var submitBtn = document.getElementById('submitBtn');
        if (!form || !zone || !input) return;

        function formatSize(bytes) {
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        function validate(file) {
            if (!file) return 'Seleccioná un archivo PDF.';
            if (file.type !== 'application/pdf') return 'Solo se permiten archivos PDF.';
            if (file.size > 10 * 1024 * 1024) return 'El archivo supera los 10 MB.';
            return '';
        }

        function showFile(file) {
            var err = validate(file);
            if (err) {
                zone.classList.remove('has-file');
                errorEl.textContent = err;
                errorEl.style.display = 'block';
                input.value = '';
                return;
            }
            errorEl.style.display = 'none';
            zone.classList.add('has-file');
            nameEl.textContent = file.name;
            sizeEl.textContent = formatSize(file.size);
            submitBtn.disabled = false;
        }

        input.addEventListener('change', function () {
            if (this.files && this.files[0]) showFile(this.files[0]);
        });

        ['dragenter', 'dragover'].forEach(function (ev) {
            zone.addEventListener(ev, function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(function (ev) {
            zone.addEventListener(ev, function (e) {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('drag-over');
            });
        });

        zone.addEventListener('drop', function (e) {
            var files = e.dataTransfer.files;
            if (files && files[0]) {
                input.files = files;
                showFile(files[0]);
            }
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                zone.classList.remove('has-file');
                input.value = '';
                submitBtn.disabled = false;
            });
        }

        form.addEventListener('submit', function (e) {
            if (!input.files || !input.files[0]) return;
            if (!window.XMLHttpRequest) return;

            e.preventDefault();

            var fd = new FormData(form);
            var xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function (ev) {
                if (!ev.lengthComputable) return;
                var pct = Math.round((ev.loaded / ev.total) * 100);
                progressContainer.classList.remove('d-none');
                progressBar.style.width = pct + '%';
                progressBar.textContent = pct + '%';
            });

            xhr.addEventListener('load', function () {
                progressContainer.classList.add('d-none');
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';

                var res = JSON.parse(xhr.responseText);
                if (xhr.status >= 200 && xhr.status < 300) {
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    }
                } else {
                    errorEl.textContent = res.error || 'Error al subir el archivo.';
                    errorEl.style.display = 'block';
                    submitBtn.disabled = false;
                }
            });

            xhr.addEventListener('error', function () {
                progressContainer.classList.add('d-none');
                errorEl.textContent = 'Error de conexión al subir el archivo.';
                errorEl.style.display = 'block';
                submitBtn.disabled = false;
            });

            submitBtn.disabled = true;
            xhr.open('POST', form.action);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(fd);
        });
    }

    function initFeedback() {
        var btns = document.querySelectorAll('.feedback-btn');
        if (!btns.length) return;
        btns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var parent = this.closest('.public-doc-feedback');
                var msg = parent.querySelector('#feedbackMsg');
                btns.forEach(function (b) { b.disabled = true; });
                msg.textContent = 'Gracias por tu opinión.';
                msg.classList.remove('d-none');
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initUploadForm();
        initFeedback();
        initEncuestaToggles();
    });

    window.Elyra.copiarEnlace = function (id, btn) {
        var url = window.location.origin + '/publico/doc?id=' + id;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                var original = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check me-1"></i>Copiado';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
                setTimeout(function () {
                    btn.innerHTML = original;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-primary');
                }, 2000);
            });
        } else {
            var textarea = document.createElement('textarea');
            textarea.value = url;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            window.Elyra.toast('Enlace copiado al portapapeles');
        }
    };

    window.Elyra.actualizarEstado = function (id, estadoActual) {
        window.location.href = '/traslados/actualizar-estado?id=' + id + '&estado=' + estadoActual;
    };

    window.Elyra.copiarEnlaceEncuesta = function (id, btn) {
        var url = window.location.origin + '/publico/encuesta?id=' + id;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function () {
                var original = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check me-1"></i>Copiado';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
                setTimeout(function () {
                    btn.innerHTML = original;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-primary');
                }, 2000);
            });
        } else {
            var textarea = document.createElement('textarea');
            textarea.value = url;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            window.Elyra.toast('Enlace copiado al portapapeles');
        }
    };

    window.Elyra.verDocPublico = function (id, titulo) {
        var modalEl = document.getElementById('docPublicoModal');
        var embedEl = document.getElementById('publicoPreviewEmbed');
        var titleEl = document.getElementById('publicoPreviewTitle');
        if (!modalEl || !embedEl || !titleEl) return;
        titleEl.textContent = titulo;
        embedEl.src = '';
        modalEl.classList.add('open');
        embedEl.src = '/publico/archivo?id=' + id;
    };

    window.Elyra.verDocumento = function (id, titulo, categoria, especialidad, subido) {
        var titleEl = document.getElementById('previewTitle');
        var metaEl = document.getElementById('previewMeta');
        var embedEl = document.getElementById('previewEmbed');
        var downloadEl = document.getElementById('previewDownload');

        if (!embedEl) return;

        titleEl.textContent = titulo;

        var badges = '';
        if (especialidad) {
            badges += '<span class="badge" style="background:#E8EDF5;color:#3B5998;border:1px solid #CCD9F0;">' + escapeHtml(especialidad) + '</span> ';
        }
        badges += '<span class="badge" style="background:#D9EDF7;color:#31708F;border:1px solid #BCE8F1;">' + escapeHtml(categoria) + '</span> ';
        badges += '<small class="text-muted">Subido el ' + escapeHtml(subido) + '</small>';
        metaEl.innerHTML = badges;

        embedEl.src = '/documentos/archivo?id=' + id;
        downloadEl.href = '/documentos/archivo?id=' + id + '&descargar=1';

        var modal = document.getElementById('docPreviewModal');
        if (modal) modal.classList.add('open');
    };

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function initEncuestaToggles() {
        var toggles = document.querySelectorAll('[data-encuesta-id]');
        toggles.forEach(function (t) {
            t.addEventListener('change', function () {
                var label = this.nextElementSibling;
                label.textContent = this.checked ? 'Activa' : 'Inactiva';
            });
        });
    }
})();
