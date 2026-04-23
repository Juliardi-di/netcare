<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /netcare/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun");
    exit;
}

$id = (int) $_SESSION['user_id'];

$password_lama = $_POST['password_lama'] ?? '';
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi    = $_POST['konfirmasi'] ?? '';

// ================= VALIDASI =================
if (!$password_lama || !$password_baru || !$konfirmasi) {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&error=kosong");
    exit;
}

if ($password_baru !== $konfirmasi) {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&error=konfirmasi");
    exit;
}

if (strlen($password_baru) < 6) {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&error=panjang");
    exit;
}

// ================= CEK PASSWORD LAMA =================
$stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data || !password_verify($password_lama, $data['password'])) {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&error=lama");
    exit;
}

// ================= HASH PASSWORD BARU =================
$hash = password_hash($password_baru, PASSWORD_DEFAULT);

// ================= UPDATE =================
$update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
$update->bind_param("si", $hash, $id);
$update->execute();

// ================= REDIRECT =================
header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&success=password");
exit;