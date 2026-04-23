<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "netcare";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$id = $_GET['id'] ?? 0;
$result = $conn->query("SELECT * FROM dokumentasi WHERE id=$id");
$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];

    if (!empty($_FILES['images']['name'])) {
        $targetDir = "../uploads/";
        $fileName = time() . "_" . basename($_FILES["images"]["name"]);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES["images"]["tmp_name"], $targetFile);
        $foto = $fileName;

        $stmt = $conn->prepare("UPDATE dokumentasi SET judul=?, deskripsi=?, images=? WHERE id=?");
        $stmt->bind_param("sssi", $judul, $deskripsi, $foto, $id);
    } else {
        $stmt = $conn->prepare("UPDATE dokumentasi SET judul=?, deskripsi=? WHERE id=?");
        $stmt->bind_param("ssi", $judul, $deskripsi, $id);
    }

    $stmt->execute();
    $stmt->close();

    header("Location: ../dashboard.php?page=dokumentasi&updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Dokumentasi</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="form-container">
        <h2>✏️ Edit Dokumentasi</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Judul:</label>
            <input type="text" name="judul" value="<?php echo htmlspecialchars($data['judul']); ?>" required>

            <label>Deskripsi:</label>
            <textarea name="deskripsi" required><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>

            <label>Foto (opsional):</label>
            <input type="file" name="images" accept="images/*">
            <p><small>Foto lama: <?php echo $data['images']; ?></small></p>

            <button type="submit">Simpan Perubahan</button>
        </form>
    </div>
</body>

</html>

<?php $conn->close(); ?>