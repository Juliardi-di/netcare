<?php
include 'config.php';

$data = $conn->query("SELECT p.id, p.judul, p.jenis_layanan, p.deskripsi, p.status, p.tanggal_pengajuan, d.file_path
                      FROM pengajuan p
                      LEFT JOIN dokumentasi d ON p.id = d.pengajuan_id
                      ORDER BY p.tanggal_pengajuan DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Pengajuan Layanan</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="css/style.css">

</head>

<body>
    <div class="container">
        <h2>Daftar Pengajuan</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Judul Kegiatan</th>
                <th>Jenis Layanan</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Dokumentasi</th>
            </tr>
            <?php while ($row = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['judul']) ?></td>
                    <td>
                        <?php
                        $jenis = $row['jenis_layanan'];
                        if ($jenis == "Zoom Meeting")
                            echo '<span class="zoom">' . $jenis . '</span>';
                        elseif ($jenis == "Live Streaming")
                            echo '<span class="live">' . $jenis . '</span>';
                        else
                            echo '<span class="lainnya">' . $jenis . '</span>';
                        ?>
                    </td>
                    <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                    <td>
                        <?php
                        $status = $row['status'];
                        echo '<span class="' . $status . '">' . ucfirst($status) . '</span>';
                        ?>
                    </td>
                    <td>
                        <?php if ($row['file_path']): ?>
                            <a href="<?= $row['file_path'] ?>" target="_blank">Lihat File</a>
                        <?php else: ?>
                            Tidak ada
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <a href="index.php" class="btn">Ajukan Layanan Baru</a>
    </div>
</body>

</html>