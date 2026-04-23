<?php
/**
 * ================================
 * SESSION
 * ================================
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ================================
 * LOAD CONFIG (PATH FINAL & BENAR)
 * ================================
 */
require_once __DIR__ . '/../../config.php';

/**
 * ================================
 * VALIDASI KONEKSI
 * ================================
 */
if (!isset($conn) || !$conn instanceof mysqli) {
    die('Koneksi database tidak tersedia. Cek config.php');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/**
 * ================================
 * FILTER PERIODE
 * ================================
 */
$whereTanggal = "";
$periodeText  = "";

$tipe  = $_GET['tipe'] ?? '';
$bulan = (int)($_GET['bulan'] ?? 0);
$tahun = (int)($_GET['tahun'] ?? 0);

if ($tipe === 'bulan' && $bulan && $tahun) {
    $whereTanggal = " AND MONTH(tanggal_pengajuan) = $bulan AND YEAR(tanggal_pengajuan) = $tahun";
    $periodeText  = date('F', mktime(0,0,0,$bulan,1)) . " $tahun";
}
elseif ($tipe === 'triwulan' && $tahun) {
    $triwulan = ceil(($bulan ?: date('n')) / 3);
    $awal  = ($triwulan - 1) * 3 + 1;
    $akhir = $awal + 2;

    $whereTanggal = " 
        AND MONTH(tanggal_pengajuan) BETWEEN $awal AND $akhir
        AND YEAR(tanggal_pengajuan) = $tahun
    ";

    $periodeText = "Triwulan $triwulan Tahun $tahun";
}
elseif ($tipe === 'tahun' && $tahun) {
    $whereTanggal = " AND YEAR(tanggal_pengajuan) = $tahun";
    $periodeText  = "Tahun $tahun";
}

/**
 * ================================
 * QUERY DATA
 * ================================
 */
$totalPengajuan = $conn->query("
    SELECT COUNT(*) AS total
    FROM pengajuan
    WHERE status = 'disetujui'
    $whereTanggal
")->fetch_assoc()['total'];

$totalDokumentasi = $conn->query("
    SELECT COUNT(*) AS total
    FROM dokumentasi d
    JOIN pengajuan p ON d.pengajuan_id = p.id
    WHERE p.status = 'disetujui'
    $whereTanggal
")->fetch_assoc()['total'];

$rekapJenis = $conn->query("
    SELECT jenis_gangguan, COUNT(*) AS jumlah
    FROM pengajuan
    WHERE status = 'disetujui'
    $whereTanggal
    GROUP BY jenis_gangguan
");

$rekapBulan = $conn->query("
    SELECT 
        DATE_FORMAT(tanggal_pengajuan, '%M %Y') AS bulan,
        COUNT(*) AS jumlah
    FROM pengajuan
    WHERE status = 'disetujui'
    $whereTanggal
    GROUP BY YEAR(tanggal_pengajuan), MONTH(tanggal_pengajuan)
    ORDER BY YEAR(tanggal_pengajuan), MONTH(tanggal_pengajuan)
");

$detail = $conn->query("
    SELECT 
        p.nama,
        p.jenis_gangguan,
        p.deskripsi,
        p.tanggal_pengajuan,
        COUNT(d.id) AS jumlah_dokumen
    FROM pengajuan p
    LEFT JOIN dokumentasi d ON p.id = d.pengajuan_id
    WHERE p.status = 'disetujui'
    $whereTanggal
    GROUP BY p.id
    ORDER BY p.tanggal_pengajuan DESC
");

$foto = $conn->query("
    SELECT 
        d.file_path,
        d.deskripsi,
        p.nama
    FROM dokumentasi d
    JOIN pengajuan p ON d.pengajuan_id = p.id
    WHERE p.status = 'disetujui'
    $whereTanggal
    AND d.file_path REGEXP '\\\\.(jpg|jpeg|png)$'
    ORDER BY p.tanggal_pengajuan ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Rekapitulasi Sistem Informasi Layanan, Pemetaan, Monitoring, dan Pelaporan Jaringan Kabupaten Lingga</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    
<form method="GET" style="margin-bottom:15px;">
    <label>Filter Laporan :</label>

    <select name="tipe" required>
        <option value="">-- Pilih --</option>
        <option value="bulan" <?= ($_GET['tipe'] ?? '')=='bulan'?'selected':'' ?>>Bulanan</option>
        <option value="triwulan" <?= ($_GET['tipe'] ?? '')=='triwulan'?'selected':'' ?>>Triwulan</option>
        <option value="tahun" <?= ($_GET['tipe'] ?? '')=='tahun'?'selected':'' ?>>Tahunan</option>
    </select>

    <select name="bulan">
        <option value="">Bulan</option>
        <?php for($i=1;$i<=12;$i++): ?>
            <option value="<?= $i ?>" <?= ($_GET['bulan'] ?? '')==$i?'selected':'' ?>>
                <?= date('F', mktime(0,0,0,$i,1)) ?>
            </option>
        <?php endfor; ?>
    </select>

    <select name="tahun" required>
        <?php for($y=date('Y');$y>=2020;$y--): ?>
            <option value="<?= $y ?>" <?= ($_GET['tahun'] ?? '')==$y?'selected':'' ?>>
                <?= $y ?>
            </option>
        <?php endfor; ?>
    </select>

    <button type="submit">🔍 Tampilkan</button>
</form>

<button class="print" onclick="window.print()">🖨 Cetak Laporan</button>

<div id="area-cetak">

<div class="kop">
    <div class="kop-logo">
<img src="/netcare/images/logo_pemkab_lingga.png" alt="Logo Kabupaten Lingga">

    </div>
    <div class="kop-text">
        <div class="kop-atas">PEMERINTAH KABUPATEN LINGGA</div>
        <div class="kop-utama">DINAS KOMUNIKASI DAN INFORMATIKA</div>
        <div class="kop-alamat">
            Jalan Engku Aman Kelang, Lingga, Kepulauan Riau 29872<br>
            Pos-el : diskominfolingga@gmail.com<br>
            Laman : https://diskominfo.linggakab.go.id
        </div>
    </div>
</div>
<div class="garis"></div>
<p style="text-align:right;font-size:11pt; margin-top:5px;">
    Daik Lingga, <?= date('d F Y') ?>
</p>

<p style="text-align:center;font-style:italic;">
Periode Laporan : <?= $periodeText ?: 'Semua Periode' ?>
</p>


<h3>LAPORAN PRANATA KOMPUTER TENTANG FASILITASI VIDEO CONFERENCE DAN LIVE STREAMING PEMERINTAH KABUPATEN LINGGA</h3>

<p style="text-align:justify;">
Bidang E-Government Teknologi Informasi dan Komunikasi Dinas Komunikasi dan Informatika Kabupaten Lingga, 
Pranata Komputer berperan penting dalam memfasilitasi layanan telekomunikasi dan TIK. Peningkatan 
kebutuhan kegiatan live streaming dan video conference mendorong dilakukannya pembagian tugas meliputi 
fasilitasi jaringan internet, pengoperasian sistem, serta penyediaan perangkat keras dan lunak pendukung. 
Upaya ini mendukung terwujudnya pemerintahan yang efisien, akuntabel, dan berorientasi pada transformasi digital.
Pranata Komputer merupakan salah satu jabatan fungsional di Bidang Bidang E-Government Teknologi Informasi 
dan Komunikasi Dinas Komunikasi dan Informatika Kabupaten Lingga yang memiliki peran strategis dalam mendukung
tata kelola pemerintahan berbasis digital. Jabatan ini menuntut penguasaan kompetensi teknis, meliputi perancangan,
pengembangan, dan pengelolaan sistem informasi, disertai kemampuan analisis untuk menyesuaikan rancangan sistem 
dengan kebutuhan organisasi dan tujuan strategis instansi.
</p>
<p style="text-align:justify;">
Dengan menugaskan pegawai yang juga menulis laporan pranata komputer yaitu:
</p>

<table class="data-pegawai">
<tr>
    <td class="nomor">1.</td>
    <td class="label">Nama</td>
    <td class="titik">:</td>
    <td>Al Imran Mulyadi, S.T</td>
</tr>
<tr>
    <td></td><td class="label">NIP</td><td class="titik">:</td>
    <td>199500000000000000</td>
</tr>
<tr>
    <td></td><td class="label">Pangkat / Gol. Ruang</td><td class="titik">:</td>
    <td>III.a</td>
</tr>
<tr>
    <td></td><td class="label">Jabatan</td><td class="titik">:</td>
    <td>Ahli Pertama – Pranata Komputer Ahli Pertama</td>
</tr>
<tr>
    <td></td><td class="label">Tugas</td><td class="titik">:</td>
    <td>Fasilitasi Video Conference dan Live Streaming</td>
</tr>
<tr>
    <td></td><td class="label">Lokasi</td><td class="titik">:</td>
    <td>Pemerintahan Kabupaten Lingga</td>
</tr>
</table>

<table class="data-pegawai">
<tr>
    <td class="nomor">2.</td>
    <td class="label">Nama</td>
    <td class="titik">:</td>
    <td>Miki Wahyudi Alamsyah, S.T</td>
</tr>
<tr>
    <td></td><td class="label">NIP</td><td class="titik">:</td>
    <td>199600000000000000</td>
</tr>
<tr>
    <td></td><td class="label">Pangkat / Gol. Ruang</td><td class="titik">:</td>
    <td>III.a</td>
</tr>
<tr>
    <td></td><td class="label">Jabatan</td><td class="titik">:</td>
    <td>Ahli Pertama – Pranata Komputer Ahli Pertama</td>
</tr>
<tr>
    <td></td><td class="label">Tugas</td><td class="titik">:</td>
    <td>Fasilitasi Video Conference dan Live Streaming</td>
</tr>
<tr>
    <td></td><td class="label">Lokasi</td><td class="titik">:</td>
    <td>Pemerintahan Kabupaten Lingga</td>
</tr>
</table>

<table class="data-pegawai">
<tr>
    <td class="nomor">3.</td>
    <td class="label">Nama</td>
    <td class="titik">:</td>
    <td>M. Juliardi, S.T</td>
</tr>
<tr>
    <td></td><td class="label">NIP</td><td class="titik">:</td>
    <td>199800000000000000</td>
</tr>
<tr>
    <td></td><td class="label">Pangkat / Gol. Ruang</td><td class="titik">:</td>
    <td>III.a</td>
</tr>
<tr>
    <td></td><td class="label">Jabatan</td><td class="titik">:</td>
    <td>Ahli Pertama – Pranata Komputer Ahli Pertama</td>
</tr>
<tr>
    <td></td><td class="label">Tugas</td><td class="titik">:</td>
    <td>Fasilitasi Video Conference dan Live Streaming</td>
</tr>
<tr>
    <td></td><td class="label">Lokasi</td><td class="titik">:</td>
    <td>Pemerintahan Kabupaten Lingga</td>
</tr>
</table>

<table class="data-pegawai">
<tr>
    <td class="nomor">4.</td>
    <td class="label">Nama</td>
    <td class="titik">:</td>
    <td>Tiwi Irwan Sari, A.Md.T</td>
</tr>
<tr>
    <td></td><td class="label">NIP</td><td class="titik">:</td>
    <td>199600000000000000</td>
</tr>
<tr>
    <td></td><td class="label">Pangkat / Gol. Ruang</td><td class="titik">:</td>
    <td>II.c</td>
</tr>
<tr>
    <td></td><td class="label">Jabatan</td><td class="titik">:</td>
    <td>Pranata Komputer Terampil</td>
</tr>
<tr>
    <td></td><td class="label">Tugas</td><td class="titik">:</td>
    <td>Fasilitasi Video Conference dan Live Streaming</td>
</tr>
<tr>
    <td></td><td class="label">Lokasi</td><td class="titik">:</td>
    <td>Pemerintahan Kabupaten Lingga</td>
</tr>
</table>


<p style="text-align:justify;">
Berikut ini adalah laporan Pranata Komputer. Beberapa dokumen pendukung yang dilampirkan, antara lain:
</p>

<ol class="daftar-dokumen">
    <li>
        Surat Perintah Tugas dari Kepala Bidang Layanan E-Government Teknologi Informasi dan Komunikasi
        Dinas Komunikasi dan Informatika Kabupaten Lingga.
    </li>
    <li>
        Berkas pendukung berupa dokumentasi kegiatan dan berkas laporan
        Pranata Komputer.
    </li>
</ol>

Laporan ini disusun sebagai bentuk pertanggungjawaban pelaksanaan layanan
fasilitasi kegiatan berbasis teknologi informasi pada
Dinas Komunikasi dan Informatika Kabupaten Lingga.
</p>

<h4>I. RINGKASAN LAPORAN</h4>
<table>
<tr>
    <th width="70%">Uraian</th>
    <th width="30%">Jumlah</th>
</tr>
<tr>
    <td>Total Pengajuan Layanan</td>
    <td align="center"><?= $totalPengajuan ?></td>
</tr>
<tr>
    <td>Total Dokumentasi Pendukung</td>
    <td align="center"><?= $totalDokumentasi ?></td>
</tr>
</table>

<h4>II. REKAPITULASI BERDASARKAN JENIS LAYANAN</h4>
<table>
<tr>
    <th>No</th>
    <th>Jenis Layanan</th>
    <th>Jumlah</th>
</tr>
<?php $no=1; while($r=$rekapJenis->fetch_assoc()): ?>
<tr>
    <td align="center"><?= $no++ ?></td>
    <td><?= htmlspecialchars($r['jenis_gangguan']) ?></td>
    <td align="center"><?= $r['jumlah'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<h4>III. REKAPITULASI BERDASARKAN BULAN</h4>
<table>
<tr>
    <th>No</th>
    <th>Bulan</th>
    <th>Jumlah Pengajuan</th>
</tr>
<?php $no=1; while($b=$rekapBulan->fetch_assoc()): ?>
<tr>
    <td align="center"><?= $no++ ?></td>
    <td><?= htmlspecialchars($b['bulan']) ?></td>
    <td align="center"><?= $b['jumlah'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<h4>IV. DETAIL PENGAJUAN LAYANAN</h4>
<table>
<tr>
    <th>No</th>
    <th>Judul Kegiatan</th>
    <th>Jenis Layanan</th>
    <th>Tempat Pelaksanaan</th>
    <th>Tanggal</th>
    <th>Bukti Dukung</th>
</tr>
<?php $no=1; while($d=$detail->fetch_assoc()): ?>
<tr>
    <td align="center"><?= $no++ ?></td>
    <td><?= htmlspecialchars($d['nama']) ?></td>
    <td><?= htmlspecialchars($d['jenis_gangguan']) ?></td>
    <td><?= nl2br(htmlspecialchars($d['deskripsi'])) ?></td>
    <td><?= date('d-m-Y', strtotime($d['tanggal_pengajuan'])) ?></td>
    <td align="center"><?= $d['jumlah_dokumen'] ?></td>
</tr>
<?php endwhile; ?>
</table>

<br>

<p style="text-align:justify;">
Diharapkan kepada bagian / personel yang bertanggungjawab dalam hal penanganan dan tatakelola pengarsipan
untuk melakukan hal yang sesuai dengan klasifikasi Laporan Pranata Komputer ini.
Demikian laporan ini disusun untuk digunakan sebagaimana mestinya.
</p>

<br>

<table class="no-border" width="100%">
<tr>
<td width="55%"></td>
<td align="center">
    <div class="ttd-tempat">
        Daik Lingga, <?= date('d F Y') ?><br>
        <strong>Mengetahui,</strong>
    </div>

<div class="ttd-jabatan">
    Kepala Bidang Layanan E-Government<br>
    Teknologi Informasi dan Komunikasi
</div>

<div class="ttd-nama">
    ( Ady Setiawan, S.T )
</div>

<div class="ttd-nip">
    NIP. 1987XXXXXXXXXXXX
</div>

</td>
</tr>
</table>

<div class="lampiran">

<h4>LAMPIRAN<br>DOKUMENTASI FOTO KEGIATAN</h4>

<div class="foto-grid">
<?php 
$no=1; 
while($f = $foto->fetch_assoc()): 
?>
    <div class="foto-box">
        <strong>Foto <?= $no++ ?>.</strong><br>
        <strong>Kegiatan:</strong> <?= htmlspecialchars($f['nama']) ?><br><br>

        <img src="/netcare/<?= $f['file_path'] ?>" alt="Foto Kegiatan">

        <div class="foto-caption">
            <?= htmlspecialchars($f['deskripsi']) ?>
        </div>
    </div>
<?php endwhile; ?>
</div>

</div>

</div>
</body>
</html>
