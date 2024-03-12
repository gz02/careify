<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

require_once("/var/www/private/lib/vendor/autoload.php");

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(getcwd()));

echo $twig->render("desktop.html");

exit;
?>