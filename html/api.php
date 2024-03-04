<?php

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

// validate number positive
function val_num_pos(&$var, &$msg_fail = "no description.")
{
	if (is_numeric($var) && $var > 0) { return $var; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate val_password, min 8 characters
function val_password(&$password, &$msg_fail = "no description.")
{
	if (is_string($password) && strlen($password) >= 8) { return $password; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate name, min 2 max 20 characters and no numbers
function val_name(&$name, &$msg_fail = "no description.")
{
	if (preg_match('/^[a-zA-Z]{2,20}+$/', $name)) { return $name; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate date of birth, YYYY-MM-DD
function val_date_of_birth(&$date_of_birth, &$msg_fail = "no description.")
{
	if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $date_of_birth)) { return $date_of_birth; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate phone number, starts with 0 then 10 numbers after
function val_phone(&$phone_number, &$msg_fail = "no description.")
{
	if (preg_match('/^0[0-9]{10}+$/', $phone_number)) { return $phone_number; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate email
function val_email(&$email, &$msg_fail = "no description.")
{
	if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) { return $email; }
	echo $msg_fail;
	http_response_code(400); exit;
}

// validate string allow empty
function val_str_null(&$var, &$msg_fail = "no description.")
{
	return $var;
}

// validate bool
function val_bool($var, &$msg_fail = "no description.")
{
	if ($var = filter_var($var, FILTER_VALIDATE_BOOLEAN) !== false) { return $var; }
	echo $msg_fail;
	http_response_code(400); exit;
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
	else if (isset($_GET["validate_password"])) { val_password($_GET["validate_password"]); }
	else if (isset($_GET["validate_allergies"])) { val_str_null($_GET["validate_allergies"]); }
	else if (isset($_GET["validate_carename"])) { val_name($_GET["validate_carename"]); }
	
	http_response_code(200); exit;
}
else if ($_SERVER["REQUEST_METHOD"] === "POST")
{
	$_POST = get_post_json(); // POST data is JSON encoded, store back into post
	
	// action to do is stored in GET because of internal redirect
	if (isset($_GET["test"]))
	{
		echo json_encode(["test" => "ok"]);
		http_response_code(200); exit;
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
		$password = val_password($_POST["password"], "password is incorrect.");
		$allergies = val_str_null($_POST["allergies"], "allergies are incorrect.");
		$hypertension = val_bool($_POST["hypertension"]);
		$arthritis = val_bool($_POST["arthritis"]);
		$heartdisease = val_bool($_POST["heartdisease"]);
		$dementia = val_bool($_POST["dementia"]);
		$osteoporosis = val_bool($_POST["osteoporosis"]);
		$carename = val_name($_POST["carename"], "carer name is incorrect.");

		$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
		
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
			SELECT
				?,
				?,
				?,
				?,
				?,
				ca.carer_id,
				?
			FROM
				carer_details ca
			WHERE
				ca.first_name = ?
		") or trigger_error($db->error, E_USER_ERROR);
		$stmt->execute([
			$firstname,
			$lastname,
			"2020-12-12",
			$phone,
			$email,
			$emergency_contact_id,
			$carename
		]) or trigger_error($stmt->error, E_USER_ERROR);
		$stmt->close();
		
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