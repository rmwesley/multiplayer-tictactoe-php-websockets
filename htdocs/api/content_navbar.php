<?php

// Setup of authentication forms
$loginForm = <<< HERE
<form action="api/login.php" method="post">
  <div class="form-group mb-3">
    <input name="username" type="text" placeholder="Enter Username" class="form-control">
  </div>
  <div class="form-group mb-3">
    <input name="password" type="password" placeholder="Password" class="form-control">
  </div>
  <div class="d-flex justify-content-center">
    <input type="submit" value="Login" class="btn btn-primary">
  </div>
</form>
HERE;

$registerForm = <<< HERE
<form action="api/register.php" onsubmit="return validateForm()" method="post">
  <div class="form-group mb-2">
    <input name="username" type="text" placeholder="Username" class="form-control">
  </div>
  <div class="form-group mb-2">
    <input name="password" type="password" placeholder="Password" class="form-control">
  </div>
  <div class="form-group mb-2">
    <input name="confirmation" type="password" placeholder="Confirm Password" class="form-control">
  </div>
  <div class="d-flex justify-content-center">
    <input type="submit" value="Register">
  </div>
</form>
HERE;

// Setup of authentication accordion (login/register accordion)
$authAccordion = <<< HERE
<div class="accordion" id="authAccordion">
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        Login
      </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#authAccordion">
      <div class="accordion-body">
        {{login_form}}
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
        Register
      </button>
    </h2>
    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#authAccordion">
      <div class="accordion-body">
        {{register_form}}
      </div>
    </div>
  </div>
</div>
HERE;

// Substtuting login/register forms into the accordion
$authAccordion = str_replace("{{login_form}}", $loginForm, $authAccordion);
$authAccordion = str_replace("{{register_form}}", $registerForm, $authAccordion);

// Setup authentication offcanvas
$authOffcanvas = <<< HERE
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAuth" aria-labelledby="offcanvasAuthLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasAuthLabel">Authentication</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    {{auth_accordion}}
  </div>
</div>
HERE;

// Substituting authentication accordion into the offcanvas body
$authOffcanvas = str_replace("{{auth_accordion}}", $authAccordion, $authOffcanvas);

// Setting up profile button with username and icon
$profileButton = <<< HERE
<button id="profile-btn" class="btn btn-outline-primary me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAuth" aria-controls="offcanvasAuth">
  {{profile_icon}}
  {{username}}
</button>
HERE;

$profileIcon = <<< HERE
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
  <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
</svg>
HERE;

$profileButton = str_replace("{{username}}", $_SESSION['username'], $profileButton);
$profileButton = str_replace("{{profile_icon}}", $profileIcon, $profileButton);

// Finally setting up navbar
$navbar = <<< HERE
<nav class='navbar mb-1 navbar-expand-md navbar-dark static-top bg-dark'>
  <div class='container-fluid'>
    <a class='navbar-brand' href='#'>TicTacToe</a>
    <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarCollapse' aria-controls='navbarCollapse' aria-expanded='false' aria-label='Toggle navigation'>
      <span class='navbar-toggler-icon'></span>
    </button>
    <div class='collapse navbar-collapse' id='navbarCollapse'>
      <ul class='navbar-nav me-auto mb-2 mb-md-0'>
        <li class='nav-item'>
          <a class='nav-link active' href='?page=home'>Home</a>
        </li>
        <li class='nav-item'>
          <a class='nav-link' href='?page=game'>Game Room</a>
        </li>
        <li class='nav-item'>
          <a class='nav-link' href='?page=history'>Match history</a>
        </li>
      </ul>
    </div>

    {{profile_button}}
    {{logout_button}}
  </div>
</nav>
{{auth_offcanvas}}
HERE;

$navbar = str_replace("{{auth_offcanvas}}", $authOffcanvas, $navbar);
$navbar = str_replace("{{profile_button}}", $profileButton, $navbar);

$logoutButton = "";
// If user is guest, logout button is unnecessary
if(!isset($_SESSION['guest_id'])){
	// Setting up logout button
	$logoutButton = <<< HERE
<form action="api/logout.php" method="post">
  <input name="Logout" type="submit" value="Logout" class="btn btn-outline-danger">
</form>
HERE;
}

$navbar = str_replace("{{logout_button}}", $logoutButton, $navbar);
