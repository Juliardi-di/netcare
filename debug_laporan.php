<?php
/**
 * DATABASE DIAGNOSTIC - Untuk debugging laporan kosong
 * Akses: http://localhost/netcare/debug_laporan.php
 */

require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die('Silakan login terlebih dahulu');
}

$user_id = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Database Diagnostic</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .box { background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #2196F3; }
        .success { border-left-color: #4CAF50; }
        .error { border-left-color: #f44336; }
        .warning { border-left-color: #ff9800; }
        .info { border-left-color: #2196F3; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; }
        code { background: #fff3cd; padding: 2px 5px; }
        h2 { color: #333; border-bottom: 2px solid #2196F3; padding-bottom: 10px; }
        .count { font-weight: bold; color: #f44336; }
    </style>
</head>
<body>
<h1>🔍 Database Diagnostic untuk Laporan Petugas</h1>
<p><strong>User ID Login:</strong> <?= $user_id ?></p>

<?php

// ==================== STEP 1: CEK TABEL ====================
echo "<h2>STEP 1: Struktur Tabel</h2>";

$tables_to_check = [
    'user' => 'Tabel User Login',
    'users' => 'Tabel Users',
    'akun' => 'Tabel Akun',
    'pengajuan' => 'Tabel Pengajuan Layanan',
    'tugas_petugas' => 'Tabel Penugasan Petugas',
    'dokumentasi' => 'Tabel Dokumentasi',
    'master_petugas' => 'Tabel Master Petugas'
];

$existing_tables = [];

foreach ($tables_to_check as $table => $desc) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        $existing_tables[$table] = $desc;
        echo "<div class='box success'>✅ <strong>$table</strong> - $desc</div>";
    } else {
        echo "<div class='box error'>❌ <strong>$table</strong> - $desc (TIDAK ADA)</div>";
    }
}

// ==================== STEP 2: CEK DATA USER ====================
echo "<h2>STEP 2: Data User Login (ID: $user_id)</h2>";

foreach ($existing_tables as $table => $desc) {
    if (strpos($table, 'user') !== false || strpos($table, 'akun') !== false) {
        $result = $conn->query("SELECT * FROM $table WHERE id = $user_id LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<div class='box success'><strong>Ditemukan di tabel: $table</strong></div>";
            echo "<table>";
            foreach ($row as $col => $val) {
                echo "<tr><td><strong>$col</strong></td><td>" . htmlspecialchars($val ?? 'NULL') . "</td></tr>";
            }
            echo "</table>";
            break;
        }
    }
}

// ==================== STEP 3: CEK STRUKTUR PENGAJUAN ====================
echo "<h2>STEP 3: Struktur Tabel Pengajuan</h2>";

if (isset($existing_tables['pengajuan'])) {
    $columns = $conn->query("DESCRIBE pengajuan");
    echo "<table><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($col = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td><code>" . $col['Field'] . "</code></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// ==================== STEP 4: CEK STRUKTUR TUGAS_PETUGAS ====================
echo "<h2>STEP 4: Struktur Tabel Tugas Petugas</h2>";

if (isset($existing_tables['tugas_petugas'])) {
    $columns = $conn->query("DESCRIBE tugas_petugas");
    echo "<table><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    while ($col = $columns->fetch_assoc()) {
        echo "<tr>";
        echo "<td><code>" . $col['Field'] . "</code></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . $col['Key'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// ==================== STEP 5: CEK DATA PENGAJUAN ====================
echo "<h2>STEP 5: Sample Data Pengajuan (Status Disetujui)</h2>";

if (isset($existing_tables['pengajuan'])) {
    $result = $conn->query("SELECT * FROM pengajuan WHERE status = 'disetujui' LIMIT 5");
    
    if ($result->num_rows > 0) {
        echo "<div class='box success'><span class='count'>" . $result->num_rows . "</span> data pengajuan dengan status 'disetujui'</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Judul</th><th>Status</th><th>Tanggal</th><th>User/Created By</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['judul'] ?? '-') . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>" . $row['tanggal_pengajuan'] . "</td>";
            echo "<td>";
            echo $row['created_by'] ?? $row['user_id'] ?? '-';
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='box warning'>⚠️ Tidak ada data pengajuan dengan status 'disetujui'</div>";
    }
}

// ==================== STEP 6: CEK RELASI TUGAS_PETUGAS ====================
echo "<h2>STEP 6: Data Tugas Petugas</h2>";

if (isset($existing_tables['tugas_petugas'])) {
    $result = $conn->query("SELECT * FROM tugas_petugas LIMIT 10");
    
    if ($result->num_rows > 0) {
        echo "<div class='box info'>Total: <span class='count'>" . $result->num_rows . "</span> records ditemukan</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Petugas ID</th><th>Pengajuan ID</th><th>Status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . ($row['petugas_id'] ?? $row['user_id'] ?? '-') . "</td>";
            echo "<td>" . $row['pengajuan_id'] . "</td>";
            echo "<td>" . ($row['status'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='box error'>❌ Tidak ada data di tugas_petugas</div>";
    }
}

// ==================== STEP 7: COBA QUERY LANGSUNG ====================
echo "<h2>STEP 7: Test Query untuk User " . $user_id . "</h2>";

if (isset($existing_tables['pengajuan']) && isset($existing_tables['tugas_petugas'])) {
    
    // Query 1: Pengajuan langsung dari user
    echo "<h3>Query 1: Pengajuan yang dibuat/dimiliki user</h3>";
    $q1 = $conn->query("SELECT COUNT(*) as total FROM pengajuan WHERE created_by = $user_id OR user_id = $user_id");
    $r1 = $q1->fetch_assoc();
    echo "<div class='box info'>Hasil: <span class='count'>" . $r1['total'] . "</span> pengajuan</div>";
    
    // Query 2: Pengajuan via tugas_petugas
    echo "<h3>Query 2: Pengajuan yang ditugaskan ke petugas user</h3>";
    $q2 = $conn->query("SELECT COUNT(*) as total FROM tugas_petugas WHERE petugas_id = $user_id OR user_id = $user_id");
    $r2 = $q2->fetch_assoc();
    echo "<div class='box info'>Hasil: <span class='count'>" . $r2['total'] . "</span> tugas_petugas</div>";
    
    // Query 3: Combined
    echo "<h3>Query 3: Pengajuan dengan status disetujui (via tugas_petugas)</h3>";
    $q3 = $conn->query("
        SELECT COUNT(DISTINCT p.id) as total
        FROM pengajuan p
        LEFT JOIN tugas_petugas t ON t.pengajuan_id = p.id
        WHERE (t.petugas_id = $user_id OR p.created_by = $user_id OR p.user_id = $user_id)
        AND p.status = 'disetujui'
    ");
    $r3 = $q3->fetch_assoc();
    echo "<div class='box info'>Hasil: <span class='count'>" . $r3['total'] . "</span> pengajuan</div>";
    
    // Query 4: Lihat detail
    echo "<h3>Query 4: Detail Pengajuan</h3>";
    $q4 = $conn->query("
        SELECT p.id, p.judul, p.status, t.petugas_id, p.created_by, p.user_id
        FROM pengajuan p
        LEFT JOIN tugas_petugas t ON t.pengajuan_id = p.id
        WHERE (t.petugas_id = $user_id OR p.created_by = $user_id OR p.user_id = $user_id)
        AND p.status = 'disetujui'
        LIMIT 5
    ");
    
    if ($q4->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Judul</th><th>Status</th><th>Petugas ID</th><th>Created By</th><th>User ID</th></tr>";
        while ($row = $q4->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['judul']) . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>" . ($row['petugas_id'] ?? '-') . "</td>";
            echo "<td>" . ($row['created_by'] ?? '-') . "</td>";
            echo "<td>" . ($row['user_id'] ?? '-') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='box error'>❌ Tidak ada hasil dari query</div>";
    }
}

echo "<h2>📝 Kesimpulan</h2>";
echo "<div class='box info'>";
echo "<p>Berdasarkan diagnostic di atas, Anda bisa:</p>";
echo "<ol>";
echo "<li>Lihat tabel mana yang tidak ada</li>";
echo "<li>Lihat struktur kolom tabel (nama field yang sebenarnya)</li>";
echo "<li>Lihat apakah ada data pengajuan dengan status 'disetujui'</li>";
echo "<li>Lihat apakah ada relasi antara user dan pengajuan</li>";
echo "<li>Lihat hasil query langsung untuk user ID Anda</li>";
echo "</ol>";
echo "<p><strong>CATATAN:</strong> Bawa informasi ini saat meminta bantuan, atau gunakan untuk memperbaiki query di laporan_petugas.php</p>";
echo "</div>";

?>
</body>
</html>
