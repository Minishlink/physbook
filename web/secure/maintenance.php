<?php

$input = file_get_contents('php://input');

if (!isset($input)) {
    echo "No input.";
    http_response_code(400);
    exit();
}

$json = json_decode($input, true);

if ($json === null) {
    echo "Bad JSON.";
    http_response_code(400);
    exit();
}

function execute($string) {
    echo "Execute : '".$string."'\n";
    echo shell_exec($string)."\n";
}

// web directory
chdir("../");

if ($json['enable']) {
    // remove app.php
    execute("[[ -e app.php ]] && rm app.php");

    // copy maintenance.html to app.php
    execute("[[ -e html/maintenance.html ]] && cp -rf html/maintenance.html app.php");
} else {
    // htdocs directory
    chdir("../");

    //copy parameters
    execute("cp -rf ../parameters.yml app/config/parameters.yml");

    // composer install
    execute("php ../composer.phar self-update");
    execute("php ../composer.phar install --optimize-autoloader --no-interaction");

    // update schema
    execute("php app/console doctrine:schema:update --dump-sql --force");

    if (isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], array("test.physbook.fr", "push.physbook.fr"))) {
        execute("php app/console cache:clear --env=staging --no-debug");
        execute("php app/console doctrine:fixtures:load -n");
    } else {
        execute("php app/console cache:clear --env=prod --no-debug");
    }

    // put app.php
    execute("cp -rf ../app.php web/app.php");
}

echo "OK";
