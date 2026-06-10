<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login('mahasiswa');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('user/history.php?tab=account');
}

$idUser = (int) current_user()['id_user'];
$nama = trim($_POST['nama'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirm'] ?? '';

if ($nama === '' || $username === '' || $email === '') {
    set_flash('error', 'Nama, username, dan email wajib diisi.');
    redirect_to('user/history.php?tab=account');
}

if ($password !== '' && $password !== $passwordConfirm) {
    set_flash('error', 'Konfirmasi password tidak sama.');
    redirect_to('user/history.php?tab=account');
}

$checkStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email) AND id_user <> :id_user');
$checkStmt->execute([
    'username' => $username,
    'email' => $email,
    'id_user' => $idUser,
]);

if ((int) $checkStmt->fetchColumn() > 0) {
    set_flash('error', 'Username atau email sudah digunakan akun lain.');
    redirect_to('user/history.php?tab=account');
}

$data = [
    'nama' => $nama,
    'username' => $username,
    'email' => $email,
    'id_user' => $idUser,
];

if ($password !== '') {
    $data['password'] = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET nama = :nama, username = :username, email = :email, password = :password WHERE id_user = :id_user');
} else {
    $stmt = $pdo->prepare('UPDATE users SET nama = :nama, username = :username, email = :email WHERE id_user = :id_user');
}

$stmt->execute($data);

$_SESSION['user']['nama'] = $nama;
$_SESSION['user']['username'] = $username;
$_SESSION['user']['email'] = $email;

set_flash('success', 'Pengaturan akun berhasil disimpan.');
redirect_to('user/history.php?tab=account');
