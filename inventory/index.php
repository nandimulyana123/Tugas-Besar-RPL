<?php
session_start();
require_once 'config/database.php';
require_once 'auth/middleware.php';
require_once __DIR__ . '/config/security.php';

checkLogin();

// Dashboard statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM barang");
$total_barang = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM barang_masuk");
$total_barang_masuk = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM barang_keluar");
$total_barang_keluar = $stmt->fetch()['total'];

// Data untuk grafik barang masuk & keluar per bulan (12 bulan terakhir)
$query = "SELECT 
    DATE_FORMAT(tanggal, '%Y-%m') as bulan,
    COUNT(*) as total
    FROM barang_masuk
    WHERE tanggal >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
    ORDER BY bulan";
$stmt = $db->query($query);
$data_barang_masuk = $stmt->fetchAll();

$query = "SELECT 
    DATE_FORMAT(tanggal, '%Y-%m') as bulan,
    COUNT(*) as total
    FROM barang_keluar
    WHERE tanggal >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(tanggal, '%Y-%m')
    ORDER BY bulan";
$stmt = $db->query($query);
$data_barang_keluar = $stmt->fetchAll();

// Data untuk grafik distribusi stok
$query = "SELECT b.nama_barang, s.jumlah 
    FROM barang b 
    JOIN stok s ON b.id = s.barang_id 
    ORDER BY s.jumlah DESC 
    LIMIT 5";
$stmt = $db->query($query);
$data_stok = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-card {
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            margin: auto;
            height: 300px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Sistem Inventory</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php 
                    require_once 'includes/menu.php';
                    echo renderMenu($menu_structure, $_SESSION['role']);
                    ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Dashboard</h2>
        <div class="row">
        <!-- Card Total Barang -->
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card bg-primary text-white h-100">
                <div class="card-body d-flex">
                    <div class="d-flex flex-column justify-content-between flex-grow-1">
                        <h5 class="card-title mb-0">Total Barang</h5>
                        <h2 class="card-text"><?php echo $total_barang; ?></h2>
                        <a href="master/barang.php" class="text-white">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <i class="bi bi-box fs-1 ms-3 align-self-center"></i>
                </div>
            </div>
        </div>

        <!-- Card Barang Masuk -->
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card bg-success text-white h-100">
                <div class="card-body d-flex">
                    <div class="d-flex flex-column justify-content-between flex-grow-1">
                        <h5 class="card-title mb-0">Barang Masuk</h5>
                        <h2 class="card-text"><?php echo $total_barang_masuk; ?></h2>
                        <a href="transaksi/barang_masuk.php" class="text-white">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <i class="bi bi-box-arrow-in-down fs-1 ms-3 align-self-center"></i>
                </div>
            </div>
        </div>

        <!-- Card Barang Keluar -->
        <div class="col-md-4 mb-4">
            <div class="card dashboard-card bg-danger text-white h-100">
                <div class="card-body d-flex">
                    <div class="d-flex flex-column justify-content-between flex-grow-1">
                        <h5 class="card-title mb-0">Barang Keluar</h5>
                        <h2 class="card-text"><?php echo $total_barang_keluar; ?></h2>
                        <a href="transaksi/barang_keluar.php" class="text-white">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <i class="bi bi-box-arrow-up-right fs-1 ms-3 align-self-center"></i>
                </div>
            </div>
        </div>
    </div>

        <div class="row">
            <div class="col-md-8 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-center">Tren Barang Masuk & Keluar (12 Bulan Terakhir)</h5>
                        <div class="chart-container">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title text-center">Distribusi Stok Barang (Top 5)</h5>
                        <div class="chart-container">
                            <canvas id="stockChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Data untuk grafik tren
        const trendData = {
            labels: <?php echo json_encode(array_column($data_barang_masuk, 'bulan')); ?>,
            datasets: [{
                label: 'Barang Masuk',
                data: <?php echo json_encode(array_column($data_barang_masuk, 'total')); ?>,
                borderColor: 'rgb(40, 167, 69)',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.4
            },
            {
                label: 'Barang Keluar',
                data: <?php echo json_encode(array_column($data_barang_keluar, 'total')); ?>,
                borderColor: 'rgb(220, 53, 69)',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                fill: true,
                tension: 0.4
            }]
        };

        // Data untuk grafik stok
        const stockData = {
            labels: <?php echo json_encode(array_column($data_stok, 'nama_barang')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($data_stok, 'jumlah')); ?>,
                backgroundColor: [
                    '#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8'
                ]
            }]
        };

        // Inisialisasi grafik tren
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: trendData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: false
                    },
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        // Inisialisasi grafik stok
        new Chart(document.getElementById('stockChart'), {
            type: 'pie',
            data: stockData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12
                        }
                    }
                }
            }
        });
    </script>
<?php echo renderPageEnd(); ?>
</body>
</html>