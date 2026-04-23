<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Upload Dokumentasi</title>
</head>

<body>
    <h2>Upload Dokumentasi</h2>
    <form action="proses_upload.php" method="POST" enctype="multipart/form-data">
        <label>Judul:</label><br>
        <input type="text" name="judul" required><br><br>

        <label>Deskripsi:</label><br>
        <textarea name="deskripsi" required></textarea><br><br>

        <label>Foto:</label><br>
        <input type="file" name="images" accept=".jpg,.jpeg,.png,.gif" required><br><br>

        <button type="submit">Simpan</button>
    </form>
</body>

</html>