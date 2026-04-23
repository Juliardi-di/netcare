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
    header("Location: /netcare/dashboard.php?page=profil_akun");
    exit;
}

$id = (int) $_SESSION['user_id'];

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    header("Location: /netcare/dashboard.php?page=profil_akun&error=format");
    exit;
}

$file = $_FILES['foto'];

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

$mime = mime_content_type($file['tmp_name']);
if (!isset($allowed[$mime])) {
    header("Location: /netcare/dashboard.php?page=profil_akun&error=format");
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    header("Location: /netcare/dashboard.php?page=profil_akun&error=size");
    exit;
}

$ext = $allowed[$mime];
$filename = 'user_' . $id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

$upload_dir = __DIR__ . '/../uploads/profil/';
$target = $upload_dir . $filename;

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if (!move_uploaded_file($file['tmp_name'], $target)) {
    header("Location: /netcare/dashboard.php?page=profil_akun&error=upload");
    exit;
}

/* HAPUS FOTO LAMA */
$stmt = $conn->prepare("SELECT images FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$old = $stmt->get_result()->fetch_assoc();

if ($old && !empty($old['images']) && $old['images'] !== 'default.png') {
    $old_path = $upload_dir . $old['images'];
    if (file_exists($old_path)) {
        unlink($old_path);
    }
}

/* UPDATE DB */
$update = $conn->prepare("UPDATE users SET images=? WHERE id=?");
$update->bind_param("si", $filename, $id);
$update->execute();

/* REDIRECT */
header("Location: /netcare/dashboard.php?page=profil_akun&success=foto");
exit;
