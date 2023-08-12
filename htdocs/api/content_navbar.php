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
  <div class="d-flex justify-content-center btn btn-outline-primary">
    <input type="submit" value="Login">
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
  <div class="d-flex justify-content-center btn btn-outline-primary">
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

// Logout button is not set by default, only if user is logged in
$logoutButton = "";

// If user is guest, logout button is unset
if(!isset($_SESSION['guest_id'])){
	// Setting up logout button
	$logoutButton = <<< HERE
<div class="text-center pt-5">
  <form action="api/logout.php" method="post" class="btn btn-outline-danger">
    <input name="Logout" type="submit" value="Logout">
  </form>
</div>
HERE;
}

// Setting up login status message
$loginStatusMsg = <<< HERE
<p>Logged in as
  <span class="text-primary">
  {{username}}
  </span>
  {{guest_user_message}}
</p>
HERE;

// Sustitute username into login status message
$loginStatusMsg = str_replace("{{username}}", $_SESSION['username'], $loginStatusMsg);

// Show whether user is a guest
if(isset($_SESSION['guest_id'])){
	$loginStatusMsg = str_replace("{{guest_user_message}}", "(Guest user)", $loginStatusMsg);
}
else{
	$loginStatusMsg = str_replace("{{guest_user_message}}", "", $loginStatusMsg);
}

// Setup authentication offcanvas
$authOffcanvas = <<< HERE
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasAuth" aria-labelledby="offcanvasAuthLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasAuthLabel">Authentication</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    {{login_status_message}}
    {{auth_accordion}}
    {{logout_button}}
  </div>
</div>
HERE;

// Add login status message at top of authentication offcanvas
$authOffcanvas = str_replace("{{login_status_message}}", $loginStatusMsg, $authOffcanvas);
// Substituting authentication accordion into the offcanvas body
$authOffcanvas = str_replace("{{auth_accordion}}", $authAccordion, $authOffcanvas);
// Adding logout button right below authentication accordion
$authOffcanvas = str_replace("{{logout_button}}", $logoutButton, $authOffcanvas);

// Setting up profile button with username and icon
$profileButton = <<< HERE
<button id="profile-btn" class="btn btn-outline-primary ms-auto me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAuth" aria-controls="offcanvasAuth">
  {{profile_icon}}
  <span class='d-none d-sm-inline'>
    {{username}}
  </span>
</button>
HERE;

$profileIcon = <<< HERE
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
  <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
</svg>
HERE;

$profileButton = str_replace("{{username}}", $_SESSION['username'], $profileButton);
$profileButton = str_replace("{{profile_icon}}", $profileIcon, $profileButton);

$themeButton = <<< HERE
<div class="nav-item dropdown">
  <button class="btn btn-link nav-link py-2 px-0 px-lg-2 dropdown-toggle d-flex align-items-center text-light" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" data-bs-display="static" aria-label="Toggle theme (dark)">
    <svg class="bi theme-icon-active" width="1.3rem" height="1.3rem">
      <use href="#moon-stars-fill" fill="currentcolor"></use>
    </svg>
    <span class="d-none ms-2" id="bd-theme-text">Toggle theme</span>
  </button>
  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme-text">
    <li>
      <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
        <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#sun-fill"></use></svg>
        Light
        <svg class="bi ms-auto d-none"><use href="#check2"></use></svg>
      </button>
    </li>
    <li>
      <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="dark" aria-pressed="true">
        <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
        Dark
        <svg class="bi ms-auto d-none"><use href="#check2"></use></svg>
      </button>
    </li>
    <li>
      <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="auto" aria-pressed="false">
        <svg class="bi me-2 opacity-50 theme-icon" width="1em" height="1em"><use href="#circle-half"></use></svg>
        Auto
        <svg class="bi ms-auto d-none"><use href="#check2"></use></svg>
      </button>
    </li>
  </ul>
</div>
HERE;

$icons = <<< HERE
<svg xmlns="http://www.w3.org/2000/svg" class="d-none">
  <symbol id="circle-half" viewBox="0 0 16 16">
    <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"></path>
  </symbol>

  <symbol id="moon-stars-fill" viewBox="0 0 16 16">
    <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"></path>
    <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"></path>
  </symbol>

  <symbol id="sun-fill" viewBox="0 0 16 16">
    <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"></path>
  </symbol>
</svg>
HERE;
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
    {{theme_button}}
  </div>
</nav>

{{auth_offcanvas}}
HERE;

$navbar = str_replace("{{auth_offcanvas}}", $authOffcanvas, $navbar);
$navbar = str_replace("{{profile_button}}", $profileButton, $navbar);
$navbar = str_replace("{{theme_button}}", $themeButton, $navbar);

$navbar = $icons . $navbar;
