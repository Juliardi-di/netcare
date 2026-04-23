<?php
$res = $conn->query("SELECT l.*, u.nama FROM laporan l JOIN users u ON l.user_id=u.id ORDER BY l.id DESC");
?>
<h3>Daftar Laporan</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Pelapor</th>
        <th>Isi Laporan</th>
        <th>Status</th>
        <th>Tanggal</th>
    </tr>
    <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['nama']; ?></td>
            <td><?= $row['isi_laporan']; ?></td>
            <td><?= $row['status']; ?></td>
            <td><?= $row['tanggal_laporan']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>