<?php
// Enable strict typing in PHP.
// This prevents automatic type conversions that can cause subtle bugs.
declare(strict_types=1);

// Database connection settings

// Hostname or IP address of the MySQL server.
// 'localhost' or '127.0.0.1' refers to the local machine.
$DB_HOST = '127.0.0.1';

// Name of the application database.
$DB_NAME = 'secure_login_lab';

// Limited-privilege MySQL user created as maame@localhost.
$DB_USER = 'maame';

// Password for maame@localhost.
// NOTE: Hardcoding credentials is acceptable for local development only.
// In production, credentials should be stored in environment variables.
$DB_PASS = 'YOUR_PASSWORD_HERE';

// MySQLi error handling settings

// Configure MySQLi to:
// - Throw exceptions on errors
// - Use strict reporting mode
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Database connection function

// db() creates and returns a new MySQL database connection.
function db(): mysqli
{
    // Import database credentials into function scope.
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

    // Create a new MySQLi connection.
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

    // Force UTF-8 encoding for full Unicode support and security.
    $conn->set_charset('utf8mb4');

    // Return the active database connection.
    return $conn;
}
