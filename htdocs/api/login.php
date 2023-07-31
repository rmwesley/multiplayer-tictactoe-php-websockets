<?php
session_start();

// Check if a form was submitted and handle it
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Opening database connection
    require_once '../../config/db.php';

    // Sanitize input data
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

    // Validate input data
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Login failed";
        header("location: ../index.php?loginFailed=true");
        exit;
    }

    // Prepare and execute the query using prepared statements
    $sql = "SELECT * FROM users WHERE username = ?";
    $statement = $conn->prepare($sql);

    // Bind username, a string paramater
    $statement->bind_param('s', $username);
    $statement->execute();

    // Check if username exists
    $result = $statement->get_result();
    if (mysqli_num_rows($result) == 0) {
        $_SESSION['error'] = "Login failed";
        header("location: ../index.php?loginFailed=true");
        exit;
    }

    // Fetch the user data
    $row = $result->fetch_assoc();

    // Verify the password
    if (!password_verify($password, $row['hash'])) {
        $_SESSION['error'] = "Login failed";
        header("location: ../index.php?loginFailed=true");
        exit;
    }

    // Store the username in session after clearing it
	session_unset();
    $_SESSION['username'] = $username;

    // Close prepared statement
    $statement->close();
    // Closing database connection
    $conn->close();

    header("Location: ../index.php?page=lobby");
}

?>
