CREATE DATABASE db_inventory;
USE db_inventory;

-- Table users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'operator') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table barang
CREATE TABLE barang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    kode_barang VARCHAR(20) NOT NULL UNIQUE,
    nama_barang VARCHAR(100) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table stok
CREATE TABLE stok (
    id INT PRIMARY KEY AUTO_INCREMENT,
    barang_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (barang_id) REFERENCES barang(id)
);

-- Table barang_masuk
CREATE TABLE barang_masuk (
    id INT PRIMARY KEY AUTO_INCREMENT,
    barang_id INT NOT NULL,
    jumlah INT NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (barang_id) REFERENCES barang(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table barang_keluar
CREATE TABLE barang_keluar (
    id INT PRIMARY KEY AUTO_INCREMENT,
    barang_id INT NOT NULL,
    jumlah INT NOT NULL,
    tanggal DATE NOT NULL,
    keterangan TEXT,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (barang_id) REFERENCES barang(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert pengguna
INSERT INTO users (username, password, nama_lengkap, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');
INSERT INTO users (username, password, nama_lengkap, role) 
VALUES ('operator', '$2y$10$K2bviK/f.ZSEKHKTU5/19OADqWZyF1W6WFP2bjFlWGznOqjh8zUWG', 'Operator', 'operator');