<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    exit('Akses ditolak');
}

$user_id = (int) $_SESSION['user_id'];

require_once __DIR__ . '/../../config.php';

// Ambil nama petugas login untuk highlight
$stmt = $conn->prepare("SELECT nama FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$nama_login = $stmt->get_result()->fetch_assoc()['nama'] ?? '';

$pengajuan = $dokumen = $userCount = 0;

// Hitung Total Pengajuan (Tugas)
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT p.id) AS total
    FROM pengajuan p
    JOIN tim_petugas tp ON p.id = tp.pengajuan_id
    JOIN master_petugas mp ON tp.petugas_id = mp.id
    WHERE mp.nama = (SELECT nama FROM users WHERE id = ?)
      AND p.status = 'disetujui'
      AND p.deleted_at IS NULL
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pengajuan = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

// Hitung Total Dokumentasi
$stmt = $conn->prepare("
    SELECT COUNT(d.id) AS total
    FROM dokumentasi d
    JOIN pengajuan p ON d.pengajuan_id = p.id
    JOIN tim_petugas tp ON p.id = tp.pengajuan_id
    JOIN master_petugas mp ON tp.petugas_id = mp.id
    WHERE mp.nama = (SELECT nama FROM users WHERE id = ?)
      AND p.status = 'disetujui'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$dokumen = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

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
JOIN tim_petugas tp ON p.id = tp.pengajuan_id
JOIN master_petugas mp ON tp.petugas_id = mp.id
WHERE p.deleted_at IS NULL
  AND mp.nama = (SELECT nama FROM users WHERE id = ?)
  AND p.status = 'disetujui'
ORDER BY p.tanggal_pengajuan ASC
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
<title>Dashboard Petugas - netcare</title>
<style>
body{
    font-family:'Segoe UI',sans-serif;
    background:#f4f8f6;
    margin:0;
    color:#2c3e50
}
h1{
    text-align:center;
    margin:20px 0;
    color:#157347
}
.btn-tim{
    background:#198754;
    color:#fff;
    border:none;
    padding:6px 10px;
    border-radius:6px;
    cursor:pointer;
    font-size:13px;
}
.btn-tim:hover{
    background:#157347;
}
.tim-box{
    margin-top:8px;
    background:#f8f9fa;
    padding:10px;
    border-radius:8px;
    border:1px solid #ddd;
}
.tim-box ol{
    margin:4px 0 10px 18px;
}
.dashboard{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
    padding:20px
}
.card{
    background:#fff;
    border-radius:12px;
    padding:25px;
    text-align:center;
    box-shadow:0 4px 10px rgba(0,0,0,.08)
}
.card h2{
    font-size:42px;
    margin:0;
    color:#157347
}
.notif{
    background:#fff;
    margin:20px;
    padding:20px;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,.08)
}
table{
    width:100%;
    border-collapse:collapse
}
th,td{
    padding:8px;
    border-bottom:1px solid #ddd;
    text-align:left
}
th{
    background:#e9f5ee
}
footer{
    text-align:center;
    padding:15px;
    font-size:13px;
    color:#666
}
</style>
</head>
<body>

<h1>📊 Dashboard Petugas netcare</h1>

<div class="dashboard">
    <div class="card">
        <h2><?= $pengajuan ?></h2>
        <p>Total Pengajuan</p>
    </div>
    <div class="card">
        <h2><?= $dokumen ?></h2>
        <p>Dokumentasi</p>
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

    <td><?= htmlspecialchars($k['nama']) ?></td>

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
                        if ($a === $nama_login) {
                            echo "<li><b>".htmlspecialchars($a)."</b></li>";
                        } else {
                            echo "<li>".htmlspecialchars($a)."</li>";
                        }
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
</div>

<div class="notif">
<h3>🔔 Notifikasi</h3>
<ul>
    <li><?= $pengajuan ?> pengajuan tercatat</li>
    <li><?= $dokumen ?> dokumentasi tersimpan</li>
</ul>
</div>

<footer>
&copy; <?= date('Y') ?> Sistem Layanan Government Video Conference dan Live Streaming Kabupaten Lingga.
</footer>

<script>
setInterval(()=>location.reload(),15000);
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
</body>
</html>