<?php

require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../helper/helper.php';
/**
 * Returns the currently logged-in user array, or null if not logged in.
 * Shape: ['user_id' => int, 'username' => string, 'full_name' => string, 'role' => string]
 */
function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Returns true if anyone is logged in.
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

/**
 * Redirect to login if not authenticated.
 * Call at the top of every protected page.
 */
function require_login(): void {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to continue.');
        redirect('/login');
    }
}

/**
 * Require a specific role. Pass one role or an array of roles.
 *   require_role('advisor');
 *   require_role(['advisor', 'technician']);
 */
function require_role($roles): void {
    require_login();
    $user = current_user();
    $allowed = is_array($roles) ? $roles : [$roles];
    if (!in_array($user['role'], $allowed, true)) {
        http_response_code(403);
        die('Forbidden — you do not have permission to view this page.');
    }
}

/**
 * Log a user in by storing minimal info in the session.
 * Always regenerate the session ID on login (prevents fixation).
 */
function login_user($user): void {
    session_regenerate_id(true);

    if (is_object($user)) {
        $_SESSION['user'] = [
            'user_id'   => $user->getId(),
            'username'  => $user->getEmail(),
            'full_name' => $user->getFullName(),
            'role'      => $user->getRole()->value,
        ];
    } else {
        $_SESSION['user'] = [
            'user_id'   => (int)($user['user_id'] ?? $user['id']),
            'username'  => $user['username'] ?? $user['email'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
        ];
    }
}

/**
 * Log out the current user — destroys session entirely.
 */
function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']);
    }
    session_destroy();
}
