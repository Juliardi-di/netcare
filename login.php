<?php
session_start();
session_regenerate_id(true);

require_once __DIR__ . '/db/config.php';

$login_error = '';
$allow_html = false; // 🔥 tambahan
$role_param = strtolower($_GET['role'] ?? 'utama');

$role_map = [
    'admin utama' => 'admin_utama',
    'admin al'    => 'admin_atasan_langsung',
    'opd'         => 'opd_pengaju_layanan',
    'petugas'     => 'petugas_layanan',
];

$expected_role = $role_map[$role_param] ?? 'admin_utama';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $login_error = "Email dan password wajib diisi.";
    } else {

        $stmt = $mysqli->prepare("
            SELECT id, email, password, role, nama_instansi, status
            FROM users
            WHERE email = ? AND status = 'aktif'
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            $login_error = "Email tidak terdaftar.";
        }
        elseif (!password_verify($pass, $user['password'])) {
            $login_error = "Password salah.";
        }

        elseif ($user['role'] !== $expected_role) {

            $role_nama = [
                'admin utama' => 'Admin Utama',
                'admin al'    => 'Atasan Langsung',
                'opd'         => 'Pengaju Layanan',
                'petugas'     => 'Petugas Layanan'
            ];

            $role_tampil = $role_nama[$role_param] ?? ucfirst($role_param);

            $login_error = "Anda tidak memiliki akses sebagai <b>$role_tampil</b>. 
                            Silakan pilih jenis login yang sesuai dengan akun Anda.";

            $allow_html = true; // 🔥 izinkan HTML
        }

        elseif ($user['status'] !== 'aktif') {
            $login_error = "Akun Anda telah dinonaktifkan oleh Admin.";
        }

        else {
            session_regenerate_id(true);

            $_SESSION['user_id']        = (int)$user['id'];
            $_SESSION['email']          = $user['email'];
            $_SESSION['role']           = $user['role'];
            $_SESSION['nama_instansi']  = $user['nama_instansi'];

            if ($user['role'] === 'admin_atasan_langsung') {
                header("Location: kabid/dashboard-kabid.php");
            }
            elseif ($user['role'] === 'admin_utama') {
                header("Location: dashboard.php");
            }
            elseif ($user['role'] === 'opd_pengaju_layanan') {
                header("Location: opd/dashboard_opd.php");
            }
            elseif ($user['role'] === 'petugas_layanan') {

                $q = $mysqli->prepare("SELECT id FROM master_petugas WHERE user_id = ?");
                $q->bind_param("i", $user['id']);
                $q->execute();
                $res = $q->get_result()->fetch_assoc();

                if (!$res) {
                    die("Akun ini belum terhubung dengan data petugas.");
                }

                $_SESSION['petugas_id'] = $res['id'];

                header("Location: petugas/dashboard_petugas.php");
            }
            else {
                header("Location: index.php");
            }
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <?php
    $judul_halaman = [
        'admin utama' => 'Login Admin Utama',
        'admin al'    => 'Login Atasan Langsung',
        'opd'         => 'Login Pengaju Layanan',
        'petugas'     => 'Login Petugas Layanan'
    ];

    $title = $judul_halaman[$role_param] ?? 'Login Sistem';
    ?>

    <title><?= $title ?> - NETCARE</title>
</head>

<body>

<style>
body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:linear-gradient(120deg,#e9f2fb,#dfeaf6);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.login-card{
    width:380px;
    background:#fff;
    padding:40px;
    border-radius:18px;
    box-shadow:0 15px 35px rgba(0,0,0,0.15);
    text-align:center;
}

.subtitle{color:#777;margin-bottom:25px}

.role-select select{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ddd;
}

.input-group{
    display:flex;
    align-items:center;
    background:#f1f5fb;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

.input-group input{
    border:none;
    outline:none;
    background:none;
    width:100%;
}

.eye{cursor:pointer;margin-left:5px}

.btn-login{
    width:100%;
    padding:12px;
    background:#2c5f9e;
    border:none;
    color:#fff;
    font-size:16px;
    border-radius:8px;
    cursor:pointer;
}

.error-box{
    background:#ffe5e5;
    color:#b30000;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    font-size:14px;
    line-height:1.5;
}

.copy{font-size:12px;color:#999;margin-top:15px}
</style>

<div class="login-card">

<img src="images/netcare.png" style="width:150px">

<div class="subtitle"><b>Sistem Helpdesk Berbasis Digital</b></div>

<?php if ($login_error): ?>
<div class="error-box">
    <?= $allow_html ? $login_error : htmlspecialchars($login_error) ?>
</div>
<?php endif; ?>

<form method="GET" action="login.php" class="role-select">
<select name="role" onchange="this.form.submit()">
    <option value="admin utama" <?= $role_param == 'admin utama' ? 'selected' : '' ?>>Login Admin Utama</option>
    <option value="admin al" <?= $role_param == 'admin al' ? 'selected' : '' ?>>Login Atasan Langsung</option>
    <option value="opd" <?= $role_param == 'opd' ? 'selected' : '' ?>>Login Pengadu Layanan</option>
    <option value="petugas" <?= $role_param == 'petugas' ? 'selected' : '' ?>>Login Petugas Layanan</option>
</select>
</form>

<form action="login.php?role=<?= urlencode($role_param) ?>" method="post">

<div class="input-group">
<span>👤</span>
<input type="email" name="email" placeholder="Masukkan Email" required>
</div>

<div class="input-group">
<span>🔒</span>
<input type="password" id="password" name="password" placeholder="Masukkan Kata Sandi" required>
<span class="eye" onclick="togglePassword()">👁</span>
</div>

<button class="btn-login">Login</button>
</form>

<div class="copy">© 2026 NETCARE. Dinas Komunikasi dan Informatika.</div>

</div>

<script>
function togglePassword(){
    var x=document.getElementById("password");
    x.type=(x.type==="password")?"text":"password";
}
</script>

</body>
</html>