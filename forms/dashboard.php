<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/session.php';

// This will start the secure session and redirect to login.html if not logged in
require_login('login.html');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Dashboard</title>
</head>
<body>
  <h1>Dashboard</h1>

  <p>
    Welcome,
    <b><?= htmlspecialchars((string)($_SESSION['username'] ?? 'User')) ?></b>!
  </p>

  <p><a href="logout.php">Logout</a></p>
</body>
</html>
