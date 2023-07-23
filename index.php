<?php
session_start();

include_once "api/content_navbar.php";

// Check if user is not yet logged in
if (empty($_SESSION['username'])) {
	// Display the registration/login form
	include "views/home.html";
	exit;
}

$username = $_SESSION['username'];

// Get navbar and insert username
$navbar = str_replace("{{username}}", $username, $navbar);


switch($_GET['page']){
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
default:
	$html = file_get_contents("views/lobby.html");
	break;
}
$html = str_replace("<body>", "<body>".$navbar, $html);
echo $html;
?>
