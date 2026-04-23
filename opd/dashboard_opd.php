<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'opd_pengaju_layanan') {
    header("Location: ../login.php?role=opd");
    exit;
}

$email = $_SESSION['email'] ?? 'OPD';
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

    <?php include __DIR__ . '/sidebar_opd.php'; ?>

    <main class="main-content">
    <div style="
        width:100%;
        color:green;
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


    <?php include __DIR__ . '/content_opd.php'; ?>

</main>

</body>
</html>
