<?php
session_start();

if(isset($_SESSION['username'])) {
    echo json_encode([
        'username' => $_SESSION['username'],
		'guestUser' => isset($_SESSION['guest_id'])
        ]);
}
?>
