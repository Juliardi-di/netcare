<?php
$res = $conn->query("SELECT * FROM pengajuan ORDER BY id DESC");
?>
<h3>Daftar Pengajuan</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Nama Pemohon</th>
        <th>Email</th>
        <th>Kategori</th>
        <th>Judul</th>
        <th>Status</th>
        <th>Tanggal</th>
    </tr>
    <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['nama_pemohon']; ?></td>
            <td><?= $row['email']; ?></td>
            <td><?= $row['kategori']; ?></td>
            <td><?= $row['judul_pengajuan']; ?></td>
            <td><?= $row['status']; ?></td>
            <td><?= $row['tanggal_pengajuan']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>