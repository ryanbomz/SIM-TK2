LibraryHub - PHP Native + MySQL

Cara menjalankan di XAMPP:
1. Salin folder libraryhub ke htdocs.
2. Jalankan Apache dan MySQL dari XAMPP Control Panel.
3. Buka phpMyAdmin, lalu import database/libraryhub.sql.
4. Buka http://localhost/libraryhub di browser.

Akun default:
Admin
- username: admin
- password: admin123

Mahasiswa
- username: mahasiswa
- password: mahasiswa123

Catatan:
- Konfigurasi koneksi database ada di config/database.php.
- Password seed awal akan otomatis di-hash setelah login pertama.
- Fitur yang tersedia: login, session, logout, katalog, pencarian, filter kategori, detail buku, peminjaman, pengembalian, riwayat, dashboard admin, CRUD buku, CRUD anggota, dan laporan peminjaman.
