<?php
session_start();
require_once '../config/database.php';
require_once '../auth/middleware.php';
require_once '../config/security.php';

checkLogin();

// Proses tambah barang masuk
if (isset($_POST['tambah'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $barang_id = filter_var($_POST['barang_id'], FILTER_VALIDATE_INT);
    $jumlah = filter_var($_POST['jumlah'], FILTER_VALIDATE_INT);
    $tanggal = validateInput($_POST['tanggal']);
    $keterangan = validateInput($_POST['keterangan']);
    $user_id = $_SESSION['user_id'];

    if (!$barang_id || !$jumlah || $jumlah <= 0 || 
        strlen($keterangan) > 255 || !strtotime($tanggal)) {
        $error = "Input tidak valid";
    } else {
        try {
            $db->beginTransaction();

            // Insert ke tabel barang_masuk
            $stmt = $db->prepare("INSERT INTO barang_masuk (barang_id, jumlah, tanggal, keterangan, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$barang_id, $jumlah, $tanggal, $keterangan, $user_id]);

            // Update stok
            $stmt = $db->prepare("UPDATE stok SET jumlah = jumlah + ? WHERE barang_id = ?");
            $stmt->execute([$jumlah, $barang_id]);

            $db->commit();
            header("Location: barang_masuk.php");
            exit();
        } catch (Exception $e) {
            $db->rollBack();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Ambil data barang masuk
$stmt = $db->query("SELECT bm.*, b.kode_barang, b.nama_barang, u.nama_lengkap 
                    FROM barang_masuk bm 
                    JOIN barang b ON bm.barang_id = b.id 
                    JOIN users u ON bm.user_id = u.id 
                    ORDER BY bm.tanggal DESC");
$barang_masuk = $stmt->fetchAll();

// Ambil data barang untuk dropdown
$stmt = $db->query("SELECT id, kode_barang, nama_barang FROM barang ORDER BY nama_barang");
$barang = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Masuk - Sistem Inventory</title>
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
            <h2>Barang Masuk</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus"></i> Tambah Barang Masuk
            </button>
        </div>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                                <th>Petugas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($barang_masuk as $bm): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($bm['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($bm['kode_barang']); ?></td>
                                <td><?php echo htmlspecialchars($bm['nama_barang']); ?></td>
                                <td><?php echo htmlspecialchars($bm['jumlah']); ?></td>
                                <td><?php echo htmlspecialchars($bm['keterangan']); ?></td>
                                <td><?php echo htmlspecialchars($bm['nama_lengkap']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Barang Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Barang</label>
                            <select name="barang_id" class="form-select" required>
                                <option value="">Pilih Barang</option>
                                <?php foreach ($barang as $b): ?>
                                <option value="<?php echo $b['id']; ?>">
                                    <?php echo htmlspecialchars($b['kode_barang'] . ' - ' . $b['nama_barang']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah</label>
                            <input type="number" name="jumlah" class="form-control" required min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo renderPageEnd(); ?>
</body>
</html>