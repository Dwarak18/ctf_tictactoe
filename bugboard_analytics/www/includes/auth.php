<?php
require_once __DIR__ . '/db.php';

function session_start_safe(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function current_user(): ?array {
    session_start_safe();
    if (empty($_SESSION['user_id'])) return null;
    $db = get_db();
    $stmt = $db->prepare("SELECT id, email, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_login(): array {
    $user = current_user();
    if (!$user) {
        header('Location: /login.php');
        exit;
    }
    return $user;
}

function login(string $email, string $password): ?array {
    $db = get_db();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_start_safe();
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        // Set a readable (non-httpOnly) flag cookie for admins so the XSS challenge works
        if ($user['role'] === 'admin') {
            setcookie('FLAG_XSS', 'BUGBOARD{stored_xss_triage_pwn}', [
                'expires'  => time() + 7200,
                'path'     => '/',
                'httponly' => false,   // Intentionally vulnerable
                'samesite' => 'Lax',
            ]);
        }
        return $user;
    }
    return null;
}
