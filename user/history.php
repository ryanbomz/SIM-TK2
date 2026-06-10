<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_login('mahasiswa');

$stmt = $pdo->prepare("SELECT l.id_loan, l.loan_date, l.due_date, l.return_date, l.status, b.title FROM loans l JOIN books b ON b.id_book = l.id_book WHERE l.id_user = :id_user ORDER BY l.id_loan DESC");
$stmt->execute(['id_user' => current_user()['id_user']]);
$loans = $stmt->fetchAll();

$favoriteStmt = $pdo->prepare("SELECT f.id_favorite, b.id_book, b.title, b.author, b.category, b.total_stock, b.available_stock FROM favorites f JOIN books b ON b.id_book = f.id_book WHERE f.id_user = :id_user ORDER BY f.id_favorite DESC");
$favoriteStmt->execute(['id_user' => current_user()['id_user']]);
$favorites = $favoriteStmt->fetchAll();

$activeTab = $_GET['tab'] ?? 'history';
if (!in_array($activeTab, ['history', 'favorites', 'account'], true)) {
    $activeTab = 'history';
}

render_head('Riwayat Peminjaman');
render_user_topbar('history');
?>
<main class="content-shell">
    <?php render_flash_messages(); ?>
    <div class="profile-layout">
        <aside class="profile-sidebar">
            <div class="avatar big"><?= e(strtoupper(substr(current_user()['nama'], 0, 1))) ?></div>
            <h3><?= e(current_user()['nama']) ?></h3>
            <p><?= e(current_user()['email']) ?></p>
            <div class="side-menu light">
                <a class="<?= $activeTab === 'history' ? 'active' : '' ?>" href="<?= e(base_url('user/history.php?tab=history')) ?>">Riwayat Peminjaman</a>
                <a class="<?= $activeTab === 'favorites' ? 'active' : '' ?>" href="<?= e(base_url('user/history.php?tab=favorites')) ?>">Favorit</a>
                <a class="<?= $activeTab === 'account' ? 'active' : '' ?>" href="<?= e(base_url('user/history.php?tab=account')) ?>">Pengaturan Akun</a>
            </div>
        </aside>
        <?php if ($activeTab === 'history'): ?>
        <section class="table-card flex-fill">
            <div class="section-title compact">
                <h2>Riwayat Peminjaman</h2>
                <p>Daftar transaksi peminjaman buku pengguna</p>
            </div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>No</th><th>Judul Buku</th><th>Status</th><th>Tgl. Pinjam</th><th>Jatuh Tempo</th><th>Tgl. Kembali</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($loans as $index => $loan): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e($loan['title']) ?></td>
                            <td><span class="badge <?= e(badge_class($loan['status'])) ?>"><?= e($loan['status']) ?></span></td>
                            <td><?= e($loan['loan_date']) ?></td>
                            <td><?= e($loan['due_date']) ?></td>
                            <td><?= e($loan['return_date'] ?: '-') ?></td>
                            <td>
                                <?php if ($loan['status'] === 'Dipinjam'): ?>
                                    <form class="inline-form" action="<?= e(base_url('process/return.php')) ?>" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id_loan" value="<?= (int) $loan['id_loan'] ?>">
                                        <button class="small-btn" type="submit">Kembalikan</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$loans): ?><tr><td colspan="7">Belum ada riwayat peminjaman.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php elseif ($activeTab === 'favorites'): ?>
        <section class="table-card flex-fill">
            <div class="section-title compact">
                <h2>Buku Favorit</h2>
                <p>Koleksi buku yang Anda tandai sebagai favorit</p>
            </div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>No</th><th>Judul Buku</th><th>Penulis</th><th>Kategori</th><th>Stok</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($favorites as $index => $book): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e($book['title']) ?></td>
                            <td><?= e($book['author']) ?></td>
                            <td><?= e($book['category']) ?></td>
                            <td><?= (int) $book['available_stock'] ?> / <?= (int) $book['total_stock'] ?></td>
                            <td class="status-row">
                                <a class="small-btn" href="<?= e(base_url('user/detail.php?id=' . $book['id_book'])) ?>">Detail</a>
                                <form class="inline-form" action="<?= e(base_url('process/favorite.php')) ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id_book" value="<?= (int) $book['id_book'] ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button class="danger-btn" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$favorites): ?><tr><td colspan="6">Belum ada buku favorit.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php else: ?>
        <section class="table-card flex-fill">
            <div class="section-title compact">
                <h2>Pengaturan Akun</h2>
                <p>Perbarui informasi akun mahasiswa</p>
            </div>
            <form class="form-grid mt-16" action="<?= e(base_url('process/account.php')) ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-field"><label>Nama</label><input name="nama" value="<?= e(current_user()['nama']) ?>" required></div>
                <div class="form-field"><label>Username</label><input name="username" value="<?= e(current_user()['username']) ?>" required></div>
                <div class="form-field full"><label>Email</label><input type="email" name="email" value="<?= e(current_user()['email']) ?>" required></div>
                <div class="form-field"><label>Password Baru</label><input type="password" name="password" placeholder="Kosongkan jika tidak diganti"></div>
                <div class="form-field"><label>Konfirmasi Password</label><input type="password" name="password_confirm" placeholder="Ulangi password baru"></div>
                <div class="form-field full"><button class="primary-btn" type="submit">Simpan Pengaturan</button></div>
            </form>
        </section>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
