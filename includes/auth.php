<?php
/**
 * CarBuy - Admin Authentication Helper (middleware)
 *
 * Include on every admin page. Redirects to login when not authenticated.
 * Also provides helpers for logged-in admin data and logout.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool {
    return isset($_SESSION['admin_id']);
}

function current_admin(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'       => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'] ?? '',
    ];
}

// Protect an admin page. Pass $require = true to force authentication.
function require_admin(): void {
    if (!is_logged_in()) {
        remember_login();
    }
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to access the admin area.');
        header('Location: login.php');
        exit;
    }
}

function logout_admin(): void {
    $_SESSION = [];
    if (isset($_COOKIE['carbyu_remember'])) {
        setcookie('carbyu_remember', '', time() - 42000, '/');
    }
    if (ini_get('session.use_cookies')) {
        $params = session_get_clean_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

// Attempt to restore a session from the "Remember me" cookie.
function remember_login(): void {
    if (is_logged_in() || empty($_COOKIE['carbyu_remember'])) {
        return;
    }
    $parts = explode(':', $_COOKIE['carbyu_remember'], 2);
    if (count($parts) !== 2) return;
    [$id, $token] = $parts;
    if (!ctype_digit($id)) return;

    $admin = fetch_one("SELECT * FROM admins WHERE id = ?", [(int)$id]);
    $hashed = hash('sha256', (string)$token);
    if ($admin && hash_equals((string)$admin['remember_token'] ?? '', $hashed)) {
        session_regenerate_id(true);
        $_SESSION['admin_id']       = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
    }
}

function session_get_clean_params(): array {
    return [
        'path'    => session_get_cookie_params()['path'],
        'domain'  => session_get_cookie_params()['domain'],
        'secure'  => session_get_cookie_params()['secure'],
        'httponly'=> session_get_cookie_params()['httponly'],
    ];
}