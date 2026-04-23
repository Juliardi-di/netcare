<section class="content">
<?php

$page = $_GET['page'] ?? 'dashboard';

$routes = [
    'dashboard'           => 'dashboard.php',
    'pekerjaan'           => 'pekerjaan_saya.php',
    'progres'             => 'progres_pekerjaan.php',
    'profil_akun'         => 'profil_akun.php',
    'riwayat_pekerjaan'   => 'riwayat_pekerjaan.php',
    'laporan_petugas'     => 'laporan_petugas.php',
    'bantuan_faq'         => 'bantuan_faq.php',
];

if (array_key_exists($page, $routes)) {

    include __DIR__ . '/modules/' . $routes[$page];

} else {

    echo "<p>Halaman <b>" . htmlspecialchars($page) . "</b> tidak ditemukan.</p>";

}
?>
</section>