CREATE DATABASE IF NOT EXISTS tas_rajut;

USE tas_rajut;

-- 1. Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    PASSWORD VARCHAR(255) NOT NULL,
    ROLE ENUM('admin', 'user') NOT NULL DEFAULT 'user'
) ENGINE=INNODB;

-- 2. Tabel Produk
CREATE TABLE IF NOT EXISTS produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    harga_satuan INT NOT NULL DEFAULT 0,
    pembuat VARCHAR(100) NOT NULL,
    gambar VARCHAR(255) NULL
) ENGINE=INNODB;

-- Password bawaan admin: admin123 (Sudah di-hash dengan PASSWORD_DEFAULT)
INSERT INTO users (username, PASSWORD, ROLE) VALUES 
('admin', '$2y$10$mRz7b4W7DMcT3k5hC2A7K.8rM2fWpG47kHbe7QhE2qj32LwZ5bS6e', 'admin');


CREATE TABLE transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    nota_trx VARCHAR(50) NOT NULL,
    id_user INT NOT NULL, -- Tambahkan kolom ini agar tahu siapa yang beli
    id_produk INT NOT NULL,
    nama_penerima VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    alamat TEXT NOT NULL,
    ekspedisi VARCHAR(50) NOT NULL,
    waktu_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    STATUS VARCHAR(30) NOT NULL DEFAULT 'Menunggu',
    FOREIGN KEY (id_produk) REFERENCES produk(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id) ON DELETE CASCADE -- Relasikan ke tabel users
);