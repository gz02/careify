<?php

header("Cache-Control: no-store");
header("Pragma: no-cache");

require_once("/var/www/private/config.php"); // db connection definitions
require_once("/var/www/private/session.php"); // custom sessions
require_once("/var/www/private/lib/vendor/autoload.php");

$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(getcwd()));

// pages allowed for everyone
if (isset($_GET["login"])) { echo $twig->render("login.html"); }
else if (isset($_GET["user-login"])) { echo $twig->render("user-login.html"); }
else if (isset($_GET["carer-login"])) { echo $twig->render("carer-login.html"); }
else if (isset($_GET["signup"])) { echo $twig->render("signup.html"); }
else if (isset($_GET["contact-us"])) { echo $twig->render("contact-us.html"); }

else if (isset($_SESSION["elderly_id"])) // pages requiring elderly login
{
	if (isset($_GET["todo"])) { echo $twig->render("todo.html"); }
	else if (isset($_GET["mood"])) { echo $twig->render("mood.html"); }
	else if (isset($_GET["profile"])) { echo "Not complete.";/*$twig->render("profile.html");*/ }
	else if (isset($_GET["reminder"])) { echo "Not complete.";/*$twig->render("reminder.html");*/ }
	else // default to interface
	{
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		$stmt = $db->prepare("
			SELECT
				mood
			FROM
				mood_ratings
			WHERE
				elderly_id = ?
			ORDER BY
				rating_timestamp DESC
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$_SESSION["elderly_id"]
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$res = $stmt->get_result()->fetch_array(MYSQLI_ASSOC);
		$stmt->close();
		$db->close();
		
		echo $twig->render("user-interface.html", ["moodImg" => "images/mood/{$res["mood"]}.png"]);
	}
}

else if (isset($_SESSION["carer_id"])) // pages requiring carer login
{
	echo $twig->render("carer-interface.html");
}
else { echo $twig->render("desktop.html"); } // always respond with index

http_response_code(200); exit;
?>