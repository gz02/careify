<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, proxy-revalidate, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Cache-Control: max-age=10000000, s-maxage=1000000");
header("Pragma: no-cache");

//function is_mobile() { return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]); }

require_once("/var/www/private/lib/vendor/autoload.php");

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(getcwd()));

echo $twig->render("desktop.html");

//if (is_mobile()) { echo $twig->render("mobile.html", $data); }
//else { echo $twig->render("desktop.html", $data); }
exit;
?>