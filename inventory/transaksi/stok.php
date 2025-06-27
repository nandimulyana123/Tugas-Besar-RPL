<?php
session_start();
require_once '../config/database.php';
require_once '../auth/middleware.php';
require_once '../config/security.php';

checkLogin();

// Ambil data stok
$stmt = $db->query("SELECT b.kode_barang, b.nama_barang, b.satuan, s.jumlah 
                    FROM barang b 
                    JOIN stok s ON b.id = s.barang_id 
                    ORDER BY b.nama_barang");
$stok = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Barang - Sistem Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Sistem Inventory</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php 
                    require_once '../includes/menu.php';
                    echo renderMenu($menu_structure, $_SESSION['role'], '../');
                    ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Stok Barang</h2>
            <div>
                <button onclick="window.print()" class="btn btn-secondary">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Stok</th>
                                <th>Satuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stok as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['kode_barang']); ?></td>
                                <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                <td><?php echo htmlspecialchars($item['jumlah']); ?></td>
                                <td><?php echo htmlspecialchars($item['satuan']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style media="print">
        .navbar, .btn, .dropdown-toggle {
            display: none !important;
        }
        .container {
            width: 100% !important;
            max-width: none !important;
        }
    </style>
<?php echo renderPageEnd(); ?>
</body>
</html>