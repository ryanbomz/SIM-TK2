<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_login('mahasiswa');

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM books WHERE id_book = :id');
$stmt->execute(['id' => $id]);
$book = $stmt->fetch();
if (!$book) {
    set_flash('error', 'Buku tidak ditemukan.');
    redirect_to('user/catalog.php');
}

$activeStmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE id_user = :id_user AND id_book = :id_book AND status = 'Dipinjam'");
$activeStmt->execute(['id_user' => current_user()['id_user'], 'id_book' => $id]);
$alreadyBorrowed = (int) $activeStmt->fetchColumn() > 0;

$favoriteStmt = $pdo->prepare('SELECT COUNT(*) FROM favorites WHERE id_user = :id_user AND id_book = :id_book');
$favoriteStmt->execute(['id_user' => current_user()['id_user'], 'id_book' => $id]);
$isFavorite = (int) $favoriteStmt->fetchColumn() > 0;

render_head('Detail Buku');
render_user_topbar('catalog');
?>
<main class="content-shell">
    <?php render_flash_messages(); ?>
    <a class="back-btn" href="<?= e(base_url('user/catalog.php')) ?>">Kembali ke Katalog</a>
    <section class="detail-card">
        <div class="detail-cover">
            <small>LibraryHub Book</small>
            <h2><?= e($book['title']) ?></h2>
            <small><?= e($book['author']) ?></small>
        </div>
        <div>
            <h1><?= e($book['title']) ?></h1>
            <div class="meta-list">
                <div class="meta-row"><strong>Penulis</strong><span><?= e($book['author']) ?></span></div>
                <div class="meta-row"><strong>Penerbit</strong><span><?= e($book['publisher']) ?></span></div>
                <div class="meta-row"><strong>Tahun Terbit</strong><span><?= e($book['year']) ?></span></div>
                <div class="meta-row"><strong>Kategori</strong><span><?= e($book['category']) ?></span></div>
                <div class="meta-row"><strong>ISBN</strong><span><?= e($book['isbn']) ?></span></div>
                <div class="meta-row"><strong>Ketersediaan</strong><span><span class="badge <?= e(badge_class($book['status'])) ?>"><?= e($book['status']) ?></span> <?= (int) $book['available_stock'] ?> / <?= (int) $book['total_stock'] ?> tersedia</span></div>
            </div>
            <h3>Sinopsis</h3>
            <p class="detail-synopsis"><?= e($book['synopsis']) ?></p>
            <div class="action-row">
                <?php if ((int) $book['available_stock'] > 0 && !$alreadyBorrowed): ?>
                    <form action="<?= e(base_url('process/borrow.php')) ?>" method="post">
                        <input type="hidden" name="id_book" value="<?= (int) $book['id_book'] ?>">
                        <button class="primary-btn" type="submit">Pinjam Buku</button>
                    </form>
                <?php elseif ($alreadyBorrowed): ?>
                    <span class="badge borrowed">Sedang Anda pinjam</span>
                <?php else: ?>
                    <span class="badge borrowed">Stok habis</span>
                <?php endif; ?>
                <form action="<?= e(base_url('process/favorite.php')) ?>" method="post">
                    <input type="hidden" name="id_book" value="<?= (int) $book['id_book'] ?>">
                    <input type="hidden" name="action" value="<?= $isFavorite ? 'remove' : 'add' ?>">
                    <button class="secondary-btn" type="submit"><?= $isFavorite ? 'Hapus dari Favorit' : 'Tambah ke Favorit' ?></button>
                </form>
            </div>
        </div>
    </section>
</main>
</body>
</html>
