# Modul Dosen - SIPRES

Dokumentasi lengkap untuk modul dosen (lecturer) dalam Sistem Informasi Presensi (SIPRES).

## 📋 Fitur Utama

Modul dosen menyediakan tiga halaman utama untuk mengelola presensi:

### 1. Absensi Dosen (`absensi.php`)
**Fungsi:** Check-in dan check-out untuk membuka dan menutup sesi perkuliahan

**Fitur:**
- ✅ Check-in dosen untuk membuka sesi perkuliahan
- ✅ Generate token 6 karakter otomatis untuk mahasiswa
- ✅ Input materi kuliah
- ✅ Check-out dosen untuk menutup sesi perkuliahan
- ✅ Tampilkan sesi aktif dengan token
- ✅ Riwayat absensi terakhir

**Cara Penggunaan:**
1. Login sebagai dosen
2. Buka menu "Absensi Dosen"
3. Pilih mata kuliah dari dropdown
4. Isi tanggal, jam masuk, dan materi kuliah
5. Klik "Check-In" untuk membuka sesi
6. Token akan ditampilkan - bagikan ke mahasiswa
7. Setelah selesai, klik "Check-Out" untuk menutup sesi

### 2. Kelola Mahasiswa (`kelola_mahasiswa.php`)
**Fungsi:** Set status kehadiran mahasiswa secara manual

**Fitur:**
- ✅ Pilih sesi perkuliahan
- ✅ Lihat daftar semua mahasiswa
- ✅ Set status: Hadir, Izin, Sakit, Alpha
- ✅ Tambahkan keterangan untuk setiap status
- ✅ Update status mahasiswa yang sudah ada

**Cara Penggunaan:**
1. Login sebagai dosen
2. Buka menu "Kelola Mahasiswa"
3. Pilih sesi perkuliahan dari dropdown
4. Untuk setiap mahasiswa, klik tombol status yang sesuai:
   - **Hadir**: Mahasiswa hadir di kelas
   - **Izin**: Mahasiswa tidak hadir dengan izin resmi
   - **Sakit**: Mahasiswa sakit dengan surat keterangan
   - **Alpha**: Mahasiswa tidak hadir tanpa keterangan
5. Tambahkan keterangan jika diperlukan
6. Klik "Simpan" untuk menyimpan status

### 3. Log Presensi (`log_presensi.php`)
**Fungsi:** Lihat riwayat absensi dosen dan statistik mahasiswa

**Fitur:**
- ✅ Filter berdasarkan periode tanggal
- ✅ Statistik kehadiran: Total sesi, Hadir, Izin, Sakit, Alpha
- ✅ Riwayat lengkap absensi per sesi
- ✅ Detail per mata kuliah
- ✅ Export-ready format (siap untuk screenshot/print)

**Cara Penggunaan:**
1. Login sebagai dosen
2. Buka menu "Log Presensi"
3. Pilih periode tanggal (default: bulan berjalan)
4. Klik "Tampilkan" untuk melihat data
5. Review statistik dan riwayat lengkap

## 🗄️ Database Schema

Modul ini menggunakan tabel-tabel berikut:

### Tabel Utama:
- `dosen` - Profil dosen
- `mahasiswa` - Profil mahasiswa
- `matakuliah` - Data mata kuliah
- `jadwal_kuliah` - Jadwal perkuliahan
- `jurnal_perkuliahan` - Log check-in/out dosen (termasuk token)
- `presensi_mahasiswa` - Status kehadiran mahasiswa per sesi

### Relasi:
```
users (1) → (1) dosen
jadwal_kuliah (N) → (1) matakuliah
jadwal_kuliah (N) → (1) dosen
jurnal_perkuliahan (N) → (1) jadwal_kuliah
presensi_mahasiswa (N) → (1) jurnal_perkuliahan
presensi_mahasiswa (N) → (1) mahasiswa
```

## 🔧 Setup dan Instalasi

### 1. Jalankan Extended Schema
```sql
mysql -u root -p sipres < database/sipres_extended.sql
```

File ini akan membuat tabel-tabel yang diperlukan:
- `dosen`
- `mahasiswa`
- `matakuliah`
- `jadwal_kuliah`
- `jurnal_perkuliahan`
- `presensi_mahasiswa`

### 2. Verifikasi Instalasi
```sql
USE sipres;
SHOW TABLES;
```

Harus menampilkan minimal 7 tabel (users + 6 tabel baru).

### 3. Insert Sample Data (Opsional)
Extended schema sudah include sample data untuk:
- 1 Dosen (linked ke user dosen yang sudah ada)
- 1 Mahasiswa (linked ke user mahasiswa yang sudah ada)
- 3 Mata Kuliah
- 1 Jadwal Kuliah

## 🔐 Keamanan

Modul ini mengimplementasikan praktik keamanan berikut:

1. **Session Security**
   - Role-based access control (hanya role 'dosen')
   - Session timeout 30 menit
   - Secure session cookies

2. **SQL Injection Prevention**
   - Semua query menggunakan prepared statements
   - Parameter binding untuk semua input user

3. **XSS Prevention**
   - Semua output di-escape dengan `htmlspecialchars()`
   - Input sanitization untuk semua form

4. **CSRF Protection**
   - Form menggunakan POST method
   - Session validation pada setiap request

## 📝 Fungsi Helper (`includes/dosen_functions.php`)

Fungsi-fungsi yang tersedia:

### Core Functions:
- `getDosenId($user_id)` - Ambil ID dosen dari user ID
- `getJadwalByDosen($id_dosen)` - Ambil jadwal kuliah dosen
- `generateToken()` - Generate token 6 karakter random

### Jurnal Functions:
- `bukaSesilPerkuliahan($id_jadwal, $tanggal, $jam_masuk, $materi)` - Check-in dosen
- `tutupSesiPerkuliahan($id_jurnal, $jam_keluar)` - Check-out dosen
- `getJurnalByDosen($id_dosen, $limit)` - Ambil riwayat jurnal

### Mahasiswa Functions:
- `getMahasiswaByJurnal($id_jurnal)` - Ambil daftar mahasiswa per sesi
- `setStatusMahasiswa($id_jurnal, $id_mahasiswa, $status, $keterangan)` - Set/update status mahasiswa

### Report Functions:
- `getRekapPresensiDosen($id_dosen, $start_date, $end_date)` - Rekap presensi lengkap

## 🎯 Workflow Penggunaan

### Skenario Normal:

1. **Dosen Check-In** (Senin, 08:00)
   - Dosen buka `absensi.php`
   - Pilih mata kuliah "Pemrograman Web"
   - Input materi "Pengenalan HTML dan CSS"
   - Check-in → dapat token: `ABC123`
   - Share token ke mahasiswa

2. **Mahasiswa Presensi** (08:00 - 10:30)
   - Mahasiswa input token `ABC123` di aplikasi mereka
   - Status otomatis: "Hadir"

3. **Dosen Set Status Manual** (optional)
   - Buka `kelola_mahasiswa.php`
   - Pilih sesi hari ini
   - Set status untuk mahasiswa yang izin/sakit
   - Set status Alpha untuk yang tidak hadir

4. **Dosen Check-Out** (10:30)
   - Kembali ke `absensi.php`
   - Input jam keluar
   - Check-out → sesi ditutup
   - Mahasiswa tidak bisa lagi presensi

5. **Review Log** (kapan saja)
   - Buka `log_presensi.php`
   - Lihat statistik dan riwayat lengkap

## 🐛 Troubleshooting

### Error: "Can't find table 'dosen'"
**Solusi:** Jalankan `database/sipres_extended.sql`

### Error: "getDosenId returns null"
**Solusi:** Pastikan user dosen memiliki record di tabel `dosen`:
```sql
SELECT * FROM dosen WHERE user_id = [your_user_id];
```

### Token tidak muncul setelah check-in
**Solusi:** Cek apakah ada error di log. Pastikan `id_jadwal` valid.

### Status mahasiswa tidak tersimpan
**Solusi:** Cek:
1. Sesi masih open atau sudah closed?
2. ID jurnal dan ID mahasiswa valid?
3. Cek error log aplikasi

## 📱 Responsive Design

Semua halaman menggunakan CSS yang sama dengan dashboard utama (`assets/css/dashboard.css`), sehingga tampilan konsisten dan responsive untuk:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (< 768px)

## 🔄 Update dan Maintenance

### Menambah Mata Kuliah Baru:
```sql
INSERT INTO matakuliah (kode_mk, nama_mk, sks, semester) 
VALUES ('IF103', 'Algoritma', 3, 2);
```

### Menambah Jadwal Baru:
```sql
INSERT INTO jadwal_kuliah (id_mk, id_dosen, hari, jam_mulai, jam_selesai, ruangan)
VALUES (1, 1, 'Senin', '13:00:00', '15:30:00', 'Lab 2');
```

### Backup Data:
```bash
mysqldump -u root -p sipres > backup_sipres_$(date +%Y%m%d).sql
```

## 📊 Reporting

Data dari modul ini dapat di-export untuk:
- Laporan kehadiran bulanan
- Analisis persentase kehadiran
- Evaluasi mahasiswa
- Monitoring kinerja dosen

Format data sudah siap untuk:
- Excel/CSV export
- PDF generation
- Dashboard analytics

## 🤝 Kontribusi

Untuk menambah fitur atau memperbaiki bug:
1. Fork repository
2. Buat branch baru (`feature/nama-fitur`)
3. Commit changes
4. Push ke branch
5. Buat Pull Request

## 📄 Lisensi

Proyek ini dibuat untuk tujuan edukasi.

## 📞 Support

Untuk pertanyaan atau bantuan:
- Buka Issue di GitHub
- Lihat dokumentasi di `/database/README.md`
- Cek QUICK_START guide

---

**Version:** 1.0.0  
**Last Updated:** 2024-12-08  
**Status:** ✅ Production Ready
