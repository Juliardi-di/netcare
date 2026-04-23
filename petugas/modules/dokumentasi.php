<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'petugas';
$isAdmin = in_array($role, ['admin', 'kabid']);

$configPath1 = __DIR__ . "/../../config.php";
$configPath2 = __DIR__ . "/../config.php";

if (file_exists($configPath1)) {
    require_once $configPath1;
} elseif (file_exists($configPath2)) {
    require_once $configPath2;
} else {
    die("Config tidak ditemukan");
}

if (isset($_POST['simpan'])) {

  $pengajuan_id = (int) $_POST['pengajuan_id'];
$deskripsi    = trim($_POST['deskripsi']);

$cekStatus = $conn->prepare("
    SELECT p.status 
    FROM pengajuan p
    JOIN tim_petugas tp ON p.id = tp.pengajuan_id
    JOIN master_petugas mp ON tp.petugas_id = mp.id
    JOIN users u ON u.id = ?
    WHERE p.id = ? AND mp.nama = u.nama
");
$cekStatus->bind_param("ii", $user_id, $pengajuan_id);
$cekStatus->execute();
$resStatus = $cekStatus->get_result()->fetch_assoc();

if (!$resStatus || $resStatus['status'] !== 'disetujui') {
    echo "<script>alert('Pengajuan tidak ditemukan atau belum disetujui!');</script>";
    return;
}


    if ($pengajuan_id > 0 && !empty($_FILES['file']['name'][0])) {

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/netcare/uploads/dokumentasi/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        foreach ($_FILES['file']['name'] as $i => $name) {

            if ($_FILES['file']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','pdf'])) continue;

            $newName = time().'_'.rand(1000,9999).".".$ext;
            move_uploaded_file($_FILES['file']['tmp_name'][$i], $uploadDir.$newName);

            $path = "uploads/dokumentasi/".$newName;

            $stmt = $conn->prepare("
                INSERT INTO dokumentasi
                (pengajuan_id, deskripsi, file_path, tanggal_upload)
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->bind_param("iss", $pengajuan_id, $deskripsi, $path);
            $stmt->execute();
        }
    }
}

$pengajuan = $conn->prepare("
SELECT p.id, p.nama 
FROM pengajuan p
JOIN tim_petugas tp ON p.id = tp.pengajuan_id
JOIN master_petugas mp ON tp.petugas_id = mp.id
JOIN users u ON u.id = ?
WHERE mp.nama = u.nama
AND p.status = 'disetujui'
ORDER BY p.tanggal_pengajuan DESC
");
$pengajuan->bind_param("i", $user_id);
$pengajuan->execute();
$listPengajuan = $pengajuan->get_result();

$bulan = (int)($_GET['bulan'] ?? date('m'));
$tahun = (int)($_GET['tahun'] ?? date('Y'));

$kal = $conn->prepare("
    SELECT p.id, p.nama, p.tanggal_pengajuan, p.deskripsi AS deskripsi_pengajuan,
           d.file_path, d.deskripsi AS deskripsi_dokumen
    FROM pengajuan p
    JOIN tim_petugas tp ON p.id = tp.pengajuan_id
    JOIN master_petugas mp ON tp.petugas_id = mp.id
    JOIN users u ON u.id = ?
    LEFT JOIN dokumentasi d ON p.id = d.pengajuan_id
    WHERE mp.nama = u.nama
      AND p.status = 'disetujui'
      AND MONTH(p.tanggal_pengajuan) = ?
      AND YEAR(p.tanggal_pengajuan) = ?
    ORDER BY p.tanggal_pengajuan ASC
");

$kal->bind_param("iii", $user_id, $bulan, $tahun);
$kal->execute();
$res = $kal->get_result();

$events = [];
while ($r = $res->fetch_assoc()) {
    $tgl = date('j', strtotime($r['tanggal_pengajuan']));
    $events[$tgl][$r['id']]['nama'] = $r['nama'];
    $events[$tgl][$r['id']]['deskripsi'] = $r['deskripsi_pengajuan'];
    if (!empty($r['file_path'])) {
        $events[$tgl][$r['id']]['items'][] = [
            'deskripsi' => $r['deskripsi_dokumen'],
            'file'      => $r['file_path']
        ];
    }
}
?>

<style>
*{box-sizing:border-box;font-family:"Segoe UI",Tahoma,Arial,sans-serif}

*{
  box-sizing:border-box;
  font-family:"Segoe UI",Tahoma,Arial,sans-serif
}

.top-toolbar{
  display:flex;
  gap:12px;
  margin:20px 0;
  flex-wrap:wrap;
}

.top-toolbar button{
  border:none;
  padding:12px 18px;
  border-radius:14px;
  font-size:14px;
  cursor:pointer;
  display:flex;
  align-items:center;
  gap:8px;
  transition:.2s ease;
  box-shadow:0 6px 16px rgba(0,0,0,.15);
}

.btn-add{
  background:linear-gradient(135deg,#2e7d32,#43a047);
  color:#fff;
}

.btn-toggle{
  background:#ffffff;
  color:#2e7d32;
  border:1px solid #c8e6c9;
}

.top-toolbar button:hover{
  transform:translateY(-2px);
  box-shadow:0 10px 22px rgba(0,0,0,.2);
}

@media (max-width:768px){
  .top-toolbar{
    flex-direction:column;
  }

  .top-toolbar button{
    width:100%;
    justify-content:center;
    font-size:15px;
    padding:14px;
  }
}

.modal,.preview-modal{
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,80,40,.85);
  z-index:9999;
  align-items:center;
  justify-content:center;
}

.modal-content{
  background:#f9fff9;
  width:480px;             
  max-width:92%;
  border-radius:20px;
  padding:26px 28px;
  box-shadow:0 25px 60px rgba(0,0,0,.4);
  animation:fadeUp .25s ease;
}

.preview-modal .modal-content{
  width:85%;
  max-width:1000px;
  background:#fff;
}

.modal-content h3{
  margin:0 0 18px;
  text-align:center;
  color:#1b5e20;
  font-size:20px;
}

.modal-content form{
  display:flex;
  flex-direction:column;
  gap:14px;
}

.modal-content select,
.modal-content input[type="text"],
.modal-content input[type="file"]{
  width:100%;
  padding:12px 14px;
  border-radius:10px;
  border:1px solid #c8e6c9;
  font-size:14px;
}

.modal-content select:focus,
.modal-content input:focus{
  outline:none;
  border-color:#2e7d32;
  box-shadow:0 0 0 2px rgba(46,125,50,.15);
}

.modal-content form div{
  display:flex;
  gap:12px;
  justify-content:flex-end;
  margin-top:10px;
}

.modal-content button{
  border:none;
  padding:10px 18px;
  border-radius:10px;
  font-size:14px;
  cursor:pointer;
}

.modal-content button[type="button"]{
  background:#e0e0e0;
}

.modal-content button[name="simpan"]{
  background:#2e7d32;
  color:#fff;
}

.modal-content button:hover{
  opacity:.9;
}

.calendar{
  display:grid;
  grid-template-columns:repeat(7,1fr);
  gap:10px;
  margin-top:15px;
}

.day{
  border:1px solid #ddd;
  min-height:150px;
  padding:10px;
  border-radius:10px;
  background:#fff;
}

.event{
  background:#2e7d32;
  color:#fff;
  border-radius:10px;
  padding:8px;
  font-size:12px;
  margin-top:6px;
}

.event a{
  color:#ffeb3b;
  text-decoration:none;
}

@media (max-width: 768px){

  .modal,
  .preview-modal{
    align-items:flex-end;
    background:linear-gradient(
      180deg,
      rgba(0,90,45,.92),
      rgba(0,60,30,.97)
    );
  }

  .modal-content{
    width:100%;
    max-width:100%;
    height:92vh;
    margin:0;
    border-radius:22px 22px 0 0;
    padding:22px 18px 26px;
    overflow-y:auto;
    animation:slideUp .35s ease;
  }

  .modal-content h3{
    font-size:18px;
    font-weight:600;
    margin-bottom:16px;
  }

  .modal-content form{
    gap:16px;
  }

  .modal-content select,
  .modal-content input[type="text"],
  .modal-content input[type="file"]{
    padding:14px 16px;
    font-size:15px;
    border-radius:14px;
  }

  .modal-content form div{
    position:sticky;
    bottom:-18px;
    background:#f9fff9;
    padding:16px 0 8px;
    margin-top:10px;
    display:flex;
    gap:12px;
  }

  .modal-content button{
    flex:1;
    padding:14px;
    font-size:15px;
    border-radius:14px;
  }

  .preview-modal .modal-content{
    height:95vh;
    border-radius:22px 22px 0 0;
  }

  .calendar{
    grid-template-columns:repeat(2,1fr);
    gap:12px;
  }

  .day{
    min-height:180px;
  }
}

@keyframes slideUp{
  from{
    transform:translateY(100%);
    opacity:0;
  }
  to{
    transform:translateY(0);
    opacity:1;
  }
}

</style>

<div class="top-toolbar">
  <button class="btn-add" onclick="openDokModal()">
    ➕ Tambah Dokumentasi
  </button>

  <button class="btn-toggle" onclick="toggleKalender()">
    📅 Kalender
  </button>
</div>

<div class="modal" id="dokModal">
  <div class="modal-content">
    <h3>Tambah Dokumentasi</h3>
    <form method="post" enctype="multipart/form-data">
      <select name="pengajuan_id" required>
        <option value="">-- Pilih --</option>
        <?php while($p=$listPengajuan->fetch_assoc()): ?>
        <option value="<?=$p['id']?>"><?=$p['nama']?></option>
        <?php endwhile;?>
      </select>
      <input type="text" name="deskripsi" required>
      <input type="file" name="file[]" multiple required>
      <div>
        <button type="button" onclick="closeDokModal()">Batal</button>
        <button name="simpan">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div id="kalenderBox" style="display:none">
<form method="get" action="dashboard_petugas.php" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background: #f1f8e9; padding: 15px; border-radius: 12px;">
    <input type="hidden" name="page" value="dokumentasi">
    <label for="bulan" style="font-weight: bold; color: #1b5e20;">Pilih Periode:</label>
    <select name="bulan" id="bulan" style="padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
        <?php
        for ($m = 1; $m <= 12; $m++) {
            $nama_bulan = date('F', mktime(0, 0, 0, $m, 1));
            $selected = ($m == $bulan) ? 'selected' : '';
            echo "<option value='$m' $selected>$nama_bulan</option>";
        }
        ?>
    </select>
    <select name="tahun" style="padding: 8px; border-radius: 8px; border: 1px solid #ccc;">
        <?php
        $tahun_sekarang = date('Y');
        for ($y = $tahun_sekarang - 5; $y <= $tahun_sekarang + 5; $y++) {
            $selected = ($y == $tahun) ? 'selected' : '';
            echo "<option value='$y' $selected>$y</option>";
        }
        ?>
    </select>
    <button type="submit" style="padding: 8px 15px; border-radius: 8px; border: none; background: #388e3c; color: white; cursor: pointer;">Tampilkan</button>
</form>
<div class="calendar">
<?php
$days=cal_days_in_month(CAL_GREGORIAN,$bulan,$tahun);
for($d=1;$d<=$days;$d++):
?>
<div class="day">
<b><?=$d?></b>
<?php if(!empty($events[$d])): foreach($events[$d] as $ev): if(!is_array($ev)) continue; ?>

<div class="event">
<b><?= $ev['nama'] ?? '-' ?></b>
<div style="font-size:11px;margin-bottom:5px;color:#e8f5e9"><?= htmlspecialchars($ev['deskripsi'] ?? '') ?></div>
<ul>
<?php if(!empty($ev['items'])): foreach($ev['items'] as $it): ?>

<li>
<?=$it['deskripsi']?> -
<a href="javascript:void(0)" onclick="openPreview('<?=$it['file']?>')">Lihat</a>
</li>
<?php endforeach; endif; ?>

</ul>
</div>
<?php endforeach; endif;?>
</div>
<?php endfor;?>
</div>
</div>

<div class="preview-modal" id="previewModal">
  <div class="modal-content">
    <button onclick="closePreview()">❌ Tutup</button>
    <div id="preview"></div>
  </div>
</div>

<script>
const dokModal=document.getElementById('dokModal');
const kalenderBox=document.getElementById('kalenderBox');
const previewModal=document.getElementById('previewModal');
const preview=document.getElementById('preview');

function openDokModal(){dokModal.style.display='flex'}
function closeDokModal(){dokModal.style.display='none'}
function toggleKalender(){
  kalenderBox.style.display=kalenderBox.style.display==='none'?'block':'none'
}
function openPreview(file){
  let ext=file.split('.').pop().toLowerCase();
  preview.innerHTML=(ext==='pdf')
    ? `<iframe src="/netcare/${file}" width="100%" height="500"></iframe>`
    : `<img src="/netcare/${file}" style="max-width:100%">`;
  previewModal.style.display='flex'
}
function closePreview(){
  previewModal.style.display='none';
  preview.innerHTML=''
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('bulan') || urlParams.has('tahun')) {
        toggleKalender();
    }
});
</script>
