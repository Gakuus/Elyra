(function () {
    'use strict';

    var MONTEVIDEO = [-34.9011, -56.1645];

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(String(str)));
        return div.innerHTML;
    }

    var estadoColores = {
        pendiente: '#9E9E9E',
        en_curso: '#4CAF50',
        en_destino: '#FF9800',
        en_retorno: '#2196F3',
        completado: '#4CAF50',
        cancelado: '#f44336'
    };

    var hospitals = [
        { name: 'Hospital de Clínicas', lat: -34.9211, lng: -56.1645 },
        { name: 'Clínica Privada - Centro', lat: -34.9012, lng: -56.1900 },
        { name: 'Sanatorio Español', lat: -34.8950, lng: -56.1720 }
    ];

    var map = L.map('map', {
        center: MONTEVIDEO,
        zoom: 13,
        zoomControl: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    var hospitalsLayer = L.layerGroup().addTo(map);
    var trasladosLayer = L.layerGroup().addTo(map);
    var conductoresLayer = L.layerGroup().addTo(map);
    var sidebar = document.getElementById('sidebar');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var listaTraslados = document.getElementById('listaTraslados');
    var countActivos = document.getElementById('countActivos');
    var statusEl = document.getElementById('mapaStatus');

    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
    });

    hospitals.forEach(function (h) {
        var icon = L.divIcon({
            className: '',
            html: '<div style="width:18px;height:18px;background:#E91E63;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;">' +
                '<i class="bi bi-hospital" style="color:white;font-size:10px;"></i></div>',
            iconSize: [18, 18],
            iconAnchor: [9, 9]
        });
        L.marker([h.lat, h.lng], { icon: icon })
            .bindPopup('<strong>' + escapeHtml(h.name) + '</strong>')
            .addTo(hospitalsLayer);
    });

    function createConductorIcon(estado) {
        var color = estadoColores[estado] || '#9E9E9E';
        return L.divIcon({
            className: '',
            html: '<div style="background:' + color + ';width:28px;height:28px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;">' +
                '<i class="bi bi-ambulance" style="color:white;font-size:14px;"></i></div>',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
    }

    function createOrigenIcon() {
        return L.divIcon({
            className: '',
            html: '<div style="width:14px;height:14px;background:#4CAF50;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.3);"></div>',
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });
    }

    function createDestinoIcon() {
        return L.divIcon({
            className: '',
            html: '<div style="width:14px;height:14px;background:#f44336;border-radius:50%;border:2px solid white;box-shadow:0 1px 4px rgba(0,0,0,.3);"></div>',
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });
    }

    var conductorMarkers = {};
    var routeCache = {};

    function routeKey(oLat, oLng, dLat, dLng) {
        return oLat.toFixed(5) + '_' + oLng.toFixed(5) + '_' + dLat.toFixed(5) + '_' + dLng.toFixed(5);
    }

    function fetchRoute(oLat, oLng, dLat, dLng) {
        var key = routeKey(oLat, oLng, dLat, dLng);
        if (routeCache[key]) {
            return Promise.resolve(routeCache[key]);
        }
        var url = (window.BASE_PATH || '') + '/api/ruta/real?origen_lat=' + oLat + '&origen_lng=' + oLng +
                  '&destino_lat=' + dLat + '&destino_lng=' + dLng;
        return fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                routeCache[key] = data;
                return data;
            });
    }

    function updateSidebar(traslados) {
        if (traslados.length === 0) {
            listaTraslados.innerHTML =
                '<div class="mapa-empty"><i class="bi bi-geo-alt"></i>Sin traslados activos</div>';
            countActivos.textContent = '0 traslados';
            return;
        }

        var enCurso = traslados.filter(function (t) {
            return t.conductor_lat !== null;
        }).length;

        countActivos.textContent = traslados.length + ' traslado' + (traslados.length !== 1 ? 's' : '') +
            (enCurso > 0 ? ' (' + enCurso + ' con GPS)' : '');

        var html = '';
        traslados.forEach(function (t) {
            var estadoBadge = '<span class="badge badge-' + t.estado.replace('_', '-') + '">' +
                t.estado.replace('_', ' ') + '</span>';
            var gpsIndicator = t.conductor_lat !== null
                ? '<i class="bi bi-broadcast" style="color:#4CAF50;font-size:0.7rem;" title="GPS activo"></i> '
                : '';

            html +=
                '<div class="traslado-card" data-traslado="' + t.id + '">' +
                '<div class="codigo">' + gpsIndicator + escapeHtml(t.codigo) + ' ' + estadoBadge + '</div>' +
                '<div class="conductor">' + escapeHtml(t.conductor_nombre) +
                (t.copiloto_nombre ? ' / ' + escapeHtml(t.copiloto_nombre) : '') + '</div>' +
                '<div class="route">' +
                '<i class="bi bi-circle-fill" style="font-size:5px;color:#4CAF50;"></i> ' + escapeHtml(t.origen) +
                ' <i class="bi bi-arrow-right" style="font-size:9px;"></i> ' +
                '<i class="bi bi-circle-fill" style="font-size:5px;color:#f44336;"></i> ' + escapeHtml(t.destino) +
                '</div>' +
                '</div>';
        });

        listaTraslados.innerHTML = html;

        var cards = listaTraslados.querySelectorAll('.traslado-card');
        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                var tid = parseInt(this.getAttribute('data-traslado'), 10);
                selectTraslado(tid);
            });
        });
    }

    function selectTraslado(trasladoId) {
        var markers = trasladosLayer.getLayers();
        for (var i = 0; i < markers.length; i++) {
            if (markers[i]._trasladoId === trasladoId) {
                map.setView(markers[i].getLatLng(), 15, { animate: true });
                markers[i].openPopup();
                break;
            }
        }

        var cards = listaTraslados.querySelectorAll('.traslado-card');
        cards.forEach(function (c) {
            c.classList.toggle('active', parseInt(c.getAttribute('data-traslado'), 10) === trasladoId);
        });
    }

    function renderTraslados(traslados) {
        trasladosLayer.clearLayers();

        traslados.forEach(function (t) {
            if (t.origen_lat === null || t.origen_lng === null) return;

            var origenLatLng = [t.origen_lat, t.origen_lng];

            var marker = L.marker(origenLatLng, { icon: createOrigenIcon() }).addTo(trasladosLayer);
            marker._trasladoId = t.id;

            var popupContent =
                '<div class="popup-codigo">' + escapeHtml(t.codigo) + '</div>' +
                '<div class="popup-conductor">' + escapeHtml(t.conductor_nombre) +
                (t.copiloto_nombre ? ' / ' + escapeHtml(t.copiloto_nombre) : '') + '</div>' +
                '<div class="popup-route">' +
                '<i class="bi bi-circle-fill" style="font-size:6px;color:#4CAF50;"></i> ' + escapeHtml(t.origen) +
                ' <i class="bi bi-arrow-right" style="font-size:10px;"></i> ' +
                '<i class="bi bi-circle-fill" style="font-size:6px;color:#f44336;"></i> ' + escapeHtml(t.destino) +
                '</div>' +
                (t.hora_salida ? '<div style="font-size:0.8rem;color:#666;margin-top:4px;"><i class="bi bi-clock me-1"></i>' + escapeHtml(t.hora_salida) + '</div>' : '');

            marker.bindPopup(popupContent, { maxWidth: 280 });

            if (t.destino_lat !== null && t.destino_lng !== null) {
                var destinoLatLng = [t.destino_lat, t.destino_lng];

                L.marker(destinoLatLng, { icon: createDestinoIcon() })
                    .bindPopup('<strong>Destino:</strong> ' + escapeHtml(t.destino))
                    .addTo(trasladosLayer);

                var color = estadoColores[t.estado] || '#9E9E9E';
                (function (c, oLat, oLng, dLat, dLng) {
                    fetchRoute(oLat, oLng, dLat, dLng)
                        .then(function (result) {
                            var coords = result.coordinates || [];
                            if (coords.length < 2) return;
                            L.polyline(coords, {
                                color: c,
                                weight: 4,
                                opacity: 0.75
                            }).addTo(trasladosLayer);
                        });
                })(color, t.origen_lat, t.origen_lng, t.destino_lat, t.destino_lng);
            }
        });
    }

    function renderConductores(ubicaciones) {
        var existingIds = {};
        ubicaciones.forEach(function (u) {
            existingIds[u.conductor_id] = true;
            var latlng = [u.latitud, u.longitud];
            var estado = u.traslado_estado || 'pendiente';

            if (conductorMarkers[u.conductor_id]) {
                conductorMarkers[u.conductor_id].setLatLng(latlng);
                conductorMarkers[u.conductor_id].setIcon(createConductorIcon(estado));
            } else {
                var m = L.marker(latlng, { icon: createConductorIcon(estado) }).addTo(conductoresLayer);
                conductorMarkers[u.conductor_id] = m;
            }

            var popupContent =
                '<div class="popup-codigo">' + escapeHtml(u.traslado_codigo || 'Sin traslado') + '</div>' +
                '<div class="popup-conductor">' + escapeHtml(u.conductor_nombre) + '</div>' +
                '<div class="popup-route">' +
                '<i class="bi bi-circle-fill" style="font-size:6px;color:#4CAF50;"></i> ' + escapeHtml(u.traslado_origen || '-') +
                ' <i class="bi bi-arrow-right" style="font-size:10px;"></i> ' +
                '<i class="bi bi-circle-fill" style="font-size:6px;color:#f44336;"></i> ' + escapeHtml(u.traslado_destino || '-') +
                '</div>' +
                (u.velocidad ? '<div style="font-size:0.8rem;color:#666;margin-top:4px;">' + u.velocidad.toFixed(1) + ' km/h</div>' : '');

            conductorMarkers[u.conductor_id].setPopupContent(popupContent);
        });

        Object.keys(conductorMarkers).forEach(function (id) {
            var numId = parseInt(id, 10);
            if (!existingIds[numId]) {
                conductoresLayer.removeLayer(conductorMarkers[numId]);
                delete conductorMarkers[numId];
            }
        });
    }

    function fetchTraslados() {
        fetch((window.BASE_PATH || '') + '/api/traslados/activos')
            .then(function (r) { return r.json(); })
            .then(function (traslados) {
                renderTraslados(traslados);
                updateSidebar(traslados);
            })
            .catch(function (err) {
                console.error('Error fetching traslados:', err);
            });
    }

    function fetchUbicaciones() {
        fetch((window.BASE_PATH || '') + '/api/ubicaciones/activas')
            .then(function (r) { return r.json(); })
            .then(function (ubicaciones) {
                renderConductores(ubicaciones);
            })
            .catch(function (err) {
                console.error('Error fetching ubicaciones:', err);
            });
    }

    var statusText = 'Actualizando cada 5s';
    statusEl.innerHTML = '<div class="pulse-dot"></div><span>' + statusText + '</span>';

    fetchTraslados();
    fetchUbicaciones();

    setInterval(function () {
        statusEl.innerHTML = '<div class="pulse-dot"></div><span>Actualizando...</span>';
        fetchTraslados();
        fetchUbicaciones();
        setTimeout(function () {
            statusEl.innerHTML = '<div class="pulse-dot"></div><span>' + statusText + '</span>';
        }, 1000);
    }, 5000);
})();
