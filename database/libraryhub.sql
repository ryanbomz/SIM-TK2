CREATE DATABASE IF NOT EXISTS libraryhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE libraryhub;

DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS loans;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id_user INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'mahasiswa') NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
  id_book INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  author VARCHAR(120) NOT NULL,
  publisher VARCHAR(120) NOT NULL,
  year YEAR NOT NULL,
  category VARCHAR(80) NOT NULL,
  isbn VARCHAR(40) NOT NULL UNIQUE,
  total_stock INT NOT NULL DEFAULT 0,
  available_stock INT NOT NULL DEFAULT 0,
  synopsis TEXT,
  status ENUM('Tersedia', 'Dipinjam') NOT NULL DEFAULT 'Tersedia',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE loans (
  id_loan INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  id_book INT NOT NULL,
  loan_date DATE NOT NULL,
  due_date DATE NOT NULL,
  return_date DATE NULL,
  status ENUM('Dipinjam', 'Dikembalikan') NOT NULL DEFAULT 'Dipinjam',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_loans_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
  CONSTRAINT fk_loans_book FOREIGN KEY (id_book) REFERENCES books(id_book) ON DELETE CASCADE
);

CREATE TABLE favorites (
  id_favorite INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT NOT NULL,
  id_book INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_favorite (id_user, id_book),
  CONSTRAINT fk_favorites_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
  CONSTRAINT fk_favorites_book FOREIGN KEY (id_book) REFERENCES books(id_book) ON DELETE CASCADE
);

INSERT INTO users (nama, username, password, role, email) VALUES
('Admin Perpustakaan', 'admin', '$2y$10$uDbpCFVXSWFMwX/3LCZsJujeQGDy7KbvgF03E4MkHRbjhHYEec4bK', 'admin', 'admin@libraryhub.ac.id'),
('Andi Mahasiswa', 'mahasiswa', '$2y$10$aIIgoRzwzT4OGJP3J3gcAO4TCKJUjPmdb8NYyZ2oVx7J4U1ezPhYO', 'mahasiswa', 'andi@kampus.ac.id'),
('Siti Nurhaliza', 'siti', '$2y$10$aIIgoRzwzT4OGJP3J3gcAO4TCKJUjPmdb8NYyZ2oVx7J4U1ezPhYO', 'mahasiswa', 'siti@kampus.ac.id');

INSERT INTO books (title, author, publisher, year, category, isbn, total_stock, available_stock, synopsis, status) VALUES
('Algoritma & Pemrograman', 'Rinaldi Munir', 'Informatika', 2019, 'Ilmu Komputer', '978-602-6232-00-1', 5, 3, 'Buku ini membahas dasar-dasar algoritma dan pemrograman secara sistematis, mulai dari konsep dasar, struktur data, teknik algoritma, hingga implementasi menggunakan berbagai bahasa pemrograman.', 'Tersedia'),
('Manajemen Pemasaran', 'F. Tjiptono', 'Andi Publisher', 2020, 'Manajemen', '978-602-1122-10-8', 4, 2, 'Buku referensi tentang strategi pemasaran, perilaku konsumen, segmentasi pasar, dan pengelolaan nilai pelanggan dalam organisasi modern.', 'Tersedia'),
('Pengantar Ekonomi Mikro', 'S. Mankiw', 'Salemba Empat', 2018, 'Ekonomi', '978-602-4232-12-4', 3, 0, 'Buku pengantar ekonomi mikro yang membahas permintaan, penawaran, pasar, perilaku konsumen, dan teori produksi.', 'Dipinjam'),
('Sistem Basis Data', 'A. Kadir', 'Informatika', 2021, 'Ilmu Komputer', '978-602-8822-51-5', 4, 1, 'Buku ini membahas konsep basis data, model relasional, SQL, normalisasi, dan perancangan database untuk sistem informasi.', 'Tersedia'),
('Hukum Perdata', 'Subekti', 'Intermasa', 2017, 'Hukum', '978-979-414-001-5', 2, 2, 'Referensi dasar hukum perdata Indonesia yang membahas hubungan hukum antarindividu, perikatan, dan perjanjian.', 'Tersedia'),
('Psikologi Pendidikan', 'S. N. Hadi', 'Remaja Rosdakarya', 2022, 'Psikologi', '978-602-446-921-1', 4, 4, 'Buku psikologi pendidikan yang membahas perkembangan peserta didik, motivasi belajar, dan strategi pembelajaran.', 'Tersedia'),
('Bahasa Inggris Akademik', 'J. Eastwood', 'Oxford Press', 2020, 'Bahasa', '978-019-442-092-2', 5, 5, 'Buku pendukung kemampuan bahasa Inggris akademik untuk membaca referensi, menulis esai, dan presentasi.', 'Tersedia'),
('Statistika Untuk Bisnis', 'A. Riduwan', 'Alfabeta', 2019, 'Ekonomi', '978-602-9328-42-1', 2, 0, 'Buku statistika terapan yang membahas pengolahan data, penyajian data, dan analisis sederhana untuk kebutuhan bisnis.', 'Dipinjam'),
('Pemrograman Web Dinamis', 'Budi Raharjo', 'Informatika', 2021, 'Ilmu Komputer', '978-602-1514-88-5', 6, 6, 'Panduan membangun aplikasi web menggunakan HTML, CSS, JavaScript, PHP, dan database secara terstruktur.', 'Tersedia'),
('Akuntansi Dasar', 'Hery', 'Grasindo', 2018, 'Ekonomi', '978-602-375-944-5', 3, 3, 'Buku pengantar akuntansi untuk memahami siklus akuntansi, jurnal, buku besar, dan laporan keuangan.', 'Tersedia');

INSERT INTO loans (id_user, id_book, loan_date, due_date, return_date, status) VALUES
(2, 1, '2024-04-10', '2024-04-24', '2024-04-22', 'Dikembalikan'),
(2, 3, '2024-03-06', '2024-03-20', NULL, 'Dipinjam'),
(2, 4, '2024-03-18', '2024-04-01', '2024-03-30', 'Dikembalikan'),
(2, 2, '2024-05-12', '2024-05-26', NULL, 'Dipinjam'),
(3, 8, '2024-05-20', '2024-06-03', NULL, 'Dipinjam');
