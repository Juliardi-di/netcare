<?php
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bantuan dan Faq</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <main>
        <h2>❓ Bantuan & FAQ</h2>

        <div class="search-box">
            <input type="text" id="search" placeholder="Cari pertanyaan...">
        </div>

        <div class="faq" id="faq">
            <div class="faq-item">
                <div class="faq-question">Bagaimana cara mengajukan layanan?</div>
                <div class="faq-answer">
                    <p>Masuk ke menu <b>Pengajuan Layanan</b>, pilih jenis layanan, lengkapi form yang tersedia, lalu
                        klik
                        tombol <b>Kirim</b>.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Apakah saya bisa melacak status pengajuan?</div>
                <div class="faq-answer">
                    <p>Ya, status pengajuan bisa dilihat di menu <b>Dashboard</b> atau <b>Profil & Akun</b> Anda.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">Jenis layanan apa saja yang tersedia?</div>
                <div class="faq-answer">
                    <p>Layanan perizinan, fasilitas teknologi informasi, dokumentasi kegiatan, dan layanan umum
                        pemerintahan
                        lainnya.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Bagaimana cara menghubungi admin jika ada kendala?</div>
                <div class="faq-answer">
                    <p>Gunakan menu <b>Bantuan</b> ini atau hubungi kontak resmi Diskominfo Kabupaten Lingga melalui
                        email/telepon yang tersedia.</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.querySelectorAll(".faq-question").forEach(item => {
            item.addEventListener("click", () => {
                const parent = item.parentElement;
                parent.classList.toggle("open");
            });
        });

        document.getElementById("search").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            document.querySelectorAll(".faq-item").forEach(item => {
                let text = item.innerText.toLowerCase();
                item.style.display = text.includes(filter) ? "block" : "none";
            });
        });
    </script>
    <footer>
        &copy; <?php echo date("Y"); ?> Sistem Informasi Layanan, Pemetaan, Monitoring, dan Pelaporan Jaringan Kabupaten Lingga - Dinas Kominfo Kab. Lingga
    </footer>
</body>

</html>