<?php
header("Content-type: text/html");
header("Cache-Control: no-cache");
header('Cache-Control: private');
header('Pragma: no-cache');

error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', '1');
    apache_setenv('dont-vary', '1');
}

function execute($string) {
    echo "<br>";
    echo "<i>Execute : '".$string."'</i>";
    echo "<br>";
    echo "<pre>".shell_exec($string)."</pre>";
    echo "<br>";
}

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Deploy Phy'sbook</title></head><body>";
echo "Started deploment...";
echo "<br>";

chdir("../");

echo "Updating composer";
echo execute("php composer.phar self-update");

echo "Checking composer dependancies";
echo execute("php composer.phar install --optimize-autoloader --no-interaction");

echo "Updating the database";
echo execute("php app/console doctrine:schema:update --dump-sql --force");

echo "Clearing Symfony cache";
echo execute("php app/console cache:clear --env=staging --no-debug");

chdir("./web");
echo "Finished deployment !<br>";
echo "<a href='http://test.physbook.fr'>Aller vers Phy'sbook</a>";
echo "</body></html>";


?>
