var isPushEnabled = false;

window.addEventListener('load', function() {
    var pushButton = document.querySelector('.js-push-button');
    if (pushButton) {
        pushButton.addEventListener('click', function() {
            if (isPushEnabled) {
                push_unsubscribe();
            } else {
                push_subscribe();
            }
        });
    }

    // double notification badge only on mobile layout
    var navbarToggle = $('.navbar-toggle:visible');
    if(navbarToggle) {
        var notificationCounters = document.getElementsByClassName('notificationCounter');

        if (notificationCounters.length) {
            var notificationCounterWrapperNavbar = document.querySelector('.navbar-toggle .notificationCounterWrapper');

            var notificationCounter = document.createElement('span');
            notificationCounter.className = 'notificationCounter badge badge-notification';
            notificationCounter.textContent = notificationCounters[0].textContent;
            notificationCounterWrapperNavbar.appendChild(notificationCounter);
        }
    }

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(window.swPath)
        .then(function(sw) {
            console.log('[SW] Service worker enregistré');
            push_initialiseState();
            initPostMessageListener();
        }, function (e) {
            console.error('[SW] Oups...', e);
            changePushButtonState('incompatible');

        });
    } else {
        console.warn('[SW] Les service workers ne sont pas encore supportés par ce navigateur.');
        changePushButtonState('incompatible');
    }
});

function changePushButtonState(state) {
    var $pushButtons = $('.js-push-button');

    for (var i = 0; i < $pushButtons.length; i++) {
        var pushButton = $pushButtons[i];
        var $pushButton = $(pushButton);

        switch (state) {
            case 'enabled':
                pushButton.disabled = false;
                pushButton.title = "Notifications Push activées";
                $pushButton.addClass("active");
                isPushEnabled = true;
                break;
            case 'disabled':
                pushButton.disabled = false;
                pushButton.title = "Notifications Push désactivées";
                $pushButton.removeClass("active");
                isPushEnabled = false;
                break;
            case 'computing':
                pushButton.disabled = true;
                pushButton.title = "Chargement...";
                break;
            case 'incompatible':
                pushButton.disabled = true;
                pushButton.title = "Notifications Push non disponibles (navigateur non compatible)";
                break;
            default:
                console.error('Unhandled push button state', state);
                break;
        }
    }
}

function initPostMessageListener() {
    var onRefreshNotifications = function () {
        var notificationCounters = document.getElementsByClassName('notificationCounter');

        if (!notificationCounters.length) {
            var notificationCounterWrappers = document.getElementsByClassName('notificationCounterWrapper');

            for (var i = 0; i < notificationCounterWrappers.length; i++) {
                var notificationCounter = document.createElement('span');
                notificationCounter.className = 'notificationCounter badge badge-notification';
                notificationCounter.textContent = '0';
                notificationCounterWrappers[i].appendChild(notificationCounter);
            }
        }

        for (var i = 0; i < notificationCounters.length; i++) {
            notificationCounters[i].textContent++;
        }
    };

    var onRemoveNotifications = function() {
        $('.notificationCounter').remove();
    };

    navigator.serviceWorker.addEventListener('message', function(e) {
        var message = e.data;

        switch (message) {
            case 'reload':
                window.location.reload(true);
                break;
            case 'refreshNotifications':
                onRefreshNotifications();
                break;
            case 'removeNotifications':
                onRemoveNotifications();
                break;
            default:
                console.warn("Message '" + message + "' not handled.");
                break;
        }
    });
}

function push_initialiseState() {
    // Are Notifications supported in the service worker?
    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
        console.warn('[SW] Les notifications ne sont pas supportées par ce navigateur.');
        changePushButtonState('incompatible');
        return;
    }

    // Check the current Notification permission.
    // If its denied, it's a permanent block until the
    // user changes the permission
    if (Notification.permission === 'denied') {
        console.warn('[SW] Les notifications ne sont pas autorisées par l\'utilisateur.');
        changePushButtonState('disabled');
        return;
    }

    // Check if push messaging is supported
    if (!('PushManager' in window)) {
        console.warn('[SW] Les messages Push ne sont pas supportés par ce navigateur.');
        changePushButtonState('incompatible');
        return;
    }

    // We need the service worker registration to check for a subscription
    navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
        // Do we already have a push message subscription?
        serviceWorkerRegistration.pushManager.getSubscription()
        .then(function(subscription) {
            // Enable any UI which subscribes / unsubscribes from
            // push messages.
            changePushButtonState('disabled');

            if (!subscription) {
                // We aren't subscribed to push, so set UI
                // to allow the user to enable push
                return;
            }

            // Keep your server in sync with the latest endpoint
            push_sendSubscriptionToServer(subscription, 'update');

            // Set your UI to show they have subscribed for push messages
            changePushButtonState('enabled');
        })
        ['catch'](function(err) {
            console.warn('[SW] Erreur pendant getSubscription()', err);
        });
    });
}

function push_subscribe() {
    // Disable the button so it can't be changed while
    // we process the permission request
    changePushButtonState('computing');

    navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
        serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true})
        .then(function(subscription) {
            // The subscription was successful
            changePushButtonState('enabled');

            // on a la subscription, il faut l'enregistrer en BDD
            return push_sendSubscriptionToServer(subscription, 'create');
        })
        ['catch'](function(e) {
            if (Notification.permission === 'denied') {
                // The user denied the notification permission which
                // means we failed to subscribe and the user will need
                // to manually change the notification permission to
                // subscribe to push messages
                console.warn('[SW] Les notifications ne sont pas autorisées par l\'utilisateur.');
                changePushButtonState('incompatible');
            } else {
                // A problem occurred with the subscription; common reasons
                // include network errors, and lacking gcm_sender_id and/or
                // gcm_user_visible_only in the manifest.
                console.error('[SW] Impossible de souscrire aux notifications.', e);
                changePushButtonState('disabled');
            }
        });
    });
}

function push_unsubscribe() {
  changePushButtonState('computing');

  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
    // To unsubscribe from push messaging, you need get the
    // subscription object, which you can call unsubscribe() on.
    serviceWorkerRegistration.pushManager.getSubscription().then(
      function(pushSubscription) {
        // Check we have a subscription to unsubscribe
        if (!pushSubscription) {
            // No subscription object, so set the state
            // to allow the user to subscribe to push
            changePushButtonState('disabled');
          return;
        }

        push_sendSubscriptionToServer(pushSubscription, 'delete');

        // We have a subscription, so call unsubscribe on it
        pushSubscription.unsubscribe().then(function(successful) {
            changePushButtonState('disabled');
        })['catch'](function(e) {
            // We failed to unsubscribe, this can lead to
            // an unusual state, so may be best to remove
            // the users data from your data store and
            // inform the user that you have done so

            console.log('[SW] Erreur pendant le désabonnement aux notifications: ', e);
            changePushButtonState('disabled');
        });
      })['catch'](function(e) {
        console.error('[SW] Erreur pendant le désabonnement aux notifications.', e);
      });
  });
}

function push_sendSubscriptionToServer(subscription, action) {
    var req = new XMLHttpRequest();
    var url = Routing.generate('pjm_app_api_pushsubscription_' + action);
    req.open('POST', url, true);
    req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    req.setRequestHeader("Content-type", "application/json");
    req.onreadystatechange = function (e) {
        if (req.readyState == 4) {
            if(req.status != 200) {
                console.error("[SW] Erreur :" + e.target.status);
            }
        }
    };
    req.onerror = function (e) {
        console.error("[SW] Erreur :" + e.target.status);
    };

    var key = subscription.getKey('p256dh');
    req.send(JSON.stringify({
        'endpoint': getEndpoint(subscription),
        'key': key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null
    }));

    return true;
}

function getEndpoint(pushSubscription) {
    var endpoint = pushSubscription.endpoint;
    var subscriptionId = pushSubscription.subscriptionId;

    // fix Chrome < 45
    if (subscriptionId && endpoint.indexOf(subscriptionId) === -1) {
        endpoint += '/' + subscriptionId;
    }

    return endpoint;
}
