# Sistem Inventory 

Sistem Inventory untuk pengelolaan stok barang. Sistem ini memungkinkan pengguna untuk melacak stok barang, mencatat barang masuk dan keluar, serta mengelola data master.

## Fitur

### Dashboard
- Tampilan total barang, barang masuk, dan barang keluar
- Grafik tren barang masuk & keluar (12 bulan terakhir)
- Grafik distribusi stok barang (top 5)

### Master Data
- Manajemen data barang (CRUD)
- Manajemen pengguna (CRUD)

### Transaksi
- Pencatatan barang masuk
- Pencatatan barang keluar
- Monitoring stok realtime

### Keamanan
- Autentikasi pengguna
- Role-based access control (Admin & Operator)
- Proteksi CSRF
- Validasi input
- Session management

## Teknologi

- PHP 8.3+
- MySQL/MariaDB
- Bootstrap 5.3
- Bootstrap Icons
- Chart.js

## Persyaratan Sistem

- Web Server (Apache/Nginx)
- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Browser modern yang mendukung JavaScript

## Instalasi

1. Clone atau download repository ini
2. Buat database baru dengan nama `db_inventory`
3. Import file `database/db_inventory.sql` ke database yang telah dibuat
4. Sesuaikan konfigurasi database di `config/database.php`
5. Akses aplikasi melalui web browser

## Struktur Direktori

```
|── auth/               # Autentikasi dan middleware
├── config/             # Konfigurasi database dan keamanan
├── database/           # File SQL database
├── includes/           # File pendukung (CSS, menu)
├── master/             # Modul master data
├── transaksi/          # Modul transaksi
└── index.php           # Halaman dashboard
```

## Tentang Developer

Halo! Saya adalah seorang developer yang suka membagikan source code, tips, dan tutorial seputar pemrograman. Saat ini saya aktif mengunggah project open source di GitHub dan akan segera berbagi konten edukasi di YouTube dan media sosial – mulai dari tutorial coding, review tools developer, sampai tips karier di dunia IT.

Jika Anda merasa terbantu dengan project ini, Anda dapat memberikan dukungan melalui:
- GitHub: <a href="https://github.com/angganurbayu">@angganurbayu</a>
- Trakteer: <a href="https://trakteer.id/angganurbayu/tip">@angganurbayu</a>

## Copyright
Copyright © 2025. <a href="https://github.com/angganurbayu">Bayu Nur Angga</a>.
