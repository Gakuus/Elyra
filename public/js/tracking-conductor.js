(function () {
    'use strict';

    var config = window.TRACKING_CONFIG;
    if (!config) return;

    var tracking = false;
    var watchId = null;
    var sendCount = 0;

    var statusIcon = document.getElementById('statusIcon');
    var statusText = document.getElementById('statusText');
    var statusSub = document.getElementById('statusSub');
    var toggleBtn = document.getElementById('toggleBtn');
    var coordsDisplay = document.getElementById('coordsDisplay');
    var errorDisplay = document.getElementById('errorDisplay');
    var successDisplay = document.getElementById('successDisplay');
    var gpsStatusEl = document.getElementById('gpsStatus');
    var lastUpdateEl = document.getElementById('lastUpdate');
    var sendCountEl = document.getElementById('sendCount');

    if (config.ubicacion_actual) {
        statusText.textContent = 'Compartiendo';
        statusSub.textContent = 'Ubicacion activa desde antes';
        statusIcon.className = 'status-indicator active';
        toggleBtn.className = 'tracking-btn stop';
        toggleBtn.innerHTML = '<i class="bi bi-stop-fill"></i> Detener';
        tracking = true;
        startWatch();
    }

    function toggleTracking() {
        if (tracking) {
            stopTracking();
        } else {
            startTracking();
        }
    }
    window.toggleTracking = toggleTracking;

    function startTracking() {
        if (!navigator.geolocation) {
            showError('Tu navegador no soporta geolocalizacion.');
            return;
        }

        tracking = true;
        statusText.textContent = 'Iniciando...';
        statusSub.textContent = 'Obteniendo posicion GPS';
        toggleBtn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function (pos) {
                toggleBtn.disabled = false;
                sendPosition(pos.coords);
                startWatch();
                updateUI(true);
            },
            function (err) {
                toggleBtn.disabled = false;
                tracking = false;
                showError(getGeoError(err));
                updateUI(false);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function stopTracking() {
        tracking = false;
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
        updateUI(false);
        statusText.textContent = 'Inactivo';
        statusSub.textContent = 'Se dejo de compartir ubicacion';
        toggleBtn.className = 'tracking-btn start';
        toggleBtn.innerHTML = '<i class="bi bi-play-fill"></i> Compartir ubicacion';
    }

    function startWatch() {
        if (watchId !== null) return;

        watchId = navigator.geolocation.watchPosition(
            function (pos) {
                sendPosition(pos.coords);
            },
            function (err) {
                showError(getGeoError(err));
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
        );
    }

    function sendPosition(coords) {
        var lat = coords.latitude;
        var lng = coords.longitude;
        var heading = coords.heading !== null && coords.heading !== undefined ? Math.round(coords.heading) : null;
        var speed = coords.speed !== null && coords.speed !== undefined ? Math.round(coords.speed * 3.6 * 10) / 10 : null;

        var body = JSON.stringify({
            latitud: lat,
            longitud: lng,
            heading: heading,
            velocidad: speed
        });

        fetch('/api/ubicacion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': config.csrfToken
            },
            body: body
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                sendCount++;
                sendCountEl.textContent = sendCount;
                lastUpdateEl.textContent = new Date().toLocaleTimeString('es-UY');
                gpsStatusEl.textContent = 'Activo';
                hideError();
                showSuccess('Ubicacion enviada');
                setTimeout(hideSuccess, 2000);
            } else {
                showError(data.error || 'Error al enviar');
            }
        })
        .catch(function () {
            showError('Error de conexion al enviar');
        });

        coordsDisplay.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        coordsDisplay.classList.remove('hidden');
    }

    function updateUI(isTracking) {
        if (isTracking) {
            statusIcon.className = 'status-indicator active';
            statusText.textContent = 'Compartiendo';
            statusSub.textContent = 'Tu ubicacion se envia cada 5 segundos';
            toggleBtn.className = 'tracking-btn stop';
            toggleBtn.innerHTML = '<i class="bi bi-stop-fill"></i> Detener';
        } else {
            statusIcon.className = 'status-indicator inactive';
            gpsStatusEl.textContent = 'Inactivo';
            coordsDisplay.classList.add('hidden');
        }
    }

    function showError(msg) {
        errorDisplay.textContent = msg;
        errorDisplay.classList.remove('hidden');
    }

    function hideError() {
        errorDisplay.classList.add('hidden');
    }

    function showSuccess(msg) {
        successDisplay.textContent = msg;
        successDisplay.classList.remove('hidden');
    }

    function hideSuccess() {
        successDisplay.classList.add('hidden');
    }

    function getGeoError(err) {
        switch (err.code) {
            case 1: return 'Permiso de ubicacion denegado. Activalo en la configuracion del navegador.';
            case 2: return 'Ubicacion no disponible. Verifica la conexion GPS.';
            case 3: return 'Tiempo de espera agotado. Intenta de nuevo.';
            default: return 'Error de geolocalizacion.';
        }
    }
})();
