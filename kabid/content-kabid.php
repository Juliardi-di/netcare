<section class="content">
    <?php
    $page = $_GET['page'] ?? 'dashboard';

    $file = __DIR__ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $page . '.php';

    if (file_exists($file)) {
        include $file;
    } else {
        if ($page === 'dashboard') {
            echo "<p>Selamat datang di Sistem Informasi Layanan, Pemetaan, Monitoring, dan Pelaporan Jaringan Kabupaten Lingga.</p>";
        } else {
            echo "<p>Halaman <b>" . htmlspecialchars($page) . "</b> belum tersedia.</p>";
        }
    }
    ?>
</section>