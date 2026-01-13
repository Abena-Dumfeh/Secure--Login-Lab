<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function create_user(string $username, string $password): void
{
    $conn = db();

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    if ($passwordHash === false) {
        throw new RuntimeException('Password hashing failed.');
    }

    $stmt = $conn->prepare(
        "INSERT INTO users (username, password_hash) VALUES (?, ?)"
    );
    $stmt->bind_param("ss", $username, $passwordHash);
    $stmt->execute();
}

function find_user_by_username(string $username): ?array
{
    $conn = db();

    $stmt = $conn->prepare(
        "SELECT id, username, password_hash, failed_attempts, lock_until
         FROM users
         WHERE username = ?"
    );
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();
    return $user ?: null;
}

function record_failed_login(int $userId, int $maxAttempts = 5): void
{
    $conn = db();

    // Transaction keeps attempt count + lock decision consistent
    $conn->begin_transaction();

    try {
        // 1) Increment failed attempts
        $inc = $conn->prepare(
            "UPDATE users
             SET failed_attempts = failed_attempts + 1
             WHERE id = ?"
        );
        $inc->bind_param("i", $userId);
        $inc->execute();

        // 2) Read the new count
        $check = $conn->prepare(
            "SELECT failed_attempts FROM users WHERE id = ?"
        );
        $check->bind_param("i", $userId);
        $check->execute();
        $attempts = (int)$check->get_result()->fetch_assoc()['failed_attempts'];

        // 3) Lock if limit reached
        if ($attempts >= $maxAttempts) {
            $lock = $conn->prepare(
                "UPDATE users
                 SET lock_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE)
                 WHERE id = ?"
            );
            $lock->bind_param("i", $userId);
            $lock->execute();
        }

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}

function clear_failed_logins(int $userId): void
{
    $conn = db();

    $stmt = $conn->prepare(
        "UPDATE users
         SET failed_attempts = 0,
             lock_until = NULL
         WHERE id = ?"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

function verify_login(string $username, string $password): ?array
{
    $user = find_user_by_username($username);

    // Optional: reduce timing-based username discovery slightly
    if (!$user) {
        usleep(150000); // 150ms
        return null;
    }

    if (!empty($user['lock_until']) && strtotime((string)$user['lock_until']) > time()) {
        return null;
    }

    if (!password_verify($password, (string)$user['password_hash'])) {
        record_failed_login((int)$user['id']);
        return null;
    }

    clear_failed_logins((int)$user['id']);
    return $user;
}
