<?php
session_start();
include_once '../../config/db.php';

function generate_token_pair(): array {
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));

    return [$selector, $validator];
}

function insert_guest_token(string $selector, string $hashed_validator, string $expiry): bool {
    global $conn;
    $sql = 'INSERT INTO guest_tokens(selector, hashed_validator, expiry)
            VALUES(?, ?, ?)';

    $statement = $conn->prepare($sql);
    $statement->bind_param('sss', $selector, $hashed_validator, $expiry);

    return $statement->execute();
}

function add_new_guest(int $day = 30) {
    global $conn;
    [$selector, $validator] = generate_token_pair();

    $token = $selector . ':' . $validator;

    // Set the expiration date
    $expired_seconds = time() + 60 * 60 * 24 * $day;
    // Hash the validator to store in the database
    $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
    // Format a unix timestamp into a string to insert in a MySQL database
    $expiry = date('Y-m-d H:i:s', $expired_seconds);

    // Insert the token in the database
    if (insert_guest_token($selector, $hashed_validator, $expiry)) {
        setcookie('remember_guest', $token, $expired_seconds);
        return $conn->insert_id;
    }
}

function find_guest_token_by_selector(string $selector):array{
    global $conn;
    $sql = 'SELECT id, selector, hashed_validator, expiry
                FROM guest_tokens
                WHERE selector = ? AND
                    expiry >= now()
                LIMIT 1';

    $statement = $conn->prepare($sql);
    // Bind selector, a string paramater
    $statement->bind_param('s', $selector);

    $statement->execute();
    return  $statement->get_result()->fetch_assoc();

}

function get_guest_from_token($token) {
    // Split/parse token
    [$selector, $validator] = explode(':', $token);

    $result = find_guest_token_by_selector($selector);
    if (empty($result)) {
        return;
    }
    
    if(password_verify($validator, $result['hashed_validator'])){
        return $result['id'];
    }
    return;
}

function log_guest_in($guest_id): bool {
    // Prevent session fixation attack
    if (session_regenerate_id()) {
        // Set username in the session
        $_SESSION['guest_id'] = $guest_id;
		$_SESSION['username'] = 'Guest' . $guest_id;
        return true;
    }

    return false;
}

// Get remember_guest cookie token
$token = filter_input(INPUT_COOKIE, 'remember_guest', FILTER_SANITIZE_STRING);

if(!empty($token)){
    $guest_id = get_guest_from_token($token);
}

// If no guest was found, create a new one
if(empty($guest_id)){
    $guest_id = add_new_guest();
}

// Log user in as guest
log_guest_in($guest_id);

header("Location: ../index.php?page=lobby");
