<?php

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');
error_reporting(E_ALL);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "netcare";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ========== EXPORT EXCEL ==========
if (isset($_GET['export_excel'])) {

    if (ob_get_length()) ob_clean();

    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Laporan_Aktivitas_Kinerja_Harian.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "
    <table width='100%'>
        <tr>
            <td colspan='10' align='center' style='font-size:16px; font-weight:bold;'>
                FORM AKTIVITAS KINERJA HARIAN
            </td>
        </tr>
        <tr>
            <td colspan='10' align='center' style='font-size:14px :font-weight:bold;'>
                DINAS KOMUNIKASI DAN INFORMATIKA<br>
                PEMERINTAH KABUPATEN LINGGA
            </td>
        </tr>
    </table>
    <br>
    ";

    echo "
    <table cellspacing='0' cellpadding='4'>
        <tr><td width='180'>1. Unit Kerja</td><td>: Bidang Layanan E-Government, Teknologi Informasi dan Komunikasi</td></tr>
        <tr><td>2. Nama</td><td>: MIKI WAHYUDI ALAMSYAH, S.T</td></tr>
        <tr><td>3. NIP</td><td>: 199612162025061002</td></tr>
        <tr><td>4. Pangkat/Golongan</td><td>: Penata Muda / III.a</td></tr>
        <tr><td>5. Jabatan</td><td>: Pranata Komputer Ahli Pertama</td></tr>
        <tr><td>6. Eselon</td><td>: -</td></tr>
        <tr><td>7. Kelas Jabatan</td><td>: 8</td></tr>
        <tr><td>8. Atasan Langsung</td><td>: ADY SETIAWAN, S.T</td></tr>
        <tr><td>9. Periode</td><td>: September</td></tr>
        <tr><td>10. Tahun</td><td>: 2025</td></tr>
    </table>
    <br>
    ";

    echo "<table border='1' cellspacing='0' cellpadding='5' style='border-collapse:collapse;'>";

    echo "
    <tr style='background:#e0e0e0; text-align:center; font-weight:bold;'>
        <th rowspan='2'>NO</th>
        <th rowspan='2'>TANGGAL</th>
        <th rowspan='2'>URAIAN PEKERJAAN</th>
        <th rowspan='2'>JUMLAH OUTPUT</th>
        <th colspan='2'>WAKTU PEKERJAAN</th>
        <th colspan='2'>PARAF ATASAN LANGSUNG</th>
        <th rowspan='2'>KETERANGAN</th>
        <th rowspan='2'>CATATAN</th>
    </tr>
    <tr style='background:#e0e0e0; text-align:center; font-weight:bold;'>
        <th>AKTIVITAS UTAMA / MENIT</th>
        <th>AKTIVITAS TAMBAHAN / MENIT</th>
        <th>DISETUJUI</th>
        <th>DITOLAK</th>
    </tr>
    ";

    $no = 1;
    $totalOutput   = 0;
    $totalUtama    = 0;
    $totalTambahan = 0;

    $q = $conn->query("SELECT p.id, DATE_FORMAT(p.tanggal_pelaksanaan, '%Y-%m-%d') as tgl_pelaksanaan, p.judul, pl.output, pl.utama, pl.tambahan, pl.jenis, pl.keterangan, pl.keterangan2 FROM pengajuan p LEFT JOIN pengajuan_layanan pl ON p.id = pl.pengajuan_id ORDER BY p.tanggal_pelaksanaan ASC, p.id ASC");

    while ($r = $q->fetch_assoc()) {
        $totalOutput   += (int)($r['output'] ?? 0);
        $totalUtama    += (int)($r['utama'] ?? 0);
        $totalTambahan += (int)($r['tambahan'] ?? 0);

        echo "
        <tr>
            <td align='center'>{$no}</td>
            <td>{$r['tgl_pelaksanaan']}</td>
            <td>{$r['judul']}</td>
            <td align='center'>".($r['output'] ?? '')."</td>
            <td align='center'>".($r['utama'] ?? '')."</td>
            <td align='center'>".($r['tambahan'] ?? '')."</td>
            <td align='center'>".($r['jenis'] === 'Disetujui' ? '✔' : '')."</td>
            <td align='center'>".($r['jenis'] === 'Ditolak' ? '✔' : '')."</td>
            <td>".($r['keterangan'] ?? '')."</td>
            <td>".($r['keterangan2'] ?? '')."</td>
        </tr>
        ";
        $no++;
    }

    $totalMenit = $totalUtama + $totalTambahan;
    $persentase = min(100, ($totalMenit / 6000) * 100);

    echo "
    <tr style='font-weight:bold; background:#f0f0f0;'>
        <td colspan='3' align='center'>JUMLAH KESELURUHAN</td>
        <td align='center'>{$totalOutput}</td>
        <td align='center'>{$totalUtama}</td>
        <td align='center'>{$totalTambahan}</td>
        <td colspan='2' align='center'>{$totalMenit} Menit</td>
        <td align='center'>".number_format($persentase,2)." %</td>
        <td></td>
    </tr>
    ";

    echo "</table>";
    exit;
}

// ========== AJAX HANDLER ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {

    if (ob_get_length() !== false && ob_get_length() > 0) ob_clean();
    header('Content-Type: application/json; charset=utf-8');

    try {
        // Delete record
        if (!empty($_POST['hapus']) && isset($_POST['id'])) {
            $id = intval($_POST['id']);

            $stmt = $conn->prepare("DELETE FROM pengajuan_layanan WHERE id=?");
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'msg' => 'Prepare DELETE gagal: ' . $conn->error]);
                exit;
            }

            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['status' => 'ok']);
            exit;
        }

        // Update or Insert
        $id              = intval($_POST['id'] ?? 0);
        $pengajuan_id    = intval($_POST['pengajuan_id'] ?? 0);
        $jenis           = trim($_POST['jenis'] ?? '');
        $keterangan      = trim($_POST['keterangan'] ?? '');
        $output          = trim($_POST['output'] ?? '');
        $utama           = intval($_POST['utama'] ?? 0);
        $tambahan        = intval($_POST['tambahan'] ?? 0);
        $keterangan2     = trim($_POST['keterangan2'] ?? '');

        // UPDATE mode (ketika sudah ada data di pengajuan_layanan)
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE pengajuan_layanan SET jenis=?, keterangan=?, output=?, utama=?, tambahan=?, keterangan2=? WHERE id=?");
            
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'msg' => 'Prepare UPDATE gagal: ' . $conn->error]);
                exit;
            }
            
            $stmt->bind_param("sssiiis", $jenis, $keterangan, $output, $utama, $tambahan, $keterangan2, $id);
            
            if (!$stmt->execute()) {
                echo json_encode(['status' => 'error', 'msg' => 'Execute UPDATE gagal: ' . $stmt->error]);
                $stmt->close();
                exit;
            }

            $stmt->close();
            echo json_encode(['status' => 'ok', 'id' => $id, 'mode' => 'update']);
            exit;
        }

        // INSERT mode (membuat entry baru di pengajuan_layanan)
        if ($pengajuan_id <= 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Pilih pengajuan terlebih dahulu']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO pengajuan_layanan (pengajuan_id, jenis, keterangan, output, utama, tambahan, keterangan2) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'msg' => 'Prepare INSERT gagal: ' . $conn->error]);
            exit;
        }
        
        $stmt->bind_param("isssiii", $pengajuan_id, $jenis, $keterangan, $output, $utama, $tambahan, $keterangan2);
        
        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'msg' => 'Execute INSERT gagal: ' . $stmt->error]);
            $stmt->close();
            exit;
        }

        $newId = $conn->insert_id;
        $stmt->close();

        echo json_encode([
            'status' => 'ok',
            'id' => $newId,
            'pengajuan_id' => $pengajuan_id,
            'mode' => 'insert'
        ]);
        exit;

    } catch (Throwable $e) {
        echo json_encode(['status' => 'error', 'msg' => $e->getMessage()]);
        exit;
    }
}

// ========== LOAD DATA FOR DISPLAY ==========
$sql = "SELECT p.id as pengajuan_id, DATE_FORMAT(p.tanggal_pelaksanaan, '%Y-%m-%d') as tgl_pelaksanaan, p.judul, pl.id, pl.output, pl.utama, pl.tambahan, pl.jenis, pl.keterangan, pl.keterangan2 FROM pengajuan p LEFT JOIN pengajuan_layanan pl ON p.id = pl.pengajuan_id ORDER BY p.tanggal_pelaksanaan ASC, p.id ASC";

$result = $conn->query($sql);
if (!$result) {
    error_log('Query SELECT gagal: ' . $conn->error);
    $result = null;
}

// Get list pengajuan for new entry
$pengajuanList = [];
$qPengajuan = $conn->query("SELECT id, DATE_FORMAT(tanggal_pelaksanaan, '%Y-%m-%d') as tgl, judul FROM pengajuan ORDER BY tanggal_pelaksanaan DESC, id DESC");
if ($qPengajuan) {
    while ($p = $qPengajuan->fetch_assoc()) {
        $pengajuanList[] = $p;
    }
}

$cekAcc = $conn->query("SELECT COUNT(*) AS total FROM pengajuan WHERE status = 'disetujui'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Form Aktivitas Kinerja Harian</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="css/style.css">
<script>

function tambahBarisBaru() {
    const pengajuanOptions = <?= json_encode($pengajuanList) ?>;
    
    const tbody = document.querySelector('tbody');
    const tr = document.createElement('tr');
    tr.classList.add('new-row');

    let optionsHtml = '<option value="">-- Pilih Pengajuan --</option>';
    pengajuanOptions.forEach(p => {
        optionsHtml += `<option value="${p.id}">${p.tgl} - ${p.judul}</option>`;
    });

    tr.innerHTML = `
        <td class="center">*</td>
        <td class="static-cell">-</td>
        <td class="static-cell">-</td>
        <td><input type="text" name="pengajuan_select" data-pengajuan-select="true">
            <select name="pengajuan_id" onchange="this.previousElementSibling.value = this.options[this.selectedIndex].text">
                ${optionsHtml}
            </select>
        </td>
        <td><input type="text" name="output" placeholder="Jumlah output"></td>
        <td><input type="number" name="utama" placeholder="0"></td>
        <td><input type="number" name="tambahan" placeholder="0"></td>
        <td><input type="text" name="jenis" placeholder="Disetujui/Ditolak"></td>
        <td><input type="text" name="keterangan" placeholder="Keterangan"></td>
        <td><input type="text" name="keterangan2" placeholder="Catatan"></td>
        <td class="center action-col">
            <button type="button" class="save-btn" onclick="saveNewRow(this)">💾 Simpan</button>
            <button type="button" class="delete-btn" onclick="this.closest('tr').remove()">✖️ Batal</button>
        </td>
    `;

    tbody.appendChild(tr);
}

function editRow(btn) {
    const tr = btn.closest('tr');
    const inputs = tr.querySelectorAll('input[type="text"], input[type="number"]');
    
    inputs.forEach(i => {
        i.removeAttribute('readonly');
        i.style.background = '#fff';
        i.style.border = '1px solid #2563eb';
    });

    const editBtn = tr.querySelector('.edit-btn');
    const saveBtn = tr.querySelector('.save-btn');
    const deleteBtn = tr.querySelector('.delete-btn');
    
    if (editBtn) editBtn.style.display = 'none';
    if (saveBtn) saveBtn.style.display = 'inline-block';
    if (deleteBtn) deleteBtn.style.display = 'none';
}

async function saveNewRow(btn) {
    const tr = btn.closest('tr');
    const layanan_id = (tr.getAttribute('data-layanan-id') || '').trim();
    const pengajuan_id = (tr.getAttribute('data-pengajuan-id') || '').trim();
    
    const data = new URLSearchParams();
    data.append('ajax', '1');
    data.append('id', layanan_id);
    data.append('pengajuan_id', pengajuan_id);
    
    // Collect all input values from row
    tr.querySelectorAll('input[name], select[name]').forEach(el => {
        if (el.name && el.name !== 'pengajuan_select' && el.name !== 'pengajuan_id') {
            data.append(el.name, el.value);
        }
    });

    btn.disabled = true;

    try {
        const res = await fetch(location.href, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: data.toString()
        });

        const j = await res.json();

        if (j.status === 'ok') {
            location.reload();
        } else {
            alert('❌ Gagal menyimpan!\n' + (j.msg || 'Error tidak diketahui'));
        }
    } catch (err) {
        alert('⚠️ Error jaringan: ' + err.message);
    } finally {
        btn.disabled = false;
    }
}

function hapusRow(id) {
    if (!confirm('Yakin ingin menghapus data ini?')) return;

    const data = new URLSearchParams({
        ajax: '1',
        hapus: '1',
        id
    });

    fetch(location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: data.toString()
    })
    .then(r => r.json())
    .then(j => {
        if (j.status === 'ok') {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) row.remove();
        } else {
            alert('❌ Gagal menghapus data!\n' + (j.msg ?? 'Tidak ada detail error.'));
        }
    })
    .catch(err => alert('⚠️ Kesalahan jaringan!\n' + err.message));
}

function ubahPeriode() {
    const bulan = document.getElementById('bulan') ? document.getElementById('bulan').value : '';
    const tahun = document.getElementById('tahun') ? document.getElementById('tahun').value : '';

    const url = new URL(window.location.href);
    if (bulan) url.searchParams.set('bulan', bulan);
    if (tahun) url.searchParams.set('tahun', tahun);

    window.location.href = url.toString();
}

function toggleRekap() {
    const box = document.getElementById("rekap");
    const btn = event.target;

    if (box.style.display === "none") {
        box.style.display = "block";
        btn.innerHTML = "❌ Sembunyikan Hasil Rekapitulasi";
    } else {
        box.style.display = "none";
        btn.innerHTML = "📊 Tampilkan Hasil Rekapitulasi";
    }
}

</script>
</head>

<body>

<div class="top-title">FORM AKTIVITAS KINERJA HARIAN</div>
<div class="sub-title">
    DINAS KOMUNIKASI DAN INFORMATIKA<br>
    PEMERINTAH KABUPATEN LINGGA
</div>

<table class="no-border" style="margin-top:15px;">
<tr><td class="section-title">1. Unit Kerja</td><td>Bidang Layanan E-Government, Teknologi Informasi dan Komunikasi</td></tr>
<tr><td class="section-title">2. Nama</td><td>MIKI WAHYUDI ALAMSYAH, S.T</td></tr>
<tr><td class="section-title">3. NIP</td><td>199612162025061002</td></tr>
<tr><td class="section-title">4. Pangkat/Golongan</td><td>Penata Muda / III.a</td></tr>
<tr><td class="section-title">5. Jabatan</td><td>Pranata Komputer Ahli Pertama</td></tr>
<tr><td class="section-title">6. Eselon</td><td>-</td></tr>
<tr><td class="section-title">7. Kelas Jabatan</td><td>8</td></tr>
<tr><td class="section-title">8. Atasan Langsung</td><td>ADY SETIAWAN, S.T</td></tr>
<tr>
    <td class="section-title">9. Periode</td>
    <td>
        <select id="bulan" onchange="ubahPeriode()">
            <?php
            $bulanList = [
                '01'=>'January','02'=>'February','03'=>'March','04'=>'April',
                '05'=>'May','06'=>'June','07'=>'July','08'=>'August',
                '09'=>'September','10'=>'October','11'=>'November','12'=>'December'
            ];
            $bulanAktif = $_GET['bulan'] ?? date('m');

            foreach ($bulanList as $k => $v) {
                $sel = $bulanAktif == $k ? 'selected' : '';
                echo "<option value='$k' $sel>$v</option>";
            }
            ?>
        </select>
    </td>
</tr>

<tr>
    <td class="section-title">10. Tahun</td>
    <td>
        <select id="tahun" onchange="ubahPeriode()">
            <?php
            $tahunAktif = $_GET['tahun'] ?? date('Y');
            for ($y = 2022; $y <= date('Y') + 1; $y++) {
                $sel = $tahunAktif == $y ? 'selected' : '';
                echo "<option value='$y' $sel>$y</option>";
            }
            ?>
        </select>
    </td>
</tr>

</table>

<a href="dashboard.php?page=laporan_rekapitulasi&export_excel=1" class="export-btn">
   ⬇️ Download Excel
</a>

<button class="add-btn" onclick="tambahBarisBaru()">+ Tambah Data Baru</button>

<table class="table table-bordered laporan-table">

<thead>
<tr class="header">
    <th rowspan="2">NO</th>
    <th rowspan="2">TANGGAL</th>
    <th rowspan="2">URAIAN PEKERJAAN</th>
    <th rowspan="2">JUMLAH OUTPUT</th>
    <th colspan="2">WAKTU PEKERJAAN</th>
    <th colspan="2">PARAF ATASAN LANGSUNG</th>
    <th rowspan="2">KETERANGAN</th>
    <th rowspan="2">AKSI</th>
</tr>
<tr class="header">
    <th>AKTIVITAS UTAMA / MENIT</th>
    <th>AKTIVITAS TAMBAHAN / MENIT</th>
    <th>DISETUJUI</th>
    <th>DITOLAK</th>
</tr>
</thead>

<tbody>

<?php
if ($result && $result->num_rows > 0):
    $no = 1;
    $totalOutput   = 0;
    $totalUtama    = 0;
    $totalTambahan = 0;
?>

    <?php while($row = $result->fetch_assoc()): ?>
        <?php
$totalOutput   += (int)($row['output'] ?? 0);
$totalUtama    += (int)($row['utama'] ?? 0);
$totalTambahan += (int)($row['tambahan'] ?? 0);
?>

        <tr data-pengajuan-id="<?= $row['pengajuan_id'] ?>" data-layanan-id="<?= $row['id'] ?? '' ?>">
            <td class="center"><?= $no++; ?></td>
            <td class="static-cell"><?= htmlspecialchars($row['tgl_pelaksanaan']) ?></td>
            <td class="static-cell"><?= htmlspecialchars($row['judul']) ?></td>
            <td><input type="text" name="output" value="<?= htmlspecialchars($row['output'] ?? '') ?>" <?= $row['id'] ? 'readonly' : '' ?>></td>
            <td><input type="number" name="utama" value="<?= (int)($row['utama'] ?? 0) ?>" <?= $row['id'] ? 'readonly' : '' ?>></td>
            <td><input type="number" name="tambahan" value="<?= (int)($row['tambahan'] ?? 0) ?>" <?= $row['id'] ? 'readonly' : '' ?>></td>
            <td><input type="text" name="jenis" value="<?= htmlspecialchars($row['jenis'] ?? '') ?>" <?= $row['id'] ? 'readonly' : '' ?>></td>
            <td><input type="text" name="keterangan" value="<?= htmlspecialchars($row['keterangan'] ?? '') ?>" <?= $row['id'] ? 'readonly' : '' ?>></td>
            <td><input type="text" name="keterangan2" value="<?= htmlspecialchars($row['keterangan2'] ?? '') ?>" <?= $row['id'] ? 'readonly' : '' ?>></td>
            <td class="center action-col">
                <?php if ($row['id']): ?>
                    <button type="button" class="edit-btn" onclick="editRow(this)">✏️ Edit</button>
                    <button type="button" class="save-btn" style="display:none" onclick="saveNewRow(this)">💾 Simpan</button>
                    <button type="button" class="delete-btn" onclick="hapusRow(<?= $row['id'] ?>)">🗑 Hapus</button>
                <?php else: ?>
                    <button type="button" class="save-btn" onclick="saveNewRow(this)">💾 Simpan</button>
                    <button type="button" class="delete-btn" onclick="this.closest('tr').remove()">✖️ Batal</button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="10" align="center" style="padding: 20px; color: #999;">
            Tidak ada data pengajuan. Silakan tambah data baru atau hubungi administrator.
        </td>
    </tr>
<?php endif; ?>

<?php
$totalMenit = $totalUtama + $totalTambahan;
$persentase = min(100, ($totalMenit / 6000) * 100);
?>

<tr style="font-weight:bold; background:#f0f0f0;">
    <td colspan="3" align="center">JUMLAH KESELURUHAN</td>
    <td align="center"><?= $totalOutput ?></td>
    <td align="center"><?= $totalUtama ?></td>
    <td align="center"><?= $totalTambahan ?></td>
    <td colspan="2" align="center">
        <?= $totalMenit ?> Menit
    </td>
    <td align="center">
        <?= number_format($persentase, 2) ?> %
        <br>
        <small>(<?= $totalMenit ?> / 6000)</small>
    </td>
    <td></td>
</tr>

</tbody>

</table>

<?php if($cekAcc > 0): ?>

<button onclick="toggleRekap()" class="btn-toggle" style="margin-top:15px; padding:8px 12px;">
    📊 Tampilkan Hasil Rekapitulasi
</button>

<div id="rekap" style="display:none; margin-top:15px;">
    <hr>
    <h3>Hasil Rekapitulasi Pengajuan</h3>

    <iframe
        src="/netcare/admin/hasil_rekapitulasi.php"
        style="width:100%; height:900px; border:1px solid #ccc;">
    </iframe>
</div>

<?php endif; ?>

</body>
</html>
