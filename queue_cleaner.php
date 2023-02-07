<?php
include_once("db.php");

while (true) {
  $sql = "SELECT * FROM match_queue";
  $result = $conn->query($sql);
  
  if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
      $username = $row["username"];
      $last_call = $row["last_call"];
      $current_time = time();
      
      if ($current_time - $last_call >= 10) {
        $remove_sql = "DELETE FROM match_queue WHERE username='$username'";
        if ($conn->query($remove_sql) === TRUE) {
          echo "$username removed from waiting list\n";
        } else {
          echo "Error deleting $username from waiting list: " . $conn->error . "\n";
        }
      }
    }
  }
  
  sleep(20);
}
?>
