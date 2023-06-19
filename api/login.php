<?php
session_start();

// Check if a form was submitted and handle it
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Opening database connection
    require_once '../config/db.php';

    // Sanitize input data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Prepare and execute the query using prepared statements
    $sql = "SELECT * FROM users WHERE username = ?";
    $statement = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($statement, 's', $username);
    mysqli_stmt_execute($statement);
    $result = mysqli_stmt_get_result($statement);

    $sql = "SELECT * FROM users WHERE username = '$username'";

    // Check if username exists
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = "Login failed";
        header("location: ../index.php?loginFailed=true");
        exit;
    }

    // Fetch the user data
    $row = mysqli_fetch_assoc($result);

    // Verify the password
    if (!password_verify($password, $row['hash'])) {
        $_SESSION['error'] = "Login failed";
        header("location: ../index.php?loginFailed=true");
        exit;
    }

    // Store the username in session
    $_SESSION['username'] = $username;

    // Close prepared statement
    mysqli_stmt_close($statement);
    // Closing database connection
    mysqli_close($conn);

    header("Location: ../index.php?loginSuccess=true");
}

?>
