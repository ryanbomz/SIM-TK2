<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_login('mahasiswa');

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? 'all');
$categories = $pdo->query('SELECT DISTINCT category FROM books ORDER BY category')->fetchAll();

$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(title LIKE :search OR author LIKE :search OR category LIKE :search OR isbn LIKE :search)';
    $params['search'] = '%' . $search . '%';
}
if ($category !== '' && $category !== 'all') {
    $where[] = 'category = :category';
    $params['category'] = $category;
}
$sql = 'SELECT * FROM books' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY title';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();

render_head('Katalog Buku');
render_user_topbar('catalog');
?>
<main class="content-shell">
    <?php render_flash_messages(); ?>
    <section class="hero-card">
        <div>
            <p class="eyebrow">Portal Perpustakaan Kampus</p>
            <h1>Katalog Buku</h1>
            <p>Cari koleksi buku, cek stok, lalu lakukan peminjaman jika buku masih tersedia.</p>
        </div>
        <div class="hero-illustration">BK</div>
    </section>
    <form class="search-panel" method="get">
        <div class="search-box">
            <span>Q</span>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari buku, penulis, kategori, atau ISBN...">
        </div>
        <div class="chips">
            <button class="chip <?= $category === 'all' ? 'active' : '' ?>" name="category" value="all">Semua</button>
            <?php foreach ($categories as $item): ?>
                <button class="chip <?= $category === $item['category'] ? 'active' : '' ?>" name="category" value="<?= e($item['category']) ?>"><?= e($item['category']) ?></button>
            <?php endforeach; ?>
        </div>
    </form>
    <div class="section-title">
        <h2>Katalog Buku</h2>
        <p><?= count($books) ?> buku ditemukan</p>
    </div>
    <div class="book-grid">
        <?php foreach ($books as $book): ?>
            <article class="book-card">
                <div class="book-cover">
                    <small>LibraryHub Collection</small>
                    <h3><?= e($book['title']) ?></h3>
                    <small><?= e($book['category']) ?></small>
                </div>
                <div class="book-info">
                    <h3><?= e($book['title']) ?></h3>
                    <p><?= e($book['author']) ?></p>
                    <div class="book-actions">
                        <span class="badge <?= e(badge_class($book['status'])) ?>"><?= e($book['status']) ?></span>
                        <a class="link-btn" href="<?= e(base_url('user/detail.php?id=' . $book['id_book'])) ?>">Detail</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
