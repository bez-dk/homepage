<?php
// Login for the admin pages (/admin). There is a single admin, whose password
// hash is in db-config.php. Ordinary visitors never get a session cookie.
require_once __DIR__ . '/db.php';

const SESSION_NAME       = 'bez_admin';
const SESSION_HOURS      = 8;  // stay logged in this long without activity
const MAX_LOGIN_FAILURES = 5;  // wrong passwords per IP address ...
const LOGIN_LOCK_MINUTES = 15; // ... within this many minutes locks the login

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    // Long enough that writing a recipe doesn't log you out halfway.
    ini_set('session.gc_maxlifetime', (string) (SESSION_HOURS * 3600));
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => ($_SERVER['HTTPS'] ?? 'off') !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// True when the visitor is logged in as admin.
function is_admin(): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE && !isset($_COOKIE[SESSION_NAME])) {
        return false;
    }
    start_session();
    return ($_SESSION['admin'] ?? false) === true;
}

// For admin-only pages: send visitors who aren't logged in to the login page.
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: /admin', true, 302);
        exit;
    }
}

// Protection against other sites submitting forms on your behalf (CSRF).
function csrf_field(): string
{
    start_session();
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}

function check_csrf(): bool
{
    start_session();
    $token = $_POST['csrf'] ?? null;
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

// Logs in with the password. Returns null on success, otherwise a text key for the error.
function admin_login(string $password): ?string
{
    $hash = db_config()['admin_password_hash'] ?? '';
    $db   = db();
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '';

    if ($db) {
        $db->exec('DELETE FROM login_failures WHERE created_at < NOW() - INTERVAL 1 DAY');
        $count = $db->prepare('SELECT COUNT(*) FROM login_failures WHERE ip = ? AND created_at > NOW() - INTERVAL ' . LOGIN_LOCK_MINUTES . ' MINUTE');
        $count->execute([$ip]);
        if ($count->fetchColumn() >= MAX_LOGIN_FAILURES) {
            return 'admin.error.locked';
        }
    }

    if ($hash !== '' && password_verify($password, $hash)) {
        start_session();
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $db?->prepare('DELETE FROM login_failures WHERE ip = ?')->execute([$ip]);
        return null;
    }

    $db?->prepare('INSERT INTO login_failures (ip) VALUES (?)')->execute([$ip]);
    sleep(1); // slows down password guessing
    return 'admin.error.login';
}

function admin_logout(): void
{
    start_session();
    $_SESSION = [];
    session_destroy();
    setcookie(SESSION_NAME, '', ['expires' => time() - 3600, 'path' => '/']);
}
