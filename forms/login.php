<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/session.php';

// 1) Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "405 Method Not Allowed";
    exit;
}

// 2) Read input safely
$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

// 3) Basic validation
if ($username === '' || $password === '') {
    http_response_code(400);
    echo "Username and password are required.";
    exit;
}

// 4) Verify login using auth layer (handles lockout + password verify)
$user = verify_login($username, $password);

if (!$user) {
    http_response_code(401);
    echo "Invalid username or password.";
    exit;
}

// 5) Start secure session and store login state
start_secure_session();

// Regenerate ID after login (extra protection against session fixation)
session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['id'];
$_SESSION['username'] = (string)$user['username'];

// 6) Redirect to protected page
header('Location: dashboard.php');
exit;
