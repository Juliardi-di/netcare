<?php
$conn = new mysqli('localhost', 'root', '', 'netcare');
$conn->set_charset('utf8mb4');

echo "===== ALL TABLES =====\n";
$result = $conn->query('SHOW TABLES');
while($row = $result->fetch_row()) {
    echo $row[0] . "\n";
}

echo "\n===== PENGAJUAN COLUMNS =====\n";
$result = $conn->query('DESCRIBE pengajuan');
if($result) {
    while($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . '\n';
    }
}

echo "\n===== SAMPLE PENGAJUAN DATA =====\n";
$result = $conn->query('SELECT * FROM pengajuan LIMIT 2');
if($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . ", Judul: " . $row['judul'] . ", Tanggal: " . $row['tanggal_pengajuan'] . "\n";
    }
}
?>
