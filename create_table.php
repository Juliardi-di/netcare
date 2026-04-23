<?php
$conn = new mysqli('localhost', 'root', '', 'netcare');
$conn->set_charset('utf8mb4');

// Create table if not exists
$sql = "CREATE TABLE IF NOT EXISTS `pengajuan_layanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `pengajuan_id` int(11) NOT NULL,
  `output` varchar(100),
  `utama` int(11) DEFAULT 0,
  `tambahan` int(11) DEFAULT 0,
  `jenis` varchar(50),
  `keterangan` text,
  `keterangan2` text,
  `created_at` timestamp DEFAULT current_timestamp(),
  KEY `pengajuan_id_idx` (`pengajuan_id`),
  CONSTRAINT `fk_pengajuan` FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($conn->query($sql)) {
    echo "✓ Tabel pengajuan_layanan berhasil dibuat/sudah ada.\n";
    
    // Test query
    $test_sql = "SELECT p.id as pengajuan_id, DATE_FORMAT(p.tanggal_pelaksanaan, '%Y-%m-%d') as tgl_pelaksanaan, p.judul, pl.id, pl.output, pl.utama, pl.tambahan, pl.jenis FROM pengajuan p LEFT JOIN pengajuan_layanan pl ON p.id = pl.pengajuan_id ORDER BY p.tanggal_pelaksanaan ASC LIMIT 5";
    
    $result = $conn->query($test_sql);
    if($result) {
        echo "\n✓ Query berhasil. Data yang ditampilkan:\n";
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "- Tanggal: " . $row['tgl_pelaksanaan'] . " | Judul: " . substr($row['judul'], 0, 60) . "...\n";
            }
        } else {
            echo "Tidak ada data pengajuan.\n";
        }
    } else {
        echo "Query error: " . $conn->error . "\n";
    }
} else {
    echo "Error creating table: " . $conn->error;
}
?>
