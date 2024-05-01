<?php

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

/* validate font size
	*
	* @param any $var  input
	* @param string $msg_fail  message to display on failure
	* 
	* @return string  $var if valid
	*/
function val_font_size($var, $msg_fail = "Invalid font size."): string
{
	if (in_array($var, ["Extra Large", "Large", "Medium", "Small"])) { return $var; }
	user_input_invalid($msg_fail);
}

/* validate theme set
	*
	* @param any $var  input
	* @param string $msg_fail  message to display on failure
	* 
	* @return int  $var if valid
	*/
function val_theme_set($var, $msg_fail = "Invalid theme set."): int
{
	if ($var >= 1 && $var <= 4) { return $var; }
	user_input_invalid($msg_fail);
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
	* @param string $var  value
	* @param string $msg_fail  message to display on failure
	* 
	* @return bool  $var if valid
	*/
function val_bool($var, $msg_fail = "no description."): bool
{
	if ($var = filter_var($var, FILTER_VALIDATE_BOOLEAN) !== false) { return boolval($var); }
	user_input_invalid($msg_fail);
}

/* validate mood name
	*
	* @param string $mood  mood name
	* @param string $msg_fail  message to display on failure
	* 
	* @return bool  $mood if valid
	*/
function val_mood(string $mood, string $msg_fail = "invalid mood."): string
{
	if (in_array($mood, ["good", "ok", "bad", ""])) { return $mood; }
	user_input_invalid($msg_fail);
}

/* validate medication name
	*
	* @param string $medication  medication name
	* @param string $msg_fail  message to display on failure
	* 
	* @return bool  $medication if valid
	*/
function val_medication(string $medication, string $msg_fail = "invalid medication name."): string
{
	$db = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME_CAREIFY) or trigger_error(mysqli_connect_errno(), E_USER_ERROR);
	// does the database have a medication with the given name?
	$stmt = $db->prepare("
		SELECT 1
		FROM medication
		WHERE medication_name = ?
	") or trigger_error($db->error, E_USER_ERROR);
	$stmt->execute([$medication]) or trigger_error($stmt->error, E_USER_ERROR);
	$result = $stmt->get_result();
	$num_rows = $result->num_rows;
	$stmt->close();
	$db->close();
	
	if ($num_rows) { return $medication; } // valid
	user_input_invalid($msg_fail); // invalid
}

/* validate medication dosage
	*
	* @param string $frequency  medication dosage
	* @param string $msg_fail  message to display on failure
	* 
	* @return bool  $dosage if valid
	*/
function val_medication_dosage(string $dosage, string $msg_fail = "invalid medication dosage."): string
{
	if (in_array($dosage, [
		'5 mg', '10 mg', '20 mg', '50 mg', '100 mg', '250 mg', '500 mg', '1 g'
	])) { return $dosage; }
	user_input_invalid($msg_fail);
}

/* validate medication name
	*
	* @param string $frequency  medication frequency
	* @param string $msg_fail  message to display on failure
	* 
	* @return bool  $frequency if valid
	*/
function val_medication_frequency(string $frequency, string $msg_fail = "invalid medication frequency."): string
{
	if (in_array($frequency, [
		'Once Daily', 'Twice Daily', 'Three Times Daily',
		'Four Times Daily', 'Every 6 Hours', 'Every 4 Hours',
		'Every 3 Hours', 'Every 2 Hours', 'Every Hour'
	])) { return $frequency; }
	user_input_invalid($msg_fail);
}
?>