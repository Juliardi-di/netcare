<?php
require_once __DIR__ . '/../../config.php';

$keyword = $_GET['keyword'] ?? '';
$status  = $_GET['status'] ?? '';
$dari    = $_GET['dari'] ?? '';
$sampai  = $_GET['sampai'] ?? '';

// 🔒 Amankan input
$keyword = mysqli_real_escape_string($conn, $keyword);
$status  = mysqli_real_escape_string($conn, $status);
$dari    = mysqli_real_escape_string($conn, $dari);
$sampai  = mysqli_real_escape_string($conn, $sampai);
?>

<form method="GET" class="row g-2 mb-3">
    <input type="hidden" name="page" value="riwayat_pengajuan">

    <div class="col-md-3">
        <input type="text" name="keyword" class="form-control"
               placeholder="Cari nama pengajuan..."
               value="<?= htmlspecialchars($keyword) ?>">
    </div>

    <div class="col-md-2">
        <select name="status" class="form-control">
            <option value="">Semua Status</option>
            <option value="menunggu" <?= $status=='menunggu'?'selected':'' ?>>Menunggu</option>
            <option value="disetujui" <?= $status=='disetujui'?'selected':'' ?>>Disetujui</option>
            <option value="ditolak" <?= $status=='ditolak'?'selected':'' ?>>Ditolak</option>
            <option value="ditutup" <?= $status=='ditutup'?'selected':'' ?>>Pengaduan Ditutup</option>
        </select>
    </div>

    <div class="col-md-2">
        <input type="date" name="dari" class="form-control" value="<?= $dari ?>">
    </div>

    <div class="col-md-2">
        <input type="date" name="sampai" class="form-control" value="<?= $sampai ?>">
    </div>

    <div class="col-md-3">
        <button class="btn btn-primary">Filter</button>
        <a href="dashboard-kabid.php?page=riwayat_pengajuan" class="btn btn-secondary">Reset</a>
    </div>
</form>

<?php if(isset($_GET['hapus'])){ ?>

<div class="alert alert-success">
    Riwayat pengajuan berhasil dihapus
</div>
<?php } ?>

<?php

$where = "WHERE 1=1";

if($keyword != ''){
    $where .= " AND p.nama LIKE '%$keyword%'";
}
if($status != ''){
    $where .= " AND p.status = '$status'";
}
if($dari != ''){
    $where .= " AND DATE(p.tanggal_pengajuan) >= '$dari'";
}
if($sampai != ''){
    $where .= " AND DATE(p.tanggal_pengajuan) <= '$sampai'";
}

$query = mysqli_query($conn,"
SELECT *
FROM pengajuan p
$where
ORDER BY p.id DESC
");
?>

<h3>Riwayat Pengajuan</h3>

<table class="table table-bordered table-striped">
<thead style="background:#343a40;color:white">
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>Jenis Gangguan</th>
    <th>Status</th>
    <th>Tanggal</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
<?php $no=1; while($d=mysqli_fetch_assoc($query)) { ?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $d['nama'] ?></td>
    <td><?= $d['jenis_gangguan'] ?></td>
   <td>
<?php
if ($d['status'] == 'menunggu') {
    echo "<span class='badge bg-secondary'>Menunggu</span>";

} elseif ($d['status'] == 'disetujui') {
    echo "<span class='badge bg-info'>Disetujui</span>";

} elseif ($d['status'] == 'ditolak') {
    echo "<span class='badge bg-danger'>Ditolak</span>";

} elseif ($d['status'] == 'ditutup') {
    echo "<span class='badge bg-success'>✔ Pengaduan Ditutup</span>";

} else {
    echo $d['status'];
}
?>
</td>
    <td><?= date('d-m-Y', strtotime($d['tanggal_pengajuan'])) ?></td>
    <td>
        

    <a href="modules/hapus_pengajuan.php?id=<?= $d['id'] ?>"
       class="btn btn-danger btn-sm"
       onclick="return confirm('Yakin ingin menghapus riwayat ini?')">
        <i class="fa-solid fa-trash"></i> Hapus
    </a>

    </td>
</tr>
<?php } ?>
</tbody>
</table>