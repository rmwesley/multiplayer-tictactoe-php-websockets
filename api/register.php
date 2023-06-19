<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../config/db.php';

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmation']);

    // Validate input data
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Registration failed";
        header("Location: ../index.php?registerFailed=true&reason=emptyFields");
        exit;
    }

    // Check if username already exists
    $checkUsernameSql = "SELECT * FROM users WHERE username = '$username'";
    $checkUsernameResult = mysqli_query($conn, $checkUsernameSql);

    if (mysqli_num_rows($checkUsernameResult) > 0) {
        header("location: ../index.php?registerFailed=true&reason=usernameAlreadyExists");
        exit;
    }

    // Check if both passwords match
    if ($password != $confirmPassword) {
        header("location: ../index.php?registerFailed=true&reason=passwordMismatch");
        header("Location: ../index.php");
        exit;
    }

    // Hash password before saving
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (username, hash) VALUES ('$username', '$hashedPassword')";
    if (!mysqli_query($conn, $sql)) {
        $_SESSION['error'] = "Error: " . $sql . "<br>" . mysqli_error($conn);
        header("Location: ../index.php");
        exit;
    }
    echo "New record created successfully";
    header("Location: ../index.php?registerSuccess=true");

    mysqli_close($conn);
}
?>
