<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('user/history.php');
}

verify_csrf(current_user()['role'] === 'admin' ? 'admin/loans.php' : 'user/history.php');

$idLoan = (int) ($_POST['id_loan'] ?? 0);
$user = current_user();

try {
    $pdo->beginTransaction();
    $sql = "SELECT l.id_loan, l.id_user, l.id_book, l.status, b.available_stock, b.total_stock FROM loans l JOIN books b ON b.id_book = l.id_book WHERE l.id_loan = :id_loan";
    if ($user['role'] !== 'admin') {
        $sql .= ' AND l.id_user = :id_user';
    }
    $sql .= ' FOR UPDATE';
    $stmt = $pdo->prepare($sql);
    $params = ['id_loan' => $idLoan];
    if ($user['role'] !== 'admin') {
        $params['id_user'] = $user['id_user'];
    }
    $stmt->execute($params);
    $loan = $stmt->fetch();

    if (!$loan || $loan['status'] !== 'Dipinjam') {
        throw new RuntimeException('Data peminjaman tidak valid atau sudah dikembalikan.');
    }

    $returnStmt = $pdo->prepare("UPDATE loans SET return_date = CURDATE(), status = 'Dikembalikan' WHERE id_loan = :id_loan");
    $returnStmt->execute(['id_loan' => $idLoan]);

    $newStock = min((int) $loan['available_stock'] + 1, (int) $loan['total_stock']);
    $bookStmt = $pdo->prepare("UPDATE books SET available_stock = :stock, status = 'Tersedia' WHERE id_book = :id_book");
    $bookStmt->execute(['stock' => $newStock, 'id_book' => $loan['id_book']]);

    $pdo->commit();
    set_flash('success', 'Buku berhasil dikembalikan.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', $e->getMessage());
}

redirect_to($user['role'] === 'admin' ? 'admin/loans.php' : 'user/history.php');
