<!-- Code to display registration form -->
<?php
session_start();
if (isset($_SESSION['error'])) {
  echo "<p style='color:red'>" . $_SESSION['error'] . "</p>";
  unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  </head>
  <body>
    <div class="container">
      <div class="d-flex justify-content-end">
        <button class="btn btn-primary mr-2" onclick="showLoginForm()">Login</button>
        <button class="btn btn-primary" onclick="showRegisterForm()">Register</button>
      </div>
      <div id="loginForm" class="d-none">
        <form action="login.php" method="post">
          <h3>Login</h3>
          <input type="text" name="username" placeholder="Username">
          <input type="password" name="password" placeholder="Password">
          <input type="submit" value="Login">
        </form>
      </div>
      <div id="registerForm" class="d-none">
        <form action="register.php" method="post">
          <h3>Register</h3>
          <input type="text" name="username" placeholder="Username">
          <input type="password" name="password" placeholder="Password">
          <input type="password" name="confirmPassword" placeholder="Confirm Password">
          <input type="submit" value="Register">
        </form>
      </div>
    </div>
    <script src="index-btns.js"></script>
  </body>
</html>
