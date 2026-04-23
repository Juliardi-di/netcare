<?php
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    die("Session tidak valid.");
}

$pengaduan_id = (int) ($_GET['id'] ?? 0);

// ==========================
// LIST DROPDOWN
// ==========================
$list = $conn->prepare("
    SELECT id, nama, tanggal_pengajuan 
    FROM pengajuan 
    WHERE user_id = ?
    ORDER BY id DESC
");
$list->bind_param("i", $user_id);
$list->execute();
$daftar = $list->get_result();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{
    --main:#005477;
    --main-dark:#003b4a;
}

/* HEADER */
.header-box{
    background:var(--main);
    color:#fff;
    padding:12px 20px;
    border-radius:10px 10px 0 0;
    font-weight:bold;
}

/* CARD */
.card-custom{
    border:none;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
    margin-bottom:20px;
}

/* STATUS BOX */
.status-box{
    background:#f0f7fa;
    border-left:6px solid var(--main);
    padding:15px;
    border-radius:8px;
}

/* INFO BOX */
.info-box{
    background:#eef6f8;
    border-left:5px solid var(--main);
    padding:15px;
    border-radius:8px;
}

/* DROPDOWN */
select{
    border-radius:8px;
    border:1px solid #ccc;
}

/* TEXT COLOR */
.text-main{
    color:var(--main);
    font-weight:bold;
}
</style>

<div class="container mt-4">

<div class="header-box">
    📊 Riwayat & Status Pengajuan
</div>

<div class="card card-custom">
<div class="card-body">

<!-- DROPDOWN -->
<form method="GET" class="mb-3">
    <input type="hidden" name="page" value="riwayat_pengajuan">

    <label class="mb-1"><b>Pilih Pengaduan:</b></label>

    <select name="id" onchange="this.form.submit()" class="form-control" style="max-width:400px;">
        <option value="">-- Pilih Pengaduan --</option>

        <?php while($row = $daftar->fetch_assoc()): ?>
            <option value="<?= $row['id'] ?>" 
                <?= ($row['id'] == $pengaduan_id) ? 'selected' : '' ?>>
                
                <?= htmlspecialchars($row['nama']) ?> 
                | <?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])) ?>

            </option>
        <?php endwhile; ?>
    </select>
</form>

<?php
if (!$pengaduan_id) {
    echo "<p class='text-muted'>Silakan pilih pengaduan terlebih dahulu.</p>";
    return;
}

// ==========================
// DETAIL
// ==========================
$stmt = $conn->prepare("
SELECT 
    p.*, 
    GROUP_CONCAT(DISTINCT mp.nama SEPARATOR ', ') AS tim
FROM pengajuan p
LEFT JOIN tim_petugas tp ON p.id = tp.pengajuan_id
LEFT JOIN master_petugas mp ON tp.petugas_id = mp.id
WHERE p.id = ? AND p.user_id = ?
GROUP BY p.id
");

$stmt->bind_param("ii", $pengaduan_id, $user_id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// ==========================
// PROGRES TERAKHIR
// ==========================
$prog = $conn->prepare("
SELECT pt.*, mp.nama 
FROM progres_teknisi pt
LEFT JOIN master_petugas mp ON pt.petugas_id = mp.id
WHERE pt.pengaduan_id = ?
ORDER BY pt.id DESC
LIMIT 1
");

$prog->bind_param("i", $pengaduan_id);
$prog->execute();
$riwayat = $prog->get_result();

if (!$data) {
    echo "<p>Data tidak ditemukan.</p>";
    return;
}

// ==========================
// STATUS
// ==========================
$status = $data['status'];

if ($status == 'menunggu') {
    $label = "Menunggu Persetujuan";
    $warna = "orange";

} elseif ($status == 'disetujui') {
    $label = "Disetujui & Diproses";
    $warna = "#005477";

} elseif ($status == 'ditutup') {
    $label = "✔ Gangguan Telah Diperbaiki";
    $warna = "green";

} elseif ($status == 'ditolak') {
    $label = "Ditolak";
    $warna = "red";

} else {
    $label = $status;
    $warna = "gray";
}
?>

<!-- STATUS -->
<div class="status-box mb-3">
    <h4 style="color:<?= $warna ?>"><?= $label ?></h4>
</div>

<!-- INFO -->
<div class="info-box mb-3">
    <b>Nama:</b> <?= htmlspecialchars($data['nama']) ?><br>
    <b>Jenis:</b> <?= ucwords(str_replace('_',' ', $data['jenis_gangguan'])) ?><br>
    <b>Deskripsi:</b> <?= htmlspecialchars($data['deskripsi']) ?><br>
    <b>Tanggal:</b> <?= date('d-m-Y', strtotime($data['tanggal_pengajuan'])) ?>
</div>

<!-- TIM -->
<div class="mb-3">
    <b class="text-main">👨‍🔧 Tim Petugas:</b><br>
    <?= $data['tim'] ? $data['tim'] : '<span class="text-muted">Belum ditentukan</span>' ?>
</div>

<!-- PROGRES -->
<h5 class="text-main">📊 Progres Teknisi Terakhir</h5>

<?php if ($riwayat->num_rows > 0): ?>

<?php $d = $riwayat->fetch_assoc(); ?>

<div class="card card-custom">
<div class="card-body">

<b>Petugas:</b> <?= htmlspecialchars($d['nama']) ?><br>

<b>Status:</b> 
<?php
if ($data['status'] == 'ditutup') {
    echo "<span style='color:green'>✔ Sudah Diperbaiki</span>";

} elseif ($d['status_pekerjaan'] == 'dikerjakan') {
    echo "<span style='color:orange'>Sedang Dikerjakan</span>";

} elseif ($d['status_pekerjaan'] == 'menunggu_konfirmasi') {
    echo "<span style='color:#005477'>Menunggu Konfirmasi</span>";

} else {
    echo "<span class='text-muted'>Belum Dikerjakan</span>";
}
?>
<br>

<b>Analisis:</b> <?= htmlspecialchars($d['analisis']) ?><br>
<b>Tindakan:</b> <?= htmlspecialchars($d['tindakan']) ?><br>

<?php 
$file = "../uploads/" . $d['foto'];
if (!empty($d['foto']) && file_exists($file)): ?>
    <br><img src="<?= $file ?>" width="120">
<?php endif; ?>

<br><small class="text-muted"><?= $d['created_at'] ?? '' ?></small>

</div>
</div>

<?php else: ?>
<p class="text-muted">Belum ada progres.</p>
<?php endif; ?>

</div>
</div>

</div>