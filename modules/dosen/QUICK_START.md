# Quick Start Guide - Modul Dosen

Panduan cepat untuk menggunakan modul dosen SIPRES.

## 🚀 Setup Awal

### 1. Install Database Extension
```bash
mysql -u root -p sipres < database/sipres_extended.sql
```

### 2. Verifikasi Instalasi
```sql
USE sipres;
SHOW TABLES;
-- Harus ada: users, dosen, mahasiswa, matakuliah, jadwal_kuliah, 
--             jurnal_perkuliahan, presensi_mahasiswa
```

### 3. Login
- URL: `http://localhost:8000/auth/login.php`
- Username: `198001012005011001` (atau NIP dosen lainnya)
- Password: `dosen123`

## 📖 Skenario Penggunaan

### Skenario 1: Membuka Sesi Perkuliahan

**Langkah-langkah:**
1. Login sebagai dosen
2. Klik menu "Absensi Dosen" di dashboard
3. Pilih mata kuliah dari dropdown
4. Isi form:
   - Tanggal: (default hari ini)
   - Jam Masuk: (default jam sekarang)
   - Materi Kuliah: Contoh: "Pengenalan PHP dan MySQL"
5. Klik tombol "Check-In"
6. Token akan ditampilkan (contoh: `ABC123`)
7. Bagikan token ke mahasiswa untuk presensi

**Hasil:**
- Sesi dibuat dengan status "Open"
- Token 6 karakter di-generate otomatis
- Mahasiswa dapat mulai presensi dengan token

### Skenario 2: Menutup Sesi Perkuliahan

**Langkah-langkah:**
1. Di halaman "Absensi Dosen"
2. Lihat bagian "Sesi Aktif"
3. Isi jam keluar
4. Klik tombol "Check-Out"

**Hasil:**
- Sesi ditutup (status: "Closed")
- Mahasiswa tidak bisa lagi presensi dengan token tersebut

### Skenario 3: Set Status Mahasiswa Manual

**Kapan digunakan:**
- Mahasiswa izin/sakit dan tidak bisa presensi
- Perlu mengubah status mahasiswa yang sudah presensi
- Koreksi kesalahan presensi

**Langkah-langkah:**
1. Klik menu "Kelola Mahasiswa"
2. Pilih sesi perkuliahan dari dropdown
3. Untuk setiap mahasiswa, klik tombol status:
   - **Hadir**: Mahasiswa hadir di kelas
   - **Izin**: Ada izin resmi
   - **Sakit**: Ada surat sakit
   - **Alpha**: Tidak hadir tanpa keterangan
4. Isi keterangan (opsional)
5. Klik "Simpan"

**Catatan:**
- Hanya mahasiswa yang sudah presensi yang muncul di daftar
- Untuk menambah mahasiswa baru, mereka harus presensi dulu atau ditambah via database

### Skenario 4: Melihat Laporan Kehadiran

**Langkah-langkah:**
1. Klik menu "Log Presensi"
2. Pilih periode tanggal:
   - Tanggal Mulai: Contoh `2024-01-01`
   - Tanggal Akhir: Contoh `2024-01-31`
3. Klik "Tampilkan"

**Hasil:**
- Statistik kehadiran (cards di atas)
- Tabel detail per sesi
- Total per kategori (Hadir, Izin, Sakit, Alpha)

## 🔄 Workflow Harian

```
08:00 → Dosen check-in di kelas
     ↓
08:05 → Dosen dapat token (contoh: XYZ789)
     ↓
08:05-08:30 → Mahasiswa presensi dengan token
     ↓
10:00 → Dosen set status manual (jika ada izin/sakit)
     ↓
10:30 → Dosen check-out, sesi ditutup
     ↓
Anytime → Review log presensi
```

## 📊 Interpretasi Status

| Status | Arti | Kapan Digunakan |
|--------|------|-----------------|
| **Hadir** | Mahasiswa hadir di kelas | Presensi otomatis dengan token atau set manual |
| **Izin** | Mahasiswa tidak hadir dengan izin resmi | Set manual oleh dosen |
| **Sakit** | Mahasiswa sakit dengan surat keterangan | Set manual oleh dosen |
| **Alpha** | Mahasiswa tidak hadir tanpa keterangan | Set manual oleh dosen untuk yang tidak presensi |

## 🐛 Troubleshooting

### Problem: Token tidak muncul setelah check-in
**Solution:**
- Cek apakah form terisi lengkap
- Pastikan mata kuliah dipilih
- Refresh halaman dan coba lagi
- Cek error log: `/var/log/apache2/error.log`

### Problem: Mahasiswa tidak muncul di "Kelola Mahasiswa"
**Solution:**
- Pastikan sesi sudah dipilih
- Hanya mahasiswa yang sudah presensi yang muncul
- Cek database: `SELECT * FROM presensi_mahasiswa WHERE id_jurnal = X`

### Problem: Check-out tidak bisa dilakukan
**Solution:**
- Pastikan ada sesi aktif (status "Open")
- Refresh halaman
- Cek apakah jam keluar sudah diisi

### Problem: Statistik tidak akurat
**Solution:**
- Periksa filter tanggal
- Pastikan status mahasiswa sudah disimpan dengan benar
- Jalankan query manual untuk verifikasi:
```sql
SELECT COUNT(*) FROM presensi_mahasiswa 
WHERE status = 'Hadir' 
AND id_jurnal IN (
    SELECT id_jurnal FROM jurnal_perkuliahan 
    WHERE tanggal BETWEEN 'start_date' AND 'end_date'
);
```

## 💡 Tips & Best Practices

1. **Selalu check-in tepat waktu** agar mahasiswa bisa presensi sesuai jadwal
2. **Simpan token** di tempat yang mudah diakses (papan tulis/slide)
3. **Check-out setelah selesai** untuk mencegah presensi terlambat
4. **Set status manual** untuk mahasiswa yang izin/sakit sebelum menutup sesi
5. **Review log secara berkala** untuk monitoring kehadiran mahasiswa

## 📱 Tips untuk Berbagi Token

### Cara Efektif:
- ✅ Tulis token di papan tulis/whiteboard
- ✅ Tampilkan token di slide presentasi
- ✅ Umumkan token secara lisan di kelas
- ✅ Kirim via grup WhatsApp/Telegram (hanya saat sesi aktif)

### Hindari:
- ❌ Berbagi token sebelum kelas dimulai
- ❌ Menggunakan token yang sama berulang kali
- ❌ Lupa check-out (token tetap aktif)

## 🔐 Keamanan

### Token System:
- Token di-generate secara random setiap sesi
- Token hanya berlaku saat sesi "Open"
- Token otomatis tidak valid setelah check-out

### Best Practices:
- Ganti password default setelah first login
- Logout setelah selesai menggunakan sistem
- Jangan share token ke orang yang tidak hadir di kelas

## 📞 Butuh Bantuan?

1. Lihat dokumentasi lengkap: `modules/dosen/README.md`
2. Cek database documentation: `database/README.md`
3. Buka issue di GitHub repository
4. Hubungi admin sistem

---

**Versi:** 1.0.0  
**Terakhir Diperbarui:** 2024-12-08  
**Status:** ✅ Ready to Use
