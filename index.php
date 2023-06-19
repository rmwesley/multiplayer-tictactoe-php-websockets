<?php
session_start();

// Check if user is not yet logged in
if (empty($_SESSION['username'])) {
	// Display the registration/login form
	include "views/home.html";
	exit;
}

$username = $_SESSION['username'];

// Check if user is not yet in a room
if(empty($_GET['room_id'])){
	$html = file_get_contents("views/lobby.html");
	$html = str_replace("{{username}}", $username, $html);
	echo $html;
	exit;
}

$room_id = $_GET['room_id'];

$html = file_get_contents("views/game.html");
$html = str_replace("{{username}}", $username, $html);
$html = str_replace("{{roomId}}", $room_id, $html);
echo $html;
?>
