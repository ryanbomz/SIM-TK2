<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('index.php');
}

verify_csrf('index.php');

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare('SELECT id_user, nama, username, password, role, email FROM users WHERE username = :username OR email = :email LIMIT 1');
$stmt->execute([
    'username' => $username,
    'email' => $username,
]);
$user = $stmt->fetch();

$valid = false;
if ($user) {
    $info = password_get_info($user['password']);
    $valid = $info['algo'] ? password_verify($password, $user['password']) : hash_equals($user['password'], $password);
}

if (!$user || !$valid) {
    set_flash('error', 'Username/email atau password salah.');
    redirect_to('index.php');
}

if (!password_get_info($user['password'])['algo']) {
    $newHash = password_hash($password, PASSWORD_DEFAULT);
    $update = $pdo->prepare('UPDATE users SET password = :password WHERE id_user = :id_user');
    $update->execute(['password' => $newHash, 'id_user' => $user['id_user']]);
}

session_regenerate_id(true);
$_SESSION['user'] = [
    'id_user' => $user['id_user'],
    'nama' => $user['nama'],
    'username' => $user['username'],
    'role' => $user['role'],
    'email' => $user['email'],
];

redirect_to($user['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
