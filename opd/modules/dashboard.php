<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    exit('Akses ditolak');
}

$user_id = (int) $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "netcare");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// ==========================
// HITUNG DATA
// ==========================
$pengajuan = $menunggu = $diproses = $selesai = 0;

// TOTAL
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pengajuan WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pengajuan = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// MENUNGGU
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pengajuan WHERE user_id=? AND status='menunggu'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$menunggu = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// DIPROSES
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pengajuan WHERE user_id=? AND status='disetujui'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$diproses = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// SELESAI
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM pengajuan WHERE user_id=? AND status='ditutup'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$selesai = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// ==========================
// DATA KALENDER
// ==========================
$kalender = [];

$sql = "
SELECT
    p.id,
    p.nama,
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
WHERE p.user_id = ?
ORDER BY p.tanggal_pengajuan DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $kalender[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard OPD - Netcare</title>

<style>
body{font-family:'Segoe UI';background:#f4f8f6;margin:0}
h1{text-align:center;padding:20px;color:#005477}

.dashboard{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:20px;
padding:20px;
}
.card{
background:#fff;
border-radius:12px;
padding:20px;
text-align:center;
box-shadow:0 4px 10px rgba(0,0,0,.08);
}
.card h2{font-size:36px;margin:0}

.total{color:#005477}
.wait{color:#f39c12}
.proses{color:#3498db}
.done{color:#2ecc71}

.box{
background:#fff;
margin:20px;
padding:20px;
border-radius:12px;
box-shadow:0 2px 8px rgba(0,0,0,.08);
}

table{width:100%;border-collapse:collapse}
th,td{padding:10px;border-bottom:1px solid #eee}
th{background:#005477;
    color:white;}

.badge{
padding:5px 10px;
border-radius:6px;
font-size:12px;
font-weight:bold;
}
.badge.wait{background:#fff3cd;color:#856404}
.badge.ok{background:#d4edda;color:#155724}
.badge.reject{background:#f8d7da;color:#721c24}

.btn{
background:#005477;
color:white;
border:none;
padding:6px 10px;
border-radius:6px;
cursor:pointer;
}

.btn:hover{
    background:#003f5c;
}

.tim-box{
margin-top:10px;
padding:10px;
border:1px solid #ddd;
border-radius:8px;
background:#f9f9f9;
}
.card{
    border-top:4px solid #005477;
}
</style>
</head>

<body>

<h1>📊 Dashboard OPD NETCARE</h1>

<!-- ================= CARD ================= -->
<div class="dashboard">

<div class="card">
<h2 class="total"><?= $pengajuan ?></h2>
<p>Total Pengajuan</p>
</div>

<div class="card">
<h2 class="wait"><?= $menunggu ?></h2>
<p>Menunggu</p>
</div>

<div class="card">
<h2 class="proses"><?= $diproses ?></h2>
<p>Diproses</p>
</div>

<div class="card">
<h2 class="done"><?= $selesai ?></h2>
<p>Selesai</p>
</div>

</div>

<!-- ================= TABLE ================= -->
<div class="box">
<h3>📅 Riwayat Pengajuan</h3>

<table>
<thead>
<tr>
<th>Tanggal</th>
<th>Pengajuan</th>
<th>Status</th>
<th>Tim</th>
</tr>
</thead>

<tbody>
<?php if(empty($kalender)): ?>
<tr><td colspan="4" align="center">Belum ada data</td></tr>

<?php else: foreach($kalender as $k): ?>
<tr>

<td><?= date('d-m-Y', strtotime($k['tanggal_pengajuan'])) ?></td>
<td><?= htmlspecialchars($k['nama']) ?></td>

<td>
<?php
if ($k['status']=='menunggu') {
    echo "<span class='badge wait'>Menunggu</span>";
} elseif ($k['status']=='disetujui') {
    echo "<span class='badge ok'>Diproses</span>";
} elseif ($k['status']=='ditolak') {
    echo "<span class='badge reject'>Ditolak</span>";
} elseif ($k['status']=='ditutup') {
    echo "<span class='badge ok'>✔ Selesai</span>";
}
?>
</td>

<td>
<?php if ($k['tim_bertugas']): ?>
<button class="btn" onclick="toggleTim(<?= $k['id'] ?>)">Lihat Tim</button>

<div id="tim-<?= $k['id'] ?>" class="tim-box" style="display:none">
<?php
$groups=[];
foreach(explode('##',$k['tim_bertugas']) as $t){
    [$j,$n]=explode('||',$t);
    $groups[$j][]=$n;
}
foreach($groups as $j=>$a){
    echo "<b>$j</b><ul>";
    foreach($a as $x){
        echo "<li>".htmlspecialchars($x)."</li>";
    }
    echo "</ul>";
}
?>
</div>

<?php else: ?>
<span style="color:gray">Tim belum ditugaskan</span>
<?php endif ?>
</td>

</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<!-- ================= NOTIF ================= -->
<div class="box">
<h3>🔔 Notifikasi</h3>
<ul>
<li><?= $menunggu ?> Pengajuan menunggu persetujuan</li>
<li><?= $diproses ?> Sedang diproses</li>
<li><?= $selesai ?> Telah selesai</li>
</ul>
</div>

<script>
function toggleTim(id){
    const el=document.getElementById('tim-'+id);
    el.style.display=(el.style.display==='none')?'block':'none';
}
</script>

</body>
</html>