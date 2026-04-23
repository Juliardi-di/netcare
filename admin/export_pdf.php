<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;

$host = "localhost";
$user = "root";
$pass = "";
$db = "netcare";
$conn = new mysqli($host, $user, $pass, $db);

$result = $conn->query("SELECT * FROM laporan_rekapitulasi ORDER BY tanggal DESC");

$html = "<h2 style='text-align:center;'>Laporan Rekapitulasi</h2>";
$html .= "<table border='1' cellspacing='0' cellpadding='6' width='100%'>
<tr><th>No</th><th>Judul</th><th>Jenis</th><th>Keterangan</th><th>Tanggal</th></tr>";

$no = 1;
while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
        <td>" . $no++ . "</td>
        <td>" . htmlspecialchars($row['judul']) . "</td>
        <td>" . htmlspecialchars($row['jenis']) . "</td>
        <td>" . htmlspecialchars($row['keterangan']) . "</td>
        <td>" . $row['tanggal'] . "</td>
    </tr>";
}
$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_rekapitulasi.pdf", array("Attachment" => 0));
