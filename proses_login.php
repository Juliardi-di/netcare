<?php
session_start();

$conn = new mysqli("localhost", "root", "", "netcare");
if ($conn->connect_error) {
    die("Koneksi database gagal");
}

$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    die("Email dan password wajib diisi");
}

$stmt = $conn->prepare("
    SELECT 
        id,
        password,
        role,
        nama_instansi
    FROM users
    WHERE email = ?
      AND role = 'opd_pengaju_layanan'
    LIMIT 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Login gagal");
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    die("Login gagal");
}

session_unset();
session_regenerate_id(true);

$_SESSION['user_id']       = (int)$user['id']; 
$_SESSION['role']          = $user['role'];   
$_SESSION['nama_instansi'] = $user['nama_instansi'];

header("Location: opd/dashboard_opd.php");
exit;
