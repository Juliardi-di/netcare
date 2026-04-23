<?php
require_once __DIR__ . '/../../config.php';

$id = $_GET['id'];

// hapus tabel anak dulu
mysqli_query($conn, "DELETE FROM pengajuan_layanan WHERE pengajuan_id='$id'");
mysqli_query($conn, "DELETE FROM tim_petugas WHERE pengajuan_id='$id'");
mysqli_query($conn, "DELETE FROM progres_teknisi WHERE pengajuan_id='$id'");

// baru hapus induk
mysqli_query($conn, "DELETE FROM pengajuan WHERE id='$id'");

header("Location: ../dashboard-kabid.php?page=riwayat_pengajuan&hapus=1");
exit;
?>