<?php

$host = "localhost";
$user = "root";
$pass = "";
$db = "netcare";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';

    $targetDir = __DIR__ . "/../uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (isset($_FILES['images']) && $_FILES['images']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['images']['tmp_name'];
        $fileName = time() . "_" . basename($_FILES['images']['name']);
        $targetFile = $targetDir . $fileName;

        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($fileTmp, $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO dokumentasi (judul, deskripsi, images) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $judul, $deskripsi, $fileName);
                $stmt->execute();
                $stmt->close();

                header("Location: ../dashboard.php?page=dokumentasi&success=1");
                exit;
            } else {
                echo "❌ Gagal memindahkan file ke folder uploads.";
            }
        } else {
            echo "❌ Format file tidak diizinkan. Hanya JPG, JPEG, PNG, GIF.";
        }
    } else {
        echo "❌ Upload gagal atau tidak ada file yang dikirim.";
    }
}