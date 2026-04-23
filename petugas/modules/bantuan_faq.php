<?php?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bantuan & FAQ - E-Layanan Government</title>
    <style>
   
        html,
        body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f8f6;
            color: #2c3e50;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
        }

        h2 {
            color: hsla(113, 96%, 38%, 1.00);
            margin-bottom: 20px;
            text-align: center;
        }

        .search-box {
            margin: 0 auto 25px;
            max-width: 600px;
            width: 100%;
        }

        .search-box input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #ccc;
            border-radius: 10px;
            outline: none;
            font-size: 15px;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: #1a7f3c;
            box-shadow: 0 0 5px rgba(26, 127, 60, 0.3);
        }

        .faq {
            display: flex;
            flex-direction: column;
            gap: 16px;
            max-width: 900px;
            width: 100%;
        }

        .faq-item {
            background: #fff;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            transition: 0.2s;
        }

        .faq-item:hover {
            transform: translateY(-2px);
        }

        .faq-question {
            cursor: pointer;
            font-weight: bold;
            color: #1a7f3c;
            position: relative;
            padding-right: 20px;
        }

        .faq-question::after {
            content: "\002B";
            position: absolute;
            right: 0;
            font-weight: bold;
            color: #1a7f3c;
        }

        .faq-answer {
            display: none;
            margin-top: 10px;
            font-size: 15px;
            color: #444;
            line-height: 1.6;
        }

        .faq-item.open .faq-question::after {
            content: "\2212";
        }

        .faq-item.open .faq-answer {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
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