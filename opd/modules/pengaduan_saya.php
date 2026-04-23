<?php
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    die("Session tidak valid.");
}

// ==========================
// QUERY DATA (TIDAK DIUBAH)
// ==========================
$stmt = $conn->prepare("
SELECT 
    p.id, 
    p.nama, 
    p.jenis_gangguan, 
    p.deskripsi, 
    p.status, 
    p.tanggal_pengajuan,

    GROUP_CONCAT(DISTINCT mp.nama SEPARATOR ', ') AS nama_petugas,

    pr.status_pekerjaan

FROM pengajuan p

LEFT JOIN tim_petugas tp 
    ON p.id = tp.pengajuan_id

LEFT JOIN master_petugas mp 
    ON tp.petugas_id = mp.id

LEFT JOIN progres_teknisi pr 
    ON pr.id = (
        SELECT id 
        FROM progres_teknisi 
        WHERE pengaduan_id = p.id 
        ORDER BY id DESC 
        LIMIT 1
    )

WHERE p.user_id = ?
GROUP BY p.id
ORDER BY p.id DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$data = $stmt->get_result();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{
    --main:#005477;
}

/* HEADER BAR */
.header-bar{
    background:var(--main);
    color:#fff;
    padding:12px 20px;
    font-weight:600;
    border-radius:12px 12px 0 0;
    box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

/* TABLE HEADER */
.table thead{
    background:var(--main) !important;
    color:white;
}

/* CARD */
.card{
    border:none;
    border-radius:12px;
}

/* BADGE */
.badge-main{
    background:var(--main);
    color:#fff;
}

.badge-status{
    padding:6px 10px;
    border-radius:8px;
    font-size:13px;
}

/* HOVER */
.table-hover tbody tr:hover{
    background:#f2f6f8;
}
</style>

<div class="container mt-4">

<div class="card shadow">

<!-- 🔥 HEADER GARIS -->
<div class="header-bar">
    📋 Pengaduan Saya
</div>

<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">

<thead>
<tr>
    <th>No</th>
    <th>Tanggal</th>
    <th>Nama</th>
    <th>Jenis Gangguan</th>
    <th>Deskripsi</th>
    <th>Tim Petugas</th>
    <th>Status Pengaduan</th>
    <th>Status Gangguan</th>
</tr>
</thead>

<tbody>

<?php if ($data->num_rows > 0): ?>
<?php $no=1; while($d = $data->fetch_assoc()): ?>
<tr>

<td><?= $no++ ?></td>

<td><?= date('d-m-Y', strtotime($d['tanggal_pengajuan'])) ?></td>

<td><?= htmlspecialchars($d['nama']) ?></td>

<td>
<?= ucwords(str_replace('_',' ', htmlspecialchars($d['jenis_gangguan']))) ?>
</td>

<td><?= htmlspecialchars($d['deskripsi']) ?></td>

<td>
<?= $d['nama_petugas'] 
    ? htmlspecialchars($d['nama_petugas']) 
    : '<span class="text-muted">Belum Ditentukan</span>' ?>
</td>

<!-- STATUS PENGADUAN -->
<td>
<?php
if ($d['status'] == 'menunggu') {
    echo "<span class='badge bg-warning text-dark badge-status'>Menunggu</span>";

} elseif ($d['status'] == 'disetujui') {
    echo "<span class='badge badge-main badge-status'>Disetujui</span>";

} elseif ($d['status'] == 'ditolak') {
    echo "<span class='badge bg-danger badge-status'>Ditolak</span>";

} elseif ($d['status'] == 'ditutup') {
    echo "<span class='badge bg-success badge-status'>✔ Ditutup</span>";

} else {
    echo htmlspecialchars($d['status']);
}
?>
</td>

<!-- STATUS GANGGUAN -->
<td>
<?php
$status = $d['status_pekerjaan'] ?? '';

if ($d['status'] == 'ditutup') {
    echo "<span class='badge bg-success badge-status'>✔ Sudah Diperbaiki</span>";

} elseif ($d['status'] == 'menunggu') {
    echo "<span class='badge bg-secondary badge-status'>Menunggu Persetujuan</span>";

} elseif ($status == 'dikerjakan') {
    echo "<span class='badge bg-warning text-dark badge-status'>Sedang Dikerjakan</span>";

} elseif ($status == 'menunggu_konfirmasi') {
    echo "<span class='badge bg-info text-dark badge-status'>Menunggu Konfirmasi</span>";

} else {
    echo "<span class='badge bg-secondary badge-status'>Belum Dikerjakan</span>";
}
?>
</td>

</tr>
<?php endwhile; ?>

<?php else: ?>

<tr>
<td colspan="8" class="text-center text-muted py-4">
    📭 Belum ada pengaduan <br>
    <small>Silakan buat pengaduan terlebih dahulu</small>
</td>
</tr>

<?php endif; ?>

</tbody>
</table>
</div>

</div>
</div>

</div>