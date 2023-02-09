<?php
session_start();

include_once("db.php");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = mysqli_real_escape_string($conn, $_POST['username']);
	$password = mysqli_real_escape_string($conn, $_POST['password']);

	$sql = "SELECT * FROM users WHERE username = '$username'";
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) == 0) {
		$_SESSION['error'] = "Username does not exist";
		header("Location: ../index.php");
		exit;
	}
	$row = mysqli_fetch_assoc($result);
	if (!password_verify($password, $row['password'])) {
		$_SESSION['error'] = "Password is incorrect.";
		header("Location: ../index.php");
		exit;
	}
	$_SESSION['username'] = $username;
	header("Location: ../redirection.php");
}

mysqli_close($conn);
?>
