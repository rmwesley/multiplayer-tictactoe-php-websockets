<?php
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Opening database connection
    require_once '../config/db.php';

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Prepare and execute the query using prepared statements
    $sql = "SELECT * FROM users WHERE username = ?";
    $statement = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($statement, 's', $username);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        header("location:../index.php?loginFailed=true&reason=username");
        exit;
    }
    $row = mysqli_fetch_assoc($result);
    if (!password_verify($password, $row['hash'])) {
        $_SESSION['error'] = "Password is incorrect.";
        header("location:../index.php?loginFailed=true&reason=password");
        exit;
    }
    $_SESSION['username'] = $username;
    header("Location: ../index.php?loginSuccess=true");

    // Closing database connection
    mysqli_close($conn);
}

?>
