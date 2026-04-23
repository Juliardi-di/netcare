<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db/config.php';

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin_utama'
) {
    echo "<h3 style='color:red'>Akses ditolak. Halaman ini hanya untuk Admin Utama.</h3>";
    exit;
}

$success = '';
$error   = '';
if (!empty($_POST['jenis_akun']) && in_array($_POST['jenis_akun'], ['opd_pengaju_layanan', 'petugas_layanan'])) {

    $nama       = trim($_POST['nama']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $jenisAkun  = $_POST['jenis_akun'];

    if ($nama === '' || $email === '' || $password === '') {
        $error = "Semua field wajib diisi.";
    } else {

        $cek = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $cek->bind_param("s", $email);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = "Email sudah terdaftar.";
        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $nama_instansi = ($jenisAkun === 'opd_pengaju_layanan') ? $nama : null;

            $stmt = $mysqli->prepare("
                INSERT INTO users 
                (email, password, role, nama, nama_instansi, status, created_by)
                VALUES (?, ?, ?, ?, ?, 'aktif', 'admin_utama')
            ");

            $stmt->bind_param(
                "sssss",
                $email,
                $hash,
                $jenisAkun,
                $nama,
                $nama_instansi
            );

            if ($stmt->execute()) {
                $success = "Akun berhasil didaftarkan.";
            } else {
                $error = "Gagal menyimpan data: " . $stmt->error;
            }
        }
    }
}
/* ===============================
   PROSES AKTIF / NONAKTIFKAN AKUN
================================= */
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    // Ambil status sekarang
    $cekStatus = $mysqli->prepare("
        SELECT status 
        FROM users 
        WHERE id = ?

    ");
    $cekStatus->bind_param("i", $id);
    $cekStatus->execute();
    $resultStatus = $cekStatus->get_result();

    if ($rowStatus = $resultStatus->fetch_assoc()) {

        $statusBaru = ($rowStatus['status'] === 'aktif') ? 'nonaktif' : 'aktif';

        $update = $mysqli->prepare("
            UPDATE users 
            SET status = ? 
            WHERE id = ?
        ");
        $update->bind_param("si", $statusBaru, $id);

if ($update->execute()) {

    header("Location: dashboard.php?page=registrasi_akun&msg=success");
    exit;

} else {

    header("Location: dashboard.php?page=registrasi_akun&msg=error");
    exit;
}

    }
}

/* ===============================
   NOTIFIKASI SETELAH REDIRECT
================================= */
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'success') {
        $success = "Status akun berhasil diperbarui.";
    } elseif ($_GET['msg'] == 'error') {
        $error = "Terjadi kesalahan saat memperbarui status.";
    }
}
/* ===============================
   DOWNLOAD REKAP EXCEL
================================= */
if (isset($_GET['download']) && $_GET['download'] == 'rekap') {

    $search = trim($_GET['search'] ?? '');

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=rekap_akun_" . date('Ymd_His') . ".xls");

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
}



?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Akun OPD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="bg-light">

<div class="container mt-4">
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $success ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $error ?>
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">REGISTRASI AKUN</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRegistrasiOPD">
            + Daftarkan OPD
        </button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRegistrasiPetugas">
            + Daftarkan Petugas
        </button>
    </div>
</div>
</div>
<div class="modal fade" id="modalRegistrasiOPD" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">

      <form method="post">
        <input type="hidden" name="jenis_akun" value="opd_pengaju_layanan">

        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">REGISTRASI AKUN OPD</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label fw-semibold">Nama OPD</label>
            <input type="text" name="nama" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Email OPD</label>
            <input type="email" name="email" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Password Awal</label>
            <input type="password" name="password" class="form-control" required>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success fw-bold">Simpan OPD</button>
        </div>

      </form>

    </div>
  </div>
</div>

<div class="modal fade" id="modalRegistrasiPetugas" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 shadow">

      <form method="post">
        <input type="hidden" name="jenis_akun" value="petugas_layanan">

        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">REGISTRASI AKUN PETUGAS</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Petugas</label>
            <input type="text" name="nama" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Email Petugas</label>
            <input type="email" name="email" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Password Awal</label>
            <input type="password" name="password" class="form-control" required>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" name="submit_petugas" class="btn btn-primary fw-bold">
            Simpan Petugas
        </button>

        </div>

      </form>

    </div>
  </div>
</div>
<?php
$limit  = 10;
$page   = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$page   = max($page, 1);
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');

$rekap = [];

/* ===========================
   HITUNG TOTAL DATA
=========================== */
if ($search !== '') {

    $stmtCount = $mysqli->prepare("
        SELECT COUNT(*) as total
        FROM users
        WHERE created_by = 'admin_utama'
        AND (nama LIKE ? OR email LIKE ? OR role LIKE ?)
    ");

    $like = "%$search%";
    $stmtCount->bind_param("sss", $like, $like, $like);

} else {

    $stmtCount = $mysqli->prepare("
        SELECT COUNT(*) as total
        FROM users
        WHERE created_by = 'admin_utama'
    ");
}

$stmtCount->execute();
$totalData = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPage = ceil($totalData / $limit);
if ($totalPage < 1) {
    $totalPage = 1;
}
if ($page > $totalPage) {
    $page = $totalPage;
    $offset = ($page - 1) * $limit;
}



/* ===========================
   AMBIL DATA DENGAN PAGINATION
=========================== */
if ($search !== '') {

    $stmtRekap = $mysqli->prepare("
        SELECT id, nama, email, role, status, created_at
        FROM users
        WHERE created_by = 'admin_utama'
        AND (nama LIKE ? OR email LIKE ? OR role LIKE ?)
        ORDER BY id DESC
        LIMIT ? OFFSET ?
    ");

    $stmtRekap->bind_param("sssii", $like, $like, $like, $limit, $offset);

} else {

    $stmtRekap = $mysqli->prepare("
        SELECT id, nama, email, role, status, created_at
        FROM users
        WHERE created_by = 'admin_utama'
        ORDER BY id DESC
        LIMIT ? OFFSET ?
    ");

    $stmtRekap->bind_param("ii", $limit, $offset);
}

$stmtRekap->execute();
$result = $stmtRekap->get_result();
while ($row = $result->fetch_assoc()) {
    $rekap[] = $row;
}

?>

<div class="card shadow-sm mt-4">
    <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">🗂️ Rekap Akun Terdaftar</h5>

    <a href="admin/export_rekap_akun.php?search=<?= urlencode($search) ?>"
       class="btn btn-success">
        ⬇ Download Rekap
    </a>
</div>

<form method="get" class="row mb-3">
    <input type="hidden" name="page" value="registrasi_akun">

    <div class="col-md-4">
        <input type="text" 
               name="search" 
               class="form-control"
               placeholder="Cari nama / email / role..."
               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    </div>

    <div class="col-md-2">
        <button type="submit" class="btn btn-dark w-100">
            🔍 Cari
        </button>
    </div>

    <div class="col-md-2">
        <a href="dashboard.php?page=registrasi_akun" 
           class="btn btn-secondary w-100">
            Reset
        </a>
    </div>
</form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rekap) > 0): ?>
                        <?php $no = 1; foreach ($rekap as $r): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= htmlspecialchars($r['nama']) ?></td>
                                <td><?= htmlspecialchars($r['email']) ?></td>
<td>
    <?php 
        if ($r['role'] == 'opd_pengaju_layanan') {
            echo '<span class="badge bg-success">OPD</span>';
        } elseif ($r['role'] == 'petugas_layanan') {
            echo '<span class="badge bg-primary">Petugas</span>';
        } elseif ($r['role'] == 'admin_utama') {
            echo '<span class="badge bg-danger">Admin Utama</span>';
        } elseif ($r['role'] == 'admin_atasan_langsung') {
            echo '<span class="badge bg-warning text-dark">Kabid</span>';
        } else {
            echo '<span class="badge bg-secondary">'.$r['role'].'</span>';
        }
    ?>
</td>

<td>

<?php if ($r['status'] == 'aktif'): ?>
    
    <a href="dashboard.php?page=registrasi_akun&toggle_status=1&id=<?= $r['id'] ?>"
       class="btn btn-sm btn-success"
       onclick="return confirm('Nonaktifkan akun ini?')">
        Aktif
    </a>

<?php else: ?>

    <a href="dashboard.php?page=registrasi_akun&toggle_status=0&id=<?= $r['id'] ?>"
       class="btn btn-sm btn-danger"
       onclick="return confirm('Aktifkan akun ini?')">
        Nonaktif
    </a>

<?php endif; ?>

</td>

                                <td>
                                    <?= date('d-m-Y H:i', strtotime($r['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Belum ada akun yang didaftarkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <nav class="mt-3">
    <ul class="pagination justify-content-center">

        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link"
                   href="dashboard.php?page=registrasi_akun&halaman=<?= $page-1 ?>&search=<?= urlencode($search) ?>">
                   « Sebelumnya
                </a>
            </li>
        <?php endif; ?>

        <li class="page-item disabled">
            <span class="page-link">
                Halaman <?= $page ?> dari <?= $totalPage ?>
            </span>
        </li>

        <?php if ($page < $totalPage): ?>
            <li class="page-item">
                <a class="page-link"
                   href="dashboard.php?page=registrasi_akun&halaman=<?= $page+1 ?>&search=<?= urlencode($search) ?>">
                   Selanjutnya »
                </a>
            </li>
        <?php endif; ?>

    </ul>
</nav>

        </div>
    </div>
</div>

</body>
</html>