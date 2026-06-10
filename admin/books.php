<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_login('admin');

$editBook = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int) ($_POST['id_book'] ?? 0);
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'author' => trim($_POST['author'] ?? ''),
            'publisher' => trim($_POST['publisher'] ?? ''),
            'year' => (int) ($_POST['year'] ?? date('Y')),
            'category' => trim($_POST['category'] ?? ''),
            'isbn' => trim($_POST['isbn'] ?? ''),
            'total_stock' => max(0, (int) ($_POST['total_stock'] ?? 0)),
            'available_stock' => max(0, (int) ($_POST['available_stock'] ?? 0)),
            'synopsis' => trim($_POST['synopsis'] ?? ''),
        ];
        $data['available_stock'] = min($data['available_stock'], $data['total_stock']);
        $data['status'] = $data['available_stock'] > 0 ? 'Tersedia' : 'Dipinjam';

        if ($id > 0) {
            $data['id_book'] = $id;
            $stmt = $pdo->prepare('UPDATE books SET title=:title, author=:author, publisher=:publisher, year=:year, category=:category, isbn=:isbn, total_stock=:total_stock, available_stock=:available_stock, synopsis=:synopsis, status=:status WHERE id_book=:id_book');
            $stmt->execute($data);
            set_flash('success', 'Data buku berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO books (title, author, publisher, year, category, isbn, total_stock, available_stock, synopsis, status) VALUES (:title, :author, :publisher, :year, :category, :isbn, :total_stock, :available_stock, :synopsis, :status)');
            $stmt->execute($data);
            set_flash('success', 'Data buku berhasil ditambahkan.');
        }
        redirect_to('admin/books.php');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM books WHERE id_book = :id_book');
        $stmt->execute(['id_book' => (int) ($_POST['id_book'] ?? 0)]);
        set_flash('success', 'Data buku berhasil dihapus.');
        redirect_to('admin/books.php');
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id_book = :id_book');
    $stmt->execute(['id_book' => (int) $_GET['edit']]);
    $editBook = $stmt->fetch();
}

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? 'all');
$categories = $pdo->query('SELECT DISTINCT category FROM books ORDER BY category')->fetchAll();
$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(title LIKE :search OR author LIKE :search OR isbn LIKE :search)';
    $params['search'] = '%' . $search . '%';
}
if ($category !== 'all') {
    $where[] = 'category = :category';
    $params['category'] = $category;
}
$stmt = $pdo->prepare('SELECT * FROM books' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id_book DESC');
$stmt->execute($params);
$books = $stmt->fetchAll();

render_head('Manajemen Buku');
?>
<section class="page admin-page active">
    <?php render_admin_sidebar('books'); ?>
    <main class="admin-main">
        <?php render_admin_topbar('Manajemen Buku'); render_flash_messages(); ?>
        <section class="table-card mb-16">
            <div class="table-header">
                <div><h2><?= $editBook ? 'Edit Buku' : 'Tambah Buku' ?></h2><p>Kelola data buku, stok, kategori, dan status ketersediaan.</p></div>
            </div>
            <form method="post" class="form-grid">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id_book" value="<?= e($editBook['id_book'] ?? '') ?>">
                <div class="form-field"><label>Judul</label><input name="title" value="<?= e($editBook['title'] ?? '') ?>" required></div>
                <div class="form-field"><label>Penulis</label><input name="author" value="<?= e($editBook['author'] ?? '') ?>" required></div>
                <div class="form-field"><label>Penerbit</label><input name="publisher" value="<?= e($editBook['publisher'] ?? '') ?>" required></div>
                <div class="form-field"><label>Tahun</label><input type="number" name="year" value="<?= e($editBook['year'] ?? date('Y')) ?>" required></div>
                <div class="form-field"><label>Kategori</label><input name="category" value="<?= e($editBook['category'] ?? '') ?>" required></div>
                <div class="form-field"><label>ISBN</label><input name="isbn" value="<?= e($editBook['isbn'] ?? '') ?>" required></div>
                <div class="form-field"><label>Total Stok</label><input type="number" min="0" name="total_stock" value="<?= e($editBook['total_stock'] ?? 1) ?>" required></div>
                <div class="form-field"><label>Stok Tersedia</label><input type="number" min="0" name="available_stock" value="<?= e($editBook['available_stock'] ?? 1) ?>" required></div>
                <div class="form-field full"><label>Sinopsis</label><textarea name="synopsis"><?= e($editBook['synopsis'] ?? '') ?></textarea></div>
                <div class="form-field full"><button class="primary-btn" type="submit">Simpan Buku</button></div>
            </form>
        </section>
        <section class="table-card">
            <form class="admin-controls" method="get">
                <div class="search-box slim"><span>Q</span><input name="q" value="<?= e($search) ?>" placeholder="Cari judul, penulis, ISBN..."></div>
                <select name="category">
                    <option value="all">Semua Kategori</option>
                    <?php foreach ($categories as $item): ?><option <?= $category === $item['category'] ? 'selected' : '' ?>><?= e($item['category']) ?></option><?php endforeach; ?>
                </select>
                <button class="small-btn" type="submit">Filter</button>
            </form>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>No</th><th>Judul Buku</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($books as $index => $book): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e($book['title']) ?></td>
                            <td><?= e($book['author']) ?></td>
                            <td><?= e($book['category']) ?></td>
                            <td><?= (int) $book['available_stock'] ?> / <?= (int) $book['total_stock'] ?></td>
                            <td><span class="badge <?= e(badge_class($book['status'])) ?>"><?= e($book['status']) ?></span></td>
                            <td class="status-row">
                                <a class="small-btn" href="<?= e(base_url('admin/books.php?edit=' . $book['id_book'])) ?>">Edit</a>
                                <form class="inline-form" method="post">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_book" value="<?= (int) $book['id_book'] ?>">
                                    <button class="danger-btn" data-confirm="Hapus buku ini?" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</section>
</body>
</html>
