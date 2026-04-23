<?php
require_once __DIR__ . '/../../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================
// VALIDASI USER LOGIN
// ==========================
$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    die("Session tidak valid.");
}

// ==========================
// AMBIL PETUGAS ID
// ==========================
$q = $conn->prepare("SELECT id FROM master_petugas WHERE user_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$res = $q->get_result()->fetch_assoc();

$petugas_id = $res['id'] ?? 0;

if (!$petugas_id) {
    die("Akun tidak terhubung dengan data petugas.");
}

// ==========================
// AMBIL ID PENGADUAN
// ==========================
$pengaduan_id = (int) ($_GET['id'] ?? 0);

if (!$pengaduan_id) {
    die("Pilih Tugas Pada Halaman Pekerjaan Saya");
}

// ==========================
// AMBIL DATA PEKERJAAN
// ==========================
$job = $conn->prepare("
    SELECT nama, jenis_gangguan, deskripsi
    FROM pengajuan
    WHERE id = ?
");
$job->bind_param("i", $pengaduan_id);
$job->execute();
$data_job = $job->get_result()->fetch_assoc();

// ==========================
// SIMPAN PROGRES
// ==========================
if (isset($_POST['simpan'])) {

    $analisis  = trim($_POST['analisis']);
    $tindakan  = trim($_POST['tindakan']);
    $status    = $_POST['status'];

    // VALIDASI PENGADUAN
    $cek = $conn->prepare("SELECT id FROM pengajuan WHERE id = ?");
    $cek->bind_param("i", $pengaduan_id);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows == 0) {
        die("Pengajuan tidak ditemukan!");
    }

    // ==========================
    // UPLOAD FOTO
    // ==========================
    $foto = '';
    if (!empty($_FILES['foto']['name'])) {

        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nama_file = time() . "_" . uniqid() . "." . $ext;

        $target = __DIR__ . "/../../uploads/" . $nama_file;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
            $foto = $nama_file;
        }
    }

    // ==========================
    // INSERT PROGRES
    // ==========================
    $stmt = $conn->prepare("
        INSERT INTO progres_teknisi 
        (pengaduan_id, petugas_id, analisis, tindakan, foto, status_pekerjaan)
        VALUES (?,?,?,?,?,?)
    ");

    $stmt->bind_param(
        "iissss",
        $pengaduan_id,
        $petugas_id,
        $analisis,
        $tindakan,
        $foto,
        $status
    );

    if (!$stmt->execute()) {
        die("ERROR INSERT: " . $stmt->error);
    }

    echo "<script>
        alert('Progres berhasil disimpan');
        location.href='dashboard_petugas.php?page=progres&id=$pengaduan_id';
    </script>";
    exit;
}

// ==========================
// AMBIL RIWAYAT PROGRES
// ==========================
$q = $conn->prepare("
    SELECT * FROM progres_teknisi
    WHERE pengaduan_id = ? AND petugas_id = ?
    ORDER BY id DESC
");
$q->bind_param("ii", $pengaduan_id, $petugas_id);
$q->execute();
$riwayat = $q->get_result();
?>

<h3>🔧 Update Progres Pekerjaan</h3>
<hr>

<!-- ==========================
     INFO PEKERJAAN
========================== -->
<?php if ($data_job): ?>
<div style="background:#eef6ff;padding:15px;border-radius:10px;margin-bottom:20px">
    <b>Nama Pengajuan:</b> <?= htmlspecialchars($data_job['nama']) ?><br>
    <b>Jenis Gangguan:</b> <?= htmlspecialchars($data_job['jenis_gangguan']) ?><br>
    <b>Deskripsi:</b> <?= htmlspecialchars($data_job['deskripsi']) ?>
</div>
<?php else: ?>
<p style="color:red">Data pekerjaan tidak ditemukan.</p>
<?php endif; ?>

<!-- ==========================
     FORM INPUT PROGRES
========================== -->
<form method="POST" enctype="multipart/form-data">

    <label>Analisis</label><br>
    <textarea name="analisis" required style="width:100%;height:80px;"></textarea><br><br>

    <label>Tindakan</label><br>
    <textarea name="tindakan" required style="width:100%;height:80px;"></textarea><br><br>

    <label>Upload Foto Bukti</label><br>
    <input type="file" name="foto"><br><br>

    <label>Status</label><br>
    <select name="status">
        <option value="dikerjakan">Sedang Dikerjakan</option>
        <option value="menunggu_konfirmasi">
            Penanganan Gangguan Telah Selesai
        </option>
    </select><br><br>

    <button type="submit" name="simpan">💾 Simpan Progres</button>
</form>

<hr>

<h4>📊 Riwayat Progres</h4>

<?php if ($riwayat->num_rows > 0): ?>
    <?php while($d = $riwayat->fetch_assoc()): ?>
        <div style="border:1px solid #ddd;padding:10px;margin:10px 0;border-radius:6px">
            
            <b>Status:</b> 
            <?php
            $status = $d['status_pekerjaan'] ?? '';

            if ($status == 'dikerjakan') {
                echo "<span style='color:orange'>Sedang Dikerjakan</span>";
            } elseif ($status == 'menunggu_konfirmasi') {
                echo "<span style='color:blue'>Teknisi Telah Melakukan Perbaikan</span>";
            } else {
                echo "<span style='color:gray'>Belum Dikerjakan</span>";
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

            <br><small><?= $d['created_at'] ?? '' ?></small>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>Belum ada progres.</p>
<?php endif; ?>