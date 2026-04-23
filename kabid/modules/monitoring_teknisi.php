<?php
require_once __DIR__ . '/../../config.php';

// ==========================
// AKSI KONFIRMASI SELESAI
// ==========================
if (isset($_GET['selesai'])) {
    $id = (int) $_GET['selesai'];

    $update = $conn->prepare("
        UPDATE pengajuan 
        SET status = 'ditutup' 
        WHERE id = ?
    ");
    $update->bind_param("i", $id);
    $update->execute();
    $cek = mysqli_query($conn, "SELECT status FROM pengajuan WHERE id = $id");
    $data = mysqli_fetch_assoc($cek);

   

    echo "<script>
        alert('Pengaduan berhasil ditutup');
        location.href='dashboard-kabid.php?page=monitoring_teknisi';
    </script>";
    exit;
}


// ==========================
// QUERY DATA
// ==========================
$query = mysqli_query($conn, "
SELECT 
    p.id,
    p.nama,
    p.jenis_gangguan,
    p.status,

    mp.nama AS nama_petugas,

    pr.analisis,
    pr.tindakan,
    pr.foto,
    pr.status_pekerjaan

FROM pengajuan p

LEFT JOIN tim_petugas tp 
    ON p.id = tp.pengajuan_id

LEFT JOIN master_petugas mp 
    ON tp.petugas_id = mp.id

LEFT JOIN progres_teknisi pr 
    ON pr.id = (
        SELECT id 
        FROM progres_teknisi 
        WHERE pengaduan_id = p.id 
        AND petugas_id = tp.petugas_id
        ORDER BY id DESC 
        LIMIT 1
    )

WHERE p.status IN ('disetujui','ditutup')

ORDER BY p.id DESC
");

if(!$query){
    die("SQL ERROR: ".mysqli_error($conn));
}
?>

<h3>Monitoring Pengerjaan Teknisi</h3>

<table class="table table-bordered table-striped">
    <thead style="background:#343a40;color:white">
        <tr>
            <th>No</th>
            <th>Pengaduan</th>
            <th>Petugas</th>
            <th>Status Pengaduan</th>
            <th>Analisis</th>
            <th>Tindakan</th>
            <th>Foto Bukti</th>
            <th>Status Teknisi</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
        <?php $no=1; while($d=mysqli_fetch_assoc($query)) { ?>
        <tr>
            <td><?= $no++ ?></td>

            <!-- DATA PENGADUAN -->
            <td>
                <b><?= $d['nama'] ?></b><br>
                <?= $d['jenis_gangguan'] ?>
            </td>

            <td><?= $d['nama_petugas'] ?? '-' ?></td>

            <!-- STATUS PENGADUAN -->
            <td>
                <?php
                if ($d['status'] == 'disetujui') {
                    echo "<span class='badge bg-info'>Sedang Diproses</span>";
                } elseif ($d['status'] == 'ditutup') {
                    echo "<span class='badge bg-success'>Selesai / Ditutup</span>";
                } else {
                    echo "<span class='badge bg-secondary'>{$d['status']}</span>";
                }
                ?>
            </td>

            <!-- ANALISIS -->
            <td><?= $d['analisis'] ?? '-' ?></td>

            <!-- TINDAKAN -->
            <td><?= $d['tindakan'] ?? '-' ?></td>

            <!-- FOTO -->
            <td>
                <?php 
                $file = "../uploads/" . $d['foto'];
                if (!empty($d['foto']) && file_exists($file)) { ?>
                    <img src="<?= $file ?>" width="80">
                <?php } else { echo '-'; } ?>
            </td>

            <!-- STATUS TEKNISI -->
            <td>
                <?php
                $status_teknisi = $d['status_pekerjaan'] ?? '';
                $status_pengaduan = $d['status'];

                if ($status_teknisi == 'dikerjakan') {
                    echo "<span class='badge bg-warning'>Sedang Dikerjakan</span>";

                } elseif ($status_teknisi == 'menunggu_konfirmasi') {

                    if ($status_pengaduan == 'ditutup') {
                        echo "<span class='badge bg-success'>✔ Perbaikan Selesai</span>";
                    } else {
                        echo "<span class='badge bg-primary'>Teknisi Telah Melakukan Perbaikan</span>";
                    }

                } else {
                    echo "<span class='badge bg-secondary'>Belum Dikerjakan</span>";
                }
                ?>
            </td>

            <!-- AKSI -->
            <td>
                <?php if ($d['status'] == 'disetujui' && $d['status_pekerjaan'] == 'menunggu_konfirmasi'): ?>
                    
                    <a href="?page=monitoring_teknisi&selesai=<?= $d['id'] ?>"
                       onclick="return confirm('Yakin pekerjaan sudah selesai dan ingin menutup pengaduan?')"
                       class="btn btn-success btn-sm">
                       ✔ Konfirmasi & Tutup
                    </a>

                <?php elseif ($d['status'] == 'ditutup'): ?>
                    
                    <span class="text-success">✔ Sudah Ditutup</span>

                <?php else: ?>
                    
                    <span class="text-muted">Menunggu Teknisi</span>

                <?php endif; ?>
            </td>

        </tr>
        <?php } ?>
    </tbody>
</table>