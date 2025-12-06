# 📝 SQL Cheatsheet - Database Operations

Quick reference for common database operations in `db_presensi_uas`.

## 🔐 Authentication Queries

### Login User
```sql
-- Get user with profile information
SELECT 
    u.id_user,
    u.username,
    u.role,
    CASE 
        WHEN u.role = 'admin' THEN a.nama_lengkap
        WHEN u.role = 'dosen' THEN d.nama_lengkap
        WHEN u.role = 'mhs' THEN m.nama_lengkap
    END AS nama_lengkap,
    CASE 
        WHEN u.role = 'dosen' THEN d.id_dosen
        WHEN u.role = 'mhs' THEN m.id_mahasiswa
        ELSE NULL
    END AS profile_id
FROM users u
LEFT JOIN admin a ON u.id_user = a.user_id
LEFT JOIN dosen d ON u.id_user = d.user_id
LEFT JOIN mahasiswa m ON u.id_user = m.user_id
WHERE u.username = 'mhs001';
```

### Change Password
```sql
-- Update user password (remember to hash in application)
UPDATE users 
SET password = '$2y$10$NEW_HASHED_PASSWORD_HERE'
WHERE username = 'mhs001';
```

### Create New User
```sql
-- Step 1: Create user account
INSERT INTO users (username, password, role)
VALUES ('newuser', '$2y$10$HASHED_PASSWORD', 'mhs');

-- Step 2: Create profile (example for mahasiswa)
INSERT INTO mahasiswa (nim, nama_lengkap, jurusan, angkatan, email, user_id)
VALUES ('22003', 'John Doe', 'Teknik Informatika', '2022', 'john@student.univ.ac.id', LAST_INSERT_ID());
```

## 👨‍🏫 Dosen Operations

### View My Schedule
```sql
-- Get dosen schedule by NIDN
SELECT * FROM view_jadwal_lengkap
WHERE nidn = '0012345678'
ORDER BY 
    FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
    jam_mulai;
```

### Open Lecture Session (Check-in)
```sql
-- Dosen opens session and gets token
CALL sp_buka_sesi_kuliah(
    1,                          -- id_jadwal
    CURDATE(),                  -- tanggal (today)
    '08:00:00',                 -- jam_masuk
    'Pengenalan Framework PHP'  -- materi_kuliah
);
-- Returns: id_jurnal and token_presensi
```

### Close Lecture Session (Check-out)
```sql
-- Dosen closes session
CALL sp_tutup_sesi_kuliah(4, '10:30:00');
-- 4 = id_jurnal, '10:30:00' = jam_keluar
```

### View My Active Sessions
```sql
-- Get dosen's open sessions
SELECT 
    jp.id_jurnal,
    jp.token_presensi,
    mk.nama_mk,
    jp.tanggal,
    jp.jam_masuk,
    COUNT(pm.id_presensi) AS jumlah_hadir
FROM jurnal_perkuliahan jp
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
JOIN dosen d ON jk.id_dosen = d.id_dosen
LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal
WHERE d.nidn = '0012345678'
    AND jp.status_sesi = 'Open'
GROUP BY jp.id_jurnal;
```

### View Attendance for My Session
```sql
-- Get attendance list for specific session
SELECT 
    m.nim,
    m.nama_lengkap,
    pm.waktu_scan,
    pm.status
FROM presensi_mahasiswa pm
JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
WHERE pm.id_jurnal = 4
ORDER BY pm.waktu_scan;
```

### View My Teaching History
```sql
-- Get dosen's lecture journal
SELECT * FROM view_jurnal_lengkap
WHERE nama_dosen LIKE '%Ahmad Wijaya%'
ORDER BY tanggal DESC
LIMIT 10;
```

## 🎓 Mahasiswa Operations

### Find Active Token (Open Sessions)
```sql
-- Find sessions that are currently open
SELECT 
    jp.token_presensi,
    mk.nama_mk,
    d.nama_lengkap AS dosen,
    jp.tanggal,
    jp.jam_masuk
FROM jurnal_perkuliahan jp
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
JOIN dosen d ON jk.id_dosen = d.id_dosen
WHERE jp.status_sesi = 'Open'
    AND jp.tanggal = CURDATE()
ORDER BY jp.jam_masuk DESC;
```

### Submit Attendance
```sql
-- Mahasiswa submits attendance with token
CALL sp_presensi_mahasiswa(1, 'ABC123');
-- 1 = id_mahasiswa, 'ABC123' = token
```

### View My Attendance History
```sql
-- Get mahasiswa attendance records
SELECT 
    mk.kode_mk,
    mk.nama_mk,
    jp.tanggal,
    pm.waktu_scan,
    pm.status
FROM presensi_mahasiswa pm
JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
WHERE m.nim = '21001'
ORDER BY jp.tanggal DESC;
```

### View My Attendance Summary
```sql
-- Get attendance summary per course
SELECT * FROM view_rekap_presensi
WHERE nim = '21001'
ORDER BY kode_mk;
```

### Check If Already Attended
```sql
-- Check if already attended a specific session
SELECT 
    pm.id_presensi,
    pm.waktu_scan,
    pm.status
FROM presensi_mahasiswa pm
WHERE pm.id_jurnal = 4 
    AND pm.id_mahasiswa = 1;
-- Returns record if already attended, empty if not
```

## 👔 Admin Operations

### View All Users
```sql
-- Get all users with profiles
SELECT 
    u.username,
    u.role,
    CASE 
        WHEN u.role = 'admin' THEN a.nama_lengkap
        WHEN u.role = 'dosen' THEN d.nama_lengkap
        WHEN u.role = 'mhs' THEN m.nama_lengkap
    END AS nama_lengkap,
    CASE 
        WHEN u.role = 'dosen' THEN d.nidn
        WHEN u.role = 'mhs' THEN m.nim
        ELSE NULL
    END AS identifier,
    u.created_at
FROM users u
LEFT JOIN admin a ON u.id_user = a.user_id
LEFT JOIN dosen d ON u.id_user = d.user_id
LEFT JOIN mahasiswa m ON u.id_user = m.user_id
ORDER BY u.role, u.username;
```

### Add New Course
```sql
INSERT INTO matakuliah (kode_mk, nama_mk, sks, semester)
VALUES ('IF201', 'Algoritma dan Struktur Data', 3, 3);
```

### Add New Schedule
```sql
INSERT INTO jadwal_kuliah (id_mk, id_dosen, hari, jam_mulai, jam_selesai, ruangan)
VALUES (1, 2, 'Senin', '10:00:00', '12:30:00', 'Lab Komputer 3');
```

### View All Schedules
```sql
SELECT * FROM view_jadwal_lengkap
ORDER BY 
    FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
    jam_mulai;
```

### Delete User (Cascade to Profile)
```sql
-- This will also delete the corresponding profile (admin/dosen/mahasiswa)
DELETE FROM users WHERE username = 'olduser';
```

### Generate Reports
```sql
-- Attendance report by date range
SELECT 
    jp.tanggal,
    mk.nama_mk,
    d.nama_lengkap AS dosen,
    COUNT(pm.id_presensi) AS total_hadir
FROM jurnal_perkuliahan jp
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
JOIN dosen d ON jk.id_dosen = d.id_dosen
LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal AND pm.status = 'Hadir'
WHERE jp.tanggal BETWEEN '2024-01-01' AND '2024-01-31'
GROUP BY jp.id_jurnal
ORDER BY jp.tanggal;
```

## 📊 Reporting Queries

### Attendance Rate by Student
```sql
SELECT 
    m.nim,
    m.nama_lengkap,
    COUNT(pm.id_presensi) AS total_presensi,
    SUM(CASE WHEN pm.status = 'Hadir' THEN 1 ELSE 0 END) AS hadir,
    ROUND(SUM(CASE WHEN pm.status = 'Hadir' THEN 1 ELSE 0 END) * 100.0 / COUNT(pm.id_presensi), 2) AS persentase_kehadiran
FROM mahasiswa m
LEFT JOIN presensi_mahasiswa pm ON m.id_mahasiswa = pm.id_mahasiswa
GROUP BY m.id_mahasiswa
ORDER BY persentase_kehadiran DESC;
```

### Attendance Rate by Course
```sql
SELECT 
    mk.kode_mk,
    mk.nama_mk,
    COUNT(DISTINCT jp.id_jurnal) AS total_pertemuan,
    COUNT(pm.id_presensi) AS total_presensi,
    COUNT(DISTINCT pm.id_mahasiswa) AS unique_students,
    ROUND(COUNT(pm.id_presensi) / COUNT(DISTINCT jp.id_jurnal), 2) AS avg_attendance_per_session
FROM matakuliah mk
JOIN jadwal_kuliah jk ON mk.id_mk = jk.id_mk
JOIN jurnal_perkuliahan jp ON jk.id_jadwal = jp.id_jadwal
LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal
GROUP BY mk.id_mk
ORDER BY mk.kode_mk;
```

### Most Active Students (This Month)
```sql
SELECT 
    m.nim,
    m.nama_lengkap,
    m.jurusan,
    COUNT(pm.id_presensi) AS jumlah_kehadiran
FROM mahasiswa m
JOIN presensi_mahasiswa pm ON m.id_mahasiswa = pm.id_mahasiswa
JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
WHERE jp.tanggal >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
    AND pm.status = 'Hadir'
GROUP BY m.id_mahasiswa
ORDER BY jumlah_kehadiran DESC
LIMIT 10;
```

### Late Attendance (After 15 Minutes)
```sql
SELECT 
    m.nim,
    m.nama_lengkap,
    mk.nama_mk,
    jp.tanggal,
    jp.jam_masuk AS waktu_mulai,
    TIME(pm.waktu_scan) AS waktu_presensi,
    TIMESTAMPDIFF(MINUTE, jp.jam_masuk, pm.waktu_scan) AS terlambat_menit
FROM presensi_mahasiswa pm
JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
WHERE TIMESTAMPDIFF(MINUTE, jp.jam_masuk, pm.waktu_scan) > 15
ORDER BY jp.tanggal DESC, terlambat_menit DESC;
```

### Students Never Attended
```sql
SELECT 
    m.nim,
    m.nama_lengkap,
    m.jurusan,
    m.angkatan
FROM mahasiswa m
LEFT JOIN presensi_mahasiswa pm ON m.id_mahasiswa = pm.id_mahasiswa
WHERE pm.id_presensi IS NULL;
```

### Course with Highest Attendance
```sql
SELECT 
    mk.kode_mk,
    mk.nama_mk,
    COUNT(pm.id_presensi) AS total_kehadiran,
    COUNT(DISTINCT pm.id_mahasiswa) AS jumlah_mahasiswa
FROM matakuliah mk
JOIN jadwal_kuliah jk ON mk.id_mk = jk.id_mk
JOIN jurnal_perkuliahan jp ON jk.id_jadwal = jp.id_jadwal
LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal
WHERE pm.status = 'Hadir'
GROUP BY mk.id_mk
ORDER BY total_kehadiran DESC
LIMIT 5;
```

## 🔧 Maintenance Queries

### Backup Data (Before Major Changes)
```sql
-- Note: Use mysqldump for real backups
-- This is just for viewing data before changes

-- Backup users
CREATE TABLE users_backup AS SELECT * FROM users;

-- Backup presensi
CREATE TABLE presensi_backup AS SELECT * FROM presensi_mahasiswa WHERE tanggal > '2024-01-01';
```

### Check Database Size
```sql
SELECT 
    table_name,
    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb,
    table_rows
FROM information_schema.tables
WHERE table_schema = 'db_presensi_uas'
ORDER BY (data_length + index_length) DESC;
```

### Find Orphaned Records
```sql
-- Find jurnal without attendance
SELECT 
    jp.id_jurnal,
    jp.token_presensi,
    jp.tanggal,
    mk.nama_mk
FROM jurnal_perkuliahan jp
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal
WHERE pm.id_presensi IS NULL
    AND jp.status_sesi = 'Closed';
```

### Clean Old Tokens (Optional)
```sql
-- Mark old open sessions as closed (older than 1 day)
UPDATE jurnal_perkuliahan
SET status_sesi = 'Closed',
    jam_keluar = '23:59:59'
WHERE status_sesi = 'Open'
    AND tanggal < CURDATE();
```

### Archive Old Data (Example)
```sql
-- Create archive table for old attendance (older than 1 year)
CREATE TABLE presensi_archive AS
SELECT pm.* 
FROM presensi_mahasiswa pm
JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
WHERE jp.tanggal < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);

-- Delete from main table
DELETE pm FROM presensi_mahasiswa pm
JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
WHERE jp.tanggal < DATE_SUB(CURDATE(), INTERVAL 1 YEAR);
```

## 🎯 Performance Tips

### Add Index for Frequent Queries
```sql
-- If you query by tanggal a lot
CREATE INDEX idx_tanggal ON jurnal_perkuliahan(tanggal);

-- If you query by mahasiswa and status
CREATE INDEX idx_mhs_status ON presensi_mahasiswa(id_mahasiswa, status);
```

### Analyze Slow Queries
```sql
-- Check query execution plan
EXPLAIN SELECT * FROM view_rekap_presensi WHERE nim = '21001';

-- Show indexes on a table
SHOW INDEXES FROM presensi_mahasiswa;
```

### Optimize Table
```sql
-- Defragment and rebuild indexes
OPTIMIZE TABLE presensi_mahasiswa;
OPTIMIZE TABLE jurnal_perkuliahan;
```

## 🔒 Security Best Practices

### Change Default Passwords
```sql
-- Change password for all users (do one by one in production)
UPDATE users 
SET password = '$2y$10$NEW_SECURE_HASH_HERE'
WHERE username = 'admin';
```

### Create Read-Only User (For Reports)
```sql
CREATE USER 'report_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT ON db_presensi_uas.* TO 'report_user'@'localhost';
FLUSH PRIVILEGES;
```

### Create App User (Limited Privileges)
```sql
CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE ON db_presensi_uas.* TO 'app_user'@'localhost';
GRANT DELETE ON db_presensi_uas.presensi_mahasiswa TO 'app_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 📚 Quick Reference

### Common Status Values
- **role:** 'admin', 'dosen', 'mhs'
- **status_sesi:** 'Open', 'Closed'
- **status (presensi):** 'Hadir', 'Izin', 'Sakit', 'Alpha'
- **hari:** 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'

### Date/Time Functions
```sql
CURDATE()           -- Current date (2024-01-05)
CURTIME()           -- Current time (14:30:00)
NOW()               -- Current datetime (2024-01-05 14:30:00)
DATE_FORMAT(date, '%Y-%m-%d')  -- Format date
TIMESTAMPDIFF(MINUTE, start, end)  -- Difference in minutes
```

### Useful Functions
```sql
LAST_INSERT_ID()    -- Get last inserted ID
COUNT(*)            -- Count rows
DISTINCT            -- Unique values
GROUP BY            -- Group results
HAVING              -- Filter groups
LIMIT               -- Limit results
```

---

**Need More Help?**
- See [README.md](README.md) for detailed documentation
- See [ERD.md](ERD.md) for database structure
- Run [TEST_VALIDATION.sql](TEST_VALIDATION.sql) to verify setup
