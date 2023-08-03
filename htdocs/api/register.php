<?php
require_once '../../config/db.php';

function validate_register_fields($username, $password): bool {
    global $conn;
    // Check if username already exists
    $checkUsernameSql = "SELECT * FROM users WHERE username = '$username'";
    $checkUsernameResult = mysqli_query($conn, $checkUsernameSql);

    // Check if input data is empty
    if (empty($username) || empty($password)) {
        header("location: ../index.php?registerSuccess=false");
        exit;
    }

    if(preg_match("/Guest\d+$/", $username)){
        return false;
    };
    if (mysqli_num_rows($checkUsernameResult) > 0) {
        return false;
    }
    return true;
}

function log_user_in($username): bool {
    session_start();
    session_unset();
    // Prevent session fixation attack
    if (session_regenerate_id()) {
        // Set username in the session
        $_SESSION['username'] = $username;
        return true;
    }

    return false;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $conn;
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmation']);

    validate_register_fields($username, $password);

    // Hash password before saving
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Prepare and execute the query using prepared statements
    $sql = "INSERT INTO users(username, hash) VALUES(?, ?)";
    $statement = $conn->prepare($sql);
    // Bind username, a string paramater
    $statement->bind_param('ss', $username, $hashedPassword);
    $statement->execute();

	$result = $statement->get_result();

    if ($result != false) {
        header("Location: ../index.php?registerSuccess=false");
        exit;
    }

    // Log in by storing the username in the session
    log_user_in($username);

    // Close database connection
    $conn->close();

    header("Location: ../index.php?registerSuccess=true");
}
?>
