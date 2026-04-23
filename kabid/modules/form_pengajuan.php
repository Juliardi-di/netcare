<div class="form-container">
    <form method="post" action="/netcare/modules/proses_pengajuan.php" enctype="multipart/form-data"
        class="pengajuan-form">


        <div class="form-group">
            <label for="jenis">Pilih Jenis Layanan</label>
            <select id="jenis" name="jenis" required>
                <option value="">-- Pilih Layanan --</option>
                <option value="zoom">Pengajuan Fasilitasi Zoom Meeting</option>
                <option value="streaming">Pengajuan Fasilitasi Live Streaming</option>
            </select>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="4" placeholder="Tuliskan detail kebutuhan Anda..."
                required></textarea>
        </div>

        <div class="form-group">
            <label for="surat">Upload Surat Pengajuan (PDF / JPG / PNG)</label>
            <input type="file" id="surat" name="surat" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>

        <button type="submit" class="btn-submit">🚀 Ajukan Sekarang</button>
    </form>
</div>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="css/style.css">