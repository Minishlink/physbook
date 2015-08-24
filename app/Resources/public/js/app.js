var isPushEnabled = false;
const pushEnableText ="<span class='glyphicon glyphicon-bell'></span> S'abonner aux notifications";
const pushDisableText = "<span class='glyphicon glyphicon-remove'></span> Désactiver les notifications";

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

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(window.swPath)
        .then(function(sw) {
            console.log('[SW] Service worker enregistré');
            push_initialiseState();
        }, function (e) {
            console.error('[SW] Oups...');
            console.error(e);
        });
    } else {
        console.warn('[SW] Les service workers ne sont pas encore supportés par ce navigateur.');
    }
});

function push_initialiseState() {
    // Are Notifications supported in the service worker?
    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
        console.warn('[SW] Les notifications ne sont pas supportées par ce navigateur.');
        return;
    }

    // Check the current Notification permission.
    // If its denied, it's a permanent block until the
    // user changes the permission
    if (Notification.permission === 'denied') {
        console.warn('[SW] Les notifications ne sont pas autorisées par l\'utilisateur.');
        return;
    }

    // Check if push messaging is supported
    if (!('PushManager' in window)) {
        console.warn('[SW] Les messages Push ne sont pas supportés par ce navigateur.');
        return;
    }

    // We need the service worker registration to check for a subscription
    navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
        // Do we already have a push message subscription?
        serviceWorkerRegistration.pushManager.getSubscription()
        .then(function(subscription) {
            // Enable any UI which subscribes / unsubscribes from
            // push messages.
            var pushButton = document.querySelector('.js-push-button');
            if (pushButton) {
                pushButton.disabled = false;
            }

            if (!subscription) {
                // We aren't subscribed to push, so set UI
                // to allow the user to enable push
                return;
            }

            // Keep your server in sync with the latest subscriptionId
            push_sendSubscriptionToServer(subscription, 'maj');

            // Set your UI to show they have subscribed for push messages
            if (pushButton) {
                pushButton.innerHTML = pushDisableText;
            }
            isPushEnabled = true;
        })
        ['catch'](function(err) {
            console.warn('[SW] Erreur pendant getSubscription()', err);
        });
    });
}

function push_subscribe() {
    // Disable the button so it can't be changed while
    // we process the permission request
    var pushButton = document.querySelector('.js-push-button');
    pushButton.disabled = true;

    navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
        serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true})
        .then(function(subscription) {
            // The subscription was successful
            isPushEnabled = true;
            pushButton.innerHTML = pushDisableText;
            pushButton.disabled = false;

            // on a la subscription, il faut l'enregistrer en BDD
            return push_sendSubscriptionToServer(subscription, 'new');
        })
        ['catch'](function(e) {
            if (Notification.permission === 'denied') {
                // The user denied the notification permission which
                // means we failed to subscribe and the user will need
                // to manually change the notification permission to
                // subscribe to push messages
                console.warn('[SW] Les notifications ne sont pas autorisées par l\'utilisateur.');
                pushButton.disabled = true;
            } else {
                // A problem occurred with the subscription; common reasons
                // include network errors, and lacking gcm_sender_id and/or
                // gcm_user_visible_only in the manifest.
                console.error('[SW] Impossible de souscrire aux notifications.', e);
                pushButton.disabled = false;
                pushButton.innerHTML = pushEnableText;
            }
        });
    });
}

function push_unsubscribe() {
  var pushButton = document.querySelector('.js-push-button');
  pushButton.disabled = true;

  navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
    // To unsubscribe from push messaging, you need get the
    // subscription object, which you can call unsubscribe() on.
    serviceWorkerRegistration.pushManager.getSubscription().then(
      function(pushSubscription) {
        // Check we have a subscription to unsubscribe
        if (!pushSubscription) {
          // No subscription object, so set the state
          // to allow the user to subscribe to push
          isPushEnabled = false;
          pushButton.disabled = false;
          pushButton.innerHTML = pushEnableText;
          return;
        }

        push_sendSubscriptionToServer(pushSubscription, 'annuler');

        // We have a subscription, so call unsubscribe on it
        pushSubscription.unsubscribe().then(function(successful) {
            pushButton.disabled = false;
            pushButton.innerHTML = pushEnableText;
            isPushEnabled = false;
        })['catch'](function(e) {
            // We failed to unsubscribe, this can lead to
            // an unusual state, so may be best to remove
            // the users data from your data store and
            // inform the user that you have done so

            console.log('[SW] Erreur pendant le désabonnement aux notifications: ', e);
            pushButton.disabled = false;
            pushButton.innerHTML = pushEnableText;
        });
      })['catch'](function(e) {
        console.error('[SW] Erreur pendant le désabonnement aux notifications.', e);
      });
  });
}

function push_sendSubscriptionToServer(subscription, action) {
    subscription = getSubscriptionInfos(subscription);
    var req = new XMLHttpRequest();
    var params = "id=" + subscription.subscriptionId + "&endpoint="+ subscription.endpoint;
    var url = Routing.generate('pjm_app_push_manageSubscription', {
        'action': action
    });
    req.open('POST', url, true);
    req.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
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
    req.send(params);

    return true;
}

function getSubscriptionInfos(pushSubscription) {
    endpoint = pushSubscription.endpoint;

    // fix Chrome < 45
    if (pushSubscription.subscriptionId &&
        pushSubscription.endpoint.indexOf(pushSubscription.subscriptionId) === -1) {
        endpoint = pushSubscription.endpoint + '/' + pushSubscription.subscriptionId;
    }

    var endpointSections = endpoint.split('/');
    var subscriptionId = endpointSections[endpointSections.length - 1];

    return {
        subscriptionId: subscriptionId,
        endpoint: endpoint
    };
}
