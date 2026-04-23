<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_layanan') {
    exit('Akses ditolak');
}

$user_id = (int) $_SESSION['user_id'];

// Filter tanggal (opsional)
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

$whereTanggal = '';
if ($tgl_awal && $tgl_akhir) {
    $whereTanggal = " AND DATE(p.tanggal_pelaksanaan) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$sql = "
SELECT 
    p.nama,
    p.jenis_gangguan,
    p.deskripsi,
    p.tanggal_pelaksanaan,
    p.tanggal_pengajuan,
    p.status,
    pengaju_user.nama_instansi AS instansi_pengaju,
    GROUP_CONCAT(DISTINCT mp.jabatan SEPARATOR ', ') AS tugas_petugas
FROM pengajuan p
JOIN tim_petugas tp ON p.id = tp.pengajuan_id
JOIN master_petugas mp ON tp.petugas_id = mp.id
JOIN users login_user ON login_user.id = ?
JOIN users pengaju_user ON p.user_id = pengaju_user.id
WHERE mp.nama = login_user.nama
  AND p.deleted_at IS NULL
  AND p.status = 'disetujui'
  $whereTanggal
GROUP BY p.id
ORDER BY p.tanggal_pelaksanaan ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Petugas</title>
<style>
body{font-family:Arial;background:#f5f6fa}
.container{width:95%;max-width:1000px;margin:20px auto;background:#fff;padding:30px}
h2,h3{text-align:center;margin:0}
hr{margin:20px 0}
table{width:100%;border-collapse:collapse;margin-top:15px}
th,td{border:1px solid #000;padding:8px;font-size:13px}
th{text-align:center;background:#eee}
.info{margin-top:10px;font-size:14px}
.no-print{margin-bottom:15px}
@media print{
    .no-print{display:none}
}
</style>
</head>
<body>

<div class="container">

    <div class="no-print">
        <form method="get">
    <input type="hidden" name="page" value="laporan_petugas">

    <label>Tanggal Awal</label>
    <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tgl_awal) ?>">

    <label>Tanggal Akhir</label>
    <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tgl_akhir) ?>">

    <button type="submit">🔍 Filter</button>
    <button type="button" onclick="resetFilter()">♻ Reset</button>
    <button type="button" onclick="window.print()">🖨 Cetak</button>
</form>

    </div>

    <h2>LAPORAN PELAKSANAAN PETUGAS</h2>
    <h3>Dinas Komunikasi dan Informatika</h3>

    <div class="info">
        <strong>Nama Petugas :</strong> <?= htmlspecialchars($_SESSION['nama'] ?? '-') ?><br>
        <strong>Periode :</strong>
        <?= $tgl_awal && $tgl_akhir ? date('d-m-Y', strtotime($tgl_awal)).' s/d '.date('d-m-Y', strtotime($tgl_akhir)) : 'Keseluruhan' ?>
    </div>

    <hr>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Kegiatan</th>
                <th>Jenis Layanan</th>
                <th>Instansi Pengaju</th>
                <th>Tanggal Pelaksanaan</th>
                <th>Tugas Petugas</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            $no = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td align='center'>{$no}</td>";
                echo "<td>".htmlspecialchars($row['nama'])."</td>";
                echo "<td>".htmlspecialchars($row['jenis_gangguan'])."</td>";
                echo "<td>".htmlspecialchars($row['instansi_pengaju'])."</td>";
                echo "<td align='center'>".($row['tanggal_pelaksanaan'] ? date('d-m-Y', strtotime($row['tanggal_pelaksanaan'])) : '-')."</td>";
                echo "<td>".htmlspecialchars($row['tugas_petugas'])."</td>";
                echo "<td>Telah dilaksanakan</td>";
                echo "</tr>";
                $no++;
            }
        } else {
            echo "<tr><td colspan='7' align='center'><em>Data tidak ditemukan</em></td></tr>";
        }
        ?>
        </tbody>
    </table>

    <br><br>
    <table width="100%" style="border:none">
        <tr>
            <td width="60%"></td>
            <td align="center">
                Lingga, <?= date('d-m-Y') ?><br>
                Petugas Pelaksana<br><br><br>
                <strong><?= htmlspecialchars($_SESSION['nama'] ?? '-') ?></strong>
            </td>
        </tr>
    </table>

</div>
<script>
function resetFilter() {
    window.location.href = 'dashboard_petugas.php?page=laporan_petugas';
}

</script>

</body>
</html>
