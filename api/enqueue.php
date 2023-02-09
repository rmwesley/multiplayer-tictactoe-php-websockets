<?php
session_start();
include_once("db.php");

$username = $_SESSION['username'];
$timestamp = time();

$sql = "INSERT INTO match_queue (username, last_call) VALUES ('$username', '$timestamp')";
$conn->query($sql);

$conn->close();
?>
