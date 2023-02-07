<?php
session_start();

include_once("db.php");

// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}

// Get the username from the query string
$username = $_SESSION['username'];

// Check if the username is in the match_queue table
$sql = "SELECT * FROM match_queue WHERE username = '$username'";
$result = $conn->query($sql);

// Return true if the username is in the match_queue table
$isInWaitingList = ($result->num_rows > 0);

// Return the result as JSON
echo json_encode($isInWaitingList);

// Close the connection
$conn->close();
?>
