<?php
$res = $conn->query("SELECT p.*, u.nama FROM pengaduan p JOIN users u ON p.user_id=u.id ORDER BY p.id DESC");
?>
<h3>Daftar Pengaduan</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Pelapor</th>
        <th>Judul</th>
        <th>Isi</th>
        <th>Status</th>
        <th>Tanggal</th>
    </tr>
    <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['nama']; ?></td>
            <td><?= $row['judul']; ?></td>
            <td><?= $row['isi']; ?></td>
            <td><?= $row['status']; ?></td>
            <td><?= $row['tanggal']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>