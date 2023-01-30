<?php
// connect to database
$servername = "hostname";
$dbusername = "dbuser";
$dbpassword = "dbpass";
$dbname = "dbname";

// Create connection
$conn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            header("Location: redirection.html");
            exit;
        } else {
            echo "Password is incorrect.";
        }
    } else {
        echo "Username does not exist.";
    }
}

mysqli_close($conn);
?>
