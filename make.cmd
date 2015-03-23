del /q web\css\*
del /q web\js\*
del /q app\cache
php app/console fos:js-routing:dump --env=prod
php app/console assetic:dump --env=prod
pause