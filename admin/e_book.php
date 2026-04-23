<section class="ebook-page">
    <h1><i class="fa fa-book-open"></i> E-Book netcare</h1>
    <p>Pilih e-book panduan berikut untuk dibaca atau diunduh:</p>

    <ul class="ebook-list">
        <li>
            <div class="ebook-card">
                <i class="fa fa-video"></i>
                <span>E-Book Panduan Zoom Meeting</span>
                <div class="ebook-actions">
                    <a href="#" class="btn-view" onclick="openPDF('ebooks/zoom_meeting.pdf')">
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
                    <a href="#" class="btn-view" onclick="openPDF('ebooks/live_streaming.pdf')">
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
            <span id="pdfTitle">📖 E-Book Layanan Government </span>
            <button class="close-btn" onclick="closePDF()">✖</button>
        </div>

        <iframe id="pdfFrame" src="" frameborder="0"></iframe>
    </div>
</section>

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