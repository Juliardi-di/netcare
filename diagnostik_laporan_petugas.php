<?php
/**
 * DIAGNOSTIC TOOL - Laporan Petugas Database Inspector
 * Alat untuk mengidentifikasi struktur database dan query issues
 * 
 * Akses: http://localhost/netcare/diagnostik_laporan_petugas.php
 * Perlu login sebagai admin atau petugas untuk mengakses
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';
session_start();

// Simple auth - allow anyone logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'unknown';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic Tool - Laporan Petugas</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 30px; border-left: 5px solid #2196F3; padding-left: 10px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .status.ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #4CAF50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        pre { background: #f4f4f4; padding: 10px; overflow-x: auto; border: 1px solid #ddd; }
        code { background: #f0f0f0; padding: 2px 5px; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnostic Tool - Laporan Petugas</h1>
        <p>User ID: <strong><?= $user_id ?></strong> | Role: <strong><?= htmlspecialchars($role) ?></strong></p>

        <?php
        // ============ TEST 1: Check Database Connection ============
        echo "<h2>1. Database Connection</h2>";
        if ($conn && !$conn->connect_error) {
            echo '<div class="status ok">✓ Database berhasil terkoneksi</div>';
            echo '<p>Database: <code>' . htmlspecialchars($conn->select_db($conn->select_db($conn->query("SELECT DATABASE()")->fetch_array()[0]))) . '</code></p>';
        } else {
            echo '<div class="status error">✗ Error koneksi database: ' . htmlspecialchars($conn->connect_error ?? 'Unknown') . '</div>';
        }

        // ============ TEST 2: Check Table Existence ============
        echo "<h2>2. Check Tabel Database</h2>";
        $tables_to_check = [
            'user' => 'User table (default)',
            'users' => 'Users table',
            'akun' => 'Akun table',
            'pegawai' => 'Pegawai table',
            'pengajuan' => 'Pengajuan table',
            'tugas_petugas' => 'Tugas Petugas table',
            'dokumentasi' => 'Dokumentasi table'
        ];

        foreach ($tables_to_check as $table => $desc) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo '<div class="status ok">✓ ' . $desc . ' (<code>' . $table . '</code>) DITEMUKAN</div>';
            } else {
                echo '<div class="status error">✗ ' . $desc . ' (<code>' . $table . '</code>) TIDAK DITEMUKAN</div>';
            }
        }

        // ============ TEST 3: Check User Table Structure ============
        echo "<h2>3. Struktur Tabel User</h2>";
        $user_tables = ['user', 'users', 'akun', 'pegawai'];
        $found_user_table = null;

        foreach ($user_tables as $tbl) {
            $result = $conn->query("SHOW TABLES LIKE '$tbl'");
            if ($result && $result->num_rows > 0) {
                $found_user_table = $tbl;
                echo '<div class="status ok">Menggunakan tabel: <code>' . $tbl . '</code></div>';
                
                $columns = $conn->query("SHOW COLUMNS FROM $tbl");
                echo '<table><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>';
                while ($col = $columns->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td><code>' . htmlspecialchars($col['Field']) . '</code></td>';
                    echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                    echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                break;
            }
        }

        if (!$found_user_table) {
            echo '<div class="status error">Tidak ada tabel user ditemukan!</div>';
        }

        // ============ TEST 4: Check Current User Data ============
        echo "<h2>4. Data User Saat Ini (ID: $user_id)</h2>";
        if ($found_user_table) {
            $stmt = $conn->prepare("SELECT * FROM $found_user_table WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    echo '<div class="status ok">✓ Data user ditemukan</div>';
                    $user = $result->fetch_assoc();
                    echo '<table><tr><th>Field</th><th>Value</th></tr>';
                    foreach ($user as $key => $value) {
                        echo '<tr><td><code>' . htmlspecialchars($key) . '</code></td><td>' . htmlspecialchars($value ?? 'NULL') . '</td></tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="status warning">⚠ Data user ID ' . $user_id . ' TIDAK DITEMUKAN di tabel ' . $found_user_table . '</div>';
                }
                $stmt->close();
            }
        }

        // ============ TEST 5: Check Pengajuan Data ============
        echo "<h2>5. Data Pengajuan - Jumlah & Status</h2>";
        
        // Count by status
        $status_query = $conn->query("SELECT status, COUNT(*) as count FROM pengajuan GROUP BY status ORDER BY count DESC");
        if ($status_query && $status_query->num_rows > 0) {
            echo '<h3>Pengajuan by Status:</h3>';
            echo '<table><tr><th>Status</th><th>Jumlah</th></tr>';
            while ($row = $status_query->fetch_assoc()) {
                echo '<tr><td>' . htmlspecialchars($row['status']) . '</td><td>' . (int)$row['count'] . '</td></tr>';
            }
            echo '</table>';
        }

        // Check if there's disetujui data
        $approved = $conn->query("SELECT COUNT(*) as total FROM pengajuan WHERE status = 'disetujui' OR status = 'Disetujui'");
        if ($approved) {
            $app_count = $approved->fetch_assoc()['total'];
            if ($app_count > 0) {
                echo '<div class="status ok">✓ Total pengajuan dengan status "disetujui": <strong>' . $app_count . '</strong></div>';
            } else {
                echo '<div class="status warning">⚠ Tidak ada pengajuan dengan status "disetujui"</div>';
            }
        }

        // ============ TEST 6: Check Tugas Petugas Relationship ============
        echo "<h2>6. Struktur Tabel tugas_petugas</h2>";
        $result = $conn->query("SHOW TABLES LIKE 'tugas_petugas'");
        if ($result && $result->num_rows > 0) {
            echo '<div class="status ok">✓ Tabel tugas_petugas DITEMUKAN</div>';
            
            $columns = $conn->query("SHOW COLUMNS FROM tugas_petugas");
            echo '<table><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>';
            while ($col = $columns->fetch_assoc()) {
                echo '<tr>';
                echo '<td><code>' . htmlspecialchars($col['Field']) . '</code></td>';
                echo '<td>' . htmlspecialchars($col['Type']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Null']) . '</td>';
                echo '<td>' . htmlspecialchars($col['Key']) . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            $count = $conn->query("SELECT COUNT(*) as total FROM tugas_petugas");
            if ($count) {
                $c = $count->fetch_assoc()['total'];
                echo '<p>Total records di tugas_petugas: <strong>' . $c . '</strong></p>';
            }
        } else {
            echo '<div class="status warning">⚠ Tabel tugas_petugas TIDAK DITEMUKAN</div>';
        }

        // ============ TEST 7: Test Query Approaches ============
        echo "<h2>7. Test Query Untuk User ID: $user_id</h2>";
        
        echo '<div class="section"><h3>Approach A: Via tugas_petugas junction</h3>';
        $sql = "SELECT COUNT(DISTINCT p.id) AS total FROM pengajuan p
                LEFT JOIN tugas_petugas t ON t.pengajuan_id = p.id
                WHERE (t.petugas_id = ? OR p.created_by = ? OR p.user_id = ?)
                AND (p.status = 'disetujui' OR p.status = 'Disetujui')";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iii", $user_id, $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            echo '<pre>SQL: ' . htmlspecialchars($sql) . '</pre>';
            echo '<p>Result: <strong>' . ($row['total'] ?? 0) . '</strong> pengajuan disetujui</p>';
            
            if (($row['total'] ?? 0) > 0) {
                echo '<div class="status ok">✓ Query approach A berhasil mendapat data</div>';
            } else {
                echo '<div class="status warning">⚠ Query approach A tidak mengembalikan data</div>';
            }
            $stmt->close();
        } else {
            echo '<div class="status error">✗ Prepare statement gagal: ' . htmlspecialchars($conn->error) . '</div>';
        }
        echo '</div>';

        echo '<div class="section"><h3>Approach B: Direct user match saja</h3>';
        $sql2 = "SELECT COUNT(*) AS total FROM pengajuan 
                 WHERE (user_id = ? OR created_by = ?)
                 AND (status = 'disetujui' OR status = 'Disetujui')";
        
        $stmt2 = $conn->prepare($sql2);
        if ($stmt2) {
            $stmt2->bind_param("ii", $user_id, $user_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $row2 = $result2->fetch_assoc();
            
            echo '<pre>SQL: ' . htmlspecialchars($sql2) . '</pre>';
            echo '<p>Result: <strong>' . ($row2['total'] ?? 0) . '</strong> pengajuan disetujui</p>';
            
            if (($row2['total'] ?? 0) > 0) {
                echo '<div class="status ok">✓ Query approach B berhasil mendapat data</div>';
            } else {
                echo '<div class="status warning">⚠ Query approach B tidak mengembalikan data</div>';
            }
            $stmt2->close();
        }
        echo '</div>';

        // ============ TEST 8: Show Sample Pengajuan Data ============
        echo "<h2>8. Sample Data Pengajuan (First 5 Disetujui)</h2>";
        $sample = $conn->query("SELECT id, judul, jenis_layanan, status, created_by, user_id, tanggal_pengajuan FROM pengajuan 
                               WHERE status = 'disetujui' OR status = 'Disetujui' 
                               LIMIT 5");
        
        if ($sample && $sample->num_rows > 0) {
            echo '<table><tr><th>ID</th><th>Judul</th><th>Jenis Layanan</th><th>Status</th><th>Created By</th><th>User ID</th><th>Tanggal</th></tr>';
            while ($row = $sample->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . (int)$row['id'] . '</td>';
                echo '<td>' . htmlspecialchars(substr($row['judul'] ?? '-', 0, 50)) . '</td>';
                echo '<td>' . htmlspecialchars($row['jenis_layanan'] ?? '-') . '</td>';
                echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                echo '<td>' . htmlspecialchars($row['created_by'] ?? 'NULL') . '</td>';
                echo '<td>' . htmlspecialchars($row['user_id'] ?? 'NULL') . '</td>';
                echo '<td>' . htmlspecialchars($row['tanggal_pengajuan'] ?? '-') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="status warning">⚠ Tidak ada pengajuan dengan status "disetujui"</div>';
        }

        // ============ TEST 9: Check if user has any pengajuan ============
        echo "<h2>9. Pengajuan untuk User ID: $user_id</h2>";
        
        $user_pengajuan = $conn->query("SELECT COUNT(*) as total FROM pengajuan 
                                       WHERE user_id = $user_id OR created_by = '$user_id'");
        if ($user_pengajuan) {
            $u_count = $user_pengajuan->fetch_assoc()['total'];
            echo '<p>User $user_id memiliki <strong>' . $u_count . '</strong> pengajuan (semua status)</p>';
        }

        $user_pengajuan_approved = $conn->query("SELECT COUNT(*) as total FROM pengajuan 
                                                WHERE (user_id = $user_id OR created_by = '$user_id')
                                                AND (status = 'disetujui' OR status = 'Disetujui')");
        if ($user_pengajuan_approved) {
            $u_approved = $user_pengajuan_approved->fetch_assoc()['total'];
            if ($u_approved > 0) {
                echo '<div class="status ok">✓ User memiliki <strong>' . $u_approved . '</strong> pengajuan DISETUJUI</div>';
            } else {
                echo '<div class="status warning">⚠ User TIDAK memiliki pengajuan yang disetujui. Mungkin perlu test data.</div>';
            }
        }

        ?>

        <h2>10. Kesimpulan & Rekomendasi</h2>
        <div class="section">
            <p>Berdasarkan diagnostic di atas, silakan:</p>
            <ol>
                <li>Pastikan ada data pengajuan dengan status 'disetujui'</li>
                <li>Pastikan user saat ini (ID: <?= $user_id ?>) terhubung ke pengajuan (via user_id, created_by, atau tugas_petugas)</li>
                <li>Jika masih tidak ada data, buat test data untuk laporan ini</li>
                <li>Akses laporan di: <code><a href="/netcare/petugas/dashboard_petugas.php?page=laporan_petugas" target="_blank">/netcare/petugas/dashboard_petugas.php?page=laporan_petugas</a></code></li>
            </ol>
        </div>

        <p style="text-align: center; margin-top: 40px; color: #999;">
            Generated at <?= date('Y-m-d H:i:s') ?> | Diagnostic Tool v1.0
        </p>
    </div>
</body>
</html>
