<?php
$host = 'localhost';
$dbname = 'db_inventory';
$username = 'root';
$password = '';
$port = 3307;

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        PDO::ATTR_PERSISTENT => false
    ];
    
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die('Koneksi database gagal. Silakan hubungi administrator.');
}
?>