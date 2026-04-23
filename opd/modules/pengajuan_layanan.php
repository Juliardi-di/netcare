<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'opd_pengaju_layanan') {
    exit('Akses ditolak');
}

$user_id = (int) $_SESSION['user_id'];

$pesan_sukses = "";
$pesan_error  = "";

// ==========================
// PROSES SIMPAN + UPLOAD FOTO
// ==========================
if (
    isset($_POST['submit'], $_POST['form_token'], $_SESSION['form_token']) &&
    hash_equals($_SESSION['form_token'], $_POST['form_token'])
) {
    unset($_SESSION['form_token']);

    try {
        $nama   = trim($_POST['nama'] ?? '');
        $jenis  = trim($_POST['jenis_gangguan'] ?? '');
        $desk   = trim($_POST['deskripsi'] ?? '');
        $tanggal_pengajuan = $_POST['tanggal_pengajuan'] ?? null;

        if ($nama === '' || $jenis === '') {
            throw new Exception("Nama dan jenis gangguan wajib diisi.");
        }

        if (!$tanggal_pengajuan) {
            throw new Exception("Tanggal pengajuan wajib diisi.");
        }

        $conn->begin_transaction();

        $stmt = $conn->prepare("
            INSERT INTO pengajuan
            (nama, jenis_gangguan, deskripsi, user_id, status, tanggal_pengajuan)
            VALUES (?, ?, ?, ?, 'menunggu', ?)
        ");

        $stmt->bind_param("sssis", $nama, $jenis, $desk, $user_id, $tanggal_pengajuan);
        $stmt->execute();

        $pengajuan_id = $conn->insert_id;

        // ================= UPLOAD FOTO =================
        if (!empty($_FILES['file']['name'])) {

            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
            $allow = ['jpg','jpeg','png'];

            if (!in_array($ext, $allow)) {
                throw new Exception("Format file tidak diizinkan.");
            }

            $uploadDir = __DIR__ . '/../../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $namaFile = time().'_'.bin2hex(random_bytes(5)).'.'.$ext;
            $path = 'uploads/'.$namaFile;

            move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir.$namaFile);

            $stmt = $conn->prepare("
                INSERT INTO dokumentasi (pengajuan_id, file_path, tanggal_upload)
                VALUES (?, ?, NOW())
            ");
            $stmt->bind_param("is", $pengajuan_id, $path);
            $stmt->execute();
        }

        $conn->commit();
        $pesan_sukses = "✅ Pengajuan berhasil disimpan.";

    } catch (Exception $e) {
        $conn->rollback();
        $pesan_error = "❌ " . $e->getMessage();
    }
}

// ==========================
// AMBIL DATA
// ==========================
$stmt = $conn->prepare("
    SELECT id, nama, jenis_gangguan, status, tanggal_pengajuan
    FROM pengajuan
    WHERE user_id=?
    ORDER BY tanggal_pengajuan DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$_SESSION['form_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Pengaduan Gangguan</title>

<!-- FLATPICKR -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<style>
body{
    background:#f4f8f6;
    font-family:'Segoe UI',sans-serif;
}
.container{
    width:95%;
    max-width:900px;
    margin:20px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 4px 15px rgba(0,0,0,0.1);
}
h2{
    text-align:center;
    color:#005477;
}
input,select,textarea{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border:1px solid #ccc;
    border-radius:6px;
}
button{
    background:#005477;
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:6px;
    cursor:pointer;
}
button:hover{
    background:#003b4a;
}
.alert{
    padding:10px;
    border-radius:6px;
    margin-bottom:15px;
}
.success{background:#d4edda;color:#155724;}
.error{background:#f8d7da;color:#721c24;}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
th, td{
    padding:10px;
    border-bottom:1px solid #eee;
    text-align:center;
}
th{
    background:#005477;
    color:#fff;
}
.badge{
    padding:5px 10px;
    border-radius:6px;
    font-size:12px;
    font-weight:bold;
}
.wait{background:#fff3cd;color:#856404;}
.ok{background:#d4edda;color:#155724;}
.reject{background:#f8d7da;color:#721c24;}
</style>
</head>

<body>

<div class="container">
<h2>Form Pengaduan Gangguan Jaringan</h2>

<?php if($pesan_sukses): ?>
<div class="alert success"><?= $pesan_sukses ?></div>
<?php endif; ?>

<?php if($pesan_error): ?>
<div class="alert error"><?= $pesan_error ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="form_token" value="<?= $_SESSION['form_token']; ?>">

<label>Nama Pelapor</label>
<input type="text" name="nama" required>

<label>Jenis Gangguan</label>
<select name="jenis_gangguan" required>
    <option value="">-- Pilih --</option>
    <option value="internet_mati">Internet Mati</option>
    <option value="internet_lelet">Internet Lelet</option>
    <option value="lainnya">Lainnya</option>
</select>

<label>Deskripsikan Gangguan</label>
<textarea name="deskripsi"></textarea>

<label>Tanggal Pengajuan</label>
<input type="text" id="tanggal_pengajuan" name="tanggal_pengajuan" placeholder="Pilih tanggal & jam" required>

<label>Foto Perangkat</label>
<input type="file" name="file" accept=".jpg,.jpeg,.png">

<button type="submit" name="submit">Ajukan</button>

</form>
</div>

<div class="container">
<h3>Riwayat Pengajuan</h3>

<table>
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>Jenis Gangguan</th>
    <th>Status</th>
    <th>Tanggal</th>
</tr>

<?php $no=1; while($row = $result->fetch_assoc()): ?>
<tr>

<td><?= $no++ ?></td>

<td><?= htmlspecialchars($row['nama']) ?></td>

<td>
<?php
$mapJenis = [
    'internet_mati'  => 'Internet Mati',
    'internet_lelet' => 'Internet Lelet',
    'lainnya'        => 'Lainnya'
];
$jenis = $row['jenis_gangguan'];
echo $mapJenis[$jenis] ?? ucwords(str_replace('_', ' ', $jenis));
?>
</td>

<td>
<?php
if ($row['status']=='menunggu') {
    echo "<span class='badge wait'>Menunggu</span>";
} elseif ($row['status']=='disetujui') {
    echo "<span class='badge ok'>Diproses</span>";
} elseif ($row['status']=='ditolak') {
    echo "<span class='badge reject'>Ditolak</span>";
} elseif ($row['status']=='ditutup') {
    echo "<span class='badge ok'>✔ Selesai</span>";
}
?>
</td>

<td><?= date('d-m-Y H:i', strtotime($row['tanggal_pengajuan'])) ?></td>

</tr>
<?php endwhile; ?>

</table>
</div>

<!-- SCRIPT DATE -->
<script>
const inputTanggal = document.getElementById("tanggal_pengajuan");

flatpickr(inputTanggal, {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
    altInput: true,
    altFormat: "d F Y H:i",
    locale: "id",
    time_24hr: true,

    onReady: function(selectedDates, dateStr, instance) {
        if (!inputTanggal.value) {
            instance.setDate(new Date(), false);
        }
    }
});
</script>

</body>
</html>