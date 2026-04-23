<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (session_status() === PHP_SESSION_NONE) session_start();
$conn->set_charset('utf8mb4');

$APP_ROOT = '/netcare';
$uploadDir = realpath(__DIR__ . '/../') . '/uploads/';
if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['ajax'] ?? '') === 'upload') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $pengajuan_id = intval($_POST['pengajuan_id'] ?? 0);
        $judul = trim($_POST['judul'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($pengajuan_id <= 0 || $judul === '' || $deskripsi === '') {
            echo json_encode(['status'=>'error','message'=>'Semua field wajib diisi.']); exit;
        }
        if (!isset($_FILES['file'])) {
            echo json_encode(['status'=>'error','message'=>'Tidak ada file diunggah.']); exit;
        }

        $allowed = ['jpg','jpeg','png','pdf'];
        $uploaded = [];
        $files = $_FILES['file'];
        if (!is_array($files['name'])) {
            foreach (['name','tmp_name','error','size'] as $k) $files[$k] = [$files[$k]];
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
    if ($f && $f['file_path'] != '' && file_exists(__DIR__.'/../'.$f['file_path']))
        unlink(__DIR__.'/../'.$f['file_path']);
    $conn->query("DELETE FROM dokumentasi WHERE id=$id");
    echo json_encode(['status'=>'success','message'=>'Dokumentasi berhasil dihapus.']); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['ajax'] ?? '') === 'tabel') {
    header('Content-Type: text/html; charset=utf-8');

    $res = $conn->query("
        SELECT 
            p.id AS pengajuan_id,
            p.judul AS judul_pengajuan,
            GROUP_CONCAT(DISTINCT d.judul SEPARATOR ', ') AS judul_dok,
            GROUP_CONCAT(DISTINCT d.deskripsi SEPARATOR ', ') AS deskripsi_dok,
            GROUP_CONCAT(DISTINCT d.file_path SEPARATOR '|') AS files,
            MAX(d.tanggal_upload) AS tanggal_upload_terakhir
        FROM pengajuan p
        LEFT JOIN dokumentasi d ON p.id = d.pengajuan_id
        GROUP BY p.id
        ORDER BY tanggal_upload_terakhir DESC
    ");

    if ($res->num_rows == 0) {
        echo "<tr><td colspan='7' style='text-align:center'>Belum ada dokumentasi kegiatan</td></tr>";
        exit;
    }

    $no = 1;
    while ($r = $res->fetch_assoc()):
        $preview = '';
        if ($r['files']) {
            foreach (explode('|', $r['files']) as $path) {
                if (trim($path)=='') continue;
                $file = $APP_ROOT.'/'.htmlspecialchars($path);
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                if ($ext === 'pdf') {
                    $preview .= "<span onclick=\"showPDF('{$file}', this)\" 
                        style='cursor:pointer;color:#157347;font-weight:bold;margin-right:5px'>
                        📄 PDF
                    </span>";
                } else {
                    $preview .= "<img src='{$file}' width='80' height='60' 
                        style='object-fit:cover;border-radius:5px;margin-right:5px'>";
                }
            }
        }

        echo "
        <tr data-id='{$r['pengajuan_id']}'>
            <td>{$no}</td>
            <td>".htmlspecialchars($r['judul_dok'] ?: '-')."</td>
            <td>".htmlspecialchars($r['judul_pengajuan'])."</td>
            <td>".nl2br(htmlspecialchars($r['deskripsi_dok'] ?: '-'))."</td>
            <td class='dokumentasi-col'>{$preview}</td>
            <td>".($r['tanggal_upload_terakhir'] ?: '-')."</td>
            <td>
                <button onclick='hapusSemua({$r['pengajuan_id']})'>🗑️ Hapus Semua</button>
            </td>
        </tr>";
        $no++;
    endwhile;
    exit;
}

$pengajuan = $conn->query("SELECT id,judul FROM pengajuan ORDER BY id DESC");
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
    <h2>🗂️ Rekapitulasi Dokumentasi Kegiatan</h2>

    <form id="uploadForm" enctype="multipart/form-data" style="margin-bottom:20px;">
        <input type="hidden" name="ajax" value="upload">
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
        </div>
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul Dokumentasi</th>
                <th>Judul Pengajuan</th>
                <th>Tempat Pelaksanaan</th>
                <th>Dokumentasi Kegiatan</th>
                <th>Tanggal Upload</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="dataTabel"><tr><td colspan="7" style="text-align:center">Memuat...</td></tr></tbody>
    </table>
</div>

<script>
const ajaxURL = '<?= $APP_ROOT ?>/kabid/modules/dokumentasi.php';

function loadTabel(){
  fetch(ajaxURL+'?ajax=tabel')
    .then(r=>r.text())
    .then(h=>document.getElementById('dataTabel').innerHTML=h)
    .catch(()=>document.getElementById('dataTabel').innerHTML='<tr><td colspan="7">Gagal memuat data.</td></tr>');
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

function hapusSemua(pengajuan_id){
  if(!confirm('Hapus semua dokumentasi untuk pengajuan ini?')) return;
  const fd=new FormData();
  fd.append('ajax','hapus');
  fd.append('id',pengajuan_id);
  fetch(ajaxURL,{method:'POST',body:fd})
    .then(r=>r.json()).then(j=>{alert(j.message);loadTabel();});
}

function showPDF(url, el) {

    document.querySelectorAll('.pdf-frame-row').forEach(r => r.remove());

    const tr = el.closest("tr");
    const newRow = document.createElement("tr");
    newRow.className = "pdf-frame-row";

    newRow.innerHTML = `
        <td colspan="7" style="background:#f0f0f0; padding:10px;">
            
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
