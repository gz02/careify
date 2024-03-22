<?php

require_once("/var/www/private/session.php"); // custom sessions
require_once("/var/www/private/config.php"); // db connection definitions

session_start();

/* check if user (elderly) is logged in, exit with error message otherwise
	*
	* @param string $msg_fail  message to display on failure
	* 
	* @return void
	*/
function user_loggedin_or_exit($msg_fail = "not logged in."): void
{
	if (!isset($_SESSION["elderly_id"]))
	{
		header("Content-type: application/json");
		echo json_encode(["error" => $msg_fail]);
		http_response_code(403); exit;
	}
}

/* check if carer is logged in, exit with error message otherwise
	*
	* @param string $msg_fail  message to display on failure
	* 
	* @return void
	*/
function carer_loggedin_or_exit($msg_fail = "not logged in."): void
{
	if (!isset($_SESSION["carer_id"]))
	{
		header("Content-type: application/json");
		echo json_encode(["error" => $msg_fail]);
		http_response_code(403); exit;
	}
}

/* display error caused by invalid user input
	*
	* @param string $msg_fail  message to display
	* 
	* @return void
	*/
function user_input_invalid($msg = "the entered value is not valid, try again."): void
{
	echo $msg;
	http_response_code(400);
	exit;
}

/* validate number positive
	*
	* @param any $var  input
	* @param string $msg_fail  message to display on failure
	* 
	* @return int  $var if valid
	*/
function val_num_pos($var, $msg_fail = "no description."): int
{
	if (is_numeric($var) && $var > 0) { return $var; }
	user_input_invalid($msg_fail);
}

/* validate pin, exactly 4 numbers
	*
	* @param string $pin  PIN
	* @param string $msg_fail  message to display on failure
	* 
	* @return string  $pin if valid
	*/
function val_pin(&$pin, $msg_fail = "pin is invalid."): string
{
	if (preg_match("/^[0-9]{4}+$/", $pin)) { return $pin; }
	user_input_invalid($msg_fail);
}

/* validate password, min 8 characters
	*
	* @param string $password  password
	* @param string $msg_fail  message to display on failure
	* 
	* @return string  $password if valid
	*/
function val_password(&$password, $msg_fail = "no description."): string
{
	if (is_string($password) && strlen($password) >= 8) { return $password; }
	user_input_invalid($msg_fail);
}

/* validate name, min 2 max 20 characters and no numbers
	*
	* @param string $name  name
	* @param string $msg_fail  message to display on failure
	* 
	* @return string  $name if valid
	*/
function val_name(&$name, $msg_fail = "name is invalid."): string
{
	if (preg_match("/^[a-zA-Z]{2,20}+$/", $name)) { return $name; }
	user_input_invalid($msg_fail);
}

/* validate date of birth
	*
	* @param string $date_of_birth  date of birth as DATE (YYYY-MM-DD)
	* @param string $msg_fail  message to display on failure
	* 
	* @return string  $date_of_birth if valid
	*/
function val_date_of_birth(&$date_of_birth, $msg_fail = "date is invalid."): string
{
	if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date_of_birth)) { return $date_of_birth; }
	user_input_invalid($msg_fail);
}

/* validate phone number, starts with 0 then 10 numbers after
	*
	* @param string $phone_number  phone number
	* @param string $msg_fail  message to display on failure
	* 
	* @return string  $phone_number if valid
	*/
// #################################################################################### check db if already in use!
function val_phone(&$phone_number, $msg_fail = "phone number is invalid."): string
{
	if (preg_match("/^0[0-9]{10}+$/", $phone_number)) { return $phone_number; }
	user_input_invalid($msg_fail);
}

/* validate email
	*
	* @param string $email  email
	* @param string $msg_fail  message to display on failure
	* 
	* @return string  $email if valid
	*/
// 
function val_email(&$email, $msg_fail = "email is invalid."): string
{
	if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) { return $email; }
	user_input_invalid($msg_fail);
}

/* validate bool
	*
	* @param string $var  SQL query
	* @param string $msg_fail  message to display on failure
	* 
	* @return bool  $var if valid
	*/
function val_bool($var, $msg_fail = "no description."): bool
{
	if ($var = filter_var($var, FILTER_VALIDATE_BOOLEAN) !== false) { return boolval($var); }
	user_input_invalid($msg_fail);
}

/* get id of carer by their name
	*
	* @param object $db  mysqli database connection object
	* @param string $carer_name  carer name
	* 
	* @return bool|int  fail OR carer id
	*/
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
	if (!$result->num_rows) // if carer does not exist
	{
		$stmt->close();
		return false;
	}
	$ret = $result->fetch_array()[0]; // carer id
	$stmt->close();
	return $ret;
}

/* get either urlencoded POST body or decode JSON body if available
	*
	* 
	* @return array  POST data
	*/
function get_post_json(): array
{
	// POST can be empty, so empty array "?? []" in that case
	$json_input = @json_decode(file_get_contents('php://input'), true);
	if ($json_input !== false) { return $json_input ?? []; }
	return $_POST ?? [];
}

if ($_SERVER["REQUEST_METHOD"] === "GET")
{
	/*  
		*
		* @param string GET  
		* 
		* @return 200|400  valid/invalid
		*/
	
	/*  validate first name
		*
		* @param string GETvalidate_firstname  first name
		* 
		* @return 200|400  valid/invalid
		*/
	if (isset($_GET["validate_firstname"])) { val_name($_GET["validate_firstname"]); }
	
	/*  validate last name
		*
		* @param string GETvalidate_lastname  last name
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_lastname"])) { val_name($_GET["validate_lastname"]); }
	
	/*  validate phone number
		*
		* @param string GETvalidate_phone  phone number
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_phone"])) { val_phone($_GET["validate_phone"]); }
	
	/*  validate emergency contact first name
		*
		* @param string GETvalidate_emfirstname  emergency contact first name
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_emfirstname"])) { val_name($_GET["validate_emfirstname"]); }
	
	/*  validate emergency contact last name
		*
		* @param string GETvalidate_emlastname  emergency contact last name
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_emlastname"])) { val_name($_GET["validate_emlastname"]); }
	
	/*  validate emergency contact phone number
		*
		* @param string GETvalidate_emphone  emergency contact phone number
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_emphone"])) { val_phone($_GET["validate_emphone"]); }
	
	/*  validate email
		*
		* @param string GETvalidate_email  email
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_email"])) { val_email($_GET["validate_email"]); }
	
	/*  validate pin number
		*
		* @param string GETvalidate_pin  pin number
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_pin"])) { val_pin($_GET["validate_pin"]); }
	
	/*  validate password
		*
		* @param string GETvalidate_password  password
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_password"])) { val_password($_GET["validate_password"]); }
	
	/*  validate date of birth
		*
		* @param string GETvalidate_date_of_birth  date of birth
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_date_of_birth"])) { val_date_of_birth($_GET["validate_date_of_birth"]); }
	
	/*  validate carer name
		*
		* @param string GETvalidate_carename  carer name
		* 
		* @return 200|400  valid/invalid
		*/
	else if (isset($_GET["validate_carename"]))
	{
		$name = val_name($_GET["validate_carename"]);
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		$carer_id = get_carer_id($db, $name);
		$db->close();
		if ($carer_id === false) // 0 and false is not the same
		{
			echo "Carer does not exist.";
			http_response_code(400); exit;
		}
	}
	
	/*  todo list html formatted
		*
		* @param null GETall-todo
		* 
		* @return string  todo list
		*/
	else if (isset($_GET["all-todo"]))
	{
		user_loggedin_or_exit();
		
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
		
		require_once("/var/www/private/lib/vendor/autoload.php"); // include twig
		$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . "/templates")); // set template dir
		echo $twig->render("todo.html", ["tasks" => $res->fetch_all(MYSQLI_ASSOC)]); // render

		$stmt->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	
	/*  mood list html formatted
		*
		* @param null GETall-mood
		* 
		* @return string  mood list
		*/
	else if (isset($_GET["all-mood"]))
	{
		carer_loggedin_or_exit();
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			SELECT
				mr.elderly_id AS id,
				CONCAT(ed.first_name, \" \", ed.last_name) as name,
				GROUP_CONCAT(
					mr.mood
					ORDER BY mr.rating_timestamp DESC
					LIMIT 3
				) as moods
			FROM
				mood_ratings mr
			INNER JOIN
				elderly_details ed
			ON
				mr.elderly_id = ed.elderly_id
			GROUP BY
				ed.elderly_id
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([]) or trigger_error($stmt->error, E_USER_ERROR);
		$res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
		
		$replace = [ // image name conversions
			'good' => 'happy',
			'ok' => 'fine',
			"bad" => "sad"
		];
		foreach ($res as &$user) // each user
		{
			// convert csv encoded moods into array and convert their names
			$user["moods"] = str_replace(array_keys($replace), $replace, explode(",", $user["moods"]));
		}
		
		require_once("/var/www/private/lib/vendor/autoload.php"); // include twig
		$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . "/templates")); // set template dir
		echo $twig->render("mood.html", ["users" => $res]); // render
		
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
	
	/*  save current mood of user
		*
		* @param null GETsave-mood
		* @param string POSTmood  users current mood
		* 
		* @return null
		*/
	if (isset($_GET["save-mood"]))
	{
		user_loggedin_or_exit();
		
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
	
	/*  save todo item
		*
		* @param null GETsave-todo
		* @param string POSTtitle  todo title
		* @param string POSTdate  todo date
		* @param string POSTtime  todo time
		* 
		* @return null
		*/
	if (isset($_GET["save-todo"]))
	{
		user_loggedin_or_exit();
		
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
	
	/*  mark todo item as completed
		*
		* @param null GETcompleted-todo
		* @param string POSTid  todo id
		* 
		* @return null
		*/
	else if (isset($_GET["completed-todo"]))
	{
		user_loggedin_or_exit();
		
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
	
	/*  delete todo item
		*
		* @param null GETdelete-todo
		* @param string POSTid  todo id
		* 
		* @return null
		*/
	else if (isset($_GET["delete-todo"]))
	{
		user_loggedin_or_exit();
		
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
	
	/*  user login
		*
		* @param null GETuser-login
		* @param string POSTpin  PIN number
		* @param string POSTemail  email
		* 
		* @return null
		*/
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
		
		if (password_verify($pin, $res["hashed_pin"]) ?? null) // check password hash matches
		{
			$_SESSION["elderly_id"] = $res["elderly_id"]; // save user id for session
			http_response_code(200); exit;
		}
		
		http_response_code(403); exit;
	}
	
	/*  carer login
		*
		* @param null GETcarer-login
		* @param string POSTpassword  password
		* @param string POSTemail  email
		* 
		* @return null
		*/
	else if (isset($_GET["carer-login"]))
	{
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$password = val_password($_POST["password"], "password is incorrect.");
		$email = val_email($_POST["email"], "email is incorrect.");
		
		$stmt = $db->prepare("
			SELECT
				cp.carer_id,
				cp.hashed_password
			FROM
				carer_password cp
			WHERE
				cp.carer_id IN (
					SELECT
						cd.carer_id
					FROM
						carer_details cd
					WHERE
						cd.email = ?
				)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$email
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$res = $stmt->get_result()->fetch_array(MYSQLI_ASSOC);
		$stmt->close();
		$db->close();
		
		if (password_verify($password, $res["hashed_password"] ?? null)) // check password hash matches
		{
			$_SESSION["carer_id"] = $res["carer_id"]; // save carer id for session
			http_response_code(200); exit;
		}
		
		http_response_code(403); exit;
	}
	
	/*  logout current user/carer
		*
		* @param null GETlogout
		* 
		* @return null
		*/
	else if (isset($_GET["logout"]))
	{
		if (isset($_SESSION["elderly_id"])) { unset($_SESSION["elderly_id"]); };
		if (isset($_SESSION["carer_id"])) { unset($_SESSION["carer_id"]); };
		header("Location: https://careify.kunfucle.com/index");
		http_response_code(302); exit;
	}
	
	/*  user register
		*
		* @param null GETregister
		* @param string POSTtextSize  text size
		* @param string POSTfirstname  first name
		* @param string POSTlastname  last name
		* @param string POSTphone  phone number
		* @param string POSTemfirstname  emergency contact first name
		* @param string POSTemlastname  emergency contact last name
		* @param string POSTemphone  emergency contact phone number
		* @param string POSTemail  email
		* @param string POSTpin  pin number
		* 
		* @return 201|500  success/fail
		*/
	else if (isset($_GET["register"]))
	{
		$textSize = val_num_pos($_POST["textSize"], "text size is incorrect.");
		$firstname = val_name($_POST["firstname"], "first name is incorrect.");
		$lastname = val_name($_POST["lastname"], "last name is incorrect.");
		$phone = val_phone($_POST["phone"], "phone number is incorrect.");
		$emfirstname = val_name($_POST["emfirstname"], "emergency contact first name is incorrect.");
		$emlastname = val_name($_POST["emlastname"], "emergency contact last name is incorrect.");
		$emphone = val_phone($_POST["emphone"], "emergency contact phone number is incorrect.");
		$email = val_email($_POST["email"], "email is incorrect.");
		$pin = val_pin($_POST["pin"], "pin is incorrect.");
		$carename = val_name($_POST["carename"], "carer name is incorrect.");
		
		$allergies = $_POST["allergies"];
		$medical_conditions = $_POST["medical_conditions"];
		$medication = $_POST["medication"];
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		$db->autocommit(false); // dont commit changes
		$db->begin_transaction(); // all or nothing!
		
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
		
		{ // allergies
			$stmt = $db->prepare("
				INSERT INTO
					elderly_allergies (
						elderly_id,
						allergy_id
					)
				SELECT
					?,
					allergy_id
				FROM
					allergies
				WHERE
					allergy_name = ?
			") or trigger_error($db->error, E_USER_ERROR);
			foreach ($allergies as $allergy => $val)
			{
				if ($val) { $stmt->execute([$elderly_id, $allergy]) or trigger_error($stmt->error, E_USER_ERROR); }
			}
			$stmt->close();
		}
		{ // medical conditions
			$stmt = $db->prepare("
				INSERT INTO
					elderly_medical_conditions (
						elderly_id,
						medical_condition_id
					)
				SELECT
					?,
					medical_condition_id
				FROM
					medical_conditions
				WHERE
					condition_name = ?
			") or trigger_error($db->error, E_USER_ERROR);
			foreach ($medical_conditions as $condition => $val)
			{
				if ($val) { $stmt->execute([$elderly_id, $condition]) or trigger_error($stmt->error, E_USER_ERROR); }
			}
			$stmt->close();
		}
		{ // medication
			$stmt = $db->prepare("
				INSERT INTO
					elderly_medication (
						elderly_id,
						medication_id
					)
				SELECT
					?,
					medication_id
				FROM
					medication
				WHERE
					medication_name = ?
			") or trigger_error($db->error, E_USER_ERROR);
			foreach ($medication as $med => $val)
			{
				if ($val) { $stmt->execute([$elderly_id, $med]) or trigger_error($stmt->error, E_USER_ERROR); }
			}
			$stmt->close();
		}
		
		
		if (!$db->commit()) // commit changes and check if worked
		{ // didnt work
			$db->rollback(); // not needed, left for clarity
			$db->close();
			http_response_code(500); exit;
		}
		
		$_SESSION["elderly_id"] = $elderly_id; // elderly now logged in
		
		$db->close();
		http_response_code(201); exit;
	}
	else
	{
		http_response_code(404); echo "Invalid POST request."; exit;
	}
}
else // only GET/POST allowed, ignore other methods
{
	echo "405 Method Not Allowed";
	http_response_code(405);
	exit;
}

http_response_code(400); exit; // request ignored completely for some rason
?>