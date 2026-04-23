<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /netcare/login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Akses tidak valid");
}

$password_lama = $_POST['password_lama'] ?? '';
$password_baru = $_POST['password_baru'] ?? '';
$konfirmasi    = $_POST['konfirmasi'] ?? '';

if ($password_lama === '' || $password_baru === '' || $konfirmasi === '') {
    die("Semua field wajib diisi");
}

if ($password_baru !== $konfirmasi) {
    die("Konfirmasi password tidak cocok");
}

$stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User tidak ditemukan");
}

if (!password_verify($password_lama, $user['password'])) {
    die("Password lama salah");
}

$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$update->bind_param("si", $password_hash, $user_id);

if ($update->execute()) {
    header("Location: /netcare/admin/profil.php?success=password");
    exit;
} else {
    die("Gagal update password: " . $update->error);
}