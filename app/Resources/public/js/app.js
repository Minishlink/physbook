window.addEventListener('load', function() {
    if ('serviceWorker' in navigator) {
        var path = window.assetsDir;
        navigator.serviceWorker.register(path + "service-worker.js", { scope: path })
        .then(function(sw) {
            console.log('Service worker enregistré');
        }, function (e) {
            console.error('Oups...');
            console.error(e);
        });
    } else {
        console.warn('Les service workers ne sont pas encore supportés par ce navigateur.');
    }
});
