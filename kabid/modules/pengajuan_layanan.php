<?php 

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (session_status() === PHP_SESSION_NONE) session_start();
$conn->set_charset('utf8mb4');

$APP_ROOT = '/netcare';
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/netcare/uploads/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === 'upload') {
    header('Content-Type: application/json; charset=utf-8');

    try {

        $pengajuan_id = intval($_POST['pengajuan_id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($pengajuan_id <= 0 || $judul === '' || $deskripsi === '') {
            echo json_encode(['status'=>'error','message'=>'Semua field wajib diisi.']);
            exit;
        }

        if (!isset($_FILES['file'])) {
            echo json_encode(['status'=>'error','message'=>'Tidak ada file diunggah.']);
            exit;
        }

        $allowed = ['jpg','jpeg','png','pdf'];
        $uploaded = [];
        $files = $_FILES['file'];

        if (!is_array($files['name'])) {
            foreach (['name','tmp_name','error','size'] as $k) {
                $files[$k] = [$files[$k]];
            }
        }

        foreach ($files['name'] as $i => $name) {
            if (empty($name)) continue;

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) continue;
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $safeName = time().'_'.bin2hex(random_bytes(3)).'_'.preg_replace('/[^A-Za-z0-9_\.-]/','_',$name);
            $target = $uploadDir.$safeName;
            $relPath = 'uploads/'.$safeName;

            if (move_uploaded_file($files['tmp_name'][$i], $target)) {
                $stmt = $conn->prepare("INSERT INTO dokumentasi 
                    (pengajuan_id, judul, deskripsi, file_path, tanggal_upload)
                    VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("isss", $pengajuan_id, $judul, $deskripsi, $relPath);
                $stmt->execute();

                $uploaded[] = $APP_ROOT.'/'.$relPath;
            }
        }

        echo json_encode([
            'status'=>count($uploaded)?'success':'error',
            'message'=>count($uploaded) ? count($uploaded).' file berhasil diunggah.' : 'Gagal upload.',
            'files'=>$uploaded,
            'pengajuan_id'=>$pengajuan_id
        ]);

    } catch (Throwable $e) {
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }

    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === 'hapus') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) exit(json_encode(['status'=>'error','message'=>'ID tidak valid.']));
    $q = $conn->prepare("SELECT file_path FROM dokumentasi WHERE id=?");
    $q->bind_param("i",$id); $q->execute();
    $f = $q->get_result()->fetch_assoc();
    if (!$f) {
    exit(json_encode(['status'=>'error','message'=>'File tidak ditemukan']));
}
   $fullPath = $_SERVER['DOCUMENT_ROOT'].$APP_ROOT.'/'.$f['file_path'];
if (file_exists($fullPath)) {
    unlink($fullPath);
}
    $del = $conn->prepare("DELETE FROM dokumentasi WHERE id=?");
$del->bind_param("i",$id);
$del->execute();

    echo json_encode(['status'=>'success','message'=>'Dokumentasi berhasil dihapus.']); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === 'verifikasi') {
    header('Content-Type: application/json; charset=utf-8');

    try {
        $id   = intval($_POST['pengajuan_id'] ?? 0);
        $aksi = $_POST['aksi'] ?? '';

        if ($id <= 0 || !in_array($aksi, ['disetujui','ditolak'])) {
            throw new Exception('Data tidak valid');
        }

        $conn->begin_transaction();

        $stmt = $conn->prepare("
            UPDATE pengajuan 
            SET status=? 
            WHERE id=? AND status='menunggu'
        ");
        $stmt->bind_param("si", $aksi, $id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception('Pengajuan sudah diproses atau ID salah');
        }

        if ($aksi === 'disetujui') {

        }

        $conn->commit();

        echo json_encode([
            'status'  => 'success',
            'message' => 'Pengajuan berhasil '.$aksi
        ]);

    } catch (Throwable $e) {

        $conn->rollback();

        echo json_encode([
            'status'  => 'error',
            'message' => $e->getMessage()
        ]);
    }

    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['ajax'] ?? '') === 'tabel') {
    header('Content-Type: text/html; charset=utf-8');

$sql = "
SELECT 
    p.id AS pengajuan_id,
    p.nama,
   
    p.deskripsi,
    p.status,
    p.jenis_gangguan,
    p.tanggal_pengajuan,
    u.nama_instansi,
    u.role,

    GROUP_CONCAT(DISTINCT d.file_path SEPARATOR '|') AS files,
    MAX(d.tanggal_upload) AS tanggal_upload_terakhir,

    (
    SELECT GROUP_CONCAT(mp.nama SEPARATOR '<br>')
    FROM tim_petugas tp
    JOIN master_petugas mp ON tp.petugas_id = mp.id
    WHERE tp.pengajuan_id = p.id

    ) AS tim

FROM pengajuan p
JOIN users u ON p.user_id = u.id
LEFT JOIN dokumentasi d ON p.id = d.pengajuan_id
GROUP BY p.id
ORDER BY p.tanggal_pengajuan DESC";


    $res = $conn->query($sql);

    if (!$res || $res->num_rows === 0) {
        echo "<tr><td colspan='8' style='text-align:center'>Belum ada data</td></tr>";
        exit;
    }

    $no = 1;

    while ($r = $res->fetch_assoc()) {
        $tim = !empty($r['tim'])
    ? "<strong>{$r['tim']}</strong>"
    : "<em style='color:red'>Tim tidak tersedia</em>";

        $sumber = ($r['role'] === 'admin_instansi')
            ? '<span style="color:#0d6efd;font-weight:bold">ADMIN INSTANSI</span>'
            : '<span style="color:#198754;font-weight:bold">OPD – '.htmlspecialchars($r['nama_instansi']).'</span>';

        $preview = '';
        if (!empty($r['files'])) {
            foreach (explode('|', $r['files']) as $path) {
                $file = $APP_ROOT.'/'.htmlspecialchars($path);
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if ($ext === 'pdf') {
                    $preview .= "<span onclick=\"showPDF('$file', this)\" style='cursor:pointer'>📄 PDF</span> ";
                } else {
                    $preview .= "<img src='$file' width='70' style='margin-right:4px'>";
                }
            }
        }

        $aksi = ($r['status'] === 'menunggu')
            ? "<button onclick=\"verifikasi({$r['pengajuan_id']}, 'disetujui')\">Terima</button>
               <button onclick=\"verifikasi({$r['pengajuan_id']}, 'ditolak')\">Tolak</button>"
            : strtoupper($r['status']);


    $judul     = htmlspecialchars($r['nama']);
    $deskripsi = nl2br(htmlspecialchars($r['deskripsi']));
    
    $tgl       = $r['tanggal_upload_terakhir'] ?? '-';

    echo <<<HTML
<tr>
    <td>{$no}</td>
    <td>{$judul}</td>
    <td>{$deskripsi}</td>
    <td>{$preview}</td>
   
    <td>{$tgl}</td>
    <td>{$sumber}</td>
    <td>{$tim}</td>
    <td>{$aksi}</td>
</tr>
HTML;

    $no++;
}

exit;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Dokumentasi Kegiatan</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
    <h2>🗂️ Daftar Pengajuan Layanan dan Dokumentasi</h2>

    <form id="uploadForm" enctype="multipart/form-data" style="margin-bottom:20px;">
        <input type="hidden" name="ajax" value="upload">
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            
        </div>
    </form>

    <table>
<thead>
<tr>
    <th>No</th>
    <th>Nama Pelapor</th>
    <th>Deskripsi</th>
    <th>Bukti Foto</th>
  
    <th>Tanggal Upload</th>
    <th>Sumber Pengaduan</th>
    <th>Tim Bertugas</th>
    <th>Aksi</th>
</tr>
</thead>
        <tbody id="dataTabel"><tr><td colspan="9" style="text-align:center">Memuat...</td></tr></tbody>
    </table>
</div>

<script>
const ajaxURL = '<?= $APP_ROOT ?>/kabid/modules/pengajuan_layanan.php';
function verifikasi(id, aksi){
    const konfirmasi = confirm(
        aksi === 'disetujui' 
        ? 'Setujui pengajuan ini?' 
        : 'Tolak pengajuan ini?'
    );
    if(!konfirmasi) return;

    const fd = new FormData();
    fd.append('ajax','verifikasi');
    fd.append('pengajuan_id', id);
    fd.append('aksi', aksi);

    fetch(ajaxURL, { method:'POST', body:fd })
        .then(r=>r.json())
        .then(j=>{
            alert(j.message);
            if(j.status==='success') loadTabel();
        })
        .catch(()=>alert('Gagal memproses data'));
}


function loadTabel(){
  fetch(ajaxURL+'?ajax=tabel')
    .then(r=>r.text())
    .then(h=>document.getElementById('dataTabel').innerHTML=h)
    .catch(()=>document.getElementById('dataTabel').innerHTML='<tr><td colspan="8">Gagal memuat data.</td></tr>');
}

document.getElementById('uploadForm').addEventListener('submit',e=>{
  e.preventDefault();
  const fd=new FormData(e.target);
  fetch(ajaxURL,{method:'POST',body:fd})
    .then(r=>r.json())
    .then(j=>{
      alert(j.message);
      if(j.status==='success'){ loadTabel(); e.target.reset(); }
    })
    .catch(()=>alert('Kesalahan upload'));
});

function hapusDokumentasi(id){
  if(!confirm('Hapus dokumentasi ini?')) return;
  const fd=new FormData();
  fd.append('ajax','hapus');
  fd.append('id', id);
  fetch(ajaxURL,{method:'POST',body:fd})
    .then(r=>r.json())
    .then(j=>{ alert(j.message); loadTabel(); });
}
function showPDF(url, el) {

    document.querySelectorAll('.pdf-frame-row').forEach(r => r.remove());

    const tr = el.closest("tr");
    const newRow = document.createElement("tr");
    newRow.className = "pdf-frame-row";

    newRow.innerHTML = `
        <td colspan="8" style="background:#f0f0f0; padding:10px;">
            
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <strong>Preview PDF</strong>
                <button onclick="tutupPreview()" 
                    style="background:#dc3545;color:white;padding:6px 12px;border:none;border-radius:6px;cursor:pointer;">
                    ✖ Tutup Preview
                </button>
            </div>

            <iframe src="${url}" 
                style="margin-top:10px;width:100%;height:650px;border:1px solid #ccc;border-radius:8px"></iframe>
        </td>
    `;
    tr.after(newRow);
}

function tutupPreview() {
    document.querySelectorAll('.pdf-frame-row').forEach(r => r.remove());
}

loadTabel();

</script>
</body>
</html>