<section class="ebook-page">
    <h1><i class="fa fa-book-open"></i> E-Book netcare</h1>
    <p>Pilih e-book panduan berikut untuk dibaca atau diunduh:</p>

    <ul class="ebook-list">
        <li>
            <div class="ebook-card">
                <i class="fa fa-video"></i>
                <span>E-Book Panduan Zoom Meeting</span>
                <div class="ebook-actions">
                    <a href="#" class="btn-view" onclick="openPDF('../ebooks/zoom_meeting.pdf')">
                        <i class="fa fa-eye"></i> Lihat
                    </a>
                    <a href="ebooks/zoom_meeting.pdf" download class="btn-download">
                        <i class="fa fa-download"></i> Unduh
                    </a>
                </div>
            </div>
        </li>

        <li>
            <div class="ebook-card">
                <i class="fa fa-broadcast-tower"></i>
                <span>E-Book Panduan Live Streaming</span>
                <div class="ebook-actions">
                    <a href="#" class="btn-view" onclick="openPDF('../ebooks/live_streaming.pdf')">
                        <i class="fa fa-eye"></i> Lihat
                    </a>
                    <a href="ebooks/live_streaming.pdf" download class="btn-download">
                        <i class="fa fa-download"></i> Unduh
                    </a>
                </div>
            </div>
        </li>
    </ul>

    <div id="pdfContainer" class="pdf-container">
        <div class="pdf-header">
            <span id="pdfTitle">📖 E-Book netcare </span>
            <button class="close-btn" onclick="closePDF()">✖</button>
        </div>

        <iframe id="pdfFrame" src="" frameborder="0"></iframe>
    </div>
</section>

<style>

    .ebook-page {
        padding: 40px 20px;
        text-align: center;
        background: linear-gradient(135deg, #e8f5e9, #f1f8e9);
        border-radius: 12px;
        box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .ebook-page h1 {
        color: #1b5e20;
        margin-bottom: 8px;
        font-size: 2rem;
    }

    .ebook-page p {
        color: #4e6d4e;
        margin-bottom: 20px;
    }

    .ebook-list {
        list-style: none;
        padding: 0;
        max-width: 800px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }

    .ebook-card {
        background: #fff;
        padding: 20px;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .ebook-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .ebook-card i {
        font-size: 1.6rem;
        color: #388e3c;
        margin-right: 10px;
    }

    .ebook-card span {
        flex: 1;
        text-align: left;
        font-weight: bold;
        color: #1b5e20;
        font-size: 1.05rem;
    }

    .ebook-actions a {
        margin-left: 8px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.85rem;
        text-decoration: none;
        color: #fff;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.3s;
    }

    .btn-view {
        background: #0288d1;
    }

    .btn-view:hover {
        background: #0277bd;
    }

    .btn-download {
        background: #388e3c;
    }

    .btn-download:hover {
        background: #2e7d32;
    }

    .pdf-container {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.6s ease, padding 0.4s ease;
        background: #fff;
        margin-top: 20px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .pdf-container.active {
        max-height: 90vh;
        padding: 10px;
    }

    .pdf-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 14px;
        background: #1b5e20;
        color: #fff;
        border-radius: 8px 8px 0 0;
        font-weight: bold;
    }

    .close-btn {
        background: transparent;
        border: none;
        color: #fff;
        font-size: 18px;
        cursor: pointer;
    }

    #pdfFrame {
        width: 100%;
        height: 70vh;
        border: none;
        margin-top: 5px;
        border-radius: 0 0 8px 8px;
    }

    .pdf-nav {
        display: flex;
        justify-content: center;
        padding: 10px;
        background: #f1f8e9;
        border-top: 1px solid #ddd;
        gap: 10px;
    }

    .pdf-nav button {
        padding: 8px 16px;
        border: none;
        background: #388e3c;
        color: #fff;
        font-weight: bold;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .pdf-nav button:hover {
        background: #2e7d32;
    }
</style>

<script>
    function openPDF(file) {
        const pdfContainer = document.getElementById("pdfContainer");
        const pdfFrame = document.getElementById("pdfFrame");

        if (pdfContainer.classList.contains("active")) {
            pdfFrame.src = "";
            pdfContainer.classList.remove("active");
        }

        setTimeout(() => {
            pdfFrame.src = file;
            pdfContainer.classList.add("active");

            setTimeout(() => {
                pdfContainer.scrollIntoView({ behavior: "smooth" });
            }, 400);
        }, 300);
    }

    function closePDF() {
        const pdfContainer = document.getElementById("pdfContainer");
        const pdfFrame = document.getElementById("pdfFrame");
        pdfFrame.src = "";
        pdfContainer.classList.remove("active");
    }

    function prevPage() {
        document.getElementById("pdfFrame").contentWindow.history.back();
    }

    function nextPage() {
        document.getElementById("pdfFrame").contentWindow.history.forward();
    }
</script>