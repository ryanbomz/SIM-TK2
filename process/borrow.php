<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login('mahasiswa');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('user/catalog.php');
}

$idBook = (int) ($_POST['id_book'] ?? 0);
$idUser = (int) current_user()['id_user'];

try {
    $pdo->beginTransaction();
    $bookStmt = $pdo->prepare('SELECT * FROM books WHERE id_book = :id_book FOR UPDATE');
    $bookStmt->execute(['id_book' => $idBook]);
    $book = $bookStmt->fetch();

    if (!$book || (int) $book['available_stock'] < 1) {
        throw new RuntimeException('Buku tidak tersedia untuk dipinjam.');
    }

    $activeStmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE id_user = :id_user AND id_book = :id_book AND status = 'Dipinjam'");
    $activeStmt->execute(['id_user' => $idUser, 'id_book' => $idBook]);
    if ((int) $activeStmt->fetchColumn() > 0) {
        throw new RuntimeException('Anda masih memiliki peminjaman aktif untuk buku ini.');
    }

    $loanStmt = $pdo->prepare("INSERT INTO loans (id_user, id_book, loan_date, due_date, status) VALUES (:id_user, :id_book, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Dipinjam')");
    $loanStmt->execute(['id_user' => $idUser, 'id_book' => $idBook]);

    $newStock = (int) $book['available_stock'] - 1;
    $newStatus = $newStock > 0 ? 'Tersedia' : 'Dipinjam';
    $updateStmt = $pdo->prepare('UPDATE books SET available_stock = :stock, status = :status WHERE id_book = :id_book');
    $updateStmt->execute(['stock' => $newStock, 'status' => $newStatus, 'id_book' => $idBook]);

    $pdo->commit();
    set_flash('success', 'Buku berhasil dipinjam.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', $e->getMessage());
}

redirect_to('user/history.php');
