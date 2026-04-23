<?php?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bantuan & FAQ - E-Layanan Government</title>

    <style>
        :root{
            --main:#005477;
            --main-light:#e6f2f5;
        }

        html, body {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f4f8f6;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* HEADER */
        .header-box{
            width:100%;
            max-width:900px;
            background:var(--main);
            color:#fff;
            padding:15px 20px;
            border-radius:10px;
            margin-bottom:20px;
            font-size:20px;
            font-weight:bold;
        }

        /* SEARCH */
        .search-box {
            margin-bottom: 25px;
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
            transition: 0.3s;
        }

        .search-box input:focus {
            border-color: var(--main);
            box-shadow: 0 0 5px rgba(0,84,119,0.3);
        }

        /* FAQ */
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
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            border-left:5px solid var(--main);
            transition: 0.2s;
        }

        .faq-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 14px rgba(0,0,0,0.1);
        }

        .faq-question {
            cursor: pointer;
            font-weight: bold;
            color: var(--main);
            position: relative;
            padding-right: 25px;
        }

        .faq-question::after {
            content: "+";
            position: absolute;
            right: 0;
            font-weight: bold;
            color: var(--main);
        }

        .faq-answer {
            display: none;
            margin-top: 10px;
            font-size: 15px;
            color: #444;
            line-height: 1.6;
        }

        .faq-item.open .faq-question::after {
            content: "−";
        }

        .faq-item.open .faq-answer {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity:0; transform:translateY(-5px); }
            to { opacity:1; transform:translateY(0); }
        }

        /* FOOTER */
        footer{
            text-align:center;
            padding:15px;
            font-size:14px;
            color:#555;
        }
    </style>
</head>

<body>

<main>

    <!-- HEADER -->
    <div class="header-box">
        ❓ Bantuan & FAQ
    </div>

    <!-- SEARCH -->
    <div class="search-box">
        <input type="text" id="search" placeholder="Cari pertanyaan...">
    </div>

    <!-- FAQ -->
    <div class="faq" id="faq">

        <div class="faq-item">
            <div class="faq-question">Bagaimana cara mengajukan layanan?</div>
            <div class="faq-answer">
                Masuk ke menu <b>Pengajuan Layanan</b>, isi form, lalu klik <b>Kirim</b>.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">Apakah saya bisa melacak status pengajuan?</div>
            <div class="faq-answer">
                Bisa, lihat di menu <b>Pengaduan Saya</b> atau <b>Riwayat Pengajuan</b>.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">Jenis layanan apa saja yang tersedia?</div>
            <div class="faq-answer">
                Layanan jaringan, internet, dokumentasi, dan layanan IT lainnya.
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">Bagaimana cara menghubungi admin?</div>
            <div class="faq-answer">
                Hubungi melalui kontak resmi Diskominfo atau fitur bantuan sistem.
            </div>
        </div>

    </div>

</main>

<script>
document.querySelectorAll(".faq-question").forEach(item => {
    item.addEventListener("click", () => {
        item.parentElement.classList.toggle("open");
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
    &copy; <?php echo date("Y"); ?> NETCARE Sistem Helpdesk Bebasis Digital
</footer>

</body>
</html>