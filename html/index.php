<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

require_once("/var/www/private/session.php");
require_once("/var/www/private/lib/vendor/autoload.php");

session_start(); // check for login

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(getcwd()));

// pages allowed for everyone
if (isset($_GET["login"])) { echo $twig->render("login.html"); }
else if (isset($_GET["user-login"])) { echo $twig->render("user-login.html"); }
else if (isset($_GET["carer-login"])) { echo $twig->render("carer-login.html"); }
else if (isset($_GET["signup"])) { echo $twig->render("signup.html"); }

else if (isset($_SESSION["elderly_id"])) // pages requiring elderly login
{
	if (isset($_GET["todo"])) { echo $twig->render("todo.html"); }
	else if (isset($_GET["mood"])) { echo $twig->render("mood.html"); }
	else if (isset($_GET["profile"])) { echo "Not complete.";/*$twig->render("profile.html");*/ }
	else if (isset($_GET["reminder"])) { echo "Not complete.";/*$twig->render("reminder.html");*/ }
	else // default to interface
	{
		echo $twig->render("user-interface.html", ["moodImg" => $_GET["mood"] ?? ""]);
	}
}

else if (0) // pages requiring carer login
{
	if (0) { echo $twig->render(""); }
	else // default to interface
	{
		echo $twig->render("carer-interface.html");
	}
}
else { echo $twig->render("desktop.html"); } // always respond with index

http_response_code(200); exit;
?>