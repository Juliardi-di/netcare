<?php

require_once __DIR__ . "/../config.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$pesan_sukses = "";
$pesan_error = "";

if (isset($_POST['hapus_terpilih']) && isset($_POST['hapus_id']) && is_array($_POST['hapus_id'])) {
    try {
        foreach ($_POST['hapus_id'] as $id_hapus) {
            $id_hapus = intval($id_hapus);

            $stmt = $conn->prepare("SELECT file_path FROM dokumentasi WHERE id = ?");
            $stmt->bind_param("i", $id_hapus);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                while ($row = $res->fetch_assoc()) {
                    $file_path = __DIR__ . '/../' . $row['file_path'];
                    if (!empty($row['file_path']) && file_exists($file_path) && is_file($file_path)) {
                        unlink($file_path);
                    }
                }
            }

            $stmtDel = $conn->prepare("DELETE FROM dokumentasi WHERE id = ?");
            $stmtDel->bind_param("i", $id_hapus);
            $stmtDel->execute();
        }
        $pesan_sukses = "🗑️ Dokumentasi terpilih berhasil dihapus!";
    } catch (Exception $e) {
        $pesan_error = "❌ Terjadi kesalahan saat menghapus: " . $e->getMessage();
    }
} else {
    $pesan_error = "⚠️ Tidak ada dokumentasi yang dipilih untuk dihapus.";
}

session_start();
$_SESSION['pesan_sukses'] = $pesan_sukses;
$_SESSION['pesan_error']  = $pesan_error;

header("Location: dokumentasi.php");
exit;
