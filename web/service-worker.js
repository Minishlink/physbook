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

    if (event.request.method === 'GET'
            && event.request.headers.get('accept').includes('text/html')) {

        // a request should not have any body (blob), but sometimes it does
        event.request.blob().then(function(blob) {
            if (blob.size == 0) {
                event.respondWith(
                    fetch(event.request).catch(function (e) {
                        // hors ligne
                        return caches.open(OFFLINE_CACHE).then(function (cache) {
                            return cache.match(OFFLINE_URL);
                        });
                    })
                );
            } else {
                console.warn("Bad GET request with body : " + event.request.url);
                console.warn(blob);
            }
        });
    }
});

self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }

    var sendNotification = function(message, tag) {
        var title = "Phy'sbook",
            icon = 'images/icons/icon-192.png';

        message = message || 'Il y a du neuf !';
        tag = tag || 'general';

        return self.registration.showNotification(title, {
            body: message,
            icon: icon,
            tag: tag
        });
    };

    if (event.data) {
        var data = event.data.json();
        event.waitUntil(
            sendNotification(data.message, data.tag)
        );
    } else {
        event.waitUntil(
            self.registration.pushManager.getSubscription().then(function(subscription) {
                if (!subscription) {
                    return;
                }

                fetch('notifications/last?endpoint=' + encodeURIComponent(subscription.endpoint)).then(function (response) {
                    if (response.status !== 200) {
                        throw new Error();
                    }

                    // Examine the text in the response
                    return response.json().then(function (data) {
                        if (data.error || !data.notification) {
                            throw new Error();
                        }

                        return sendNotification(data.notification.message);
                    });
                }).catch(function () {
                    return sendNotification();
                });
            })
        );
    }
});

self.addEventListener('notificationclick', function (event) {
    // fix http://crbug.com/463146
    event.notification.close();

    event.waitUntil(
        clients.matchAll({
            type: "window"
        })
        .then(function (clientList) {
            // si la page des notifications est ouverte on la recharge et on l'affiche
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];
                if (client.url.search(/notifications/i) >= 0 && 'focus' in client) {
                    // on devrait pouvoir recharger la page ici (postMessage ?)
                    return client.focus();
                }
            }

            // sinon s'il y a quand même une page du site ouverte on la recharge et on l'affiche
            if (clientList.length && 'focus' in client) {
                // on devrait pouvoir recharger le compteur de notifications sur la page (postMessage ?)
                return client.focus();
            }

            // sinon on ouvre la page des notifications
            if (clients.openWindow) {
                return clients.openWindow('notifications');
            }
        })
    );
});
