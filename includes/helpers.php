<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function base_url(string $path = ''): string
{
    $root = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
    if (strpos($root, '/admin') !== false || strpos($root, '/user') !== false || strpos($root, '/process') !== false) {
        $root = dirname($root);
    }
    return $root . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(string $fallback = 'index.php'): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        set_flash('error', 'Sesi form tidak valid. Silakan coba lagi.');
        redirect_to($fallback);
    }
}

function redirect_to(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

function require_login(?string $role = null): void
{
    if (empty($_SESSION['user'])) {
        redirect_to('index.php');
    }

    if ($role !== null && $_SESSION['user']['role'] !== $role) {
        redirect_to($_SESSION['user']['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
    }
}

function current_user(): array
{
    return $_SESSION['user'] ?? [];
}

function badge_class(string $status): string
{
    return in_array($status, ['Tersedia', 'Dikembalikan'], true) ? 'available' : 'borrowed';
}

function flash(?string $key = null)
{
    if ($key === null) {
        return $_SESSION['flash'] ?? [];
    }

    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function set_flash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}
