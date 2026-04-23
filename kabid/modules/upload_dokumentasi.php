<?php
require_once __DIR__ . '/../config.php';
date_default_timezone_set('Asia/Jakarta');

$response = ['success' => false, 'message' => 'Terjadi kesalahan'];

if (!empty($_FILES['file']['name'])) {
    $target_dir = __DIR__ . '/../uploads/';
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $unique_name = time() . '_' . rand(1000, 9999) . '_' . basename($_FILES['file']['name']);
    $target_file = $target_dir . $unique_name;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        $file_path = 'uploads/' . $unique_name;
        $pengajuan_id = $_POST['pengajuan_id'] ?? null;
        $tanggal = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("INSERT INTO dokumentasi (pengajuan_id, file_path, tanggal_upload) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $pengajuan_id, $file_path, $tanggal);
        $stmt->execute();

        $response = [
            'success' => true,
            'file_path' => $file_path,
            'nama_file' => htmlspecialchars($_FILES['file']['name']),
            'tanggal' => $tanggal
        ];
    } else {
        $response['message'] = "Gagal memindahkan file.";
    }
} else {
    $response['message'] = "Tidak ada file yang diunggah.";
}

echo json_encode($response);
