<section class="content">
    <?php
    $page = $_GET['page'] ?? 'dashboard';

    $file = __DIR__ . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . $page . '.php';

    if (file_exists($file)) {
        include $file;
    } else {
        if ($page === 'dashboard') {
            echo "<p>Selamat datang di dashboard utama.</p>";
        } else {
            echo "<p>Halaman <b>" . htmlspecialchars($page) . "</b> belum tersedia.</p>";
        }
    }
    ?>
</section>