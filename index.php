<!-- Code to display registration form -->
<?php
session_start();
if (isset($_SESSION['error'])) {
  echo "<p style='color:red'>" . $_SESSION['error'] . "</p>";
  unset($_SESSION['error']);
}

readfile("views/index.html");
?>
