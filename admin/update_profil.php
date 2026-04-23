<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /netcare/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /netcare/admin/profil_akun.php");
    exit;
}

$id       = (int) $_SESSION['user_id'];
$email    = $_POST['email'] ?? '';
$nama     = $_POST['nama'] ?? '';
$telepon  = $_POST['telepon'] ?? '';
$alamat   = $_POST['alamat'] ?? '';

$stmt = $conn->prepare("
    UPDATE users 
    SET email=?, nama=?, telepon=?, alamat=? 
    WHERE id=?
");
$stmt->bind_param("ssssi", $email, $nama, $telepon, $alamat, $id);
$stmt->execute();

header("Location: ../dashboard.php?page=profil_akun&success=profil");
exit;
