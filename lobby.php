<?php
session_start();

// Replacing {{username}} placeholder
$username = $_SESSION['username'];
$html = file_get_contents("views/lobby.html");
$html = str_replace("{{username}}", $username, $html);

echo $html;
?>
