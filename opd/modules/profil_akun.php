<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /netcare/login.php");
    exit;
}

$id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, nama, email, role, images, nama_instansi, telepon, alamat
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$mapRole = [
    'opd_pengaju_layanan' => 'OPD Pengaju Layanan',
    'admin' => 'Administrator'
];

$roleTampil = $mapRole[$user['role']] ?? ucwords(str_replace('_',' ',$user['role']));
$images = $user['images'] ?: 'default.png';

$fotoPath = "/netcare/uploads/profil/" . $images;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Profil Akun</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f4f8f6;
    font-family:'Segoe UI',sans-serif;
}

:root{
    --main:#005477;
    --main-dark:#003b4a;
}

.card{
    border:none;
    border-radius:12px;
}
.card-header{
    background:var(--main);
    color:#fff;
}

.profile-img{
    width:160px;
    height:160px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid var(--main);
}

.btn-main{
    background:var(--main);
    color:#fff;
}
.btn-main:hover{
    background:var(--main-dark);
}

.btn-main-outline{
    border:2px solid var(--main);
    color:var(--main);
}
.btn-main-outline:hover{
    background:var(--main);
    color:#fff;
}

.badge-main{
    background:var(--main);
}
</style>
</head>

<body>

<div class="container my-4">

<!-- ALERT -->
<?php if (($_GET['success'] ?? '') === 'profil'): ?>
<div class="alert alert-success">✅ Profil berhasil diperbarui</div>
<?php endif; ?>

<?php if (($_GET['success'] ?? '') === 'password'): ?>
<div class="alert alert-success">✅ Password berhasil diperbarui</div>
<?php endif; ?>

<?php if (($_GET['success'] ?? '') === 'foto'): ?>
<div class="alert alert-success">✅ Foto berhasil diupload</div>
<?php endif; ?>

<!-- ================= PROFIL ================= -->
<div class="card shadow mb-4">
<div class="card-header fw-bold">Profil Peserta</div>

<div class="card-body d-flex gap-4">

<!-- KIRI -->
<div style="width:220px;text-align:center;">
    <img src="<?= $fotoPath ?>"
         onerror="this.src='/netcare/uploads/profil/default.png'"
         class="profile-img">

    <button class="btn btn-main-outline w-100 mt-3" data-bs-toggle="collapse" data-bs-target="#ubah-foto">
        ✏️ Ubah Foto
    </button>

    <button class="btn btn-main-outline w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#edit-profil">
        👤 Update Data
    </button>

    <button class="btn btn-main-outline w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#ganti-password">
        🔒 Ganti Password
    </button>
</div>

<!-- KANAN -->
<div class="flex-fill">
    <h5 class="text-muted mb-3">Informasi Akun</h5>

    <p><strong>Nama</strong><br><?= htmlspecialchars($user['nama']) ?></p>
    <p><strong>Email</strong><br><?= htmlspecialchars($user['email']) ?></p>

    <p><strong>Role</strong><br>
        <span class="badge badge-main"><?= $roleTampil ?></span>
    </p>

    <p><strong>Instansi</strong><br><?= htmlspecialchars($user['nama_instansi']) ?></p>
    <p><strong>Telepon</strong><br><?= htmlspecialchars($user['telepon']) ?></p>
    <p><strong>Alamat</strong><br><?= htmlspecialchars($user['alamat']) ?></p>
</div>

</div>
</div>

<!-- ================= EDIT PROFIL ================= -->
<div class="collapse" id="edit-profil">
<div class="card shadow mb-4">
<div class="card-header fw-bold">Edit Profil</div>
<div class="card-body">

<form method="POST" action="/netcare/opd/modules/update_profil.php"
      onsubmit="return confirm('Simpan perubahan?')">

<div class="mb-3">
    <label class="form-label fw-bold">Nama</label>
    <input type="text" name="nama" class="form-control"
           value="<?= htmlspecialchars($user['nama']) ?>" required>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Email</label>
    <input type="email" name="email" class="form-control"
           value="<?= htmlspecialchars($user['email']) ?>" required>
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Telepon</label>
    <input type="text" name="telepon" class="form-control"
           value="<?= htmlspecialchars($user['telepon']) ?>">
</div>

<div class="mb-3">
    <label class="form-label fw-bold">Alamat</label>
    <textarea name="alamat" class="form-control"><?= htmlspecialchars($user['alamat']) ?></textarea>
</div>

<div class="text-end">
    <button type="button" class="btn btn-secondary"
            data-bs-toggle="collapse" data-bs-target="#edit-profil">
        Cancel
    </button>

    <button class="btn btn-main">Simpan</button>
</div>

</form>

</div>
</div>
</div>

<!-- ================= FOTO ================= -->
<div class="collapse" id="ubah-foto">
<div class="card shadow mb-4">
<div class="card-header fw-bold">Ubah Foto</div>
<div class="card-body">

<form method="POST"
      action="/netcare/opd/modules/proses_ubah_foto.php"
      enctype="multipart/form-data"
      onsubmit="return confirm('Upload foto baru?')">

<input type="file" name="foto" class="form-control mb-3"
       accept="image/jpeg,image/png,image/webp"
       onchange="previewFoto(event)" required>

<small class="text-muted">Maksimal ukuran 2MB</small>

<img id="preview" style="max-width:150px;display:none;" class="mt-2">

<div class="text-end mt-3">
    <button type="button" class="btn btn-secondary"
            data-bs-toggle="collapse" data-bs-target="#ubah-foto">
        Cancel
    </button>

    <button class="btn btn-main">Upload</button>
</div>

</form>

</div>
</div>
</div>

<!-- ================= PASSWORD ================= -->
<div class="collapse" id="ganti-password">
<div class="card shadow mb-4">
<div class="card-header fw-bold">Ganti Password</div>
<div class="card-body">

<form method="POST"
      action="/netcare/opd/modules/update_password.php"
      onsubmit="return confirm('Yakin ganti password?')">

<input type="password" name="password_lama"
       class="form-control mb-2"
       placeholder="Password Lama" required>

<input type="password" name="password_baru"
       class="form-control mb-2"
       placeholder="Password Baru" required>

<input type="password" name="konfirmasi"
       class="form-control mb-3"
       placeholder="Konfirmasi Password" required>

<div class="text-end">
    <button type="button"
            class="btn btn-secondary"
            data-bs-toggle="collapse"
            data-bs-target="#ganti-password">
        Cancel
    </button>

    <button class="btn btn-main">
        Update Password
    </button>
</div>

</form>

</div>
</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function previewFoto(event){
    const file = event.target.files[0];
    if (!file) return;

    const img = document.getElementById('preview');
    img.src = URL.createObjectURL(file);
    img.style.display = 'block';
}
</script>

</body>
</html>