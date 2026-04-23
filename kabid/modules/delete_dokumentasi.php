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

$result = $conn->query("SELECT images FROM dokumentasi WHERE id=$id");
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $imagePath = "../uploads/" . $row['images'];

    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    $conn->query("DELETE FROM dokumentasi WHERE id=$id");
}

header("Location: ../dashboard.php?page=dokumentasi&deleted=1");
exit;
?>