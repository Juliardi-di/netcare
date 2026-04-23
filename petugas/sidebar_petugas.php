<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../images/netcare.png" style="width: 100px; height: auto;" alt="Logo NETCARE" class="logo">
        <h2 style="color: #005477;">SISTEM HELPDESK BERBASIS DIGITAL</h2>
    </div>

    <ul>
        <?php
      $menus = [

    'dashboard' => ['Dashboard', 'fa-solid fa-chart-line'],

    'profil_akun' => ['Profil dan Akun', 'fa-solid fa-user'],

    // FIX DI SINI
    'pekerjaan' => ['Pekerjaan Saya', 'fa-solid fa-list-check'],

    'progres' => ['Progres Pekerjaan', 'fa-solid fa-gears'],

    'riwayat_pekerjaan' => ['Riwayat Pekerjaan', 'fa-solid fa-clock-rotate-left'],

    'laporan_petugas' => ['Laporan Petugas', 'fa-solid fa-file-lines'],

    'bantuan_faq' => ['Bantuan / FAQ', 'fa-solid fa-circle-question'],
];

        foreach ($menus as $key => [$label, $icon]) {
            $active = ($page == $key) ? 'active' : '';
            echo "
                <li>
                    <a href='dashboard_petugas.php?page=$key' class='$active'>
                        <i class='$icon'></i>
                        <span>$label</span>
                    </a>
                </li>
            ";
        }
        ?>

        <li>
            <a href="logout_petugas.php" class="logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</aside>