<?php
// Logout functionality
session_start();
session_destroy();
header("Location: ../index.php");
exit;
?>
