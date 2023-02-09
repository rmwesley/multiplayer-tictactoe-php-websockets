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
  <div class="d-flex justify-content-end">
	<button class="btn btn-primary mr-2" onclick="showProfileInfo()">
      <?= $_SESSION['username'] ?>
    </button>
  </div>
  <div class="container text-center">
    <h1>Lobby</h1>
    <div class="container">
      <div class="invisible text-center" id="waiting-room">
        <h2>Waiting for another player...</h2>
        <div class="spinner-border" role="status">
          <span class="sr-only">Loading...</span>
        </div>
      </div>
      <div class="text-center d-inline" id="playBtn">
        <button class="btn btn-primary" onclick="play()">Play</button>
      </div>
      <div class="text-center d-none" id="cancelBtn">
        <button class="btn btn-danger" onclick="cancel()">Cancel</button>
      </div>
    </div>
  </div>
  <script src="assets/js/lobby-btns.js"></script>
  <script src="assets/js/is-in-queue.js"></script>
  <script src="assets/js/ping-queue.js"></script>
  <script src="assets/js/profile.js"></script>
</body>
</html>
