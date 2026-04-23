<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "netcare";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $conn->query("DELETE FROM laporan_rekapitulasi WHERE id=$id");

    header("Location: laporan_rekapitulasi.php?status=hapus_sukses");
    exit;
}
?>