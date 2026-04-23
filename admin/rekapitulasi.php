<?php
$res = $conn->query("SELECT * FROM v_rekapitulasi");
?>
<h3>Rekapitulasi Data</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>Email</th>
        <th>Role</th>
        <th>Total Layanan</th>
        <th>Total Pengaduan</th>
        <th>Total Laporan</th>
    </tr>
    <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
            <td><?= $row['email']; ?></td>
            <td><?= $row['role']; ?></td>
            <td><?= $row['total_layanan']; ?></td>
            <td><?= $row['total_pengaduan']; ?></td>
            <td><?= $row['total_laporan']; ?></td>
        </tr>
    <?php endwhile; ?>
</table>