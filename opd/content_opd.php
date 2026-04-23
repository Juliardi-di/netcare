<section class="content">
    <?php
    $allowed_pages = [
    'dashboard',
    'profil_akun',
    'pengajuan_layanan',
    'pengaduan_saya',
    'riwayat_pengajuan',
    'bantuan_faq'
];
$file = __DIR__ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $page . '.php';
if (in_array($page, $allowed_pages)) {
    $file = __DIR__ . '/modules/' . $page . '.php';
    if (file_exists($file)) {
        include $file;
    }
} else {
    if ($page === 'dashboard') {
            echo "<p>Selamat datang di Dashboard OPD Pengaju Sistem Informasi Layanan, Pemetaan, Monitoring, dan Pelaporan Jaringan Kabupaten Lingga.</p>";
        } else {
            echo "<p>Halaman <b>" . htmlspecialchars($page) . "</b> belum tersedia.</p>";
        }
    
}


    ?>
</section>
