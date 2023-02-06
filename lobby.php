<!DOCTYPE html>
<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Lobby</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
</head>
<body>
  <div class="container text-center">
    <h1>Lobby</h1>
    <div class="container">
      <div class="text-center d-inline" id="playBtn">
        <button class="btn btn-primary" onclick="play()">Play</button>
      </div>
      <div class="text-center d-none" id="cancelBtn">
        <button class="btn btn-danger" onclick="cancel()">Cancel</button>
      </div>
    </div>
  </div>
  <script src="lobby-btns.js"></script>
</body>
</html>
