<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_login('mahasiswa');

$totalBooks = (int) $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
$available = (int) $pdo->query("SELECT COUNT(*) FROM books WHERE status = 'Tersedia'")->fetchColumn();
$activeLoansStmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE id_user = :id_user AND status = 'Dipinjam'");
$activeLoansStmt->execute(['id_user' => current_user()['id_user']]);
$activeLoans = (int) $activeLoansStmt->fetchColumn();
$latestStmt = $pdo->prepare("SELECT l.*, b.title FROM loans l JOIN books b ON b.id_book = l.id_book WHERE l.id_user = :id_user ORDER BY l.id_loan DESC LIMIT 5");
$latestStmt->execute(['id_user' => current_user()['id_user']]);
$latestLoans = $latestStmt->fetchAll();

render_head('Dashboard Mahasiswa');
render_user_topbar('dashboard');
?>
<main class="content-shell">
    <?php render_flash_messages(); ?>
    <section class="hero-card">
        <div>
            <p class="eyebrow">Portal Perpustakaan Kampus</p>
            <h1>Temukan buku yang kamu butuhkan</h1>
            <p>Cari koleksi buku, cek ketersediaan, dan pantau riwayat peminjaman secara lebih cepat.</p>
        </div>
  
    </section>
    <div class="stats-grid">
        <div class="stat-card blue"><span>BK</span><p>Total Buku</p><h2><?= $totalBooks ?></h2></div>
        <div class="stat-card green"><span>OK</span><p>Judul Tersedia</p><h2><?= $available ?></h2></div>
        <div class="stat-card yellow"><span>IN</span><p>Peminjaman Aktif</p><h2><?= $activeLoans ?></h2></div>
        <div class="stat-card purple"><span>GO</span><p>Akses Katalog</p><h2><a class="link-btn" href="<?= e(base_url('user/catalog.php')) ?>">Buka</a></h2></div>
    </div>
    <section class="table-card">
        <div class="section-title compact">
            <h2>Riwayat Terbaru</h2>
            <p>Transaksi peminjaman terakhir Anda</p>
        </div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Judul Buku</th><th>Status</th><th>Tgl. Pinjam</th><th>Jatuh Tempo</th><th>Tgl. Kembali</th></tr></thead>
                <tbody>
                <?php foreach ($latestLoans as $loan): ?>
                    <tr>
                        <td><?= e($loan['title']) ?></td>
                        <td><span class="badge <?= e(badge_class($loan['status'])) ?>"><?= e($loan['status']) ?></span></td>
                        <td><?= e($loan['loan_date']) ?></td>
                        <td><?= e($loan['due_date']) ?></td>
                        <td><?= e($loan['return_date'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$latestLoans): ?><tr><td colspan="5">Belum ada riwayat peminjaman.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
