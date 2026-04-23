<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'user';
$email = $_SESSION['email'] ?? 'Pengguna';
$page = $_GET['page'] ?? 'dashboard';

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= ucfirst($page) ?>Admin Utama</title>
    <link rel="stylesheet" href="/netcare/css/dashboard.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
    <div style="
        width:100%;
        color:#005477;
        padding: 26.5px 15px;
        text-align:center;
        display:flex;
        align-items: flex-start;
        align-items:center;
        justify-content:space-between;
        flex-wrap:wrap;
        gap:15px;
        border-bottom:1px solid #e5e5e5;
    ">
        <!-- <img src="/netcare/images/logo_pemkab_lingga.png"
             alt="Pemkab Lingga"
             style="height:80px; object-fit:contain;"> -->

        <div style="text-align:center; flex:1;">
            <div id="tanggal" style="font-size:18px;"></div>
            <div id="jam" style="font-size:46px; font-weight:bold;"></div>
        </div>

        <!-- <img src="/netcare/images/komdigi.png"
             alt="Komdigi"
             style="height:80px; object-fit:contain;"> -->

    </div>
    </section>


        <?php include __DIR__ . '/content.php'; ?>
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