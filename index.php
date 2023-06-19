<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $html = file_get_contents("views/lobby.html");
    $html = str_replace("{{username}}", $username, $html);
    echo $html;
    exit;
}



// Display the registration/login form
include "views/home.html";
