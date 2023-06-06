<?php
session_start();

if (isset($_SESSION['error'])) {
	echo "<p style='color:red'>" . $_SESSION['error'] . "</p>";
	unset($_SESSION['error']);
}

// Treat POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	// Getting database connection
	require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';
	$username = $_POST['username'];
	$password = $_POST['password'];

	$sql = "SELECT * FROM users WHERE username = '$username'";
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) == 0) {
		$_SESSION['error'] = "Username does not exist";
		header("Location: ../index.php");
		exit;
	}
	$row = mysqli_fetch_assoc($result);
	if (!password_verify($password, $row['hash'])) {
		$_SESSION['error'] = "Password is incorrect.";
		header("Location: ../index.php");
		exit;
	}
	$_SESSION['username'] = $username;
	mysqli_close($conn);
}
if(isset($_SESSION['username'])){
	// Replacing {{username}} placeholder
	$username = $_SESSION['username'];
	$html = file_get_contents("views/lobby.html");
	$html = str_replace("{{username}}", $username, $html);

	echo $html;
	exit;
}
readfile("views/index.html");
