<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'petugas_layanan') {
    header("Location: ../login.php?role=petugas");
    exit;
}
$_SESSION['petugas_id'] = $_SESSION['user_id'];
$email = $_SESSION['email'] ?? 'PETUGAS';
$page  = $_GET['page'] ?? 'dashboard';


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($page) ?> | netcare</title>
    <link rel="stylesheet" href="/netcare/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<?php include __DIR__ . '/sidebar_petugas.php'; ?>

<main class="main-content">

<section class="hero" style="padding:40px;">
    <div style="
        max-width:100%;
        background:linear-gradient(135deg,#0d6efd,#198754);
        color:white;
        padding:30px;
        border-radius:12px;
        text-align:center;
        box-shadow:0 4px 10px rgba(0,0,0,.2);
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:15px;
    ">

        <img src="../images/logo_pemkab_lingga.png"
             alt="Pemkab Lingga"
             style="height:80px; object-fit:contain;">

        <div style="text-align:center; flex:1;">
            <div id="tanggal" style="font-size:18px;"></div>
            <div id="jam" style="font-size:46px; font-weight:bold;"></div>
        </div>

        <img src="../images/komdigi.png"
             alt="Komdigi"
             style="height:80px; object-fit:contain;">

    </div>
</div>
</section>

    <?php include __DIR__ . '/content_petugas.php'; ?>


    
</main>
<script>
function updateWaktu() {
    const hariNama = ["Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu"];
    const bulanNama = [
        "Januari","Februari","Maret","April","Mei","Juni",
        "Juli","Agustus","September","Oktober","November","Desember"
    ];

    const now = new Date();

    const hari = hariNama[now.getDay()];
    const tanggal = now.getDate();
    const bulan = bulanNama[now.getMonth()];
    const tahun = now.getFullYear();

    let jam = now.getHours();
    let menit = now.getMinutes();
    let detik = now.getSeconds();

    jam = jam < 10 ? "0" + jam : jam;
    menit = menit < 10 ? "0" + menit : menit;
    detik = detik < 10 ? "0" + detik : detik;

    document.getElementById("tanggal").innerHTML =
        hari + ", " + tanggal + " " + bulan + " " + tahun;

    document.getElementById("jam").innerHTML =
        jam + ":" + menit + ":" + detik + " WIB";
}

setInterval(updateWaktu, 1000);
updateWaktu();
</script>
</body>
</html>
