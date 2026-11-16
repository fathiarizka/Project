CREATE DATABASE IF NOT EXISTS db_kosku;
USE db_kosku;

-- 1. Tabel Users (Admin & Penghuni)
CREATE TABLE IF NOT EXISTS users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'penghuni') NOT NULL
);

INSERT IGNORE INTO users (username, password, role) VALUES 
('admin', 'admin123', 'admin'),
('penghuni', 'penghuni123', 'penghuni');

-- 2. Tabel Kamar
CREATE TABLE IF NOT EXISTS kamar (
    id_kamar INT AUTO_INCREMENT PRIMARY KEY,
    nomor_kamar VARCHAR(10) NOT NULL UNIQUE,
    harga_sewa INT NOT NULL,
    fasilitas VARCHAR(255) NOT NULL,
    status ENUM('Kosong', 'Terisi') DEFAULT 'Kosong'
);

INSERT IGNORE INTO kamar (nomor_kamar, harga_sewa, fasilitas, status) VALUES
('A01', 600000, 'AC, WiFi', 'Terisi'),
('A02', 600000, 'AC, WiFi', 'Terisi'),
('A03', 750000, 'AC, WiFi, Kamar Mandi Dalam', 'Kosong');

-- 3. Tabel Penghuni
CREATE TABLE IF NOT EXISTS penghuni (
    id_penghuni INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20) NOT NULL,
    tgl_masuk DATE NOT NULL,
    id_kamar INT NOT NULL,
    FOREIGN KEY (id_kamar) REFERENCES kamar(id_kamar) ON DELETE CASCADE
);

INSERT IGNORE INTO penghuni (id_penghuni, nama, no_hp, tgl_masuk, id_kamar) VALUES
(1, 'Rina', '0812-1111-2222', '2026-01-01', 1),
(2, 'Siti Aminah', '0813-3333-4444', '2026-02-01', 2);

-- 4. Tabel Pembayaran
CREATE TABLE IF NOT EXISTS pembayaran (
    id_bayar INT AUTO_INCREMENT PRIMARY KEY,
    id_penghuni INT NOT NULL,
    bulan_tagihan VARCHAR(30) NOT NULL,
    jumlah_bayar INT NOT NULL,
    tgl_bayar DATE NOT NULL,
    metode VARCHAR(30) NOT NULL,
    status ENUM('Lunas', 'Belum Lunas') NOT NULL,
    UNIQUE KEY unik_pembayaran (id_penghuni, bulan_tagihan),
    FOREIGN KEY (id_penghuni) REFERENCES penghuni(id_penghuni) ON DELETE CASCADE
);