<?php
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
      </ul>
    </div>

    <div class="d-flex">

      <button id="profile-btn" class="btn btn-outline-primary me-2">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
  <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/>
</svg>
		Profile
        {{username}}
      </button>
      <form action="api/logout.php" method="post">
        <input name="Logout" type="submit" value="Logout" class="btn btn-outline-danger">
      </form>
    </div>
  </div>
</nav>
HERE;
