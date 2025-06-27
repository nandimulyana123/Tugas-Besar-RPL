<?php
function checkLogin() {
    // Validasi session
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['last_activity'])) {
        header("Location: /inventory/auth/login.php");
        exit();
    }

    // Cek session timeout (30 menit)
    if (time() - $_SESSION['last_activity'] > 1800) {
        session_unset();
        session_destroy();
        header("Location: /inventory/auth/login.php?msg=timeout");
        exit();
    }

    // Update last activity
    $_SESSION['last_activity'] = time();
}

function checkAdmin() {
    checkLogin();
    if ($_SESSION['role'] !== 'admin') {
        header("Location: /inventory/index.php");
        exit();
    }
}

// Fungsi untuk mencegah session fixation
function regenerateSession() {
    // Simpan data session
    $old_session_data = $_SESSION;
    
    // Hancurkan session lama
    session_destroy();
    
    // Buat session baru
    session_start();
    session_regenerate_id(true);
    
    // Kembalikan data session
    $_SESSION = $old_session_data;
    
    // Set waktu aktivitas terakhir
    $_SESSION['last_activity'] = time();
}
?>