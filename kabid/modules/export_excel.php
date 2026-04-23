<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "netcare";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal");
}
$conn->set_charset("utf8mb4");

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Laporan_Aktivitas_Kinerja_Harian.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<table border='1'>
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Uraian</th>
    <th>Output</th>
    <th>Utama</th>
    <th>Tambahan</th>
    <th>Status</th>
    <th>Keterangan</th>
    <th>Catatan</th>
</tr>";

$q = $conn->query("SELECT * FROM laporan_rekapitulasi ORDER BY tanggal ASC, id ASC");
$no = 1;
while ($r = $q->fetch_assoc()) {
    echo "<tr>
        <td>{$no}</td>
        <td>{$r['tanggal']}</td>
        <td>".htmlspecialchars($r['judul'])."</td>
        <td>{$r['output']}</td>
        <td>{$r['utama']}</td>
        <td>{$r['tambahan']}</td>
        <td>{$r['jenis']}</td>
        <td>{$r['keterangan']}</td>
        <td>{$r['keterangan2']}</td>
    </tr>";
    $no++;
}

echo "</table>";
exit;
