<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "netcare";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("❌ Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama_pemohon'] ?? '';
    $email = $_POST['email'] ?? '';
    $kategori = $_POST['kategori'] ?? '';
    $judul = $_POST['judul_pengajuan'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';

    $stmt = $conn->prepare("INSERT INTO pengajuan 
        (nama_pemohon, email, kategori, judul_pengajuan, deskripsi) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nama, $email, $kategori, $judul, $deskripsi);

    if ($stmt->execute()) {
        echo "✅ Pengajuan berhasil disimpan!";
    } else {
        echo "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
