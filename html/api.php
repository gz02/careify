<?php

require_once("/var/www/private/session.php"); // custom sessions
require_once("/var/www/private/config.php"); // db connection definitions

session_start();

function loggedin_or_exit($msg_fail = "not logged in."): void
{
	if (!isset($_SESSION["elderly_id"]))
	{
		header("Content-type: application/json");
		echo json_encode(["error" => $msg_fail]);
		http_response_code(403); exit;
	}
}

// validate number positive
function val_num_pos($var, $msg_fail = "no description."): int
{
	if (is_numeric($var) && $var > 0) { return $var; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate pin, exactly 4 numbers
function val_pin(&$pin, $msg_fail = "pin is invalid."): string
{
	if (preg_match("/^[0-9]{4}+$/", $pin)) { return $pin; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate password, min 8 characters
function val_password(&$password, $msg_fail = "no description."): string
{
	if (is_string($password) && strlen($password) >= 8) { return $password; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate name, min 2 max 20 characters and no numbers
function val_name(&$name, $msg_fail = "name is invalid."): string
{
	if (preg_match("/^[a-zA-Z]{2,20}+$/", $name)) { return $name; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate date of birth, YYYY-MM-DD
function val_date_of_birth(&$date_of_birth, $msg_fail = "date is invalid.")
{
	if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date_of_birth)) { return $date_of_birth; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// ############################################################################ check db if already in use!
// validate phone number, starts with 0 then 10 numbers after
function val_phone(&$phone_number, $msg_fail = "phone number is invalid.")
{
	if (preg_match("/^0[0-9]{10}+$/", $phone_number)) { return $phone_number; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate email
function val_email(&$email, $msg_fail = "email is invalid.")
{
	if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) { return $email; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate string allow empty
function val_str_null(&$var, $msg_fail = "no description.")
{
	return $var;
}

// validate bool
function val_bool($var, $msg_fail = "no description.")
{
	if ($var = filter_var($var, FILTER_VALIDATE_BOOLEAN) !== false) { return $var; }
	echo $msg_fail;
	http_response_code(400); exit;
}

function get_carer_id(&$db, $carer_name): bool|int
{	
	$stmt = $db->prepare("
		SELECT 
			carer_id
		FROM
			carer_details
		WHERE
			LOWER(first_name) = LOWER(?)
	") or trigger_error($db->error, E_USER_ERROR);
	$stmt->execute([$carer_name]) or trigger_error($stmt->error, E_USER_ERROR);
	$result = $stmt->get_result();
	if (!$result->num_rows)
	{
		$stmt->close();
		return false;
	}
	$ret = $result->fetch_array()[0];
	$stmt->close();
	return $ret;
}

// validate get JSON body from POST
function get_post_json()
{
	$json_input = @json_decode(file_get_contents('php://input'), true);
	if ($json_input !== false) { return $json_input; }
	return $_POST;
}

if ($_SERVER["REQUEST_METHOD"] === "GET")
{
	if (isset($_GET["validate_firstname"])) { val_name($_GET["validate_firstname"]); }
	else if (isset($_GET["validate_lastname"])) { val_name($_GET["validate_lastname"]); }
	else if (isset($_GET["validate_phone"])) { val_phone($_GET["validate_phone"]); }
	else if (isset($_GET["validate_emfirstname"])) { val_name($_GET["validate_emfirstname"]); }
	else if (isset($_GET["validate_emlastname"])) { val_name($_GET["validate_emlastname"]); }
	else if (isset($_GET["validate_emphone"])) { val_phone($_GET["validate_emphone"]); }
	else if (isset($_GET["validate_email"])) { val_email($_GET["validate_email"]); }
	else if (isset($_GET["validate_pin"])) { val_pin($_GET["validate_pin"]); }
	else if (isset($_GET["validate_password"])) { val_password($_GET["validate_password"]); }
	//else if (isset($_GET["validate_allergies"])) { val_str_null($_GET["validate_allergies"]); }
	else if (isset($_GET["validate_date_of_birth"])) { val_date_of_birth($_GET["validate_date_of_birth"]); }
	else if (isset($_GET["validate_carename"]))
	{
		$name = val_name($_GET["validate_carename"]);
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		$carer_id = get_carer_id($db, $name);
		$db->close();
		if ($carer_id === false)
		{
			echo "Carer does not exist.";
			http_response_code(400); exit;
		}
	}
	
	else if (isset($_GET["all-todo"]))
	{
		loggedin_or_exit();
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			SELECT
				task_id AS id,
				task_name AS name,
				completed,
				todo_date AS date,
				todo_time AS time
			FROM
				elderly_tasks
			WHERE
				elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$_SESSION["elderly_id"]
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$res = $stmt->get_result();
		
		require_once("/var/www/private/lib/vendor/autoload.php");

		$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . "/templates"));

		echo $twig->render("todo.html", ["tasks" => $res->fetch_all(MYSQLI_ASSOC)]);

		$stmt->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	
	http_response_code(200); exit;
}
else if ($_SERVER["REQUEST_METHOD"] === "POST")
{
	$_POST = get_post_json(); // POST data is JSON encoded, store back into post
	// action to do is stored in GET
	
	if (isset($_GET["save-mood"]))
	{
		loggedin_or_exit();
		
		$mood = "";
		switch ($_POST["mood"] ?? "") // validate and map input to what db expects
		{
			case "happy": $mood = "good"; break;
			case "fine": $mood = "ok"; break;
			case "sad": $mood = "bad"; break;
			default: http_response_code(400); exit;
		}
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			INSERT INTO
				mood_ratings (
					elderly_id,
					mood
				)
			VALUES (
				?,
				?
			)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$_SESSION["elderly_id"],
			$mood
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	
	if (isset($_GET["save-todo"]))
	{
		loggedin_or_exit();
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			INSERT INTO
				elderly_tasks (
					elderly_id,
					task_name,
					completed,
					todo_date, 
					todo_time
				)
			VALUES (
				?,
				?,
				?,
				?,
				?
			)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$_SESSION["elderly_id"],
			$_POST["title"],
			0,
			$_POST["date"],
			$_POST["time"]
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	
	else if (isset($_GET["completed-todo"]))
	{
		loggedin_or_exit();
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			UPDATE
				elderly_tasks
			SET
				completed = true
			WHERE
				task_id = ?
				AND elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$_POST["id"],
			$_SESSION["elderly_id"]
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	
	else if (isset($_GET["delete-todo"]))
	{
		loggedin_or_exit();
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			DELETE FROM
				elderly_tasks
			WHERE
				task_id = ?
				AND elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$_POST["id"],
			$_SESSION["elderly_id"]
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	
	else if (isset($_GET["user-login"]))
	{
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$pin = val_pin($_POST["pin"], "pin is incorrect.");
		$email = val_email($_POST["email"], "email is incorrect.");
		
		$stmt = $db->prepare("
			SELECT
				ep.elderly_id,
				ep.hashed_pin
			FROM
				elderly_pin ep
			WHERE
				ep.elderly_id IN (
					SELECT
						ed.elderly_id
					FROM
						elderly_details ed
					WHERE
						ed.email = ?
				)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$email
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$res = $stmt->get_result()->fetch_array(MYSQLI_ASSOC);
		$stmt->close();
		$db->close();
		
		if (password_verify($pin, $res["hashed_pin"]))
		{
			$_SESSION["elderly_id"] = $res["elderly_id"];
			http_response_code(200); exit;
		}
		
		http_response_code(403); exit;
	}
	
	else if (isset($_GET["user-logout"]))
	{
		if (isset($_SESSION["elderly_id"])) { unset($_SESSION["elderly_id"]); };
		header("Location: https://careify.kunfucle.com/index");
		http_response_code(302); exit;
	}
	
	else if (isset($_GET["register"]))
	{
		$textSize = val_num_pos($_POST["textSize"], "test size is incorrect.");
		$firstname = val_name($_POST["firstname"], "first name is incorrect.");
		$lastname = val_name($_POST["lastname"], "last name is incorrect.");
		$phone = val_phone($_POST["phone"], "phone number is incorrect.");
		$emfirstname = val_name($_POST["emfirstname"], "emergency contact first name is incorrect.");
		$emlastname = val_name($_POST["emlastname"], "emergency contact last name is incorrect.");
		$emphone = val_phone($_POST["emphone"], "emergency contact phone number is incorrect.");
		$email = val_email($_POST["email"], "email is incorrect.");
		$pin = val_pin($_POST["pin"], "pin is incorrect.");
		
		//$pollen = val_bool($_POST["pollen"]);
		//$latex = val_bool($_POST["latex"]);
		//$penicillin = val_bool($_POST["penicillin"]);
		//$dust = val_bool($_POST["dust"]);
		//$plasters = val_bool($_POST["plasters"]);
		//$hypertension = val_bool($_POST["hypertension"]);
		//$arthritis = val_bool($_POST["arthritis"]);
		//$heartdisease = val_bool($_POST["heartdisease"]);
		//$dementia = val_bool($_POST["dementia"]);
		//$osteoporosis = val_bool($_POST["osteoporosis"]);
		$carename = val_name($_POST["carename"], "carer name is incorrect.");
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		$db->autocommit(false);
		$db->begin_transaction();
		
		$carer_id = get_carer_id($db, $carename);
		
		$stmt = $db->prepare("
			INSERT INTO
				emergency_contact_details (
					first_name,
					last_name,
					phone_number
				)
			VALUES (
				?,
				?,
				?
			)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$emfirstname, $emlastname, $emphone]) or trigger_error($stmt->error, E_USER_ERROR);
		$emergency_contact_id = $stmt->insert_id;
		$stmt->close();
		
		$stmt = $db->prepare("
			INSERT INTO
				elderly_details (
					first_name,
					last_name,
					date_of_birth,
					phone_number,
					email,
					carer_id,
					emergency_contact_id
				)
			VALUES (
				?,
				?,
				?,
				?,
				?,
				?,
				?
			)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$firstname,
			$lastname,
			"2020-12-12",
			$phone,
			$email,
			$carer_id,
			$emergency_contact_id
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$elderly_id = $stmt->insert_id;
		$stmt->close();
		
		$_SESSION["elderly_id"] = $elderly_id;
		
		$stmt = $db->prepare("
			INSERT INTO
				elderly_pin (
					elderly_id,
					hashed_pin
				)
			VALUES (
				?,
				?
			)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$elderly_id,
			password_hash($pin, PASSWORD_DEFAULT)
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		
		if (!$db->commit())
		{
			echo "DB error: {$db->error}";
			$db->rollback();
			$db->close();
			http_response_code(500); exit;
		}
		
		$db->close();
		http_response_code(201); exit;
	}
	else
	{
		foreach($_POST as $k => $p) { echo htmlspecialchars($k) . ":" . htmlspecialchars($p) . "<br>"; }
		http_response_code(200); exit;
		//http_response_code(404); echo "Invalid POST request."; exit;
	}
}
else
{
	echo "405 Method Not Allowed";
	http_response_code(405);
	exit;
}

http_response_code(400); exit; // request ignored
?>