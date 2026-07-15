var CACHE = 'elyra-v2';
var BASE = new URL('../', self.location.href).pathname.replace(/\/$/, '');

var STATIC = [
    BASE + '/',
    BASE + '/offline',
    BASE + '/css/web20.css',
    BASE + '/js/elyra.js',
    BASE + '/js/components/ui.js',
    BASE + '/img/elyralogo.png',
    BASE + '/img/hospital-de-clinicas.jpg',
    BASE + '/img/icon-72.png',
    BASE + '/img/icon-96.png',
    BASE + '/img/icon-128.png',
    BASE + '/img/icon-144.png',
    BASE + '/img/icon-152.png',
    BASE + '/img/icon-192.png',
    BASE + '/img/icon-512.png',
    BASE + '/img/silk/house.png',
    BASE + '/img/silk/user.png',
    BASE + '/img/silk/newspaper.png',
    BASE + '/img/silk/world.png',
    BASE + '/img/silk/magnifier.png',
    BASE + '/img/silk/arrow_up.png',
    BASE + '/img/silk/chart_bar.png',
    BASE + '/img/silk/add.png',
    BASE + '/img/silk/lorry.png',
    BASE + '/img/silk/map.png',
    BASE + '/img/silk/clock.png',
    BASE + '/img/silk/group.png',
    BASE + '/img/silk/user_edit.png',
    BASE + '/img/silk/page_white_text.png',
    BASE + '/manifest.json'
];

self.addEventListener('install', function (e) {
    e.waitUntil(
        caches.open(CACHE).then(function (cache) {
            return cache.addAll(STATIC);
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

self.addEventListener('activate', function (e) {
    e.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); })
            );
        }).then(function () {
            return self.clients.claim();
        })
    );
});

self.addEventListener('fetch', function (e) {
    var req = e.request;
    var url = new URL(req.url);

    if (url.origin !== location.origin) return;
    if (req.method !== 'GET') return;

    var p = url.pathname;

    if (p.startsWith(BASE + '/uploads/')) {
        e.respondWith(
            caches.open(CACHE).then(function (cache) {
                return fetch(req).then(function (res) {
                    if (res.ok) cache.put(req, res.clone());
                    return res;
                }).catch(function () {
                    return caches.match(BASE + '/offline');
                });
            })
        );
        return;
    }

    if (p.startsWith(BASE + '/css/') || p.startsWith(BASE + '/js/') || p.startsWith(BASE + '/img/') || p === BASE + '/manifest.json') {
        e.respondWith(
            caches.match(req).then(function (hit) {
                return hit || fetch(req).then(function (res) {
                    return caches.open(CACHE).then(function (cache) {
                        cache.put(req, res.clone());
                        return res;
                    });
                });
            })
        );
        return;
    }

    if (p === BASE + '/offline') {
        e.respondWith(caches.match(BASE + '/offline'));
        return;
    }

    var pagePrefixes = [BASE + '/dashboard', BASE + '/documentos', BASE + '/encuestas', BASE + '/traslados', BASE + '/conductores', BASE + '/rutas', BASE + '/funcionarios', BASE + '/perfil', BASE + '/noticias', BASE + '/login', BASE + '/publico'];
    if (p === BASE + '/' || pagePrefixes.some(function (pref) { return p.startsWith(pref); })) {
        e.respondWith(
            fetch(req).catch(function () {
                return caches.match(BASE + '/offline');
            })
        );
        return;
    }

    e.respondWith(
        caches.match(req).then(function (hit) {
            return hit || fetch(req);
        })
    );
});
