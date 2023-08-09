<?php
session_start();
// Opening database connection
include_once '../../config/db.php';

// Validate input data
function validate_auth_fields($username, $password): bool{
    if (empty($username) || empty($password)) {
        return false;
    }
    return true;
}

function find_by_username($username){
    global $db_conn;
    // Prepare and execute the query using prepared statements
    $sql = "SELECT * FROM users WHERE username = ?";
    $statement = $db_conn->prepare($sql);

    // Bind username, a string parameter
    $statement->bind_param('s', $username);
    $statement->execute();

    $result = $statement->get_result();

    // Close prepared statement
    $statement->close();

    // Return the user data row
    return $result->fetch_assoc();
}

function log_user_in($username): bool {
    session_unset();
    // Prevent session fixation attack
    if (session_regenerate_id()) {
        // Set username in the session
        $_SESSION['username'] = $username;
        return true;
    }

    return false;
}

// Check if a POST form was submitted and handle it
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if(!validate_auth_fields($username, $password)){
        header("location: ../index.php?loginFailed=true");
        exit;
    }

    $row = find_by_username($username);
    // Check if user was found
    if (empty($row)) {
        header("location: ../index.php?loginFailed=true");
        exit;
    }

    // Verify the password
    if (!password_verify($password, $row['hash'])) {
        header("location: ../index.php?loginFailed=true");
        exit;
    }

    // Log in by clearing the session and storing the username in it
    log_user_in($username);

    // Close database connection
    $db_conn->close();

    header("Location: ../index.php?page=lobby");
}

?>
