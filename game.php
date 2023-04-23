<?php
session_start();
$username = $_SESSION['username'];

$room_id = $_GET['room_id'];

$html = file_get_contents("views/game.html");
$html = str_replace("{{username}}", $username, $html);
$html = str_replace("{{roomId}}", $room_id, $html);
echo $html;
?>
