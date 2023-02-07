<?php
session_start();
include_once 'db.php';

$username = $_SESSION['username'];
$time = time();

$query = "UPDATE match_queue SET last_call = $time WHERE username = '$username'";
mysqli_query($conn, $query);

mysqli_close($conn);
?>
