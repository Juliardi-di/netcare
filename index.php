<?php
// Mulai session kalau belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Paksa ke halaman login
    header("Location: /netcare/login.php");
    exit;
}
?>
