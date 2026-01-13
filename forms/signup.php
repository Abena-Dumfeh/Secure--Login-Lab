<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';

// 1) Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "405 Method Not Allowed";
    exit;
}

// 2) Read input safely
$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

// 3) Validate username and password (server-side)
if (strlen($username) < 3 || strlen($username) > 50) {
    http_response_code(400);
    echo "Username must be 3–50 characters.";
    exit;
}

if (strlen($password) < 10) {
    http_response_code(400);
    echo "Password must be at least 10 characters.";
    exit;
}

// Optional: strict username format (letters, numbers, underscore)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    http_response_code(400);
    echo "Username can only contain letters, numbers, and underscores.";
    exit;
}

// 4) Create user (auth.php handles hashing + prepared statement insert)
try {
    create_user($username, $password);

    // Success → go to login page
    header('Location: login.html');
    exit;

} catch (mysqli_sql_exception $e) {

    // Duplicate username (MySQL error code usually 1062)
    if ((int)$e->getCode() === 1062) {
        http_response_code(409);
        echo "That username is already taken. Please choose another.";
        exit;
    }

    // Generic error (don't leak details)
    http_response_code(500);
    echo "Signup failed due to a server error. Please try again.";
    exit;
}
