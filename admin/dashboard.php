<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "netcare";
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

function cekTabel($conn, $tabel)
{
    $result = $conn->query("SHOW TABLES LIKE '$tabel'");
    return $result && $result->num_rows > 0;
}

$pengajuan = $layanan = $dokumen = $userCount = 0;

if (cekTabel($conn, "pengajuan")) {
    $pengajuan = $conn->query("SELECT COUNT(*) AS total FROM pengajuan")
        ->fetch_assoc()['total'] ?? 0;
}
if (cekTabel($conn, "dokumentasi")) {
    $dokumen = $conn->query("SELECT COUNT(*) AS total FROM dokumentasi
    ")->fetch_assoc()['total'] ?? 0;
}
if (cekTabel($conn, "users")) {
    $userCount = $conn->query("SELECT COUNT(*) AS total FROM users
    ")->fetch_assoc()['total'] ?? 0;
}

$kalender = [];

$role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$limit = 5;
$p  = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$p  = max(1, $p);
$offset = ($p - 1) * $limit;

$countSql = "SELECT COUNT(*) as total FROM pengajuan p WHERE p.deleted_at IS NULL";
if ($role !== 'admin_utama') {
    $countSql .= " AND p.user_id = $user_id";
}
$totalData = $conn->query($countSql)->fetch_assoc()['total'] ?? 0;
$totalPage = ceil($totalData / $limit);
if ($p > $totalPage && $totalPage > 0) {
    $p = $totalPage;
    $offset = ($p - 1) * $limit;
}




$sql = "
SELECT
    p.id,
    p.nama AS judul,
    p.tanggal_pengajuan,
    p.status,
    (
        SELECT GROUP_CONCAT(
            CONCAT(mp.jabatan, '||', mp.nama)
            SEPARATOR '##'
        )
        FROM tim_petugas tp
        JOIN master_petugas mp ON tp.petugas_id = mp.id
        WHERE tp.pengajuan_id = p.id
    ) AS tim_bertugas
FROM pengajuan p
WHERE p.deleted_at IS NULL
";

$params = [];
$types  = "";

if ($role !== 'admin_utama') {
    $sql .= " AND p.user_id = ?";
    $params[] = $user_id;
    $types .= "i";
}

$sql .= " ORDER BY p.tanggal_pengajuan ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare gagal: " . $conn->error);
}

$stmt->bind_param($types, ...$params);

if (!$stmt->execute()) {
    die("Execute gagal: " . $stmt->error);
}

$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $kalender[] = $row;
}
    $self = basename($_SERVER['PHP_SELF']);
$grafikBulanan = array_fill(1, 12, 0);

$sqlGrafik = "
SELECT 
    MONTH(tanggal_pengajuan) AS bulan,
    COUNT(*) AS total
FROM pengajuan
WHERE deleted_at IS NULL
AND status = 'disetujui'
";

if ($role !== 'admin_utama') {
    $sqlGrafik .= " AND user_id = $user_id ";
}

$sqlGrafik .= "
GROUP BY MONTH(tanggal_pengajuan)
ORDER BY bulan
";

$resGrafik = $conn->query($sqlGrafik);
while ($row = $resGrafik->fetch_assoc()) {
    $grafikBulanan[(int)$row['bulan']] = (int)$row['total'];
}


$grafikDokumentasi = array_fill(1, 12, 0);

if (cekTabel($conn, "dokumentasi")) {
    $sqlDok = "
        SELECT 
            MONTH(tanggal_upload) AS bulan,
            COUNT(*) AS total
        FROM dokumentasi
        GROUP BY MONTH(tanggal_upload)
        ORDER BY bulan
    ";

    $resDok = $conn->query($sqlDok);
    if ($resDok) {
        while ($row = $resDok->fetch_assoc()) {
            $grafikDokumentasi[(int)$row['bulan']] = (int)$row['total'];
        }
    }
}


$labelUser = [];
$dataUser  = [];

if (cekTabel($conn, "users")) {
    $sqlUser = "
        SELECT nama_instansi AS opd, COUNT(*) AS total
        FROM users
        WHERE nama_instansi IS NOT NULL
              AND nama_instansi <> ''
        GROUP BY nama_instansi
        ORDER BY total DESC
    ";

    $resUser = $conn->query($sqlUser);
    if ($resUser) {
        while ($row = $resUser->fetch_assoc()) {
            $labelUser[] = $row['opd'];
            $dataUser[]  = (int)$row['total'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin Utama</title>
    
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="css/style.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 
</head>

<body>
    <h1 style="text-align:center; width:100%;"> DASHBOARD</h1>

    <div class="dashboard-wrapper">

    <div class="dashboard">
        <div class="card">
            <h2><?= $pengajuan ?></h2>
            <p>Total Pengajuan</p>
        </div>

        <div class="card">
            <h2><?= $dokumen ?></h2>
            <p>Dokumentasi</p>
        </div>

        <div class="card">
            <h2><?= $userCount ?></h2>
            <p>Pengguna Terdaftar</p>
        </div>
    </div>

    <!-- GRAFIK FULL WIDTH -->
<div style="display:flex; gap:10px;">
    <select id="pilihGrafik"
        style="padding:6px 10px;border-radius:6px;border:1px solid #ccc">
        <option value="pengajuan">Pengajuan Disetujui</option>
        <option value="dokumentasi">Dokumentasi</option>
    </select>

    <select id="chartType"
        style="padding:6px 10px;border-radius:6px;border:1px solid #ccc">
        <option value="bar">Bar Chart</option>
        <option value="line">Line Chart</option>
    </select>
</div>
<div style="width:100%; margin-top:10px;">
    <canvas id="grafikPengajuan" height="90"></canvas>
</div>

    </div>
<div class="notif">
<h3>📅 KALENDER PENGAJUAN LAYANAN</h3>
<table>
<thead>
<tr>
    <th>Tanggal</th>
    <th>Judul Pengajuan</th>
    <th>Status</th>
    <th>Tim Bertugas</th>
</tr>
</thead>
<tbody>
<?php if (empty($kalender)): ?>
<tr>
    <td colspan="4" style="text-align:center;padding:10px">
        Belum ada pengajuan
    </td>
</tr>
<?php else: ?>
<?php foreach ($kalender as $k): ?>
<tr style="border-bottom:1px solid #ddd">
    <td style="padding:8px">
        <?= date('d-m-Y', strtotime($k['tanggal_pengajuan'])) ?>
    </td>

    <td><?= htmlspecialchars($k['judul']) ?></td>

    <td>
        <?php
        if ($k['status'] === 'disetujui') {
            echo '<span style="color:green;font-weight:bold">Terjadwal</span>';
        } elseif ($k['status'] === 'ditolak') {
            echo '<span style="color:red;font-weight:bold">Ditolak Kabid</span>';
        } else {
            echo '<span style="color:orange;font-weight:bold">Menunggu Persetujuan Kabid</span>';
        }
        ?>
    </td>

    <td>
        <?php if (!empty($k['tim_bertugas'])): ?>
            <button class="btn-tim" onclick="toggleTim(<?= $k['id'] ?>)">
                👥 Lihat Tim
            </button>

            <div id="tim-<?= $k['id'] ?>" class="tim-box" style="display:none">
                <?php
                $groups = [];
                foreach (explode('##', $k['tim_bertugas']) as $t) {
                    [$jabatan, $nama] = explode('||', $t);
                    $groups[$jabatan][] = $nama;
                }

                foreach ($groups as $jabatan => $anggota) {
                    echo "<strong>$jabatan</strong><ol>";
                    foreach ($anggota as $a) {
                        echo "<li>".htmlspecialchars($a)."</li>";
                    }
                    echo "</ol>";
                }
                ?>
            </div>
        <?php else: ?>
            <em style="color:#999">Menunggu penugasan Kabid</em>
        <?php endif ?>
    </td>
</tr>
<?php endforeach ?>
<?php endif ?>
</tbody>
</table>

<?php if ($totalPage > 1): ?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:15px;align-items:center">

<a href="<?= $self ?>?p=<?= max(1, $p-1) ?>"
   style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;text-decoration:none">
   ‹
</a>

    <?php for ($i = 1; $i <= $totalPage; $i++): ?>
        <a href="<?= $self ?>?p=<?= $i ?>"
           style="
           padding:6px 12px;
           border-radius:6px;
           border:1px solid #ddd;
           text-decoration:none;
           <?= $i == $p ? 'background:#005477;color:#fff;font-weight:bold' : '' ?>
           ">
           <?= $i ?>
        </a>
    <?php endfor; ?>

    <a href="<?= $self ?>?p=<?= min($totalPage, $p+1) ?>"
    style="padding:6px 10px;border:1px solid #ddd;border-radius:6px;text-decoration:none">
       ›
    </a>

    <span style="margin-left:10px;color:#555;font-size:13px">
        dari <?= $totalPage ?>
    </span>
</div>
<?php endif; ?>


</script>
<script>
function toggleTim(id){
    const box = document.getElementById('tim-'+id);
    const btn = event.target;

    if (box.style.display === 'none') {
        box.style.display = 'block';
        btn.innerText = '🤵🏻 Sembunyikan Tim';
    } else {
        box.style.display = 'none';
        btn.innerText = '👥 Lihat Tim';
    }
}
</script>
</div>

    <div class="notif">
        <h3>🔔 Notifikasi Terbaru</h3>
        <ul>
            <li><?= $pengajuan ?> pengajuan layanan menunggu verifikasi.</li>
            <li><?= $dokumen ?> dokumentasi kegiatan sudah tersimpan.</li>
            <li><?= $userCount ?> pengguna aktif saat ini.</li>
        </ul>
    </div>
    <footer>
        &copy; <?php echo date("Y"); ?> Sistem Layanan Government Video Conference dan Live Streaming Kabupaten Lingga
    </footer>
<script>
const ctx = document.getElementById('grafikPengajuan').getContext('2d');

const grafikData = {
    pengajuan: <?= json_encode(array_values($grafikBulanan)) ?>,
    dokumentasi: <?= json_encode(array_values($grafikDokumentasi)) ?>
};

const labelGrafik = {
    pengajuan: 'Pengajuan Disetujui',
    dokumentasi: 'Dokumentasi',
};

let currentType = 'bar';
let currentData = 'pengajuan';

let chart = new Chart(ctx, {
    type: currentType,
    data: {
        labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
        datasets: [{
            label: labelGrafik[currentData],
            data: grafikData[currentData],
            backgroundColor: '#005477',
            borderRadius: 8,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { precision: 0 }
            }
        }
    }
});

function updateChart() {
    chart.destroy();

    chart = new Chart(ctx, {
        type: currentType,
        data: {
            labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
            datasets: [{
                label: labelGrafik[currentData],
                data: grafikData[currentData],
                backgroundColor: '#005477',
                borderRadius: 8,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
}

document.getElementById('pilihGrafik').addEventListener('change', function () {
    currentData = this.value;
    updateChart();
});

document.getElementById('chartType').addEventListener('change', function () {
    currentType = this.value;
    updateChart();
});
</script>



</body>

</html>