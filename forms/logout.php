<?php
// Enforce strict typing
declare(strict_types=1);

// Load secure session handling
require_once __DIR__ . '/../src/session.php';

// Securely destroy the session and redirect to login page
secure_logout('login.html');
