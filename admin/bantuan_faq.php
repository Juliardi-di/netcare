<?php
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bantuan dan Faq</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <main>
        <div class="faq-wrapper">
        <h2 class="faq-title">❓ Bantuan & FAQ netcare</h2>

        <div class="search-box">
            <input type="text" id="search" placeholder="Cari pertanyaan...">
        </div>

        <div class="faq" id="faq">

            <div class="faq-item">
                <div class="faq-question">Apa itu aplikasi netcare?</div>
                <div class="faq-answer">
                    <p>netcare adalah sistem layanan video conference dan live streaming resmi Pemerintah Kabupaten Lingga untuk mendukung kegiatan pemerintahan secara daring.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Siapa saja yang dapat menggunakan sistem ini?</div>
                <div class="faq-answer">
                    <p>Pengguna terdiri dari beberapa level seperti Administrator, Operator Perangkat Daerah, dan Petugas, sesuai dengan hak akses masing-masing.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Bagaimana cara login ke sistem?</div>
                <div class="faq-answer">
                    <p>Masukkan email dan kata sandi yang telah diberikan, lalu klik tombol <b>Masuk</b> sesuai jenis akun Anda.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Apa yang harus dilakukan jika lupa password?</div>
                <div class="faq-answer">
                    <p>Silakan hubungi administrator atau petugas pengelola sistem untuk dilakukan reset kata sandi.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Apakah akun dan data pengguna aman?</div>
                <div class="faq-answer">
                    <p>Ya. Sistem menggunakan autentikasi email dan kata sandi terenkripsi (hashed password) untuk menjaga keamanan data dan mencegah akses tidak sah.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Apa fungsi utama netcare?</div>
                <div class="faq-answer">
                    <p>Untuk pelaksanaan video conference, live streaming kegiatan pemerintah, pengelolaan jadwal siaran, dan dokumentasi kegiatan secara digital.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Bagaimana cara menjadwalkan kegiatan live streaming?</div>
                <div class="faq-answer">
                    <p>Administrator atau operator dapat membuat jadwal kegiatan melalui dashboard sistem dan membagikan tautan kepada peserta.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Apakah masyarakat dapat menonton live streaming?</div>
                <div class="faq-answer">
                    <p>Ya, untuk kegiatan yang bersifat publik, masyarakat dapat mengakses siaran melalui tautan atau kanal resmi yang disediakan.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Mengapa saat ini menggunakan akun sementara?</div>
                <div class="faq-answer">
                    <p>Akun sementara digunakan pada tahap uji coba dan implementasi awal dalam rangka kegiatan aktualisasi Latsar CPNS Tahun 2026.</p>
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">Siapa yang dapat dihubungi jika terjadi kendala teknis?</div>
                <div class="faq-answer">
                    <p>Silakan menghubungi tim pengelola TIK atau administrator Diskominfo Kabupaten Lingga untuk mendapatkan bantuan teknis.</p>
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
        &copy; <?php echo date("Y"); ?> netcare - Sistem Layanan Government Video Conference dan Live Streaming Kabupaten Lingga
    </footer>
</body>

</html>
