<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

require_once("/var/www/private/session.php");
require_once("/var/www/private/lib/vendor/autoload.php");

session_start();

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(getcwd()));

if ($_SERVER["REQUEST_METHOD"] === "GET")
{
	// pages allowed for everyone
	if (isset($_GET["login"])) { echo $twig->render("login.html"); exit; }
	if (isset($_GET["user-login"])) { echo $twig->render("user-login.html"); exit; }
	if (isset($_GET["carer-login"])) { echo $twig->render("carer-login.html"); exit; }
	if (isset($_GET["signup"])) { echo $twig->render("signup.html"); exit; }
	
	// pages requiring carer login
	if (1)
	{
		if (isset($_GET["carer-interface"])) { echo $twig->render("carer-interface.html"); exit; }
	}
	
	// pages requiring elderly login
	if (isset($_SESSION["elderly_id"]))
	{
		if (isset($_GET["user-interface"])) { echo $twig->render("user-interface.html", ["moodImg" => $_GET["mood"] ?? ""]); exit; }
		if (isset($_GET["todo"])) { echo $twig->render("todo.html"); exit; }
		if (isset($_GET["mood"])) { echo $twig->render("mood.html"); exit; }
		if (isset($_GET["profile"])) { echo "Not complete.";/*$twig->render("profile.html");*/ exit; }
		if (isset($_GET["reminder"])) { echo "Not complete.";/*$twig->render("reminder.html");*/ exit; }
	}
}

echo $twig->render("desktop.html");

exit;
?>