<?php
include 'config.php';

$data = $conn->query("SELECT status, COUNT(*) AS total 
                      FROM pengajuan 
                      GROUP BY status");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekap Laporan Pengajuan</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="container">
        <h2>Rekap Laporan Pengajuan</h2>
        <table>
            <tr>
                <th>Status</th>
                <th>Total Pengajuan</th>
            </tr>
            <?php while ($row = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td><?= $row['total'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <a href="index.php" class="btn">Ajukan Layanan Baru</a>
        <a href="daftar_pengajuan.php" class="btn">Kembali ke Daftar Pengajuan</a>
    </div>
</body>

</html>