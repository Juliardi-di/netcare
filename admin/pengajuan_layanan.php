<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset('utf8mb4');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === 'aksi_admin_utama') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $id = intval($_POST['id'] ?? 0);
        $catatan = trim($_POST['catatan'] ?? '');

        if ($id <= 0 || $catatan === '') {
            throw new Exception('Data tidak lengkap');
        }

        $conn->begin_transaction();

        // Ambil data pengajuan
        $q = $conn->prepare("
            SELECT status, status_admin_utama, jenis_gangguan AS jenis_gangguan, user_id
            FROM pengajuan
            WHERE id = ?
        ");
        $q->bind_param("i", $id);
        $q->execute();
        $q->bind_result($status, $status_admin_utama, $jenis_gangguan, $user_id);
        $q->fetch();
        $q->close();

        if ($status_admin_utama !== 'menunggu') {
            throw new Exception('Pengajuan sudah diproses Admin Utama');
        }

        // Update status_admin_utama dan catatan
        $u = $conn->prepare("
            UPDATE pengajuan
            SET 
                status_admin_utama = 'diteruskan',
                catatan_admin = ?
            WHERE id = ?
        ");
        $u->bind_param("si", $catatan, $id);
        $u->execute();

        if ($u->affected_rows === 0) {
            throw new Exception('Gagal memperbarui pengajuan');
        }
        $u->close();

        // Jika status = 'disetujui', lakukan penugasan petugas otomatis
        if ($status === 'disetujui') {
            assignTeamTugas($id, $jenis_gangguan);
        }

        $conn->commit();

        echo json_encode([
            'status'  => 'success',
            'message' => 'Pengajuan berhasil diteruskan ke petugas'
        ]);
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode([
            'status'  => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Fungsi untuk menugaskan petugas otomatis
function assignTeamTugas($pengajuan_id, $jenis_gangguan) {
    global $conn;
    
    $by = $_SESSION['user_id'] ?? 0;
    $picked = [];

    if ($jenis_gangguan === 'internet_mati') {
        // Ambil aturan tim untuk Zoom Meeting
        $ruleStmt = $conn->prepare("
            SELECT jumlah FROM aturan_tim WHERE jenis_gangguan = 'internet_mati'
        ");
        $ruleStmt->execute();
        $ruleStmt->bind_result($limit);
        $ruleStmt->fetch();
        $ruleStmt->close();
        
        if (empty($limit)) $limit = 2;

        // Tentukan role yang dibutuhkan untuk Zoom Meeting
        $roles = [
            'Koordinator Jaringan'
        ];

        foreach ($roles as $role_name) {
            $stmt = $conn->prepare("
                SELECT id
                FROM master_petugas
                WHERE jenis_gangguan = 'internet_mati'
                  AND aktif = 1
                  AND TRIM(REPLACE(jabatan, '  ', ' ')) = ?
                ORDER BY
                    last_assigned IS NOT NULL,
                    last_assigned ASC,
                    RAND()
                LIMIT ?
            ");
            $stmt->bind_param("si", $role_name, $limit);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res->num_rows === 0) {
                throw new Exception('Petugas Zoom Meeting tidak tersedia');
            }

            while ($row = $res->fetch_assoc()) {
                $picked[] = $row['id'];
            }
            $stmt->close();
        }
    }
    elseif ($jenis_gangguan === 'internet_lelet') {
        // Ambil aturan tim untuk Live Streaming
        $ruleStmt = $conn->prepare("
            SELECT jumlah FROM aturan_tim WHERE jenis_gangguan = 'internet_lelet'
        ");
        $ruleStmt->execute();
        $ruleStmt->bind_result($limit);
        $ruleStmt->fetch();
        $ruleStmt->close();
        
        if (empty($limit)) $limit = 10;

        $roles = [
            ['name' => 'Koordinator Jaringan',         'limit' => 1],
            ['name' => 'Teknisi Jaringan',            'limit' => 2],
        ];

        foreach ($roles as $r) {
            $stmt = $conn->prepare("
                SELECT id
                FROM master_petugas
                WHERE jenis_gangguan = 'internet_lelet'
                  AND aktif = 1
                  AND TRIM(REPLACE(jabatan, '  ', ' ')) = ?
                ORDER BY
                    last_assigned IS NOT NULL,
                    last_assigned ASC,
                    RAND()
                LIMIT ?
            ");
            $stmt->bind_param("si", $r['name'], $r['limit']);
            $stmt->execute();
            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()) {
                $picked[] = $row['id'];
            }
            $stmt->close();
        }

        // Tambah 2 Operator Live Streaming
        if (!empty($picked)) {
            $stmtOp = $conn->prepare("
                SELECT id
                FROM master_petugas
                WHERE jenis_gangguan = 'internet_lelet'
                  AND aktif = 1
                  AND TRIM(REPLACE(jabatan, '  ', ' ')) = 'Teknisi Jaringan'
                ORDER BY
                    last_assigned IS NOT NULL,
                    last_assigned ASC
                LIMIT 2
            ");
            $stmtOp->execute();
            $resOp = $stmtOp->get_result();

            while ($row = $resOp->fetch_assoc()) {
                $picked[] = $row['id'];
            }
            $stmtOp->close();
        }
    }
    else {
        throw new Exception('Jenis layanan tidak dikenal: ' . $jenis_gangguan);
    }

    // Hapus penugasan lama jika ada
    $delStmt = $conn->prepare("DELETE FROM tim_petugas WHERE pengajuan_id = ?");
    $delStmt->bind_param("i", $pengajuan_id);
    $delStmt->execute();
    $delStmt->close();

    // Masukkan penugasan baru
    foreach ($picked as $pid) {
        $stmtIns = $conn->prepare("
            INSERT INTO tim_petugas (pengajuan_id, petugas_id, ditentukan_oleh)
            VALUES (?, ?, ?)
        ");
        $stmtIns->bind_param("iii", $pengajuan_id, $pid, $by);
        $stmtIns->execute();
        $stmtIns->close();

        // Update last_assigned
        $stmtUpd = $conn->prepare("
            UPDATE master_petugas
            SET last_assigned = NOW()
            WHERE id = ?
        ");
        $stmtUpd->bind_param("i", $pid);
        $stmtUpd->execute();
        $stmtUpd->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['ajax'] ?? '') === 'tabel') {
    header('Content-Type: text/html; charset=utf-8');

    $limit = 5;
    $page  = max(1, intval($_GET['page'] ?? 1));
    $offset = ($page - 1) * $limit;

    // Hitung total data
    $countSql = "
        SELECT COUNT(*) as total
        FROM pengajuan p
        WHERE p.status_admin_utama IN ('menunggu', 'diteruskan')
          AND p.deleted_at IS NULL
          AND p.status IN ('disetujui','ditolak')
    ";
    $totalResult = $conn->query($countSql);
    $totalRow = $totalResult->fetch_assoc();
    $totalData = $totalRow['total'];
    $totalPage = ceil($totalData / $limit);

    $sql = "
SELECT 
    p.id,
    p.nama AS judul,
    p.jenis_gangguan AS jenis_gangguan,
    p.tanggal_pelaksanaan,
        p.status,
        p.status_admin_utama,
        u.nama_instansi
    FROM pengajuan p
        JOIN users u ON p.user_id = u.id
        WHERE p.status_admin_utama IN ('menunggu', 'diteruskan')
          AND p.deleted_at IS NULL
          AND p.status IN ('disetujui','ditolak')
        ORDER BY 
            CASE WHEN p.status_admin_utama = 'menunggu' THEN 0 ELSE 1 END,
            p.tanggal_pengajuan DESC
        LIMIT $limit OFFSET $offset
    ";

    $res = $conn->query($sql);

    if ($res->num_rows === 0) {
        echo "<tr><td colspan='7' style='text-align:center;color:#999;padding:20px'>📭 Tidak ada pengajuan</td></tr>";
        exit;
    }

    $no = $offset + 1;


    $no = 1;
    while ($r = $res->fetch_assoc()) {
        $is_diteruskan = ($r['status_admin_utama'] === 'diteruskan');

        if ($r['status'] === 'ditolak') {
            $statusLabel = "<span style='color:red;font-weight:bold'>❌ DITOLAK KABID</span>";
            if ($is_diteruskan) {
                $aksi = "<span style='color:#666;font-size:12px'>✓ Sudah diteruskan ke OPD</span>";
                $rowStyle = "background:#f0f0f0;opacity:0.7";
            } else {
                $aksi = "
                    <button style='background:#dc3545'
                        onclick=\"aksi({$r['id']}, 'Perbaiki sesuai catatan Kabid')\">
                        🔄 Teruskan Perbaikan ke OPD
                    </button>
                ";
                $rowStyle = "";
            }
        }
        elseif ($r['status'] === 'disetujui') {
            $statusLabel = "
                <span style='color:green;font-weight:bold'>
                    ✅ DISETUJUI KABID
                </span>
            ";
            if ($is_diteruskan) {
                $aksi = "<span style='color:green;font-size:12px'>✓ Sudah diteruskan ke Petugas</span>";
                $rowStyle = "background:#f0f0f0;opacity:0.7";
            } else {
                $aksi = "
                    <button style='background:#198754'
                        onclick=\"aksi({$r['id']}, 'Silahkan Laksanakan Tugas, Tetap Sehat dan Tetap Semangat!!!')\">
                        ➡️ Teruskan ke Petugas
                    </button>
                ";
                $rowStyle = "";
            }
        }
        else {
            continue;
        }

        $tglPel = $r['tanggal_pelaksanaan']
            ? date('d-m-Y', strtotime($r['tanggal_pelaksanaan']))
            : '-';

        echo "
        <tr style='{$rowStyle}'>
            <td>{$no}</td>
            <td>".htmlspecialchars($r['judul'])."</td>
            <td>{$r['jenis_gangguan']}</td>
            <td>{$tglPel}</td>
            <td>".htmlspecialchars($r['nama_instansi'])."</td>
            <td>{$statusLabel}</td>
            <td>{$aksi}</td>
        </tr>
        ";
        $no++;
    }
        echo "
    <tr>
        <td colspan='7' style='text-align:center;padding:15px'>
    ";

    if ($page > 1) {
        echo "<button onclick='loadTabel(".($page-1).")'>« Sebelumnya</button> ";
    }

    echo "<span style='margin:0 10px'>Halaman $page dari $totalPage</span>";

    if ($page < $totalPage) {
        echo " <button onclick='loadTabel(".($page+1).")'>Selanjutnya »</button>";
    }

    echo "
        </td>
    </tr>
    ";


    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin Utama – Penerus Keputusan Kabid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
<h2>ADMIN UTAMA – PENERUS TUGAS KE PETUGAS DAN OPD</h2>

<table>
<thead>
<tr>
    <th>No</th>
    <th>Judul Kegiatan</th>
    <th>Jenis Layanan</th>
    <th>Tanggal Pelaksanaan</th>
    <th>OPD Pengaju</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody id="dataTabel">
<tr><td colspan="7" class="no-data">⏳ Memuat data...</td></tr>
</tbody>
</table>
</div>

<script>
const ajaxURL = 'admin/pengajuan_layanan.php';

let currentPage = 1;

function loadTabel(page = 1){
    currentPage = page;

    fetch(ajaxURL + '?ajax=tabel&page=' + page)
        .then(r => {
            if (!r.ok) throw new Error('Gagal memuat data');
            return r.text();
        })
        .then(h => document.getElementById('dataTabel').innerHTML = h)
        .catch(e => {
            document.getElementById('dataTabel').innerHTML =
                `<tr><td colspan="7" class="no-data" style="color:red">❌ ${e.message}</td></tr>`;
        });
}


function aksi(id, defaultMsg){
    const catatan = prompt('📝 Masukkan pesan untuk Petugas/OPD:', defaultMsg);
    if(!catatan) return;

    const fd = new FormData();
    fd.append('ajax','aksi_admin_utama');
    fd.append('id',id);
    fd.append('catatan',catatan);

    fetch(ajaxURL, {method:'POST', body:fd})
        .then(r => r.json())
        .then(j => {
            alert(j.message);
            if(j.status === 'success') {
                loadTabel();
            }
        })
        .catch(e => alert('❌ Error: ' + e.message));
}

loadTabel();

setInterval(() => loadTabel(currentPage), 30000);
</script>
</body>
</html>

