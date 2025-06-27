<?php
// Set session cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
require_once '../config/database.php';
require_once '../config/security.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// Proses login jika ada request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     // Validasi CSRF token
     if (!isset($_POST['csrf_token'])) {
         die('Invalid CSRF token - Missing');
     }
     validateCsrfToken($_POST['csrf_token']);

    $username = validateInput($_POST['username']);
    $password = $_POST['password'];

    // Batasi panjang input untuk keamanan tambahan
    if (strlen($username) > 50 || strlen($password) > 255) {
        // Regenerate token CSRF baru setelah validasi gagal
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $error = "Input username atau password terlalu panjang!";
    } else {
        // Delay untuk menghindari brute force attack
        usleep(random_int(100000, 500000)); // Delay 100ms - 500ms

        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID untuk mencegah Session Fixation
            session_regenerate_id(true);

            // Set data user ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['last_activity'] = time();

            // Redirect berdasarkan peran
            if ($_SESSION['role'] === 'admin') {
                 header("Location: ../index.php");
            } else {
                 header("Location: ../transaksi/stok.php");
            }

            exit();
        } else {
            // Regenerate token CSRF baru setelah validasi gagal
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $error = "Username atau password salah!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../includes/css/login.css"> 
</head>
<body>
    <div class="container login-container">
        <div class="row g-0 align-items-stretch">
            <div class="col-md-6 image-col">
                <img src="../includes/images/bg-login.jpg" alt="Ilustrasi Pengelolaan Inventory dengan Daftar Centang dan Kotak">
            </div>

            <!-- Kolom untuk Form Login -->
            <div class="col-md-6 form-col">
                <div class="login-form-content">
                    <h3 class="text-center mb-4">Login <a href="https://github.com/angganurbayu" class="text-black text-decoration-none" target="_blank">Sistem Inventory</a></h3>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                     <?php if (isset($_GET['msg']) && $_GET['msg'] === 'timeout'): ?>
                         <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            Sesi Anda telah berakhir. Silakan login kembali.
                             <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" id="username" class="form-control form-control-lg" required autocomplete="username">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control form-control-lg" required autocomplete="current-password">
                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" name="login" class="btn btn-primary btn-lg">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>