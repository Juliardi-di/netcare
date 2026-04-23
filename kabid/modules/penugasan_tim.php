<?php
require_once __DIR__ . '/../../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

/* =========================
   PROSES ASSIGN PETUGAS
========================= */
if (isset($_POST['assign'])) {

    if (!isset($_POST['petugas_id']) || empty($_POST['petugas_id'])) {
        echo "<script>alert('Pilih minimal 1 petugas!');history.back();</script>";
        exit;
    }

    $pengajuan_id = $_POST['pengajuan_id'];
    $kabid_id     = $_SESSION['user_id'];
    $petugas_list = $_POST['petugas_id'];

    // Hapus tim lama
    $stmtDel = $conn->prepare("DELETE FROM tim_petugas WHERE pengajuan_id = ?");
$stmtDel->bind_param("i", $pengajuan_id);
$stmtDel->execute();

    // Insert tim baru
    $stmt = $conn->prepare("
        INSERT INTO tim_petugas (pengajuan_id, petugas_id, ditentukan_oleh)
        VALUES (?,?,?)
    ");

    foreach ($petugas_list as $petugas_id) {
        $stmt->bind_param("iii", $pengajuan_id, $petugas_id, $kabid_id);
        $stmt->execute();
    }

    echo "<script>
        alert('Tim berhasil ditugaskan!');
        location.href='/netcare/kabid/dashboard-kabid.php?page=penugasan_tim';
    </script>";
}

/* =========================
   AMBIL DATA PENGAJUAN
========================= */
$qPengajuan = $conn->query("
SELECT 
    p.*,
    u.nama_instansi,
    COUNT(t.id) AS sudah_ada_tim
FROM pengajuan p
JOIN users u ON p.user_id = u.id
LEFT JOIN tim_petugas t ON p.id = t.pengajuan_id
WHERE p.status='disetujui'
GROUP BY p.id
ORDER BY p.id DESC
");

/* =========================
   AMBIL DATA PETUGAS
========================= */
$dataPetugas = [];
$qPetugas = $conn->query("
    SELECT id, nama, jabatan
    FROM master_petugas
    WHERE aktif = 1
    ORDER BY jabatan, nama
");

while ($p = $qPetugas->fetch_assoc()) {
    $dataPetugas[] = $p;
}
?>

<style>
.modal-bg{
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    z-index:999;
}
.modal-box{
    background:white;
    width:600px;
    max-height:80vh;
    overflow-y:auto;
    margin:60px auto;
    padding:20px;
    border-radius:10px;
}
</style>

<h3>Penugasan Tim Petugas</h3>
<hr>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
<tr>
    <th>No</th>
    <th>Instansi</th>
    <th>Nama Pengajuan</th>
    <th>Jenis Gangguan</th>
    <th>Pilih Petugas</th>
</tr>

<?php $no=1; while($row = $qPengajuan->fetch_assoc()): ?>

<?php
$qTim = $conn->query("
    SELECT mp.nama, mp.jabatan
    FROM tim_petugas tp
    JOIN master_petugas mp ON tp.petugas_id = mp.id
    WHERE tp.pengajuan_id = {$row['id']}
");
?>

<tr>
    <td><?= $no++ ?></td>
    <td><?= $row['nama_instansi'] ?></td>
    <td><?= $row['nama'] ?></td>
    <td>
    <?= ucwords(str_replace('_', ' ', $row['jenis_gangguan'])) ?>
</td>

    <!-- KOLOM PILIH PETUGAS -->
    <td style="min-width:260px">

        <?php if($row['sudah_ada_tim'] == 0): ?>
            <button type="button" onclick="bukaModal(<?= $row['id'] ?>)">
                👥 Pilih Petugas
            </button>
        <?php else: ?>

            <div style="background:#f0fff0;padding:8px;border-radius:6px;border:1px solid green">
                <b>Tim Bertugas:</b><br>
                <?php while($t = $qTim->fetch_assoc()): ?>
                    • <?= $t['nama'] ?> - <?= $t['jabatan'] ?><br>
                <?php endwhile; ?>

                <br>
                <button type="button"
                        onclick="bukaModal(<?= $row['id'] ?>)"
                        style="background:#ff9800;color:white">
                    🔄 Ganti Petugas
                </button>
            </div>

        <?php endif; ?>

    </td>
</tr>

<!-- MODAL -->
<div class="modal-bg" id="modal_<?= $row['id'] ?>">
    <div class="modal-box">

        <h3>Pilih Tim Petugas</h3>
        <hr>

        <form method="POST">
            <input type="hidden" name="pengajuan_id" value="<?= $row['id'] ?>">

            <?php foreach($dataPetugas as $p): ?>
                <label style="display:block;margin:6px;">
                    <input type="checkbox" name="petugas_id[]" value="<?= $p['id'] ?>">
                    <?= $p['nama'] ?> - <?= $p['jabatan'] ?>
                </label>
            <?php endforeach; ?>

            <br>
            <button type="submit" name="assign">💾 Simpan Tim</button>
            <button type="button" onclick="tutupModal(<?= $row['id'] ?>)">❌ Batal</button>
        </form>

    </div>
</div>

<?php endwhile; ?>

</table>

<script>
function bukaModal(id){
    document.getElementById("modal_"+id).style.display="block";
}
function tutupModal(id){
    document.getElementById("modal_"+id).style.display="none";
}
</script>