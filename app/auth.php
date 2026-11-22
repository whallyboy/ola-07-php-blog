<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION['admin_id'], $_SESSION['admin_login']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('admin/login.php');
    }

    $lifetime = 60 * 30;
    if (!isset($_SESSION['last_activity']) || time() - $_SESSION['last_activity'] > $lifetime) {
        logout();
        redirect('admin/login.php');
    }
    $_SESSION['last_activity'] = time();
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

function login_admin(PDO $pdo, string $email, string $password): bool
{
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([strtolower(trim($email))]);
    $admin = $stmt->fetch();
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }
    if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
        $rehash = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
        $rehash->execute([password_hash($password, PASSWORD_DEFAULT), $admin['id']]);
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_login'] = true;
    $_SESSION['last_activity'] = time();
    return true;
}
