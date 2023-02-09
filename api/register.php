<?php
session_start();

include_once("db.php");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);

	// Check if username already exists
	$checkUsernameSql = "SELECT * FROM users WHERE username = '$username'";
    $checkUsernameResult = mysqli_query($conn, $checkUsernameSql);

    if (mysqli_num_rows($checkUsernameResult) > 0) {
        $_SESSION['error'] = "Username already exists";
        header("Location: ../index.php");
        exit;
    }

    // Check if both passwords match
    if ($password != $confirmPassword) {
        $_SESSION['error'] = "Passwords differ";
        header("Location: ../index.php");
        exit;
	}
	// Hash password before saving
	$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

	$sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashedPassword')";
	if (!mysqli_query($conn, $sql)) {
		$_SESSION['error'] = "Error: " . $sql . "<br>" . mysqli_error($conn);
        header("Location: ../index.php");
		exit;
	}
	echo "New record created successfully";
    header("Location: ../index.php");
}

mysqli_close($conn);
?>
