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

if (!$user) {
    die("User tidak ditemukan");
}

$nama   = $user['nama'];
$email  = $user['email'];
$role   = $user['role'];
$images = $user['images'] ?: 'default.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Akun | netcare</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="../css/style.css">
</head>

<body class="page-profil bg-light">

<div class="container my-4">

    <?php if (($_GET['success'] ?? '') === 'profil'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            ✅ Profil berhasil diperbarui
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (($_GET['success'] ?? '') === 'password'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            🔐 Password berhasil diperbarui
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm">
    <div class="card-header fw-bold">Profil Peserta</div>
    <div class="card-body d-flex gap-4">

        <!-- KIRI -->
        <div style="width:220px;text-align:center;">
            <div style="
                width:160px;
                height:160px;
                margin:0 auto 15px;
                border-radius:50%;
                background:#d60000;
                overflow:hidden;">
                <img src="/netcare/kabid/uploads/profil/<?= htmlspecialchars($images) ?>"
                     style="width:100%;height:100%;object-fit:cover">
            </div>

            <button type="button" onclick="toggleFoto()" class="btn btn-outline-primary w-100 mb-2">
            ✏️ Ubah Foto
            </button>

            <button type="button" onclick="toggleEditProfil()" class="btn btn-outline-success w-100 mb-2">
            👤 Update Data Pegawai
            </button>

            <button type="button" onclick="togglePassword()" class="btn btn-outline-warning w-100 mb-2">
            🔒 Ubah Kata Sandi
            </button>

        </div>

        <!-- KANAN -->
        <div class="flex-fill">
            <p><strong>Nama</strong><br><?= htmlspecialchars($nama) ?></p>
            <p><strong>Email</strong><br><?= htmlspecialchars($email) ?></p>
            <p><strong>Role</strong><br><?= htmlspecialchars($role) ?></p>

            <?php if (!empty($user['nama_instansi'])): ?>
                <p><strong>Instansi</strong><br><?= htmlspecialchars($user['nama_instansi']) ?></p>
            <?php endif; ?>

            <?php if (!empty($user['telepon'])): ?>
                <p><strong>Telepon</strong><br><?= htmlspecialchars($user['telepon']) ?></p>
            <?php endif; ?>

            <?php if (!empty($user['alamat'])): ?>
                <p><strong>Alamat</strong><br><?= htmlspecialchars($user['alamat']) ?></p>
            <?php endif; ?>
        </div>

    </div>
</div>


<div class="card mb-4 shadow-sm" id="edit-profil" style="display:none;">
        <div class="card-header fw-bold">Edit Profil</div>
        <div class="card-body">
            <form method="POST" action="/netcare/kabid/modules/update_profil.php">

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= htmlspecialchars($email) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control"
                           value="<?= htmlspecialchars($nama) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="telepon" class="form-control"
                           value="<?= htmlspecialchars($user['telepon']) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control"
                              rows="3"><?= htmlspecialchars($user['alamat']) ?></textarea>
                </div>

                <div class="text-end">
                    <button class="btn btn-primary">Simpan Profil</button>
                </div>
            </form>
        </div>
    </div>

<div class="card shadow-sm" id="ganti-password" style="display:none;">
        <div class="card-header fw-bold">Ganti Password</div>
        <div class="card-body">
            <form method="POST" action="/netcare/kabid/modules/update_password.php">


                <div class="mb-3">
                    <label class="form-label">Password Lama</label>
                    <input type="password" name="password_lama" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password_baru" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="konfirmasi" class="form-control" required>
                </div>

                <div class="text-end">
                    <button class="btn btn-warning">Perbarui Password</button>
                </div>
            </form>
        </div>
    </div>

</div>
<div class="card shadow-sm" id="ubah-foto" style="display:none;">
    <div class="card-header fw-bold">Ubah Foto Profil</div>
    <div class="card-body">

        <?php if (($_GET['error'] ?? '') === 'format'): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                ❌ Format file tidak valid (JPG, PNG, WEBP)
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (($_GET['error'] ?? '') === 'size'): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                ❌ Ukuran file maksimal 2MB
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (($_GET['success'] ?? '') === 'foto'): ?>
            <div class="alert alert-success alert-dismissible fade show">
                🖼️ Foto berhasil diperbarui
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

    <form method="POST"
      action="/netcare/kabid/modules/proses_ubah_foto.php"
      enctype="multipart/form-data">


            <div class="mb-3">
                <label class="form-label">Pilih Foto Baru</label>
                <input type="file"
                       name="foto"
                       class="form-control"
                       accept="image/jpeg,image/png,image/webp"
                       required>
            </div>

            <div class="text-end">
                <button class="btn btn-primary">Upload Foto</button>
            </div>

        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleEditProfil() {
    const edit = document.getElementById('edit-profil');
    const pass = document.getElementById('ganti-password');
    const foto = document.getElementById('ubah-foto');

    edit.style.display = edit.style.display === 'none' ? 'block' : 'none';
    pass.style.display = 'none';
    foto.style.display = 'none';
}

function togglePassword() {
    const edit = document.getElementById('edit-profil');
    const pass = document.getElementById('ganti-password');
    const foto = document.getElementById('ubah-foto');

    pass.style.display = pass.style.display === 'none' ? 'block' : 'none';
    edit.style.display = 'none';
    foto.style.display = 'none';
}

function toggleFoto() {
    const edit = document.getElementById('edit-profil');
    const pass = document.getElementById('ganti-password');
    const foto = document.getElementById('ubah-foto');

    foto.style.display = foto.style.display === 'none' ? 'block' : 'none';
    edit.style.display = 'none';
    pass.style.display = 'none';
}
</script>


</body>
</html>