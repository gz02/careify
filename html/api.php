<?php

require_once("/var/www/private/session.php"); // custom sessions
require_once("/var/www/private/config.php"); // db connection definitions

require_once("validations.php");

function render($template, $data)
{
	require_once("/var/www/private/lib/vendor/autoload.php"); // include twig
	$twig = new \Twig\Environment(new \Twig\Loader\FilesystemLoader(__DIR__ . "/templates")); // set template dir
	echo $twig->render($template, $data); // render
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
		SELECT carer_id
		FROM carer_details
		WHERE LOWER(first_name) = LOWER(?)
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
	$json_input = @json_decode(file_get_contents('php://input'), true); // read JSON POST from input
	if ($json_input !== false) { return $json_input ?? []; } // JSON POST exists but empty, so return empty array
	return $_POST ?? []; // POST is URL encoded
}

if ($_SERVER["REQUEST_METHOD"] === "GET")
{	
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
	
	/*  user preferences
		*
		* @param null GETtheme
		* 
		* @return int  colour_theme
		* @return int  text_size
		*/
	else if (isset($_GET["theme"]))
	{
		user_loggedin_or_exit();
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			SELECT colour_theme, text_size, elderly_id
			FROM elderly_details
			WHERE elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$_SESSION["elderly_id"]]) or trigger_error($stmt->error, E_USER_ERROR);
		echo json_encode($stmt->get_result()->fetch_assoc());
		$stmt->close();
		
		$db->close();
		http_response_code(200); exit;
	}
	
	/*  medication reminders
		*
		* @param null GETmedication-reminders
		* 
		* @return array(string medication_name, string dosage, string frequency)  
		*/
	if (isset($_GET["medication-reminders"]))
	{
		user_loggedin_or_exit();
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			SELECT m.medication_name, em.dosage, em.frequency
			FROM elderly_medication em
			JOIN medication m ON em.medication_id = m.medication_id
			JOIN elderly_details e ON em.elderly_id = e.elderly_id
			WHERE e.elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$_SESSION["elderly_id"]]) or trigger_error($stmt->error, E_USER_ERROR);
		
		echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
		$stmt->close();
		$db->close();
		
		http_response_code(200); exit;
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
			SELECT task_id AS id, task_name AS name, completed, todo_date AS date, todo_time AS time
			FROM elderly_tasks
			WHERE elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$_SESSION["elderly_id"]]) or trigger_error($stmt->error, E_USER_ERROR);
		echo render("todo.html", ["tasks" => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);

		$stmt->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	
	/*  html formatted list of users and their details
		*
		* @param null GETcarer-users
		* 
		* @return string  HTML
		*/
	else if (isset($_GET["user-medication"]))
	{
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt_medication = $db->prepare("
			SELECT m.medication_name, IFNULL(em.elderly_id, 0) as takes
			FROM medication m
			LEFT JOIN elderly_medication em ON em.medication_id = m.medication_id and em.elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt_medication->execute([$_SESSION["elderly_id"] ?? 0]) or trigger_error($stmt_medication->error, E_USER_ERROR);
		echo json_encode($stmt_medication->get_result()->fetch_all(MYSQLI_ASSOC));
		
		$stmt_medication->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	/*  html formatted list of users and their details
		*
		* @param null GETcarer-users
		* 
		* @return string  HTML
		*/
	else if (isset($_GET["carer-users"]))
	{
		carer_loggedin_or_exit();
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt_users = $db->prepare("
			SELECT elderly_id, CONCAT(first_name, \" \", last_name) AS name, age
			FROM elderly_details
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt_users->execute([]) or trigger_error($stmt_users->error, E_USER_ERROR);
		$users = $stmt_users->get_result()->fetch_all(MYSQLI_ASSOC);
		$stmt_users->close();
		
		$stmt_em_contact = $db->prepare("
			SELECT CONCAT(ecd.first_name, \" \", ecd.last_name) AS name, ecd.phone_number, email
			FROM elderly_details ed
			JOIN emergency_contact_details ecd ON ed.emergency_contact_id = ecd.emergency_contact_id
			WHERE ed.elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		
		$stmt_moods = $db->prepare("
			SELECT
				MAX(CASE WHEN mr.rank = 1 THEN mr.mood END) AS mood1,
				MAX(CASE WHEN mr.rank = 2 THEN mr.mood END) AS mood2,
				MAX(CASE WHEN mr.rank = 3 THEN mr.mood END) AS mood3
			FROM (
				SELECT mr.*, ROW_NUMBER() OVER (PARTITION BY mr.elderly_id ORDER BY mr.rating_timestamp DESC) AS rank
				FROM mood_ratings mr
			) mr
			WHERE mr.rank <= 3 AND mr.elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		
		$stmt_medication = $db->prepare("
			SELECT m.medication_name AS name, em.dosage, em.frequency
			FROM elderly_medication em
			JOIN medication m ON em.medication_id = m.medication_id
			WHERE em.elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		
		$stmt_allergies = $db->prepare("
			SELECT a.allergy_name AS name
			FROM elderly_allergies ea
			JOIN allergies a ON ea.allergy_id = a.allergy_id
			WHERE ea.elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		
		$stmt_conditions = $db->prepare("
			SELECT mc.condition_name
			FROM elderly_medical_conditions emc
			JOIN medical_conditions mc ON emc.medical_condition_id = mc.medical_condition_id
			WHERE emc.elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		
		$stmt_reminder_count = $db->prepare("
			SELECT COUNT(*)
			FROM elderly_medication
			WHERE elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		
		foreach ($users as &$user) // update each user with additional info
		{
			$elderly_id = $user["elderly_id"];
			
			$stmt_em_contact->execute([$elderly_id]) or trigger_error($stmt_em_contact->error, E_USER_ERROR);
			$user["em_contact"] = $stmt_em_contact->get_result()->fetch_assoc();
			
			$stmt_medication->execute([$elderly_id]) or trigger_error($stmt_medication->error, E_USER_ERROR);
			$user["medication"] = $stmt_medication->get_result()->fetch_all(MYSQLI_ASSOC);
			
			$stmt_moods->execute([$elderly_id]) or trigger_error($stmt_moods->error, E_USER_ERROR);
			$moods = $stmt_moods->get_result()->fetch_all(MYSQLI_ASSOC)[0];
			$user["moods"] = [$moods["mood1"], $moods["mood2"], $moods["mood3"]];
			
			$stmt_allergies->execute([$elderly_id]) or trigger_error($stmt_allergies->error, E_USER_ERROR);
			$user["allergies"] = array_column($stmt_allergies->get_result()->fetch_all(MYSQLI_ASSOC), "name");
			
			$stmt_conditions->execute([$elderly_id]) or trigger_error($stmt_conditions->error, E_USER_ERROR);
			$user["conditions"] = array_column($stmt_conditions->get_result()->fetch_all(MYSQLI_ASSOC), "condition_name");
			
			$stmt_reminder_count->execute([$elderly_id]) or trigger_error($stmt_reminder_count->error, E_USER_ERROR);
			$user["reminder_count"] = $stmt_reminder_count->get_result()->fetch_array(MYSQLI_NUM)[0];
		}
		
		echo render("user_block_for_carer.thtml", ["users" => $users]);
		
		$stmt_em_contact->close();
		$stmt_medication->close();
		$stmt_moods->close();
		$stmt_allergies->close();
		$stmt_conditions->close();
		$stmt_reminder_count->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	
	/*  details of current user
		*
		* @param null GETprofile
		* 
		* @return string  mood list
		*/
	if (isset($_GET["profile"]))
	{
		user_loggedin_or_exit();
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			SELECT
				ed.first_name, ed.last_name, ed.date_of_birth, ed.age, ed.phone_number, ed.email,
				CONCAT(cd.first_name, ' ', cd.last_name) as carer_name,
				CONCAT(ecd.first_name, ' ', ecd.last_name) as emergency_name
			FROM elderly_details ed
			INNER JOIN carer_details cd ON ed.carer_id = cd.carer_id
			INNER JOIN emergency_contact_details ecd ON ed.emergency_contact_id = ecd.emergency_contact_id
			WHERE ed.elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$_SESSION["elderly_id"]]) or trigger_error($stmt->error, E_USER_ERROR);
		echo json_encode($stmt->get_result()->fetch_assoc());
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
		
		$mood = val_mood($_POST["mood"]);
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			INSERT INTO mood_ratings (elderly_id, mood)
			VALUES (?, ?)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$_SESSION["elderly_id"], $mood]) or trigger_error($stmt->error, E_USER_ERROR);
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
			INSERT INTO elderly_tasks (elderly_id, task_name, completed, todo_date, todo_time)
			VALUES (?, ?, ?, ?, ?)
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
	
	/*  save medication reminder
		*
		* @param null GETsave-todo
		* @param string POSTtitle  todo title
		* @param string POSTdate  todo date
		* @param string POSTtime  todo time
		* 
		* @return null
		*/
	if (isset($_GET["save-medication-reminder"]))
	{
		user_loggedin_or_exit();
		
		$medication = val_medication($_POST["medication"]);
		$dosage = val_medication_dosage($_POST["dosage"]);
		$frequency = val_medication_frequency($_POST["frequency"]);
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			INSERT INTO elderly_medication (elderly_id, medication_id, dosage, frequency)
			SELECT ?, m.medication_id, ?, ?
			FROM medication m
			WHERE m.medication_name = ?
			ON DUPLICATE KEY UPDATE
				elderly_id = VALUES(elderly_id),
				medication_id = VALUES(medication_id),
				dosage = VALUES(dosage),
				frequency = VALUES(frequency);
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$_SESSION["elderly_id"],
			$dosage,
			$frequency,
			$medication
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		$db->close();
		
		http_response_code(200); exit;
	}
	
	/*  reveive contact form
		*
		* @param null GETcontact
		* @param string POSTfullname  sender full name
		* @param string POSTphoneNumber  sender phone number
		* @param string POSTemail  sender email
		* @param string POSTemailMessage  message
		* 
		* @return null
		*/
	if (isset($_GET["contact"]))
	{
		if (!isset($_POST["fullname"])) { echo "Full name is required."; http_response_code(400); exit; }
		$name_parts = explode(" ", $_POST["fullname"], 3);
		if (count($name_parts) != 2) { echo "Full name is required."; http_response_code(400); exit; }
		
		$first_name = val_name($name_parts[0], "first name is incorrect.");
		$last_name = val_name($name_parts[1], "last name is incorrect.");
		$phoneNumber = val_phone($_POST["phoneNumber"], "phone number is incorrect.");
		$email = val_email($_POST["email"], "email is incorrect.");
		$emailMessage = htmlentities($_POST["emailMessage"]);
		
		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
		$stmt = $db->prepare("
			INSERT INTO contact_us (first_name, last_name, phone_number, email, message)
			VALUES (?, ?, ?, ?, ?)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$first_name,
			$last_name,
			$phoneNumber,
			$email,
			$emailMessage
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		$db->close();
		
		echo "Message sent.";
		
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
			UPDATE elderly_tasks
			SET completed = true
			WHERE task_id = ? AND elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$_POST["id"], $_SESSION["elderly_id"]]) or trigger_error($stmt->error, E_USER_ERROR);
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
			DELETE FROM elderly_tasks
			WHERE task_id = ? AND elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$_POST["id"], $_SESSION["elderly_id"]]) or trigger_error($stmt->error, E_USER_ERROR);
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
			SELECT ep.elderly_id, ep.hashed_pin, pa.pin_attempts
			FROM elderly_pin ep
			INNER JOIN pin_attempts pa ON ep.elderly_id = pa.elderly_id
			WHERE ep.elderly_id IN (
				SELECT ed.elderly_id
				FROM elderly_details ed
				WHERE ed.email = ?
			)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$email]) or trigger_error($stmt->error, E_USER_ERROR);
		$res = $stmt->get_result()->fetch_array(MYSQLI_ASSOC);
		$stmt->close();
		
		if ($res["pin_attempts"] > 9) // max 10 login attempts allowed
		{
			$db->close();
			header("Content-type: application/json");
			echo json_encode(["error" => "login attempts limit reached."]);
			http_response_code(403); exit; // login failed
		}
		
		$elderly_id = $res["elderly_id"];
		
		if (password_verify($pin, $res["hashed_pin"] ?? null)) // check password hash matches
		{
			$_SESSION["elderly_id"] = $elderly_id; // save user id for session
			
			// reset login attempts to 0
			$stmt = $db->prepare("
				UPDATE pin_attempts
				SET pin_attempts = 0
				WHERE elderly_id = ?
			") or trigger_error($db->error, E_USER_ERROR);
			$stmt->execute([$elderly_id]) or trigger_error($stmt->error, E_USER_ERROR);
			$stmt->close();
			$db->close();
			
			http_response_code(200); exit; // login accepted, exit
		}
		
		$stmt = $db->prepare("
			UPDATE pin_attempts
			SET pin_attempts = pin_attempts + 1
			WHERE elderly_id = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$elderly_id]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		
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
			SELECT cp.carer_id, cp.hashed_password
			FROM carer_password cp
			WHERE cp.carer_id IN (
				SELECT cd.carer_id
				FROM carer_details cd
				WHERE cd.email = ?
			)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$email]) or trigger_error($stmt->error, E_USER_ERROR);
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
		* @param string POSTDateOfBirth  date of birth
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
		$textSize = val_font_size($_POST["textSize"], "text size is incorrect.");
		$themeSet = val_theme_set($_POST["themeSet"], "theme set is incorrect.");
		$firstname = val_name($_POST["firstname"], "first name is incorrect.");
		$lastname = val_name($_POST["lastname"], "last name is incorrect.");
		$DateOfBirth = val_date_of_birth($_POST["DateOfBirth"], "date of birth is incorrect.");
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
			INSERT INTO emergency_contact_details (first_name, last_name, phone_number)
			VALUES (?, ?, ?)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$emfirstname, $emlastname, $emphone]) or trigger_error($stmt->error, E_USER_ERROR);
		$emergency_contact_id = $stmt->insert_id;
		$stmt->close();
		
		$stmt = $db->prepare("
			INSERT INTO elderly_details (
					first_name,
					last_name,
					date_of_birth,
					phone_number,
					email,
					carer_id,
					emergency_contact_id,
					colour_theme,
					text_size
				)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$firstname,
			$lastname,
			$DateOfBirth,
			$phone,
			$email,
			$carer_id,
			$emergency_contact_id,
			$themeSet,
			$textSize
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$elderly_id = $stmt->insert_id;
		$stmt->close();
		
		$stmt = $db->prepare("
			INSERT INTO elderly_pin (elderly_id, hashed_pin)
			VALUES (?, ?)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$elderly_id,
			password_hash($pin, PASSWORD_DEFAULT)
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		
		$stmt = $db->prepare("
			INSERT INTO pin_attempts (elderly_id, pin_attempts)
			VALUES (?, 0)
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([$elderly_id]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		
		{ // allergies
			$stmt = $db->prepare("
				INSERT INTO elderly_allergies (elderly_id, allergy_id)
				SELECT ?, allergy_id
				FROM allergies
				WHERE allergy_name = ?
			") or trigger_error($db->error, E_USER_ERROR);
			foreach ($allergies as $allergy => $val)
			{
				if ($val) { $stmt->execute([$elderly_id, $allergy]) or trigger_error($stmt->error, E_USER_ERROR); }
			}
			$stmt->close();
		}
		{ // medical conditions
			$stmt = $db->prepare("
				INSERT INTO elderly_medical_conditions (elderly_id, medical_condition_id)
				SELECT ?, medical_condition_id
				FROM medical_conditions
				WHERE condition_name = ?
			") or trigger_error($db->error, E_USER_ERROR);
			foreach ($medical_conditions as $condition => $val)
			{
				if ($val) { $stmt->execute([$elderly_id, $condition]) or trigger_error($stmt->error, E_USER_ERROR); }
			}
			$stmt->close();
		}
		{ // medication
			$stmt = $db->prepare("
				INSERT INTO elderly_medication (elderly_id, medication_id)
				SELECT ?, medication_id
				FROM medication
				WHERE medication_name = ?
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