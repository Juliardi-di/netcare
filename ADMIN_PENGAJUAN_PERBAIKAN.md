# Perbaikan Feature: Admin Pengajuan Layanan - Teruskan ke Petugas

## Ringkasan Perubahan

Kode **admin/pengajuan_layanan.php** telah diperbaiki untuk berfungsi dengan baik dalam mengirim pengajuan yang disetujui langsung ke petugas, sehingga petugas dapat melihat tugas baru di halaman daftar tugas mereka.

## Masalah yang Diperbaiki

1. ✅ **Duplikasi bind_param** - Kode sebelumnya menjalankan `$u->bind_param()` dua kali, yang menyebabkan error
2. ✅ **Logika Update Tidak Sempurna** - Sebelumnya hanya handle status 'ditolak', tidak handle status 'disetujui'
3. ✅ **Tidak Ada Penugasan Petugas** - Ketika admin teruskan ke petugas, tidak membuat record di tabel `tim_petugas`
4. ✅ **Kolom Database Hilang** - Tabel `pengajuan` belum memiliki kolom `status_admin_utama`, `catatan_admin`, dan `tanggal_pelaksanaan`

## Solusi yang Diterapkan

### 1. Tambahkan Kolom Ke Database
Jalankan file SQL migration ini di PhpMyAdmin atau MySQL command line:
```sql
ALTER TABLE `pengajuan` 
ADD COLUMN `status_admin_utama` enum('menunggu','diteruskan') DEFAULT 'menunggu' AFTER `status_admin_instansi`,
ADD COLUMN `catatan_admin` TEXT DEFAULT NULL AFTER `status_admin_utama`,
ADD COLUMN `tanggal_pelaksanaan` DATE DEFAULT NULL AFTER `deskripsi`;

UPDATE `pengajuan` SET `status_admin_utama` = 'menunggu' WHERE `status_admin_utama` IS NULL;
```

File SQL sudah disiapkan di: `sql/migration_add_admin_columns.sql`

### 2. Perbaikan Kode PHP

#### a) Fungsi `assignTeamTugas()` - Penugasan Petugas Otomatis
- Membaca aturan tim dari tabel `aturan_tim`
- Memilih petugas berdasarkan jenis layanan (Zoom Meeting atau Live Streaming)
- Menggunakan load-balancing dengan `last_assigned` untuk distribusi adil
- Update `last_assigned` timestamp untuk petugas yang ditugaskan
- Hapus penugasan lama dan insert penugasan baru

#### b) POST Handler Perbaikan
- Ambil data lengkap: `jenis_layanan` dan `user_id` dari tabel `pengajuan`
- Update tanpa filter tambahan (dulunya filter `status = 'ditolak'` dan `status_admin_utama = 'menunggu'`, tapi harusnya handle kedua kasus)
- Jika `status = 'disetujui'`, otomatis jalankan `assignTeamTugas()`
- Jika `status = 'ditolak'`, hanya update status dan simpan catatan (tidak perlu penugasan)

#### c) GET Handler Display Tabel
- Tampilkan pengajuan dengan `status_admin_utama = 'menunggu'` saja (yang belum diproses)
- Tampilkan status dengan emoji yang lebih jelas
- Tombol aksi berbeda untuk status ditolak dan disetujui
- Added `deleted_at IS NULL` check untuk mengabaikan pengajuan yang sudah dihapus

### 3. Front-end Improvement
- Tambah emoji dan pesan yang lebih deskriptif
- Styling yang lebih baik dengan hover effects
- Refresh otomatis setiap 30 detik
- Error handling yang lebih baik

## Alur Kerja

### Ketika Admin Klik "Teruskan ke Petugas" (Status Disetujui)
1. Admin mengisi pesan di prompt dialog
2. Sistem update `status_admin_utama = 'diteruskan'` dan simpan pesan ke `catatan_admin`
3. **Sistem otomatis menugaskan petugas** based on `jenis_layanan`:
   - Ambil daftar petugas dari `master_petugas` yang sesuai
   - Pilih yang belum lama ditugaskan (load balancing)
   - Insert ke tabel `tim_petugas`
   - Update `last_assigned` timestamp petugas
4. Pesan: "Pengajuan berhasil diteruskan ke petugas"
5. Petugas akan melihat tugas baru di halaman [petugas/modules/pengajuan_layanan.php](petugas/modules/pengajuan_layanan.php)

### Ketika Admin Klik "Teruskan Perbaikan ke OPD" (Status Ditolak)
1. Admin mengisi pesan perbaikan di prompt dialog
2. Sistem update `status_admin_utama = 'diteruskan'` dan simpan pesan
3. **Tidak ada penugasan petugas** (karena masih status ditolak, belum disetujui)
4. Pesan diteruskan ke OPD untuk perbaikan

## File yang Diubah/Dibuat

1. ✅ [admin/pengajuan_layanan.php](admin/pengajuan_layanan.php) - **DIPERBAIKI LENGKAP**
   - Fixed duplikasi code
   - Added `assignTeamTugas()` function
   - Fixed POST handler logic
   - Improved UI/UX dengan emoji dan styling

2. ✅ [sql/migration_add_admin_columns.sql](sql/migration_add_admin_columns.sql) - **DIBUAT BARU**
   - SQL migration untuk tambah kolom di tabel pengajuan

## Testing Checklist

```
□ Jalankan SQL migration untuk tambah kolom
□ Buka halaman admin/pengajuan_layanan.php
□ Pastikan tabel kosong atau menampilkan data dengan benar
□ Klik "Teruskan ke Petugas" pada pengajuan yang disetujui
  - Isi pesan di prompt
  - Verifikasi record di tabel tim_petugas tercipta
  - Check bahwa petugas melihat tugas baru
□ Klik "Teruskan Perbaikan ke OPD" pada pengajuan yang ditolak
  - Isi pesan perbaikan
  - Verifikasi status_admin_utama = 'diteruskan' dan catatan tersimpan
  - Verifikasi tidak ada penugasan di tim_petugas
□ Test refresh otomatis (tunggu 30 detik)
□ Test error handling jika petugas tidak tersedia
```

## Dependencies

Tidak ada library atau dependency tambahan yang diperlukan. Kode menggunakan:
- MySQLi prepared statements (untuk security)
- Vanilla JavaScript fetch API
- HTML5/CSS3 standard

## Notes

- Jika ada masalah dengan penugasan petugas, pastikan:
  - Tabel `master_petugas` memiliki data petugas dengan `aktif = 1`
  - Tabel `aturan_tim` memiliki rule untuk "Zoom Meeting" dan "Live Streaming"
  - Jenis layanan di pengajuan sesuai: "Zoom Meeting" atau "Live Streaming" (case-sensitive)

- Session user_id harus tersimpan di `$_SESSION['user_id']` untuk tracking siapa yang menugaskan

---

**Dibuat**: February 2026  
**Status**: Ready for Testing & Deployment
