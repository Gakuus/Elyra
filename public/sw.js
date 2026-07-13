var CACHE = 'elyra-v1';
var STATIC = [
    '/',
    '/offline',
    '/css/web20.css',
    '/js/elyra.js',
    '/js/components/ui.js',
    '/img/elyralogo.png',
    '/img/hospital-de-clinicas.jpg',
    '/img/icon-72.png',
    '/img/icon-96.png',
    '/img/icon-128.png',
    '/img/icon-144.png',
    '/img/icon-152.png',
    '/img/icon-192.png',
    '/img/icon-512.png',
    '/img/silk/house.png',
    '/img/silk/user.png',
    '/img/silk/newspaper.png',
    '/img/silk/world.png',
    '/img/silk/magnifier.png',
    '/img/silk/arrow_up.png',
    '/img/silk/chart_bar.png',
    '/img/silk/add.png',
    '/img/silk/lorry.png',
    '/img/silk/map.png',
    '/img/silk/clock.png',
    '/img/silk/group.png',
    '/img/silk/user_edit.png',
    '/img/silk/page_white_text.png',
    '/manifest.json'
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

    if (url.pathname.startsWith('/uploads/')) {
        e.respondWith(
            caches.open(CACHE).then(function (cache) {
                return fetch(req).then(function (res) {
                    if (res.ok) cache.put(req, res.clone());
                    return res;
                }).catch(function () {
                    return caches.match('/offline');
                });
            })
        );
        return;
    }

    if (url.pathname.startsWith('/css/') || url.pathname.startsWith('/js/') || url.pathname.startsWith('/img/') || url.pathname === '/manifest.json') {
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

    if (url.pathname === '/offline') {
        e.respondWith(caches.match('/offline'));
        return;
    }

    if (url.pathname === '/' || url.pathname.startsWith('/dashboard') || url.pathname.startsWith('/documentos') || url.pathname.startsWith('/encuestas') || url.pathname.startsWith('/traslados') || url.pathname.startsWith('/conductores') || url.pathname.startsWith('/rutas') || url.pathname.startsWith('/funcionarios') || url.pathname.startsWith('/perfil') || url.pathname.startsWith('/noticias') || url.pathname.startsWith('/login') || url.pathname.startsWith('/publico')) {
        e.respondWith(
            fetch(req).catch(function () {
                return caches.match('/offline');
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
