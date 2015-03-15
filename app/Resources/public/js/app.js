window.addEventListener('load', function() {
    var PJMAppBundleDir = "bundles/pjmapp/js/";
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register(window.assetsDir + PJMAppBundleDir + "service-worker.js", { scope: window.assetsDir + PJMAppBundleDir })
        .then(function(r) {
            console.log('Service worker enregistré');
        })
        .catch(function(whut) {
            console.error('Oups...');
            console.error(whut);
        });
    } else {
        console.warn('Les service workers ne sont pas encore supportés par ce navigateur.');
    }
});
