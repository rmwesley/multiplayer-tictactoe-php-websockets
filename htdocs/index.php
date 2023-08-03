<?php
session_start();

include_once "api/content_pages.php";
include_once "api/content_chatbox.php";

// Check if user is not yet logged in
if (!array_key_exists('username', $_SESSION)) {
	// Access as guest
	if (!array_key_exists('guest_id', $_SESSION)) {
		header("Location: api/guest_auth.php");
	}
}

$username = $_SESSION['username'];

// After setting the username, we can now include the navbar
include_once "api/content_navbar.php";

$page = 'lobby';
if(isset($_GET['page'])){
	$page = $_GET['page'];
}
checkPage($page);

switch($page){
case 'game':
	$room_id = $_GET['room_id'];

	// Setup/choose room css style
	$style = "default";
	if(isset($_GET['room_style'])){
		$style = $_GET['room_style'];
	}

	if($style == "custom"){
		$html = file_get_contents("views/custom-room.html");
		$html = str_replace("{{username}}", $username, $html);
		$html = str_replace("{{roomId}}", $room_id, $html);
		break;
	}

	$html = file_get_contents("views/default-room.html");
	$html = str_replace("{{username}}", $username, $html);
	$html = str_replace("{{roomId}}", $room_id, $html);
	break;
case 'history':
	$html = file_get_contents("views/history.html");

	// Obtain match history
	include_once "api/content_history.php";
	$html = str_replace("{{game_history}}", $match_history_table, $html);
	break;
default:
	$html = file_get_contents("views/lobby.html");
	break;
}
$html = str_replace("<body>", "<body>".$navbar, $html);
$html = str_replace("</body>", $chatbox."<body>", $html);
echo $html;
?>
