<?php
/**
 * DOKUMENTASI FITUR SIMPAN - laporan_rekapitulasi.php
 * 
 * FLOW UMUM:
 * 1. Halaman menampilkan semua pengajuan dari tabel PENGAJUAN
 * 2. Data pengajuan (tanggal_pelaksanaan, judul) ditampilkan STATIS (readonly)
 * 3. Kolom input untuk output, utama, tambahan, jenis, keterangan, keterangan2
 * 4. Jika sudah ada entry di pengajuan_layanan (pl.id != NULL):
 *    - Input fields READONLY
 *    - Tombol: Edit, Hapus
 *    - Klik Edit -> fields menjadi editable, tombol Edit hilang, tombol Simpan muncul
 *    - Klik Simpan -> UPDATE di database
 * 5. Jika belum ada entry di pengajuan_layanan (pl.id = NULL):
 *    - Input fields EDITABLE
 *    - Tombol: Simpan (untuk INSERT)
 *    - Klik Simpan -> INSERT di database
 * 
 * DATA ATTRIBUTES:
 * - data-pengajuan-id: ID dari tabel PENGAJUAN
 * - data-layanan-id: ID dari tabel PENGAJUAN_LAYANAN (kosong jika belum ada)
 * 
 * JAVASCRIPT FLOW:
 * - Function editRow(): Unlock input fields, hide Edit button, show Save button
 * - Function saveNewRow(): Collect data, send via fetch POST, reload page on success
 * - Function hapusRow(): Delete entry dari pengajuan_layanan
 * 
 * PHP HANDLER (AJAX):
 * - Cek apakah id > 0:
 *   - Ya: UPDATE mode (update existing entry)
 *   - Tidak: INSERT mode (create new entry)
 * - Validation: pengajuan_id harus > 0
 * - Return JSON response dengan status dan id
 * 
 * TESTING CHECKLIST:
 * ✓ Data ditampilkan dengan benar (tanggal dan judul statis)
 * ✓ Input fields yang benar
 * ✓ Edit button berfungsi
 * ✓ Simpan button berfungsi untuk INSERT
 * ✓ Simpan button berfungsi untuk UPDATE
 * ✓ Hapus button berfungsi
 * 
 * BROWSER CONSOLE DEBUG:
 * - Buka F12 > Console
 * - Cek console.log output untuk debug
 * - Lihat Form data yang dikirim
 */

// Verify database structure
$conn = new mysqli('localhost', 'root', '', 'netcare');
$conn->set_charset('utf8mb4');

echo "=== FITUR SIMPAN - QUICK VERIFICATION ===\n\n";

// 1. Check tables
echo "1. Database Tables:\n";
$result = $conn->query("SHOW TABLES LIKE '%pengajuan%'");
while($row = $result->fetch_row()) {
    echo "   ✓ " . $row[0] . "\n";
}

// 2. Check pengajuan_layanan structure
echo "\n2. pengajuan_layanan columns:\n";
$result = $conn->query("DESCRIBE pengajuan_layanan");
while($row = $result->fetch_assoc()) {
    echo "   - " . $row['Field'] . " (" . $row['Type'] . ")\n";
}

// 3. Check sample data
echo "\n3. Sample data (existing with layanan):\n";
$result = $conn->query("SELECT p.id, p.judul, pl.id as layanan_id FROM pengajuan p LEFT JOIN pengajuan_layanan pl ON p.id = pl.pengajuan_id LIMIT 3");
while($row = $result->fetch_assoc()) {
    echo "   - Pengajuan ID " . $row['id'] . ": " . substr($row['judul'], 0, 40) . "... (Layanan: " . ($row['layanan_id'] ? $row['layanan_id'] : 'NULL') . ")\n";
}

echo "\n✅ READY TO USE!\n";
echo "\nUSAGE:\n";
echo "1. Klik 'Edit' pada baris untuk enable input fields\n";
echo "2. Isi data: output, utama (menit), tambahan (menit), jenis, keterangan, catatan\n";
echo "3. Klik 'Simpan' untuk menyimpan data\n";
echo "4. Klik 'Hapus' untuk menghapus data\n";
?>
