<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/layout.php';

if (!empty($_SESSION['user'])) {
    redirect_to($_SESSION['user']['role'] === 'admin' ? 'admin/dashboard.php' : 'user/dashboard.php');
}

render_head('Login');
$error = flash('error');
?>
<section class="page login-page active">
    <div class="login-bg-shape shape-one"></div>
    <div class="login-bg-shape shape-two"></div>
    <div class="login-wrapper">
        <div class="login-brand-panel">
            <div class="campus-photo">
                <div class="photo-overlay"></div>
                <div class="campus-lines"><span></span><span></span><span></span><span></span></div>
            </div>
            <div class="brand-floating">
                <div class="brand-logo large">
                    <div>
                        <h1>Library<span>Hub</span></h1>
                        <p>portal perpustakaan kampus</p>
                    </div>
                </div>
                <p class="brand-desc">akses katalog buku, riwayat peminjaman, dan layanan perpustakaan kampus dalam satu portal.</p>
            </div>
        </div>
        <div class="login-card">
            <div class="mobile-logo"><h2>Library<span>Hub</span></h2></div>
            <h2>Welcome back!</h2>
            <p>Masuk untuk melanjutkan ke akun Anda</p>
            <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
            <form action="<?= e(base_url('process/login.php')) ?>" method="post">
                <?= csrf_field() ?>
                <label>Username / Email</label>
                <div class="input-group">
                    <span>@</span>
                    <input type="text" name="username" placeholder="contoh: mahasiswa / admin" required>
                </div>
                <label>Password</label>
                <div class="input-group">
                    <span>*</span>
                    <input type="password" id="password" name="password" placeholder="masukkan password" required>
                    <button type="button" class="ghost-btn" data-toggle-password>Lihat</button>
                </div>
                <button type="submit" class="primary-btn full mt-16">Login</button>
            </form>
            <p class="login-note">Admin: admin/admin123. Mahasiswa: mahasiswa/mahasiswa123.</p>
        </div>
    </div>
</section>
</body>
</html>
