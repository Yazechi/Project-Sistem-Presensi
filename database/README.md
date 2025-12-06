# 🗄️ Database Documentation - db_presensi_uas

## Overview
Sistem database untuk aplikasi presensi dua arah antara dosen dan mahasiswa. Database ini dirancang dengan relasi yang jelas dan mendukung fitur token-based attendance.

## 📋 Table of Contents
- [Installation](#installation)
- [Database Schema](#database-schema)
- [Table Descriptions](#table-descriptions)
- [Relationships](#relationships)
- [Views](#views)
- [Stored Procedures](#stored-procedures)
- [Seeded Data](#seeded-data)
- [Usage Examples](#usage-examples)

## 🚀 Installation

### Prerequisites
- MySQL 5.7+ atau MariaDB 10.3+
- MySQL Client atau MySQL Workbench

### Setup Instructions

1. **Login ke MySQL:**
   ```bash
   mysql -u root -p
   ```

2. **Execute SQL Script:**
   ```bash
   mysql -u root -p < database/db_presensi_uas.sql
   ```
   
   Atau dari MySQL shell:
   ```sql
   source /path/to/database/db_presensi_uas.sql;
   ```

3. **Verify Installation:**
   ```sql
   USE db_presensi_uas;
   SHOW TABLES;
   ```

## 📊 Database Schema

### Schema Diagram (ERD)

```
┌─────────────┐
│   users     │
│─────────────│
│ id_user (PK)│◄──┐
│ username    │   │
│ password    │   │
│ role        │   │
└─────────────┘   │
                  │
        ┌─────────┼─────────┬────────────┐
        │         │         │            │
┌───────▼──────┐ ┌▼──────────┐ ┌────────▼──────┐
│    admin     │ │   dosen    │ │  mahasiswa    │
│──────────────│ │────────────│ │───────────────│
│ id_admin(PK) │ │ id_dosen(PK│ │ id_mahasiswa  │
│ user_id (FK) │ │ nidn       │ │ nim           │
│ nama_lengkap │ │ nama_lengkap│ │ nama_lengkap  │
└──────────────┘ │ user_id(FK)│ │ jurusan       │
                 └────────┬───┘ │ user_id (FK)  │
                          │     └───────┬───────┘
                          │             │
┌────────────────┐        │             │
│  matakuliah    │        │             │
│────────────────│        │             │
│ id_mk (PK)     │◄───┐   │             │
│ kode_mk        │    │   │             │
│ nama_mk        │    │   │             │
│ sks            │    │   │             │
└────────────────┘    │   │             │
                      │   │             │
                ┌─────┴───▼──────┐      │
                │ jadwal_kuliah  │      │
                │────────────────│      │
                │ id_jadwal (PK) │◄─┐   │
                │ id_mk (FK)     │  │   │
                │ id_dosen (FK)  │  │   │
                │ hari           │  │   │
                │ jam_mulai      │  │   │
                │ ruangan        │  │   │
                └────────────────┘  │   │
                                    │   │
                ┌───────────────────┴───┤
                │ jurnal_perkuliahan    │
                │───────────────────────│
                │ id_jurnal (PK)        │◄──┐
                │ id_jadwal (FK)        │   │
                │ tanggal               │   │
                │ jam_masuk/keluar      │   │
                │ token_presensi        │   │
                │ status_sesi           │   │
                └───────────────────────┘   │
                                            │
                        ┌───────────────────┴────────┐
                        │  presensi_mahasiswa        │
                        │────────────────────────────│
                        │ id_presensi (PK)           │
                        │ id_jurnal (FK)             │
                        │ id_mahasiswa (FK)◄─────────┘
                        │ waktu_scan                 │
                        │ status                     │
                        └────────────────────────────┘
```

## 📚 Table Descriptions

### Phase 1: Authentication & Users

#### 1. `users`
Tabel utama untuk autentikasi sistem.

| Column | Type | Description |
|--------|------|-------------|
| id_user | INT (PK) | Primary key |
| username | VARCHAR(50) | Unique username |
| password | VARCHAR(255) | Hashed password (bcrypt) |
| role | ENUM | 'admin', 'dosen', 'mhs' |
| created_at | TIMESTAMP | Timestamp pembuatan |

**Indexes:**
- PRIMARY KEY: `id_user`
- UNIQUE: `username`
- INDEX: `idx_username`, `idx_role`

#### 2. `admin`
Data profil administrator.

| Column | Type | Description |
|--------|------|-------------|
| id_admin | INT (PK) | Primary key |
| user_id | INT (FK) | Foreign key ke users |
| nama_lengkap | VARCHAR(100) | Nama lengkap admin |
| email | VARCHAR(100) | Email admin |
| created_at | TIMESTAMP | Timestamp pembuatan |

**Constraints:**
- FK: `user_id` → `users(id_user)` ON DELETE CASCADE
- UNIQUE: `user_id`

### Phase 2: Master Data

#### 3. `dosen`
Data dosen pengajar.

| Column | Type | Description |
|--------|------|-------------|
| id_dosen | INT (PK) | Primary key |
| nidn | VARCHAR(20) | NIDN/NIP dosen (unique) |
| nama_lengkap | VARCHAR(100) | Nama lengkap dosen |
| email | VARCHAR(100) | Email dosen |
| no_telp | VARCHAR(15) | Nomor telepon |
| user_id | INT (FK) | Foreign key ke users |
| created_at | TIMESTAMP | Timestamp pembuatan |

**Constraints:**
- FK: `user_id` → `users(id_user)` ON DELETE CASCADE
- UNIQUE: `nidn`, `user_id`
- INDEX: `idx_nidn`

#### 4. `mahasiswa`
Data mahasiswa.

| Column | Type | Description |
|--------|------|-------------|
| id_mahasiswa | INT (PK) | Primary key |
| nim | VARCHAR(20) | NIM mahasiswa (unique) |
| nama_lengkap | VARCHAR(100) | Nama lengkap mahasiswa |
| jurusan | VARCHAR(100) | Jurusan |
| angkatan | VARCHAR(4) | Tahun angkatan |
| email | VARCHAR(100) | Email mahasiswa |
| no_telp | VARCHAR(15) | Nomor telepon |
| user_id | INT (FK) | Foreign key ke users |
| created_at | TIMESTAMP | Timestamp pembuatan |

**Constraints:**
- FK: `user_id` → `users(id_user)` ON DELETE CASCADE
- UNIQUE: `nim`, `user_id`
- INDEX: `idx_nim`, `idx_jurusan`, `idx_angkatan`

#### 5. `matakuliah`
Data mata kuliah.

| Column | Type | Description |
|--------|------|-------------|
| id_mk | INT (PK) | Primary key |
| kode_mk | VARCHAR(20) | Kode mata kuliah (unique) |
| nama_mk | VARCHAR(100) | Nama mata kuliah |
| sks | INT | Jumlah SKS |
| semester | INT | Semester |
| created_at | TIMESTAMP | Timestamp pembuatan |

**Constraints:**
- UNIQUE: `kode_mk`
- CHECK: `sks > 0`
- INDEX: `idx_kode_mk`, `idx_semester`

#### 6. `jadwal_kuliah`
Jadwal perkuliahan.

| Column | Type | Description |
|--------|------|-------------|
| id_jadwal | INT (PK) | Primary key |
| id_mk | INT (FK) | Foreign key ke matakuliah |
| id_dosen | INT (FK) | Foreign key ke dosen |
| hari | ENUM | Senin-Minggu |
| jam_mulai | TIME | Jam mulai kuliah |
| jam_selesai | TIME | Jam selesai kuliah |
| ruangan | VARCHAR(50) | Nama ruangan |
| created_at | TIMESTAMP | Timestamp pembuatan |

**Constraints:**
- FK: `id_mk` → `matakuliah(id_mk)` ON DELETE RESTRICT
- FK: `id_dosen` → `dosen(id_dosen)` ON DELETE RESTRICT
- INDEX: `idx_hari`, `idx_dosen`, `idx_mk`

### Phase 3: Transaction Data

#### 7. `jurnal_perkuliahan`
Log perkuliahan (check-in dosen).

| Column | Type | Description |
|--------|------|-------------|
| id_jurnal | INT (PK) | Primary key |
| id_jadwal | INT (FK) | Foreign key ke jadwal_kuliah |
| tanggal | DATE | Tanggal perkuliahan |
| jam_masuk | TIME | Jam dosen check-in |
| jam_keluar | TIME | Jam dosen check-out (nullable) |
| materi_kuliah | TEXT | Materi yang diajarkan |
| token_presensi | VARCHAR(6) | Token untuk mahasiswa |
| status_sesi | ENUM | 'Open' atau 'Closed' |
| created_at | TIMESTAMP | Timestamp pembuatan |
| updated_at | TIMESTAMP | Timestamp update |

**Constraints:**
- FK: `id_jadwal` → `jadwal_kuliah(id_jadwal)` ON DELETE RESTRICT
- INDEX: `idx_tanggal`, `idx_token`, `idx_status`, `idx_jadwal`
- COMPOSITE INDEX: `idx_jurnal_tanggal_status`

#### 8. `presensi_mahasiswa`
Log kehadiran mahasiswa.

| Column | Type | Description |
|--------|------|-------------|
| id_presensi | INT (PK) | Primary key |
| id_jurnal | INT (FK) | Foreign key ke jurnal_perkuliahan |
| id_mahasiswa | INT (FK) | Foreign key ke mahasiswa |
| waktu_scan | TIMESTAMP | Waktu presensi |
| status | ENUM | 'Hadir', 'Izin', 'Sakit', 'Alpha' |
| keterangan | TEXT | Keterangan tambahan (nullable) |
| created_at | TIMESTAMP | Timestamp pembuatan |

**Constraints:**
- FK: `id_jurnal` → `jurnal_perkuliahan(id_jurnal)` ON DELETE CASCADE
- FK: `id_mahasiswa` → `mahasiswa(id_mahasiswa)` ON DELETE RESTRICT
- UNIQUE: `(id_jurnal, id_mahasiswa)` - Satu mahasiswa hanya bisa presensi sekali per sesi
- INDEX: `idx_mahasiswa`, `idx_jurnal`, `idx_status`
- COMPOSITE INDEX: `idx_presensi_status_jurnal`

## 🔗 Relationships

### Foreign Key Relationships

1. **users → admin/dosen/mahasiswa**
   - Type: One-to-One
   - On Delete: CASCADE
   - Description: Setiap user memiliki profil spesifik

2. **matakuliah → jadwal_kuliah**
   - Type: One-to-Many
   - On Delete: RESTRICT
   - Description: Satu mata kuliah bisa memiliki banyak jadwal

3. **dosen → jadwal_kuliah**
   - Type: One-to-Many
   - On Delete: RESTRICT
   - Description: Satu dosen bisa mengajar banyak jadwal

4. **jadwal_kuliah → jurnal_perkuliahan**
   - Type: One-to-Many
   - On Delete: RESTRICT
   - Description: Satu jadwal bisa memiliki banyak pertemuan

5. **jurnal_perkuliahan → presensi_mahasiswa**
   - Type: One-to-Many
   - On Delete: CASCADE
   - Description: Satu jurnal bisa memiliki banyak presensi

6. **mahasiswa → presensi_mahasiswa**
   - Type: One-to-Many
   - On Delete: RESTRICT
   - Description: Satu mahasiswa bisa memiliki banyak presensi

## 👁️ Views

### 1. `view_rekap_presensi`
Rekap kehadiran mahasiswa per mata kuliah.

**Columns:**
- nim, nama_mahasiswa, kode_mk, nama_mk
- total_presensi, total_hadir, total_izin, total_sakit, total_alpha

**Usage:**
```sql
SELECT * FROM view_rekap_presensi 
WHERE nim = '21001';
```

### 2. `view_jadwal_lengkap`
Jadwal kuliah dengan informasi lengkap.

**Columns:**
- id_jadwal, kode_mk, nama_mk, sks, nama_dosen, nidn
- hari, jam_mulai, jam_selesai, ruangan

**Usage:**
```sql
SELECT * FROM view_jadwal_lengkap 
WHERE hari = 'Senin';
```

### 3. `view_jurnal_lengkap`
Jurnal perkuliahan dengan informasi lengkap.

**Columns:**
- id_jurnal, tanggal, jam_masuk, jam_keluar, materi_kuliah
- token_presensi, status_sesi, kode_mk, nama_mk, nama_dosen
- hari, ruangan, jumlah_mahasiswa_hadir

**Usage:**
```sql
SELECT * FROM view_jurnal_lengkap 
WHERE tanggal = CURDATE();
```

## ⚙️ Stored Procedures

### 1. `sp_generate_token()`
Generate token presensi 6 karakter random.

**Usage:**
```sql
CALL sp_generate_token();
```

**Output:**
| token |
|-------|
| A3F9E2 |

### 2. `sp_buka_sesi_kuliah(p_id_jadwal, p_tanggal, p_jam_masuk, p_materi)`
Dosen membuka sesi perkuliahan (check-in).

**Parameters:**
- `p_id_jadwal` (INT): ID jadwal kuliah
- `p_tanggal` (DATE): Tanggal perkuliahan
- `p_jam_masuk` (TIME): Jam check-in dosen
- `p_materi` (TEXT): Materi kuliah

**Usage:**
```sql
CALL sp_buka_sesi_kuliah(1, '2024-01-15', '08:00:00', 'Pengenalan JavaScript');
```

**Output:**
| id_jurnal | token_presensi |
|-----------|----------------|
| 4 | B7D4E1 |

### 3. `sp_tutup_sesi_kuliah(p_id_jurnal, p_jam_keluar)`
Dosen menutup sesi perkuliahan (check-out).

**Parameters:**
- `p_id_jurnal` (INT): ID jurnal perkuliahan
- `p_jam_keluar` (TIME): Jam check-out dosen

**Usage:**
```sql
CALL sp_tutup_sesi_kuliah(4, '10:30:00');
```

**Output:**
| message |
|---------|
| Sesi berhasil ditutup |

### 4. `sp_presensi_mahasiswa(p_id_mahasiswa, p_token)`
Mahasiswa melakukan presensi dengan token.

**Parameters:**
- `p_id_mahasiswa` (INT): ID mahasiswa
- `p_token` (VARCHAR): Token presensi 6 karakter

**Usage:**
```sql
CALL sp_presensi_mahasiswa(1, 'B7D4E1');
```

**Output:**
| message |
|---------|
| Presensi berhasil dicatat |

**Error Messages:**
- "Token tidak valid" - Token tidak ditemukan
- "Sesi sudah ditutup" - Status sesi = Closed

## 📦 Seeded Data

### Default Credentials

| Role | Username | Password | Description |
|------|----------|----------|-------------|
| Admin | admin | password | Administrator sistem |
| Dosen | dosen001 | password | Dr. Ahmad Wijaya, M.Kom |
| Dosen | dosen002 | password | Prof. Siti Rahayu, M.T |
| Mahasiswa | mhs001 | password | Budi Santoso (21001) |
| Mahasiswa | mhs002 | password | Ani Lestari (21002) |
| Mahasiswa | mhs003 | password | Candra Wijaya (21003) |
| Mahasiswa | mhs004 | password | Dewi Kusuma (22001) |
| Mahasiswa | mhs005 | password | Eko Prasetyo (22002) |

> ⚠️ **Security Note:** Ganti password default setelah deployment!

### Sample Data Summary

- **1 Admin** dengan profil lengkap
- **2 Dosen** dengan NIDN berbeda
- **5 Mahasiswa** dari 2 angkatan (2021, 2022)
- **3 Mata Kuliah** (Pemrograman Web, Basis Data, Sistem Informasi)
- **3 Jadwal Kuliah** di hari berbeda
- **3 Jurnal Perkuliahan** (2 closed, 1 open)
- **8 Record Presensi** sebagai contoh

## 💡 Usage Examples

### Example 1: Login User
```sql
SELECT u.id_user, u.username, u.role, 
       CASE 
           WHEN u.role = 'admin' THEN a.nama_lengkap
           WHEN u.role = 'dosen' THEN d.nama_lengkap
           WHEN u.role = 'mhs' THEN m.nama_lengkap
       END AS nama_lengkap
FROM users u
LEFT JOIN admin a ON u.id_user = a.user_id
LEFT JOIN dosen d ON u.id_user = d.user_id
LEFT JOIN mahasiswa m ON u.id_user = m.user_id
WHERE u.username = 'mhs001';
```

### Example 2: Daftar Jadwal Dosen
```sql
SELECT * FROM view_jadwal_lengkap 
WHERE nidn = '0012345678'
ORDER BY hari, jam_mulai;
```

### Example 3: Check Token Validity
```sql
SELECT jp.id_jurnal, jp.token_presensi, jp.status_sesi,
       mk.nama_mk, d.nama_lengkap AS dosen
FROM jurnal_perkuliahan jp
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
JOIN dosen d ON jk.id_dosen = d.id_dosen
WHERE jp.token_presensi = 'ABC123';
```

### Example 4: Rekap Kehadiran Mahasiswa
```sql
SELECT * FROM view_rekap_presensi
WHERE nim = '21001'
ORDER BY kode_mk;
```

### Example 5: Daftar Mahasiswa yang Hadir Hari Ini
```sql
SELECT m.nim, m.nama_lengkap, mk.nama_mk, pm.waktu_scan
FROM presensi_mahasiswa pm
JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
WHERE jp.tanggal = CURDATE()
AND pm.status = 'Hadir'
ORDER BY pm.waktu_scan;
```

### Example 6: Complete Flow - Dosen Opens Session, Student Attends

```sql
-- 1. Dosen membuka sesi kuliah
CALL sp_buka_sesi_kuliah(1, CURDATE(), '08:00:00', 'Pengenalan PHP');
-- Output: id_jurnal=4, token_presensi='X7Y9Z2'

-- 2. Mahasiswa presensi dengan token
CALL sp_presensi_mahasiswa(1, 'X7Y9Z2');
CALL sp_presensi_mahasiswa(2, 'X7Y9Z2');
CALL sp_presensi_mahasiswa(3, 'X7Y9Z2');

-- 3. Cek siapa yang sudah presensi
SELECT m.nim, m.nama_lengkap, pm.waktu_scan
FROM presensi_mahasiswa pm
JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
WHERE pm.id_jurnal = 4;

-- 4. Dosen menutup sesi
CALL sp_tutup_sesi_kuliah(4, '10:30:00');
```

## 🛡️ Security Considerations

1. **Password Hashing:**
   - Gunakan bcrypt dengan cost factor 10+
   - Jangan simpan plain password
   - Sample hash: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`

2. **Token Security:**
   - Token presensi 6 karakter random (uppercase alphanumeric)
   - Token hanya valid saat status_sesi = 'Open'
   - Token bisa expired dengan menutup sesi

3. **SQL Injection Prevention:**
   - Gunakan prepared statements di aplikasi
   - Validasi input di application layer
   - Stored procedures sudah menggunakan parameterized queries

4. **Access Control:**
   - Implementasi role-based access di aplikasi
   - Admin: full access
   - Dosen: bisa buka/tutup sesi, lihat presensi
   - Mahasiswa: hanya bisa presensi dan lihat rekap sendiri

## 🔧 Maintenance

### Backup Database
```bash
mysqldump -u root -p db_presensi_uas > backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
mysql -u root -p db_presensi_uas < backup_20240115.sql
```

### Clean Old Data (contoh: hapus jurnal > 1 tahun)
```sql
DELETE FROM jurnal_perkuliahan 
WHERE tanggal < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);
```

### Check Table Sizes
```sql
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'db_presensi_uas'
ORDER BY (data_length + index_length) DESC;
```

## 📈 Performance Tips

1. **Indexes sudah dibuat untuk:**
   - Primary keys (auto)
   - Foreign keys
   - Frequently queried columns (username, nim, nidn, token, tanggal)
   - Composite indexes untuk join conditions

2. **Query Optimization:**
   - Gunakan views yang sudah disediakan
   - Hindari SELECT * untuk tabel besar
   - Gunakan LIMIT untuk pagination

3. **Connection Pooling:**
   - Gunakan connection pooling di aplikasi
   - Tutup koneksi setelah selesai

## 📞 Support

Untuk pertanyaan atau issue terkait database:
1. Cek dokumentasi ini terlebih dahulu
2. Review ERD dan relasi antar tabel
3. Test menggunakan sample queries yang disediakan

## 📝 Changelog

### Version 1.0.0 (2024-01-05)
- Initial database design
- Complete schema dengan semua tables
- Foreign key relationships
- Views untuk reporting
- Stored procedures untuk operasi umum
- Sample data seeding
- Comprehensive documentation

---

**Database Version:** 1.0.0  
**Last Updated:** 2024-01-05  
**Maintainer:** Development Team
