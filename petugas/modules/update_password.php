<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /netcare/login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

$password_lama = $_POST['password_lama'] ?? '';
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi    = $_POST['konfirmasi'] ?? '';

// 1. Cek input
if (!$password_lama || !$password_baru || !$konfirmasi) {
    die("❌ Semua field wajib diisi");
}

// 2. Cek password baru = konfirmasi
if ($password_baru !== $konfirmasi) {
    die("❌ Konfirmasi password tidak cocok");
}

// 3. Ambil password lama dari DB
$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("❌ User tidak ditemukan");
}

// 4. Verifikasi password lama
if (!password_verify($password_lama, $user['password'])) {
    die("❌ Password lama salah");
}

// 5. Hash password baru
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// 6. Update password
$update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update->bind_param("si", $password_hash, $user_id);
$update->execute();

// 7. Redirect sukses
header("Location: /netcare/petugas/dashboard_petugas.php?page=profil_akun&success=password");
exit;