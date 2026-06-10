<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_login('admin');

$status = $_GET['status'] ?? 'all';
$params = [];
$sql = "SELECT l.*, u.nama, b.title FROM loans l JOIN users u ON u.id_user = l.id_user JOIN books b ON b.id_book = l.id_book";
if ($status !== 'all') {
    $sql .= ' WHERE l.status = :status';
    $params['status'] = $status;
}
$sql .= ' ORDER BY l.id_loan DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$loans = $stmt->fetchAll();

$summary = $pdo->query("SELECT status, COUNT(*) total FROM loans GROUP BY status")->fetchAll();
$summaryMap = ['Dipinjam' => 0, 'Dikembalikan' => 0];
foreach ($summary as $row) {
    $summaryMap[$row['status']] = (int) $row['total'];
}

render_head('Laporan Peminjaman');
?>
<section class="page admin-page active">
    <?php render_admin_sidebar('loans'); ?>
    <main class="admin-main">
        <?php render_admin_topbar('Peminjaman & Laporan'); render_flash_messages(); ?>
        <div class="stats-grid">
            <div class="stat-card yellow"><span>IN</span><p>Dipinjam</p><h2><?= $summaryMap['Dipinjam'] ?></h2></div>
            <div class="stat-card green"><span>OK</span><p>Dikembalikan</p><h2><?= $summaryMap['Dikembalikan'] ?></h2></div>
        </div>
        <section class="table-card">
            <div class="table-header">
                <div><h2>Laporan Peminjaman</h2><p>Daftar seluruh transaksi peminjaman dan pengembalian.</p></div>
            </div>
            <form class="admin-controls" method="get">
                <select name="status">
                    <option value="all">Semua Status</option>
                    <option value="Dipinjam" <?= $status === 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                    <option value="Dikembalikan" <?= $status === 'Dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
                </select>
                <button class="small-btn" type="submit">Filter</button>
            </form>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>No</th><th>Anggota</th><th>Judul Buku</th><th>Status</th><th>Tgl. Pinjam</th><th>Jatuh Tempo</th><th>Tgl. Kembali</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($loans as $index => $loan): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e($loan['nama']) ?></td>
                            <td><?= e($loan['title']) ?></td>
                            <td><span class="badge <?= e(badge_class($loan['status'])) ?>"><?= e($loan['status']) ?></span></td>
                            <td><?= e($loan['loan_date']) ?></td>
                            <td><?= e($loan['due_date']) ?></td>
                            <td><?= e($loan['return_date'] ?: '-') ?></td>
                            <td>
                                <?php if ($loan['status'] === 'Dipinjam'): ?>
                                    <form class="inline-form" action="<?= e(base_url('process/return.php')) ?>" method="post">
                                        <input type="hidden" name="id_loan" value="<?= (int) $loan['id_loan'] ?>">
                                        <button class="small-btn" type="submit">Tandai Kembali</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">Selesai</span>
                                <?php endif; ?>
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
