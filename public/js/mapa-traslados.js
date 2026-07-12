(function () {
    'use strict';

    var MONTEVIDEO = [-34.9011, -56.1645];
    var HOSPITAL_CENTER = [-34.9211, -56.1645];

    var hospitals = [
        { nombre: 'Hospital de Clinicas - Emergencias', lat: -34.9211, lng: -56.1645 },
        { nombre: 'Hospital de Clinicas - Cardiologia', lat: -34.9215, lng: -56.1648 },
        { nombre: 'Hospital de Clinicas - Cirugia', lat: -34.9213, lng: -56.1642 },
        { nombre: 'Hospital de Clinicas - Terapia Intensiva', lat: -34.9208, lng: -56.1640 },
        { nombre: 'Hospital de Clinicas - Nefrologia', lat: -34.9205, lng: -56.1650 },
        { nombre: 'Hospital de Clinicas - Maternidad', lat: -34.9218, lng: -56.1652 },
        { nombre: 'Hospital de Clinicas - Pediatria', lat: -34.9202, lng: -56.1646 },
        { nombre: 'Hospital de Clinicas - Diagnostico por Imagenes', lat: -34.9209, lng: -56.1638 },
        { nombre: 'Hospital de Clinicas - Quirofano', lat: -34.9216, lng: -56.1636 },
        { nombre: 'Clinica Privada - Centro', lat: -34.9012, lng: -56.1900 },
        { nombre: 'Sanatorio Espanol', lat: -34.8950, lng: -56.1720 }
    ];

    var estadoColores = {
        en_curso: '#4CAF50',
        en_retorno: '#2196F3',
        en_destino: '#FF9800',
        pendiente: '#9E9E9E'
    };

    var map = L.map('map', {
        center: MONTEVIDEO,
        zoom: 13,
        zoomControl: true
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    var hospitalIcon = L.divIcon({
        className: '',
        html: '<div style="background:#E91E63;width:12px;height:12px;border-radius:2px;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>',
        iconSize: [12, 12],
        iconAnchor: [6, 6]
    });

    var hospitalMarkers = [];
    hospitals.forEach(function (h) {
        var marker = L.marker([h.lat, h.lng], { icon: hospitalIcon }).addTo(map);
        marker.bindPopup('<strong>' + h.nombre + '</strong>');
        hospitalMarkers.push(marker);
    });

    var conductorMarkers = {};
    var routeLines = {};
    var polylineLayer = L.layerGroup().addTo(map);
    var selectedConductor = null;

    var sidebar = document.getElementById('sidebar');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var listaTraslados = document.getElementById('listaTraslados');
    var countActivos = document.getElementById('countActivos');
    var statusEl = document.getElementById('mapaStatus');

    sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('collapsed');
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

    function updateMarker(conductor) {
        var id = conductor.conductor_id;
        var latlng = [conductor.latitud, conductor.longitud];
        var estado = conductor.traslado_estado || 'pendiente';

        if (conductorMarkers[id]) {
            conductorMarkers[id].setLatLng(latlng);
            conductorMarkers[id].setIcon(createConductorIcon(estado));
        } else {
            var marker = L.marker(latlng, { icon: createConductorIcon(estado) }).addTo(map);
            marker.bindPopup('', { maxWidth: 250 });
            conductorMarkers[id] = marker;
        }

        var popupContent =
            '<div class="popup-codigo">' + (conductor.traslado_codigo || 'Sin traslado') + '</div>' +
            '<div class="popup-conductor">' + conductor.conductor_nombre + '</div>' +
            '<div class="popup-route">' +
            '<i class="bi bi-circle-fill" style="font-size:6px;color:#4CAF50;"></i> ' + (conductor.traslado_origen || '-') +
            ' <i class="bi bi-arrow-right" style="font-size:10px;"></i> ' +
            '<i class="bi bi-circle-fill" style="font-size:6px;color:#f44336;"></i> ' + (conductor.traslado_destino || '-') +
            '</div>' +
            (conductor.velocidad ? '<div style="font-size:0.8rem;color:#666;margin-top:4px;">' + conductor.velocidad.toFixed(1) + ' km/h</div>' : '');

        conductorMarkers[id].setPopupContent(popupContent);
    }

    function updateSidebar(ubicaciones) {
        if (ubicaciones.length === 0) {
            listaTraslados.innerHTML =
                '<div class="mapa-empty"><i class="bi bi-geo-alt"></i>Sin conductores activos</div>';
            countActivos.textContent = '0 conductores';
            return;
        }

        countActivos.textContent = ubicaciones.length + ' conductor' + (ubicaciones.length !== 1 ? 'es' : '');

        var html = '';
        ubicaciones.forEach(function (u) {
            var isActive = selectedConductor === u.conductor_id;
            var estadoBadge = u.traslado_estado
                ? '<span class="badge badge-' + u.traslado_estado.replace('_', '-') + '">' + u.traslado_estado.replace('_', ' ') + '</span>'
                : '';

            html +=
                '<div class="traslado-card' + (isActive ? ' active' : '') + '" data-conductor="' + u.conductor_id + '">' +
                '<div class="codigo">' + (u.traslado_codigo || 'Sin asignar') + ' ' + estadoBadge + '</div>' +
                '<div class="conductor">' + u.conductor_nombre + '</div>' +
                '<div class="route">' +
                '<i class="bi bi-circle-fill" style="font-size:5px;color:#4CAF50;"></i> ' + (u.traslado_origen || '-') +
                ' <i class="bi bi-arrow-right" style="font-size:9px;"></i> ' +
                '<i class="bi bi-circle-fill" style="font-size:5px;color:#f44336;"></i> ' + (u.traslado_destino || '-') +
                '</div>' +
                '</div>';
        });

        listaTraslados.innerHTML = html;

        var cards = listaTraslados.querySelectorAll('.traslado-card');
        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                var cid = parseInt(this.getAttribute('data-conductor'), 10);
                selectConductor(cid, ubicaciones);
            });
        });
    }

    function selectConductor(conductorId, ubicaciones) {
        selectedConductor = conductorId;
        updateSidebar(ubicaciones);

        if (conductorMarkers[conductorId]) {
            var marker = conductorMarkers[conductorId];
            map.setView(marker.getLatLng(), 15, { animate: true });
            marker.openPopup();
        }
    }

    function loadHistorial(conductorId, trasladoId) {
        polylineLayer.clearLayers();

        var url = '/api/ubicaciones/historial?conductor_id=' + conductorId;
        if (trasladoId) {
            url += '&traslado_id=' + trasladoId;
        }

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (puntos) {
                if (puntos.length < 2) return;

                var latlngs = puntos.map(function (p) { return [p.latitud, p.longitud]; });

                var polyline = L.polyline(latlngs, {
                    color: '#4CAF50',
                    weight: 3,
                    opacity: 0.7,
                    dashArray: '8, 6'
                }).addTo(polylineLayer);

                if (latlngs.length > 1) {
                    var startIcon = L.divIcon({
                        className: '',
                        html: '<div style="width:10px;height:10px;background:#4CAF50;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>',
                        iconSize: [10, 10],
                        iconAnchor: [5, 5]
                    });
                    var endIcon = L.divIcon({
                        className: '',
                        html: '<div style="width:10px;height:10px;background:#f44336;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>',
                        iconSize: [10, 10],
                        iconAnchor: [5, 5]
                    });

                    L.marker(latlngs[0], { icon: startIcon }).addTo(polylineLayer);
                    L.marker(latlngs[latlngs.length - 1], { icon: endIcon }).addTo(polylineLayer);
                }
            });
    }

    var ubicacionesCache = [];

    function fetchUbicaciones() {
        fetch('/api/ubicaciones/activas')
            .then(function (r) { return r.json(); })
            .then(function (ubicaciones) {
                ubicacionesCache = ubicaciones;

                var existingIds = {};
                ubicaciones.forEach(function (u) {
                    existingIds[u.conductor_id] = true;
                    updateMarker(u);
                });

                Object.keys(conductorMarkers).forEach(function (id) {
                    var numId = parseInt(id, 10);
                    if (!existingIds[numId]) {
                        map.removeLayer(conductorMarkers[numId]);
                        delete conductorMarkers[numId];
                    }
                });

                updateSidebar(ubicaciones);
            })
            .catch(function (err) {
                console.error('Error fetching ubicaciones:', err);
            });
    }

    function connectSSE() {
        var statusText = 'Actualizando cada 5s';
        statusEl.innerHTML = '<div class="pulse-dot"></div><span>' + statusText + '</span>';

        setInterval(function () {
            statusEl.innerHTML = '<div class="pulse-dot"></div><span>Actualizando...</span>';
            fetchUbicaciones();
            setTimeout(function () {
                statusEl.innerHTML = '<div class="pulse-dot"></div><span>' + statusText + '</span>';
            }, 1000);
        }, 5000);
    }

    fetchUbicaciones();
    connectSSE();
})();
