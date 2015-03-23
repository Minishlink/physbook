var OFFLINE_CACHE = 'offline';
var OFFLINE_URL = 'html/offline.html';

self.addEventListener('install', function(event) {
    var offlineRequest = new Request(OFFLINE_URL);
    event.waitUntil(
        fetch(offlineRequest).then(function(response) {
            return caches.open(OFFLINE_CACHE).then(function(cache) {
                return cache.put(offlineRequest, response);
            });
        })
    );
});

self.addEventListener('fetch', function(event) {
    // fix pour le bug dans Chrome M40
    if (parseInt(navigator.appVersion.match(/Chrome\/(\d+)\./)[1], 10) < 41) {
        return;
    }

    if (event.request.method === 'GET' &&
        event.request.headers.get('accept').indexOf('text/html') !== -1) {

        event.respondWith(
            fetch(event.request).catch(function(e) {
                // hors ligne
                return caches.open(OFFLINE_CACHE).then(function(cache) {
                    return cache.match(OFFLINE_URL);
                });
            })
        );
    }
});
