<?php
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=laporan_rekapitulasi.doc");

$host = "localhost";
$user = "root";
$pass = "";
$db = "netcare";
$conn = new mysqli($host, $user, $pass, $db);

echo "<h2 style='text-align:center;'>Laporan Rekapitulasi</h2>";
echo "<table border='1' cellspacing='0' cellpadding='6' width='100%'>
<tr><th>No</th><th>Judul</th><th>Jenis</th><th>Keterangan</th><th>Tanggal</th></tr>";

$result = $conn->query("SELECT * FROM laporan_rekapitulasi ORDER BY tanggal DESC");
$no = 1;
while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>" . $no++ . "</td>
        <td>" . $row['judul'] . "</td>
        <td>" . $row['jenis'] . "</td>
        <td>" . $row['keterangan'] . "</td>
        <td>" . $row['tanggal'] . "</td>
    </tr>";
}
echo "</table>";
