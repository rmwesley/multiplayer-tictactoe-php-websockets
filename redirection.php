<!DOCTYPE html>
<?php
session_start();
?>
<html>
  <head>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  </head>
  <body>
    <div class="d-flex justify-content-end">
    <button class="btn btn-primary mr-2" onclick="showProfileInfo()">
      <?= $_SESSION['username'] ?>
    </button>
  </div>
    <div class="container text-center">
      <div class="spinner-border" role="status">
        <span class="sr-only">Redirecting...</span>
      </div>
      <div>
        Welcome!
      </div>
  </div>
	<script>
      setTimeout(function () {
        window.location.href = "lobby.php";
      }, 1000);
    </script>
  </body>
</html>
