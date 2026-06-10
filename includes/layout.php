<?php
require_once __DIR__ . '/helpers.php';

function render_head(string $title = 'LibraryHub'): void
{
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> - LibraryHub</title>
        <link rel="stylesheet" href="<?= e(base_url('css/style.css')) ?>">
        <script defer src="<?= e(base_url('js/main.js')) ?>"></script>
    </head>
    <body>
    <?php
}

function render_user_topbar(string $active = 'catalog'): void
{
    $user = current_user();
    ?>
    <header class="topbar">
        <a class="brand-logo small" href="<?= e(base_url('user/dashboard.php')) ?>">
            <span class="logo-icon"><img src="<?= e(base_url('assets/book-logo.png')) ?>" alt="LibraryHub"></span>
            <h3>Library<span>Hub</span></h3>
        </a>
        <nav class="nav-menu">
            <a class="nav-link <?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= e(base_url('user/dashboard.php')) ?>">Beranda</a>
            <a class="nav-link <?= $active === 'catalog' ? 'active' : '' ?>" href="<?= e(base_url('user/catalog.php')) ?>">Katalog</a>
            <a class="nav-link <?= $active === 'history' ? 'active' : '' ?>" href="<?= e(base_url('user/history.php')) ?>">Peminjaman</a>
        </nav>
        <div class="user-area">
            <div class="avatar"><?= e(strtoupper(substr($user['nama'] ?? 'M', 0, 1))) ?></div>
            <span><?= e($user['nama'] ?? 'Mahasiswa') ?></span>
            <a class="logout-btn" href="<?= e(base_url('process/logout.php')) ?>">Keluar</a>
        </div>
    </header>
    <?php
}

function render_admin_sidebar(string $active = 'dashboard'): void
{
    ?>
    <aside class="admin-sidebar">
        <a class="brand-logo admin-brand" href="<?= e(base_url('admin/dashboard.php')) ?>">
            <span class="logo-icon"><img src="<?= e(base_url('assets/book-logo.png')) ?>" alt="LibraryHub"></span>
            <div>
                <h3>Library<span>Hub</span></h3>
                <p>Admin</p>
            </div>
        </a>
        <a class="admin-menu <?= $active === 'dashboard' ? 'active' : '' ?>" href="<?= e(base_url('admin/dashboard.php')) ?>">Dashboard</a>
        <a class="admin-menu <?= $active === 'books' ? 'active' : '' ?>" href="<?= e(base_url('admin/books.php')) ?>">Buku</a>
        <a class="admin-menu <?= $active === 'members' ? 'active' : '' ?>" href="<?= e(base_url('admin/members.php')) ?>">Anggota</a>
        <a class="admin-menu <?= $active === 'loans' ? 'active' : '' ?>" href="<?= e(base_url('admin/loans.php')) ?>">Peminjaman</a>
        <a class="admin-menu logout-side" href="<?= e(base_url('process/logout.php')) ?>">Keluar</a>
    </aside>
    <?php
}

function render_admin_topbar(string $title): void
{
    $user = current_user();
    ?>
    <header class="admin-topbar">
        <div>
            <p class="eyebrow">Sistem Informasi Perpustakaan</p>
            <h1><?= e($title) ?></h1>
        </div>
        <div class="user-area admin-user">
            <div class="avatar"><?= e(strtoupper(substr($user['nama'] ?? 'A', 0, 1))) ?></div>
            <span><?= e($user['nama'] ?? 'Admin') ?></span>
        </div>
    </header>
    <?php
}

function render_flash_messages(): void
{
    foreach (['success', 'error'] as $type) {
        $message = flash($type);
        if ($message) {
            echo '<div class="alert ' . e($type) . '">' . e($message) . '</div>';
        }
    }
}
