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

    window.Elyra.confirm = function (id, titulo) {
        var modal = document.getElementById('eliminarModal');
        if (!modal) return;
        document.getElementById('eliminarMensaje').textContent = 'Eliminar "' + titulo + '"?';
        document.getElementById('eliminarConfirmar').href = '/documentos/eliminar?id=' + id;
        new bootstrap.Modal(modal).show();
    };

    window.Elyra.verQR = function (id) {
        var modal = document.getElementById('qrModal');
        if (!modal) return;
        var body = document.getElementById('qrModalBody');
        var url = window.location.origin + '/publico/doc?id=' + id;
        body.innerHTML = '<div class="mb-3"><div id="qrcode"></div></div><p class="small text-muted mb-2">Escanear para ver el documento</p><button class="btn btn-sm btn-outline-primary me-1" onclick="Elyra.copiarEnlace(' + id + ', this)"><i class="bi bi-clipboard me-1"></i>Copiar enlace</button><button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i>Imprimir</button>';
        var bsModal = new bootstrap.Modal(modal);
        bsModal.show();
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
            if (!file) return 'Seleccion&aacute; un archivo PDF.';
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
                errorEl.textContent = 'Error de conexi&oacute;n al subir el archivo.';
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
                msg.textContent = 'Gracias por tu opini&oacute;n.';
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
            window.Elyra.toast('Enlace copiado al portapapeles');
        }
    };

    window.Elyra.verDocumento = function (id, titulo, categoria, especialidad, subido, activo) {
        var titleEl = document.getElementById('previewTitle');
        var metaEl = document.getElementById('previewMeta');
        var embedEl = document.getElementById('previewEmbed');
        var downloadEl = document.getElementById('previewDownload');

        if (!embedEl) return;

        titleEl.textContent = titulo;

        var badges = '';
        if (especialidad) {
            badges += '<span class="badge bg-info bg-opacity-10 text-info me-1">' + escapeHtml(especialidad) + '</span>';
        }
        badges += '<span class="badge bg-primary bg-opacity-10 text-primary me-1">' + escapeHtml(categoria) + '</span>';
        badges += '<small class="text-muted">Subido el ' + escapeHtml(subido) + '</small>';
        metaEl.innerHTML = badges;

        embedEl.src = '/documentos/archivo?id=' + id;
        downloadEl.href = '/documentos/archivo?id=' + id + '&descargar=1';

        var modal = new bootstrap.Modal(document.getElementById('docPreviewModal'));
        modal.show();
    };

    document.addEventListener('hidden.bs.modal', function (e) {
        if (e.target.id === 'docPreviewModal') {
            var embed = document.getElementById('previewEmbed');
            if (embed) embed.src = '';
        }
    });

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
                if (this.checked) {
                    label.textContent = 'Activa';
                    this.closest('tr').querySelector('.bg-success') || this.closest('.card-item').querySelector('.bg-success');
                } else {
                    label.textContent = 'Inactiva';
                }
            });
        });
    }

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

        var statCards = document.querySelectorAll('.stat-card[data-filtro]');
        var filterRow = document.querySelector('.filter-active-bar');

        function applyFilter(filtro) {
            statCards.forEach(function (card) {
                card.classList.toggle('stat-card-active', card.getAttribute('data-filtro') === filtro);
            });
            document.querySelectorAll('[data-estado]').forEach(function (el) {
                var show = filtro === 'total' || el.getAttribute('data-estado') === filtro;
                el.style.display = show ? '' : 'none';
            });
            if (filterRow) {
                filterRow.style.display = filtro === 'total' ? 'none' : '';
                var label = filterRow.querySelector('.filter-label');
                if (label) {
                    var activeCard = document.querySelector('.stat-card[data-filtro="' + filtro + '"]');
                    label.textContent = activeCard ? activeCard.querySelector('.text-muted').textContent : 'Filtrando';
                }
            }
        }

        function clearFilter() {
            statCards.forEach(function (card) {
                card.classList.remove('stat-card-active');
            });
            document.querySelectorAll('[data-estado]').forEach(function (el) {
                el.style.display = '';
            });
            if (filterRow) {
                filterRow.style.display = 'none';
            }
        }

        statCards.forEach(function (card) {
            card.addEventListener('click', function () {
                var filtro = this.getAttribute('data-filtro');
                var isActive = this.classList.contains('stat-card-active');
                if (isActive) {
                    clearFilter();
                } else {
                    applyFilter(filtro);
                }
            });
        });

        if (filterRow) {
            var clearBtn = filterRow.querySelector('.btn-clear-filter');
            if (clearBtn) {
                clearBtn.addEventListener('click', clearFilter);
            }
        }
    });
})();
