Vider le cache app/cache
Supprimer les web/css/* et web/js/*
php app/console fos:js-routing:dump --env=prod
php app/console assetic:dump --env=prod
