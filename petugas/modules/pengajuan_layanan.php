<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_layanan') {
    exit('Akses ditolak');
}
$user_id = (int) $_SESSION['user_id'];

$sql = "
SELECT 
    p.id,
    p.nama,
    p.jenis_gangguan,
    p.deskripsi,
    p.tanggal_pelaksanaan,
    p.status,
    p.tanggal_pengajuan,
    pengaju_user.nama_instansi AS instansi_pengaju,
    GROUP_CONCAT(DISTINCT mp.jabatan SEPARATOR ', ') AS tugas_petugas,
    GROUP_CONCAT(DISTINCT d.file_path SEPARATOR '|') AS file_paths
FROM pengajuan p
JOIN tim_petugas tp ON p.id = tp.pengajuan_id
JOIN master_petugas mp ON tp.petugas_id = mp.id
JOIN users login_user ON login_user.id = ?
JOIN users pengaju_user ON p.user_id = pengaju_user.id
LEFT JOIN dokumentasi d ON p.id = d.pengajuan_id
WHERE mp.nama = login_user.nama
  AND p.deleted_at IS NULL
  AND p.status = 'disetujui'
GROUP BY p.id
ORDER BY p.tanggal_pengajuan DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Tugas Layanan</title>
<style>
body{background:#f7f8fa;font-family:Arial}
.container{width:95%;max-width:1100px;margin:20px auto;background:#fff;padding:30px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1)}
input,select,textarea{width:100%;padding:10px;margin-bottom:10px;border:1px solid #ccc;border-radius:6px}
button{padding:10px 20px;border:none;border-radius:6px;cursor:pointer;color:#fff}
button[type="submit"]{background:#4CAF50}
button.toggle{background:#2196F3;margin-top:15px}
.btn-hapus-banyak{background:#e74c3c;padding:8px 16px;border:none;border-radius:6px;color:#fff;margin-top:10px}
table{border-collapse:collapse;width:100%;margin-top:15px}
th,td{border:1px solid #ccc;padding:8px;text-align:left}
th{background:#4CAF50;color:#fff}
.alert{padding:10px;border-radius:6px;margin-bottom:15px}
.success{background:#d4edda;color:#155724}
.error{background:#f8d7da;color:#721c24}
#previewArea{display:none;margin-top:20px;text-align:center;background:#fafafa;padding:20px;border-radius:8px}
#previewArea img{max-width:90%;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.2)}
#previewArea embed{width:90%;height:600px;border:1px solid #ccc;border-radius:8px}
#previewArea button{background:#e74c3c;color:white;margin-top:10px}
</style>
</head>
<body>

<div id="daftarPengajuan" class="container">
  <h2 style="text-align:center;">📋 Daftar Tugas Anda</h2>
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Judul Kegiatan</th>
        <th>Jenis Layanan</th>
        <th>Instansi Pengaju</th>
        <th>Tanggal Pengajuan</th>
        <th>Tanggal Pelaksanaan</th>
        <th>Tempat Pelaksanaan</th>
        <th>Tugas Anda</th>
        <th>Surat Permohonan</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
          $no = 1;
          while ($row = $result->fetch_assoc()) {
              echo "<tr>";
              echo "<td>{$no}</td>";
              echo "<td>".htmlspecialchars($row['nama'])."</td>";
              echo "<td>".htmlspecialchars($row['jenis_gangguan'])."</td>";
              echo "<td>".htmlspecialchars($row['instansi_pengaju'])."</td>";
              echo "<td>".date('d-m-Y H:i', strtotime($row['tanggal_pengajuan']))."</td>";
              echo "<td>";if (!empty($row['tanggal_pelaksanaan'])) {echo date('d-m-Y', strtotime($row['tanggal_pelaksanaan']));} else {echo "<em>-</em>";}echo "</td>";
              echo "<td>".nl2br(htmlspecialchars($row['deskripsi']))."</td>";
              echo "<td>".htmlspecialchars($row['tugas_petugas'])."</td>";
              echo "<td align='center'>";
              if (!empty($row['file_paths'])) {
                  $files = explode('|', $row['file_paths']);
                  foreach ($files as $file) {
                      if (empty($file)) continue;
                      $file_url = '/netcare/' . $file;
                      $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                      echo "<a href='#' onclick='event.preventDefault(); lihatFile(\"$file_url\")'>📎 $ext</a><br>";
                  }
              } else {
                  echo "<em>-</em>";
              }
              echo "</td>";
              echo "</tr>";
              $no++;
          }
      } else {
          echo "<tr><td colspan='8' align='center'><em>Belum ada tugas yang diberikan kepada Anda.</em></td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>

<div id="previewArea">
  <h3>📄 Pratinjau Dokumen</h3>
  <div id="previewContent"></div>
  <button onclick="tutupPreview()">❌ Tutup</button>
</div>

<script>
function lihatFile(url){
  const ext=url.split('.').pop().toLowerCase();
  const area=document.getElementById('previewArea');
  const content=document.getElementById('previewContent');
  let html='';
  if(['png','jpg','jpeg'].includes(ext)){
      html=`<img src="${url}" alt="Gambar Dokumentasi">`;
  } else if(ext==='pdf'){
      html=`<embed src="${url}" type="application/pdf">`;
  } else {
      html=`<a href="${url}" download>📎 Unduh File</a>`;
  }
  content.innerHTML=html;
  area.style.display='block';
  area.scrollIntoView({behavior:'smooth'});
}

function tutupPreview(){
  document.getElementById('previewArea').style.display='none';
  document.getElementById('previewContent').innerHTML='';
}

</script>

</body>
</html>
