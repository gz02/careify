<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, proxy-revalidate, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Cache-Control: max-age=10000000, s-maxage=1000000");
header("Pragma: no-cache");

//function is_mobile() { return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]); }

require_once("/var/www/private/lib/vendor/autoload.php");

require_once("/var/www/private/config.php"); // db connection definitions, rather keep it with others and not make it public

// db test
//$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
//$result_tables = $db->query(" SHOW TABLES FROM careify") or trigger_error($db->error, E_USER_ERROR);
//$tables = array_column($result_tables->fetch_all(), 0);
//$result_tables->close();
//echo "<div style=\"font-size:10px\">";
//foreach ($tables as $table)
//{
//	$structure = $db->query("
//		SHOW CREATE TABLE careify.{$table}
//	") or trigger_error($db->error, E_USER_ERROR);
//	
//	while ($row = $structure->fetch_assoc())
//	{
//		echo "{$row["Table"]}<br>{$row["Create Table"]}<hr>";
//	}
//	$structure->close();
//}
//echo "</div>";
//$db->close();

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(getcwd()));

echo $twig->render("desktop.html");

//if (is_mobile()) { echo $twig->render("mobile.html", $data); }
//else { echo $twig->render("desktop.html", $data); }
exit;
?>