<?php

$input = file_get_contents('php://input');

if (!isset($input)) {
    http_response_code(400);
    exit();
}

$json = json_decode($input);

if ($json === null) {
    http_response_code(400);
    exit();
}

if ($json['enable']) {
    // remove app.php
    shell_exec("[[ -e app.php ]] && rm app.php");

    // copy maintenance.html to app.php
    shell_exec("[[ -e html/maintenance.html ]] && cp -rf html/maintenance.html app.php");
} else {
    //copy parameters
    shell_exec("cp -rf ../../parameters.yml app/config/parameters.yml");

    // composer install
    shell_exec("php ../composer.phar self-update");
    shell_exec("php ../composer.phar install --optimize-autoloader --no-interaction");

    // update schema
    shell_exec("php app/console doctrine:schema:update --dump-sql --force");

    if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], array("test.physbook.fr", "push.physbook.fr"))) {
        shell_exec("php app/console cache:clear --env=staging --no-debug");
        shell_exec("php app/console doctrine:fixtures:load -n");
    } else {
        shell_exec("php app/console cache:clear --env=prod --no-debug");
    }

    // put app.php
    shell_exec("cp -rf ../../app.php app.php");
}

echo "OK";
