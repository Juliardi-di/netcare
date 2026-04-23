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
    die("Akses tidak valid");
}

$user_id = (int) $_SESSION['user_id'];

$nama    = trim($_POST['nama']);
$email   = trim($_POST['email']);
$telepon = trim($_POST['telepon'] ?? '');
$alamat  = trim($_POST['alamat'] ?? '');

if ($nama === '' || $email === '') {
    die("Nama dan Email wajib diisi");
}

$stmt = $conn->prepare("
    UPDATE users
    SET email = ?, nama = ?, telepon = ?, alamat = ?
    WHERE id = ?
");

if (!$stmt) {
    die("Prepare gagal: " . $conn->error);
}

$stmt->bind_param(
    "ssssi",
    $email,
    $nama,
    $telepon,
    $alamat,
    $user_id
);

if ($stmt->execute())
header("Location: /netcare/petugas/dashboard_petugas.php?page=profil_akun&success=profil");
exit;

