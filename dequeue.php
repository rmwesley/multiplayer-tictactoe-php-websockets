<?php
include_once("db.php");
session_start();

$username = $_SESSION['username'];

$sql = "DELETE FROM match_queue WHERE username='$username'";
$conn->query($sql);

$conn->close();
?>
