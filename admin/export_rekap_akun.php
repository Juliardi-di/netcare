<?php
session_start();
require_once __DIR__ . '/../db/config.php';

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin_utama'
) {
    die("Akses ditolak.");
}

$search = trim($_GET['search'] ?? '');

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=rekap_akun_" . date('Ymd_His') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "No\tNama\tEmail\tRole\tStatus\tTanggal Dibuat\n";

if ($search !== '') {

    $like = "%$search%";
    $stmt = $mysqli->prepare("
        SELECT nama, email, role, status, created_at
        FROM users
        WHERE created_by = 'admin_utama'
        AND (nama LIKE ? OR email LIKE ? OR role LIKE ?)
        ORDER BY id DESC
    ");
    $stmt->bind_param("sss", $like, $like, $like);

} else {

    $stmt = $mysqli->prepare("
        SELECT nama, email, role, status, created_at
        FROM users
        WHERE created_by = 'admin_utama'
        ORDER BY id DESC
    ");
}

$stmt->execute();
$result = $stmt->get_result();

$no = 1;
while ($row = $result->fetch_assoc()) {
    echo $no++ . "\t";
    echo $row['nama'] . "\t";
    echo $row['email'] . "\t";
    echo $row['role'] . "\t";
    echo $row['status'] . "\t";
    echo date('d-m-Y H:i', strtotime($row['created_at'])) . "\n";
}

exit;
