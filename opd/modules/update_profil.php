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

$user_id = (int) $_SESSION['user_id'];

$nama    = trim($_POST['nama'] ?? '');
$email   = trim($_POST['email'] ?? '');
$telepon = trim($_POST['telepon'] ?? '');
$alamat  = trim($_POST['alamat'] ?? '');

// ================= VALIDASI =================
if ($nama === '' || $email === '') {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&error=kosong");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&error=email");
    exit;
}

// ================= CEK EMAIL DUPLIKAT =================
$cek = $conn->prepare("SELECT id FROM users WHERE email=? AND id!=?");
$cek->bind_param("si", $email, $user_id);
$cek->execute();

if ($cek->get_result()->num_rows > 0) {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&error=duplikat");
    exit;
}

// ================= UPDATE =================
$stmt = $conn->prepare("
    UPDATE users
    SET email = ?, nama = ?, telepon = ?, alamat = ?
    WHERE id = ?
");

if (!$stmt) {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&error=server");
    exit;
}

$stmt->bind_param("ssssi", $email, $nama, $telepon, $alamat, $user_id);

if ($stmt->execute()) {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&success=profil");
} else {
    header("Location: /netcare/opd/dashboard_opd.php?page=profil_akun&error=server");
}

exit;