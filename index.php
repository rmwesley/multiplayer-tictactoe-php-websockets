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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'];

    // Validate input data (add more validation as needed)
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please enter both username and password.";
        header("Location: ../index.php");
        exit;
    }

    // Get database connection
    require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/db.php';

    // Prepare and execute the query using prepared statements
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Check if username exists
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = "Username does not exist.";
        header("Location: ../index.php");
        exit;
    }

    // Fetch the user data
    $row = mysqli_fetch_assoc($result);

    // Verify the password
    if (!password_verify($password, $row['hash'])) {
        $_SESSION['error'] = "Incorrect password.";
        header("Location: ../index.php");
        exit;
    }

    // Store the username in session
    $_SESSION['username'] = $username;
    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    // Redirect to the lobby
    header("Location: lobby.php");
    exit;
}

// Display the registration/login form
include "views/home.html";
