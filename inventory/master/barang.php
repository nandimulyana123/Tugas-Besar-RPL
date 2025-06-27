<?php
session_start();
require_once '../config/database.php';
require_once '../auth/middleware.php';
require_once '../config/security.php';

checkLogin();
checkAdmin();

// Proses tambah barang
if (isset($_POST['tambah'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $kode_barang = validateInput($_POST['kode_barang']);
    $nama_barang = validateInput($_POST['nama_barang']);
    $satuan = validateInput($_POST['satuan']);
    $deskripsi = validateInput($_POST['deskripsi']);

    if (strlen($kode_barang) > 20 || strlen($nama_barang) > 100 || 
        strlen($satuan) > 20 || strlen($deskripsi) > 255) {
        $error = "Input melebihi batas maksimal";
    } else {
        $stmt = $db->prepare("INSERT INTO barang (kode_barang, nama_barang, satuan, deskripsi) VALUES (?, ?, ?, ?)");
        $stmt->execute([$kode_barang, $nama_barang, $satuan, $deskripsi]);
    
        // Inisialisasi stok awal
        $barang_id = $db->lastInsertId();
        $stmt = $db->prepare("INSERT INTO stok (barang_id, jumlah) VALUES (?, 0)");
        $stmt->execute([$barang_id]);
        
        header("Location: barang.php");
        exit();
    }
}

// Proses edit barang
if (isset($_POST['edit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $nama_barang = validateInput($_POST['nama_barang']);
    $satuan = validateInput($_POST['satuan']);
    $deskripsi = validateInput($_POST['deskripsi']);

    if (!$id || strlen($nama_barang) > 100 || strlen($satuan) > 20 || strlen($deskripsi) > 255) {
        $error = "Input tidak valid";
    } else {
        $stmt = $db->prepare("UPDATE barang SET nama_barang = ?, satuan = ?, deskripsi = ? WHERE id = ?");
        $stmt->execute([$nama_barang, $satuan, $deskripsi, $id]);
        
        header("Location: barang.php");
        exit();
    }
}

// Proses hapus barang
if (isset($_POST['hapus'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    $id = filter_var($_POST['hapus'], FILTER_VALIDATE_INT);
    if ($id) {
        try {
            // Mulai transaksi
            $db->beginTransaction();
            
            // Hapus data terkait di tabel barang_keluar
            $stmt = $db->prepare("DELETE FROM barang_keluar WHERE barang_id = ?");
            $stmt->execute([$id]);
            
            // Hapus data terkait di tabel barang_masuk
            $stmt = $db->prepare("DELETE FROM barang_masuk WHERE barang_id = ?");
            $stmt->execute([$id]);
            
            // Hapus data terkait di tabel stok
            $stmt = $db->prepare("DELETE FROM stok WHERE barang_id = ?");
            $stmt->execute([$id]);
            
            // Terakhir hapus data barang
            $stmt = $db->prepare("DELETE FROM barang WHERE id = ?");
            $stmt->execute([$id]);
            
            // Commit transaksi
            $db->commit();
            
        } catch (Exception $e) {
            // Rollback jika terjadi error
            $db->rollBack();
            die('Error: ' . $e->getMessage());
        }
    }
    header("Location: barang.php");
    exit();
}

// Ambil data barang
$stmt = $db->query("SELECT b.*, s.jumlah as stok FROM barang b LEFT JOIN stok s ON b.id = s.barang_id ORDER BY b.kode_barang");
$barang = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Barang - Sistem Inventory</title>
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
            <h2>Master Barang</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="bi bi-plus"></i> Tambah Barang
            </button>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Stok</th>
                                <th>Deskripsi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($barang as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['kode_barang']); ?></td>
                                <td><?php echo htmlspecialchars($item['nama_barang']); ?></td>
                                <td><?php echo htmlspecialchars($item['satuan']); ?></td>
                                <td><?php echo htmlspecialchars($item['stok']); ?></td>
                                <td><?php echo htmlspecialchars($item['deskripsi']); ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit<?php echo $item['id']; ?>">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <input type="hidden" name="hapus" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="modalEdit<?php echo $item['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Barang</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Kode Barang</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($item['kode_barang']); ?>" disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Barang</label>
                                                    <input type="text" name="nama_barang" class="form-control" value="<?php echo htmlspecialchars($item['nama_barang']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Satuan</label>
                                                    <input type="text" name="satuan" class="form-control" value="<?php echo htmlspecialchars($item['satuan']); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Deskripsi</label>
                                                    <textarea name="deskripsi" class="form-control" rows="3"><?php echo htmlspecialchars($item['deskripsi']); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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
                    <h5 class="modal-title">Tambah Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Form Tambah -->
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kode Barang</label>
                            <input type="text" name="kode_barang" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" name="nama_barang" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Satuan</label>
                            <input type="text" name="satuan" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
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