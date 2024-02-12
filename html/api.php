<?php

if ($_SERVER["REQUEST_METHOD"] === "GET")
{
	if (isset($_GET["test"]))
	{
		echo json_encode(["test" => "ok"]);
	}
	else
	{
		foreach($_GET as $k => $p) { echo htmlspecialchars($k) . ":" . htmlspecialchars($p) . "<br>"; }
		//http_response_code(400);
		//echo "Invalid GET request.";
	}
}
else if ($_SERVER["REQUEST_METHOD"] === "POST")
{
	if (isset($_POST["test"]))
	{
		echo json_encode(["test" => "ok"]);
	}
	else
	{
		foreach($_POST as $k => $p) { echo htmlspecialchars($k) . ":" . htmlspecialchars($p) . "<br>"; }
		//http_response_code(400);
		//echo "Invalid POST request.";
	}
}

http_response_code(200);
exit();
?>