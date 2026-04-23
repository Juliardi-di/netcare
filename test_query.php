<?php
$conn = new mysqli('localhost', 'root', '', 'netcare');
$conn->set_charset('utf8mb4');

echo "===== TESTING QUERY =====\n";
$sql = "SELECT p.id as pengajuan_id, DATE_FORMAT(p.tanggal_pelaksanaan, '%Y-%m-%d') as tgl_pelaksanaan, p.judul, pl.id, pl.output, pl.utama, pl.tambahan, pl.jenis FROM pengajuan p LEFT JOIN pengajuan_layanan pl ON p.id = pl.pengajuan_id ORDER BY p.tanggal_pelaksanaan ASC, p.id ASC LIMIT 5";

$result = $conn->query($sql);
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "\nPengajuan ID: " . $row['pengajuan_id'];
        echo "\nTanggal Pelaksanaan: " . $row['tgl_pelaksanaan'];
        echo "\nJudul: " . $row['judul'];
        echo "\nData Layanan ID: " . ($row['id'] ?? 'NULL');
        echo "\nOutput: " . ($row['output'] ?? 'NULL');
        echo "\nUtama: " . ($row['utama'] ?? 'NULL');
        echo "\nTambahan: " . ($row['tambahan'] ?? 'NULL');
        echo "\nJenis: " . ($row['jenis'] ?? 'NULL');
        echo "\n---\n";
    }
    echo "\nTotal rows: " . $result->num_rows;
} else {
    echo "Query error or no data: " . $conn->error;
}
?>
