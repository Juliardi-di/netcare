<?php
include 'config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$sql = "SELECT p.id, p.judul, p.jenis_layanan, p.deskripsi, p.tanggal_pengajuan, d.file_path 
        FROM pengajuan p
        LEFT JOIN dokumentasi d ON p.id = d.pengajuan_id
        ORDER BY p.tanggal_pengajuan DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Pengajuan Layanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }
        table th {
            background-color: #4CAF50;
            color: white;
            text-align: center;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .btn:hover {
            background: #45a049;
        }
        .file-link {
            color: #007BFF;
            text-decoration: none;
        }
        .file-link:hover {
            text-decoration: underline;
        }
        .no-data {
            text-align: center;
            color: #777;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daftar Pengajuan Layanan</h2>
        <table>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Jenis Layanan</th>
                <th>Deskripsi</th>
                <th>Tanggal Pengajuan</th>
                <th>Dokumentasi</th>
            </tr>
            <?php
            if ($result->num_rows > 0) {
                $no = 1;
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td style='text-align:center;'>" . $no++ . "</td>";
                    echo "<td>" . htmlspecialchars($row['judul']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['jenis_layanan']) . "</td>";
                    echo "<td>" . nl2br(htmlspecialchars($row['deskripsi'])) . "</td>";
                    echo "<td style='text-align:center;'>" . $row['tanggal_pengajuan'] . "</td>";
                    
                    if (!empty($row['file_path']) && file_exists(__DIR__ . '/' . $row['file_path'])) {
                        echo "<td style='text-align:center;'><a class='file-link' href='" . $row['file_path'] . "' target='_blank'>Lihat File</a></td>";
                    } else {
                        echo "<td style='text-align:center; color:#999;'>Tidak ada</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='no-data'>Belum ada data pengajuan.</td></tr>";
            }
            ?>
        </table>
        <a href="pengajuan_layanan.php" class="btn">+ Tambah Pengajuan Baru</a>
    </div>
</body>
</html>
