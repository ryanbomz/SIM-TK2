<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_login('admin');

$editUser = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf('admin/members.php');

    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int) ($_POST['id_user'] ?? 0);
        $data = [
            'nama' => trim($_POST['nama'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'role' => $_POST['role'] === 'admin' ? 'admin' : 'mahasiswa',
            'email' => trim($_POST['email'] ?? ''),
        ];
        $plainPassword = trim($_POST['password'] ?? '');

        if ($data['nama'] === '' || $data['username'] === '' || $data['email'] === '') {
            set_flash('error', 'Nama, username, dan email wajib diisi.');
            redirect_to('admin/members.php');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Format email tidak valid.');
            redirect_to('admin/members.php');
        }

        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE (username = :username OR email = :email) AND id_user <> :id_user');
        $checkStmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'id_user' => $id,
        ]);
        if ((int) $checkStmt->fetchColumn() > 0) {
            set_flash('error', 'Username atau email sudah digunakan akun lain.');
            redirect_to('admin/members.php');
        }

        if ($id > 0) {
            $data['id_user'] = $id;
            if ($plainPassword !== '') {
                $data['password'] = password_hash($plainPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET nama=:nama, username=:username, role=:role, email=:email, password=:password WHERE id_user=:id_user');
            } else {
                $stmt = $pdo->prepare('UPDATE users SET nama=:nama, username=:username, role=:role, email=:email WHERE id_user=:id_user');
            }
            $stmt->execute($data);
            set_flash('success', 'Data anggota berhasil diperbarui.');
        } else {
            $data['password'] = password_hash($plainPassword !== '' ? $plainPassword : 'mahasiswa123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (nama, username, password, role, email) VALUES (:nama, :username, :password, :role, :email)');
            $stmt->execute($data);
            set_flash('success', 'Data anggota berhasil ditambahkan.');
        }
        redirect_to('admin/members.php');
    }

    if ($action === 'delete') {
        $id = (int) ($_POST['id_user'] ?? 0);
        if ($id !== (int) current_user()['id_user']) {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id_user = :id_user');
            $stmt->execute(['id_user' => $id]);
            set_flash('success', 'Data anggota berhasil dihapus.');
        } else {
            set_flash('error', 'Akun yang sedang digunakan tidak dapat dihapus.');
        }
        redirect_to('admin/members.php');
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT id_user, nama, username, role, email FROM users WHERE id_user = :id_user');
    $stmt->execute(['id_user' => (int) $_GET['edit']]);
    $editUser = $stmt->fetch();
}

$search = trim($_GET['q'] ?? '');
$params = [];
$sql = 'SELECT id_user, nama, username, role, email FROM users';
if ($search !== '') {
    $sql .= ' WHERE nama LIKE :search OR username LIKE :search OR email LIKE :search';
    $params['search'] = '%' . $search . '%';
}
$sql .= ' ORDER BY id_user DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

render_head('Manajemen Anggota');
?>
<section class="page admin-page active">
    <?php render_admin_sidebar('members'); ?>
    <main class="admin-main">
        <?php render_admin_topbar('Data Anggota'); render_flash_messages(); ?>
        <section class="table-card mb-16">
            <div class="table-header">
                <div><h2><?= $editUser ? 'Edit Anggota' : 'Tambah Anggota' ?></h2><p>Kelola akun admin dan mahasiswa.</p></div>
            </div>
            <form method="post" class="form-grid">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id_user" value="<?= e($editUser['id_user'] ?? '') ?>">
                <div class="form-field"><label>Nama</label><input name="nama" value="<?= e($editUser['nama'] ?? '') ?>" required></div>
                <div class="form-field"><label>Username</label><input name="username" value="<?= e($editUser['username'] ?? '') ?>" required></div>
                <div class="form-field"><label>Email</label><input type="email" name="email" value="<?= e($editUser['email'] ?? '') ?>" required></div>
                <div class="form-field"><label>Role</label><select name="role"><option value="mahasiswa" <?= ($editUser['role'] ?? '') === 'mahasiswa' ? 'selected' : '' ?>>mahasiswa</option><option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>admin</option></select></div>
                <div class="form-field full"><label>Password <?= $editUser ? '(kosongkan jika tidak diganti)' : '' ?></label><input type="password" name="password" <?= $editUser ? '' : 'required' ?>></div>
                <div class="form-field full"><button class="primary-btn" type="submit">Simpan Anggota</button></div>
            </form>
        </section>
        <section class="table-card">
            <form class="admin-controls" method="get">
                <div class="search-box slim"><span>Q</span><input name="q" value="<?= e($search) ?>" placeholder="Cari nama, username, email..."></div>
                <button class="small-btn" type="submit">Cari</button>
            </form>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $index => $row): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= e($row['nama']) ?></td>
                            <td><?= e($row['username']) ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td><?= e($row['role']) ?></td>
                            <td class="status-row">
                                <a class="small-btn" href="<?= e(base_url('admin/members.php?edit=' . $row['id_user'])) ?>">Edit</a>
                                <form class="inline-form" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_user" value="<?= (int) $row['id_user'] ?>">
                                    <button class="danger-btn" data-confirm="Hapus anggota ini?" type="submit">Hapus</button>
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
