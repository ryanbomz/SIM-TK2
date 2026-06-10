<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_login('admin');

$stats = [
    'books' => (int) $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn(),
    'members' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'mahasiswa'")->fetchColumn(),
    'active' => (int) $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'Dipinjam'")->fetchColumn(),
    'returned' => (int) $pdo->query("SELECT COUNT(*) FROM loans WHERE status = 'Dikembalikan'")->fetchColumn(),
];
$activities = $pdo->query("SELECT l.*, u.nama, b.title FROM loans l JOIN users u ON u.id_user = l.id_user JOIN books b ON b.id_book = l.id_book ORDER BY l.id_loan DESC LIMIT 6")->fetchAll();

render_head('Dashboard Admin');
?>
<section class="page admin-page active">
    <?php render_admin_sidebar('dashboard'); ?>
    <main class="admin-main">
        <?php render_admin_topbar('Dashboard'); render_flash_messages(); ?>
        <div class="stats-grid">
            <div class="stat-card blue"><span>BK</span><p>Total Buku</p><h2><?= $stats['books'] ?></h2></div>
            <div class="stat-card green"><span>AG</span><p>Total Anggota</p><h2><?= $stats['members'] ?></h2></div>
            <div class="stat-card yellow"><span>IN</span><p>Peminjaman Aktif</p><h2><?= $stats['active'] ?></h2></div>
            <div class="stat-card purple"><span>OK</span><p>Buku Dikembalikan</p><h2><?= $stats['returned'] ?></h2></div>
        </div>
        <section class="table-card">
            <div class="section-title compact">
                <h2>Aktivitas Terbaru</h2>
                <p>Ringkasan transaksi perpustakaan</p>
            </div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Tanggal</th><th>Aktivitas</th><th>Detail</th><th>Oleh</th></tr></thead>
                    <tbody>
                    <?php foreach ($activities as $row): ?>
                        <tr>
                            <td><?= e($row['return_date'] ?: $row['loan_date']) ?></td>
                            <td><?= e($row['status'] === 'Dikembalikan' ? 'Pengembalian Buku' : 'Peminjaman Buku') ?></td>
                            <td><?= e($row['title']) ?></td>
                            <td><?= e($row['nama']) ?></td>
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
