<?php
declare(strict_types=1);

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    // Enable this only when running on HTTPS
    // ini_set('session.cookie_secure', '1');

    ini_set('session.cookie_samesite', 'Lax');

    session_start();

    $idleLimit = 20 * 60; // 20 minutes

    if (!isset($_SESSION['created_at'])) {
        $_SESSION['created_at'] = time();
        $_SESSION['last_activity'] = time();
        session_regenerate_id(true);
        return;
    }

    // If idle too long, destroy session without redirect
    if (isset($_SESSION['last_activity']) && (time() - (int)$_SESSION['last_activity']) > $idleLimit) {
        secure_logout(null); // no redirect, just destroy
        return;
    }

    $_SESSION['last_activity'] = time();
}

function require_login(string $redirectTo = 'login.html'): void
{
    start_secure_session();

    if (empty($_SESSION['user_id'])) {
        header("Location: {$redirectTo}");
        exit;
    }
}

/**
 * secure_logout
 * If $redirectTo is null, it will NOT redirect (useful for timeouts).
 */
function secure_logout(?string $redirectTo = 'login.html'): void
{
    // Start session using the SAME secure settings
    start_secure_session();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(
            session_name(),
            '',
            [
                'expires'  => time() - 42000,
                'path'     => $params['path'] ?? '/',
                'domain'   => $params['domain'] ?? '',
                'secure'   => (bool)($params['secure'] ?? false),
                'httponly' => true,
                'samesite' => $params['samesite'] ?? 'Lax',
            ]
        );
    }

    session_destroy();

    // Redirect only if a redirect target is provided
    if ($redirectTo !== null) {
        header("Location: {$redirectTo}");
        exit;
    }
}
