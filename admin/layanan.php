<?php
$res = $conn->query("SELECT l.*, u.nama FROM layanan l JOIN users u ON l.user_id=u.id ORDER BY l.id DESC");
?>
<h3>Daftar Layanan</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Pemohon</th>
        <th>Jenis</th>
        <th>Keterangan</th>
        <th>Status</th>
        <th>Jadwal</th>
    </tr>
    <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['nama']; ?></td>
            <td><?= $row['jenis']; ?></td>
            <td><?= $row['keterangan']; ?></td>
            <td><?= $row['status']; ?></td>
            <td><?= $row['jadwal_date'] . " " . $row['jadwal_time']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>