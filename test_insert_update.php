<?php
// Test simpan data via direct POST request simulation
$host = "localhost";
$user = "root";
$pass = "";
$db   = "netcare";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8mb4");

echo "===== SIMPAN DATA TEST =====\n\n";

// Test INSERT new entry
echo "1. Test INSERT (create new entry)\n";
echo "   Data to insert:\n";
echo "   - pengajuan_id: 107\n";
echo "   - output: 5\n";
echo "   - utama: 120\n";
echo "   - tambahan: 30\n";
echo "   - jenis: Disetujui\n";
echo "   - keterangan: Test simpan\n";
echo "   - keterangan2: Catatan test\n\n";

$pengajuan_id = 107;
$jenis = "Disetujui";
$keterangan = "Test simpan";
$output = "5";
$utama = 120;
$tambahan = 30;
$keterangan2 = "Catatan test";

$stmt = $conn->prepare("INSERT INTO pengajuan_layanan (pengajuan_id, jenis, keterangan, output, utama, tambahan, keterangan2) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssiii", $pengajuan_id, $jenis, $keterangan, $output, $utama, $tambahan, $keterangan2);

if ($stmt->execute()) {
    $newId = $conn->insert_id;
    echo "   ✓ INSERT berhasil. New ID: $newId\n\n";
    
    // Test UPDATE
    echo "2. Test UPDATE (update existing entry)\n";
    echo "   Data to update (ID: $newId):\n";
    echo "   - output: 10\n";
    echo "   - utama: 200\n";
    echo "   - tambahan: 50\n\n";
    
    $output_new = "10";
    $utama_new = 200;
    $tambahan_new = 50;
    
    $stmt2 = $conn->prepare("UPDATE pengajuan_layanan SET jenis=?, keterangan=?, output=?, utama=?, tambahan=?, keterangan2=? WHERE id=?");
    $stmt2->bind_param("sssiiis", $jenis, $keterangan, $output_new, $utama_new, $tambahan_new, $keterangan2, $newId);
    
    if ($stmt2->execute()) {
        echo "   ✓ UPDATE berhasil\n\n";
        
        // Verify data
        echo "3. Verify data:\n";
        $result = $conn->query("SELECT * FROM pengajuan_layanan WHERE id = $newId");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "   ✓ Data verified:\n";
            echo "     - output: " . $row['output'] . "\n";
            echo "     - utama: " . $row['utama'] . "\n";
            echo "     - tambahan: " . $row['tambahan'] . "\n";
            echo "     - jenis: " . $row['jenis'] . "\n";
        }
        
        // Delete test
        echo "\n4. Cleanup (delete test data):\n";
        $conn->query("DELETE FROM pengajuan_layanan WHERE id = $newId");
        echo "   ✓ Test data deleted\n";
    } else {
        echo "   ✗ UPDATE gagal: " . $stmt2->error . "\n";
    }
} else {
    echo "   ✗ INSERT gagal: " . $stmt->error . "\n";
}

echo "\n✅ TESTING COMPLETE\n";
?>
