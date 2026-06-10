<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login('mahasiswa');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('user/catalog.php');
}

verify_csrf('user/history.php?tab=favorites');

$idBook = (int) ($_POST['id_book'] ?? 0);
$action = $_POST['action'] ?? 'add';
$idUser = (int) current_user()['id_user'];

if ($idBook < 1) {
    set_flash('error', 'Data buku tidak valid.');
    redirect_to('user/history.php?tab=favorites');
}

if ($action === 'remove') {
    $stmt = $pdo->prepare('DELETE FROM favorites WHERE id_user = :id_user AND id_book = :id_book');
    $stmt->execute(['id_user' => $idUser, 'id_book' => $idBook]);
    set_flash('success', 'Buku dihapus dari favorit.');
    redirect_to('user/history.php?tab=favorites');
}

$stmt = $pdo->prepare('INSERT IGNORE INTO favorites (id_user, id_book) VALUES (:id_user, :id_book)');
$stmt->execute(['id_user' => $idUser, 'id_book' => $idBook]);
set_flash('success', 'Buku berhasil ditambahkan ke favorit.');
redirect_to('user/history.php?tab=favorites');
