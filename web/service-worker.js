var OFFLINE_CACHE = 'offline';
var OFFLINE_URL = 'html/offline.html';

self.addEventListener('install', function (event) {
    var offlineRequest = new Request(OFFLINE_URL);
    event.waitUntil(
        fetch(offlineRequest).then(function (response) {
            return caches.open(OFFLINE_CACHE).then(function (cache) {
                return cache.put(offlineRequest, response);
            });
        })
    );
});

self.addEventListener('fetch', function (event) {
    // fix for redirect bug in Chrome M40
    var chromeVersion = navigator.appVersion.match(/Chrome\/(\d+)\./);
    if (chromeVersion !== null && parseInt(chromeVersion[1], 10) < 41) {
        return;
    }

    // fix for unknown bug when redirecting after a form POST request
    if (event.request.method === 'GET' && event.request.context === 'form') {
        return;
    }

    if (event.request.method === 'GET'
            && event.request.headers.get('accept').includes('text/html')) {

        event.respondWith(
            fetch(event.request).catch(function (e) {
                // hors ligne
                return caches.open(OFFLINE_CACHE).then(function (cache) {
                    return cache.match(OFFLINE_URL);
                });
            })
        );
    }
});

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var data = {};
    if (event.data) {
        data = event.data.json();
    }

    var title = data.title || "Phy'sbook",
        message = data.message || 'Il y a du neuf !',
        icon = 'images/favicon/favicon-192x192.png',
        tag = 'general';

    event.waitUntil(
        self.registration.showNotification(title, {
            body: message,
            icon: icon,
            tag: tag
        })
    );
});

self.addEventListener('notificationclick', function (event) {
    // fix http://crbug.com/463146
    event.notification.close();

    event.waitUntil(
        clients.matchAll({
            type: "window"
        })
        .then(function (clientList) {
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];
                if (client.url == '/' && 'focus' in client)
                    return client.focus();
            }
            if (clients.openWindow) {
                return clients.openWindow('/');
            }
        })
    );
});
