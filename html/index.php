<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, proxy-revalidate, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Cache-Control: max-age=10000000, s-maxage=1000000");
header("Pragma: no-cache");

require_once("/var/www/private/lib/vendor/autoload.php");

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(getcwd()));

echo $twig->render("desktop.html");

exit;
?>