# PANDUAN PERBAIKAN AKSES LAPORAN PETUGAS

## ❌ Masalah
Error: **"Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini."**

## ✅ Solusi

Masalah terjadi karena **tabel `users` belum memiliki data** yang sesuai dengan user yang login. Data users perlu disinkronisasi dari tabel akun/profil yang sudah ada di sistem.

---

## 📋 LANGKAH-LANGKAH PERBAIKAN

### **STEP 1: Jalankan Script Sinkronisasi**

1. Buka browser → masuk ke URL:
   ```
   http://localhost/netcare/setup/sync_users_from_accounts.php
   ```

2. Script akan otomatis:
   - ✅ Cari tabel akun/profil yang ada
   - ✅ Buat tabel `users` jika belum ada
   - ✅ Sinkronisasi data nama, NIP, jabatan, role
   - ✅ Tampilkan preview hasil

3. **Tunggu** sampai muncul pesan "✅ Selesai!" yang berarti data berhasil disinkronisasi.

---

### **STEP 2: Verify Data di Database**

Jika Anda punya akses phpMyAdmin:

1. Buka **phpMyAdmin** → Pilih database netcare
2. Cari tabel **`users`**
3. Lihat data yang sudah ada dengan struktur:
   ```
   - id (auto increment)
   - user_id (dari origin table)
   - nama (required)
   - nip (optional)
   - jabatan (optional)
   - role (default: 'petugas')
   - email, phone (optional)
   ```

Contoh data yang benar:
```
id | user_id | nama                  | nip          | jabatan               | role
1  | 1       | Al Imran Mulyadi      | 199500000... | Pranata Komputer      | petugas
2  | 2       | Miki Wahyudi Alamsyah | 199600000... | Pranata Komputer      | petugas
3  | 3       | M. Juliardi           | 199800000... | Pranata Komputer      | petugas
```

---

### **STEP 3: Login & Test Laporan**

1. **Logout** dari sistem (jika sedang login)
2. **Login kembali** dengan akun petugas
3. Pergi ke menu **Laporan Petugas**
4. Laporan seharusnya sudah **bisa diakses** ✅

---

## 🔍 TROUBLESHOOTING

### **Error: "Tabel sumber data tidak ditemukan"**

**Penyebab:** Script tidak menemukan tabel akun/profil  
**Solusi:**
1. Script akan menampilkan daftar tabel yang ada
2. Identifikasi nama tabel akun Anda (misal: `akun`, `accounts`, `pegawai`)
3. Edit `sync_users_from_accounts.php` line 65:
   ```php
   $possible_tables = ['akun', 'accounts', 'akun_users', 'pegawai', 'NAMA_TABEL_ANDA'];
   ```
4. Jalankan ulang

### **Error: "Kolom NAMA tidak ditemukan"**

**Penyebab:** Struktur kolom tabel akun berbeda  
**Solusi:**
1. Lihat daftar kolom yang ditampilkan
2. Edit mapping di line 115-122 `sync_users_from_accounts.php`:
   ```php
   if (in_array($fname, ['nama', 'name', 'nama_lengkap', 'fullname', 'NAMA_KOLOM_ANDA'])) $nama_col = $field->name;
   ```

### **Laporan masih tidak bisa diakses setelah sync**

**Langkah debug:**
1. Buka URL: `http://localhost/netcare/petugas/modules/laporan_petugas.php`
2. Lihat di browser console (F12) → Network → jika ada error
3. Pesan error akan menunjukkan masalah spesifik

---

## 🛠️ MAINTENANCE

### **Menambah User Baru**

Jika ada user/petugas baru:

1. **Opsi A (Recomended):** Jalankan script sync ulang
   ```
   http://localhost/netcare/setup/sync_users_from_accounts.php
   ```

2. **Opsi B (Manual):** Insert langsung ke tabel users
   ```sql
   INSERT INTO users (nama, nip, jabatan, role) 
   VALUES ('Nama Petugas', 'NIP123', 'Pranata Komputer', 'petugas');
   ```

### **Update Data User**

Update di tabel `users` (bukan di tabel asli):
```sql
UPDATE users 
SET nama = 'Nama Baru', jabatan = 'Jabatan Baru' 
WHERE id = 1;
```

### **Hapus Script Setelah Selesai**

Untuk keamanan, **hapus file ini setelah sync berhasil**:
```
/setup/sync_users_from_accounts.php
```

---

## 📝 KONFIGURASI LANJUT

### **Mengubah Fields yang Disinkronisasi**

Edit `sync_users_from_accounts.php` → ubah query di STEP 4:

```php
$source_query = "SELECT 
    `id_field` as id,
    `nama_field` as nama,
    `nip_field` as nip,
    `jabatan_field` as jabatan,
    `role_field` as role
FROM nama_tabel_akun";
```

### **Mapping Kolom Custom**

Jika struktur database unik, gunakan query custom:

```php
$source_query = "SELECT 
    id,
    full_name as nama,
    employee_id as nip,
    position_title as jabatan,
    'petugas' as role,
    email,
    phone
FROM employees
WHERE status = 'active'";
```

---

## ✅ CHECKLIST FINAL

- [ ] Jalankan script: `sync_users_from_accounts.php`
- [ ] Script menunjukkan "✅ Selesai!"
- [ ] Data users terlihat di database
- [ ] Login dengan akun petugas
- [ ] Akses Laporan Petugas dari menu
- [ ] Laporan bisa ditampilkan tanpa error
- [ ] Hapus file sync (untuk keamanan)
- [ ] Test cetak laporan

---

## 🆘 SUPPORT

Jika masih error setelah mengikuti semua langkah:

1. **Cek file kode:**
   - `/petugas/modules/laporan_petugas.php` (sudah diperbaiki)
   - `/setup/sync_users_from_accounts.php` (sudah dibuat)

2. **Verifikasi database:**
   - Tabel `users` ada dan terisi data
   - User login session valid
   - Role di users table sesuai

3. **Debug dengan manual SQL:**
   ```sql
   -- Cek data users
   SELECT * FROM users LIMIT 5;
   
   -- Cek data tugas_petugas
   SELECT * FROM tugas_petugas WHERE petugas_id = 1 LIMIT 5;
   
   -- Cek data pengajuan
   SELECT * FROM pengajuan WHERE status = 'disetujui' LIMIT 5;
   ```

---

**Versi:** 2.1.0  
**Status:** Fixed - Production Ready  
**Last Updated:** February 2024
