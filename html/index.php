<?php
function is_mobile() { return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]); }

require_once("/var/www/private/lib/vendor/autoload.php");

$data = ["a" => "hi, im some data", "b" => ["some list item1", "some list item2", "some list item3"], "c" => is_mobile() ? "mobile" : "desktop"];

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(getcwd() . "/private/templates"));

if (is_mobile()) { echo $twig->render("mobile.html", $data); }
else { echo $twig->render("desktop.html", $data); }
exit;
?>