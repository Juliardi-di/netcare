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

if (cekTabel($conn, "pengajuan")) {
    $result = $conn->query("
        SELECT COUNT(DISTINCT jenis_layanan) AS total
        FROM pengajuan
        WHERE status = 'disetujui'
          AND status_admin_utama = 'diteruskan'
          AND deleted_at IS NULL
    ");

    if ($result) {
        $layanan = $result->fetch_assoc()['total'] ?? 0;
    } else {
        $layanan = 0;
    }
}

if (cekTabel($conn, "dokumentasi")) {
    $dokumen = $conn->query("SELECT COUNT(*) AS total FROM dokumentasi")
        ->fetch_assoc()['total'] ?? 0;
}

if (cekTabel($conn, "users")) {
    $userCount = $conn->query("SELECT COUNT(*) AS total FROM users")
        ->fetch_assoc()['total'] ?? 0;
}
$grafikBulanan = array_fill(1, 12, 0);

if (cekTabel($conn, "pengajuan")) {
    $sqlGrafik = "
        SELECT 
            MONTH(tanggal_pengajuan) AS bulan,
            COUNT(*) AS total
        FROM pengajuan
        WHERE deleted_at IS NULL
          AND status = 'disetujui'
        GROUP BY MONTH(tanggal_pengajuan)
        ORDER BY bulan
    ";

    $resGrafik = $conn->query($sqlGrafik);

    if ($resGrafik) {
        while ($row = $resGrafik->fetch_assoc()) {
            $grafikBulanan[(int)$row['bulan']] = (int)$row['total'];
        }
    }
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

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - netcare</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="../css/style.css">
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

    <div class="notif">
        <h3>🔔 Notifikasi Terbaru</h3>
        <ul>
            <li><?= $pengajuan ?> pengajuan layanan menunggu verifikasi.</li>
            <li><?= $dokumen ?> dokumentasi kegiatan sudah tersimpan.</li>
            <li><?= $userCount ?> pengguna aktif saat ini.</li>
        </ul>
    </div>
    <footer>
        &copy; <?php echo date("Y"); ?> Sistem  Layanan Government Video Conference dan Live Streaming Kabupaten Lingga.
    </footer>
    <script>
setInterval(function () {
    location.reload();
}, 15000);
</script>
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
            backgroundColor: '#198754',
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
                backgroundColor: '#198754',
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