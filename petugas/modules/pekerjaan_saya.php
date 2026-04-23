<?php
require_once __DIR__ . '/../../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// =========================
// AMBIL PETUGAS ID
// =========================
$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    die("Session tidak valid.");
}

$q = $conn->prepare("SELECT id FROM master_petugas WHERE user_id = ?");
$q->bind_param("i", $user_id);
$q->execute();
$res = $q->get_result()->fetch_assoc();

$petugas_id = $res['id'] ?? 0;

if (!$petugas_id) {
    die("Akun tidak terhubung dengan data petugas.");
}

// =========================
// AMBIL DATA PEKERJAAN
// =========================
$stmt = $conn->prepare("
    SELECT 
        p.id,
        p.nama,
        p.jenis_gangguan,
        p.deskripsi,
        p.status,
        p.tanggal_pengajuan,

        pr.status_pekerjaan

    FROM tim_petugas t
    JOIN pengajuan p ON p.id = t.pengajuan_id

    LEFT JOIN progres_teknisi pr 
        ON pr.id = (
            SELECT id FROM progres_teknisi
            WHERE pengaduan_id = p.id
            AND petugas_id = ?
            ORDER BY id DESC
            LIMIT 1
        )

    WHERE t.petugas_id = ?
    GROUP BY p.id
    ORDER BY p.id DESC
");

$stmt->bind_param("ii", $petugas_id, $petugas_id);
$stmt->execute();
$query = $stmt->get_result();
?>

<h3>📋 Pekerjaan Saya</h3>
<hr>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead style="background:#343a40;color:white">
        <tr>
            <th>No</th>
            <th>Nama Pengajuan</th>
            <th>Jenis Gangguan</th>
            <th>Deskripsi</th>
            <th>Status Pengajuan</th>
            <th>Progres</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
        <?php if ($query && $query->num_rows > 0): ?>
            <?php $no = 1; while ($d = $query->fetch_assoc()): ?>

                <?php $status_prog = $d['status_pekerjaan'] ?? ''; ?>

                <tr>

                    <td><?= $no++ ?></td>

                    <td><?= htmlspecialchars($d['nama']) ?></td>

                    <td><?= ucwords(str_replace('_', ' ', $d['jenis_gangguan'])) ?></td>

                    <td><?= htmlspecialchars($d['deskripsi']) ?></td>

                    <!-- STATUS PENGAJUAN -->
                    <td>
                        <?php
                        if ($d['status'] == 'disetujui') {
                            echo "<span style='color:blue'>Disetujui</span>";
                        } elseif ($d['status'] == 'menunggu') {
                            echo "<span style='color:orange'>Menunggu</span>";
                        } elseif ($d['status'] == 'selesai') {
                            echo "<span style='color:green'>Selesai</span>";
                        } else {
                            echo htmlspecialchars($d['status']);
                        }
                        ?>
                    </td>

                    <!-- PROGRES TEKNISI -->
                    <td>
                        <?php
                        if ($status_prog == 'dikerjakan') {
                            echo "<span style='color:orange'>Sedang Dikerjakan</span>";
                        } elseif ($status_prog == 'menunggu_konfirmasi') {
                            echo "<span style='color:blue'>
                                Perbaikan sudah diinformasikan ke Kabid
                            </span>";
                        } else {
                            echo "<span style='color:gray'>Belum Dikerjakan</span>";
                        }
                        ?>
                    </td>

                    <!-- TANGGAL -->
                    <td>
                        <?= date('d-m-Y', strtotime($d['tanggal_pengajuan'])) ?>
                    </td>

                    <!-- AKSI -->
                    <td>
                        <?php
                        if ($status_prog == 'menunggu_konfirmasi') {
                            echo "<a href='dashboard_petugas.php?page=progres&id={$d['id']}'
                                style='background:#ff9800;color:white;padding:6px 12px;border-radius:5px;text-decoration:none;'>
                                ✏️ Revisi Perbaikan
                            </a>";
                        } elseif ($status_prog == 'dikerjakan') {
                            echo "<a href='dashboard_petugas.php?page=progres&id={$d['id']}'
                                style='background:#ff9800;color:white;padding:6px 12px;border-radius:5px;text-decoration:none;'>
                                🔄 Lanjutkan
                            </a>";
                        } else {
                            echo "<a href='dashboard_petugas.php?page=progres&id={$d['id']}'
                                style='background:#2196F3;color:white;padding:6px 12px;border-radius:5px;text-decoration:none;'>
                                🔧 Kerjakan
                            </a>";
                        }
                        ?>
                    </td>

                </tr>

            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" align="center">
                    Tidak ada pekerjaan yang ditugaskan
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>