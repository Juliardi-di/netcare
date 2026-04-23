<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "netcare";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$jenis = $_POST['jenis'] ?? '';
$keterangan = $_POST['keterangan'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

$status = "";
$message = "";

if (empty($jenis) || empty($keterangan)) {
    $status = "error";
    $message = "⚠️ Jenis layanan dan keterangan wajib diisi.";
} else {
    $stmt = $conn->prepare("INSERT INTO pengajuan_layanan (user_id, jenis, keterangan, created_at) VALUES (?, ?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("iss", $user_id, $jenis, $keterangan);
        if ($stmt->execute()) {
            $status = "success";
            $message = "✅ Pengajuan berhasil disimpan!";
        } else {
            $status = "error";
            $message = "❌ Gagal menyimpan pengajuan: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $status = "error";
        $message = "❌ Query prepare gagal: " . $conn->error;
    }
}
$conn->close();
?>

<div class="pengajuan-wrapper">
    <div class="pengajuan-box">
        <h2>📌 Status Pengajuan</h2>
        <div class="status-icon">
            <?php echo ($status === "success") ? "✅" : "❌"; ?>
        </div>
        <p class="<?php echo $status; ?>"><?php echo htmlspecialchars($message); ?></p>
        <a href="dashboard.php?page=layanan" class="btn-kembali">⬅️ Kembali ke Dashboard</a>
    </div>
</div>

<style>
    .pengajuan-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 50px 0;
    }

    .pengajuan-box {
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        text-align: center;
        max-width: 420px;
        width: 100%;
        animation: fadeIn 0.4s ease-in-out;
    }

    .pengajuan-box h2 {
        margin-bottom: 20px;
        font-size: 20px;
        color: #333;
    }

    .status-icon {
        font-size: 40px;
        margin-bottom: 10px;
    }

    .success {
        color: #2ecc71;
        font-weight: bold;
    }

    .error {
        color: #e74c3c;
        font-weight: bold;
    }

    .btn-kembali {
        display: inline-block;
        padding: 10px 20px;
        margin-top: 20px;
        background: linear-gradient(135deg, #2c7be5, #00c6ff);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-kembali:hover {
        background: linear-gradient(135deg, #0056b3, #00aaff);
        transform: translateY(-2px);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>