-- ============================================
-- Database Validation & Testing Script
-- Run this after installing db_presensi_uas.sql
-- ============================================

USE db_presensi_uas;

-- ============================================
-- TEST 1: VERIFY ALL TABLES CREATED
-- ============================================

SELECT '=== TEST 1: Verifying All Tables ===' AS test_name;

SELECT 
    table_name,
    engine,
    table_rows
FROM information_schema.tables
WHERE table_schema = 'db_presensi_uas'
ORDER BY table_name;

-- Expected: 8 tables (admin, dosen, jadwal_kuliah, jurnal_perkuliahan, mahasiswa, matakuliah, presensi_mahasiswa, users)

-- ============================================
-- TEST 2: VERIFY FOREIGN KEY RELATIONSHIPS
-- ============================================

SELECT '=== TEST 2: Verifying Foreign Keys ===' AS test_name;

SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE 
    TABLE_SCHEMA = 'db_presensi_uas'
    AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, COLUMN_NAME;

-- Expected: Multiple FK relationships

-- ============================================
-- TEST 3: VERIFY SEEDED DATA
-- ============================================

SELECT '=== TEST 3: Verifying Seeded Data ===' AS test_name;

-- Check users count
SELECT 'users' AS table_name, COUNT(*) AS record_count FROM users
UNION ALL
SELECT 'admin', COUNT(*) FROM admin
UNION ALL
SELECT 'dosen', COUNT(*) FROM dosen
UNION ALL
SELECT 'mahasiswa', COUNT(*) FROM mahasiswa
UNION ALL
SELECT 'matakuliah', COUNT(*) FROM matakuliah
UNION ALL
SELECT 'jadwal_kuliah', COUNT(*) FROM jadwal_kuliah
UNION ALL
SELECT 'jurnal_perkuliahan', COUNT(*) FROM jurnal_perkuliahan
UNION ALL
SELECT 'presensi_mahasiswa', COUNT(*) FROM presensi_mahasiswa;

-- Expected:
-- users: 8
-- admin: 1
-- dosen: 2
-- mahasiswa: 5
-- matakuliah: 3
-- jadwal_kuliah: 3
-- jurnal_perkuliahan: 3
-- presensi_mahasiswa: 8

-- ============================================
-- TEST 4: VERIFY VIEWS
-- ============================================

SELECT '=== TEST 4: Verifying Views ===' AS test_name;

SELECT 
    table_name AS view_name
FROM information_schema.views
WHERE table_schema = 'db_presensi_uas';

-- Expected: 3 views (view_rekap_presensi, view_jadwal_lengkap, view_jurnal_lengkap)

-- Test view queries
SELECT '--- Testing view_jadwal_lengkap ---' AS test;
SELECT * FROM view_jadwal_lengkap LIMIT 3;

SELECT '--- Testing view_jurnal_lengkap ---' AS test;
SELECT * FROM view_jurnal_lengkap LIMIT 3;

SELECT '--- Testing view_rekap_presensi ---' AS test;
SELECT * FROM view_rekap_presensi WHERE nim = '21001' LIMIT 3;

-- ============================================
-- TEST 5: VERIFY STORED PROCEDURES
-- ============================================

SELECT '=== TEST 5: Verifying Stored Procedures ===' AS test_name;

SELECT 
    routine_name,
    routine_type
FROM information_schema.routines
WHERE routine_schema = 'db_presensi_uas'
ORDER BY routine_name;

-- Expected: 4 procedures (sp_buka_sesi_kuliah, sp_generate_token, sp_presensi_mahasiswa, sp_tutup_sesi_kuliah)

-- ============================================
-- TEST 6: TEST STORED PROCEDURES
-- ============================================

SELECT '=== TEST 6: Testing Stored Procedures ===' AS test_name;

-- Test 6.1: Generate Token
SELECT '--- Test 6.1: Generate Token ---' AS test;
CALL sp_generate_token();

-- Test 6.2: Test existing presensi (should work with existing open session)
SELECT '--- Test 6.2: Test Presensi with Existing Token ---' AS test;
-- Using existing token 'GHI789' from seeded data (id_jurnal = 3, status = Open)
-- Mahasiswa id 5 already attended, so let's try with mahasiswa 4
CALL sp_presensi_mahasiswa(4, 'GHI789');

-- Verify the attendance was recorded
SELECT 
    pm.id_presensi,
    m.nama_lengkap AS mahasiswa,
    mk.nama_mk,
    pm.waktu_scan,
    pm.status
FROM presensi_mahasiswa pm
JOIN mahasiswa m ON pm.id_mahasiswa = m.id_mahasiswa
JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
WHERE pm.id_mahasiswa = 4 AND jp.token_presensi = 'GHI789';

-- ============================================
-- TEST 7: TEST DATA INTEGRITY
-- ============================================

SELECT '=== TEST 7: Testing Data Integrity ===' AS test_name;

-- Test 7.1: Check unique constraints (no duplicate NIM)
SELECT '--- Test 7.1: Checking Unique NIM ---' AS test;
SELECT nim, COUNT(*) AS count
FROM mahasiswa
GROUP BY nim
HAVING count > 1;
-- Expected: Empty result (no duplicates)

-- Test 7.2: Check unique constraints (no duplicate NIDN)
SELECT '--- Test 7.2: Checking Unique NIDN ---' AS test;
SELECT nidn, COUNT(*) AS count
FROM dosen
GROUP BY nidn
HAVING count > 1;
-- Expected: Empty result (no duplicates)

-- Test 7.3: Check unique constraints (no duplicate username)
SELECT '--- Test 7.3: Checking Unique Username ---' AS test;
SELECT username, COUNT(*) AS count
FROM users
GROUP BY username
HAVING count > 1;
-- Expected: Empty result (no duplicates)

-- Test 7.4: Verify all users have corresponding profiles
SELECT '--- Test 7.4: Checking User Profiles ---' AS test;
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
        WHEN u.role = 'admin' AND a.user_id IS NULL THEN 'MISSING PROFILE'
        WHEN u.role = 'dosen' AND d.user_id IS NULL THEN 'MISSING PROFILE'
        WHEN u.role = 'mhs' AND m.user_id IS NULL THEN 'MISSING PROFILE'
        ELSE 'OK'
    END AS profile_status
FROM users u
LEFT JOIN admin a ON u.id_user = a.user_id AND u.role = 'admin'
LEFT JOIN dosen d ON u.id_user = d.user_id AND u.role = 'dosen'
LEFT JOIN mahasiswa m ON u.id_user = m.user_id AND u.role = 'mhs';
-- Expected: All rows should show 'OK'

-- ============================================
-- TEST 8: TEST INDEXES
-- ============================================

SELECT '=== TEST 8: Verifying Indexes ===' AS test_name;

SELECT 
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    NON_UNIQUE
FROM information_schema.statistics
WHERE table_schema = 'db_presensi_uas'
ORDER BY TABLE_NAME, INDEX_NAME, SEQ_IN_INDEX;

-- ============================================
-- TEST 9: SAMPLE QUERIES PERFORMANCE
-- ============================================

SELECT '=== TEST 9: Testing Sample Queries ===' AS test_name;

-- Query 9.1: Get dosen schedule
SELECT '--- Query 9.1: Dosen Schedule ---' AS test;
SELECT * FROM view_jadwal_lengkap
WHERE nidn = '0012345678'
ORDER BY hari, jam_mulai;

-- Query 9.2: Get student attendance summary
SELECT '--- Query 9.2: Student Attendance Summary ---' AS test;
SELECT 
    m.nim,
    m.nama_lengkap,
    COUNT(pm.id_presensi) AS total_attendance,
    SUM(CASE WHEN pm.status = 'Hadir' THEN 1 ELSE 0 END) AS total_hadir
FROM mahasiswa m
LEFT JOIN presensi_mahasiswa pm ON m.id_mahasiswa = pm.id_mahasiswa
WHERE m.nim = '21001'
GROUP BY m.id_mahasiswa;

-- Query 9.3: Get today's open sessions
SELECT '--- Query 9.3: Today Open Sessions ---' AS test;
SELECT * FROM view_jurnal_lengkap
WHERE status_sesi = 'Open'
ORDER BY tanggal DESC, jam_masuk DESC
LIMIT 5;

-- Query 9.4: Get attendance by course
SELECT '--- Query 9.4: Attendance by Course ---' AS test;
SELECT 
    mk.kode_mk,
    mk.nama_mk,
    COUNT(DISTINCT pm.id_mahasiswa) AS total_students_attended,
    COUNT(pm.id_presensi) AS total_attendance_records
FROM matakuliah mk
JOIN jadwal_kuliah jk ON mk.id_mk = jk.id_mk
JOIN jurnal_perkuliahan jp ON jk.id_jadwal = jp.id_jadwal
LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal
GROUP BY mk.id_mk
ORDER BY mk.kode_mk;

-- ============================================
-- TEST 10: DATABASE SIZE STATISTICS
-- ============================================

SELECT '=== TEST 10: Database Size Statistics ===' AS test_name;

SELECT 
    table_name,
    ROUND((data_length + index_length) / 1024, 2) AS size_kb,
    table_rows,
    ROUND((data_length + index_length) / table_rows, 2) AS avg_row_size_bytes
FROM information_schema.tables
WHERE table_schema = 'db_presensi_uas'
    AND table_type = 'BASE TABLE'
ORDER BY (data_length + index_length) DESC;

-- ============================================
-- TEST SUMMARY
-- ============================================

SELECT '=== VALIDATION COMPLETE ===' AS summary;

SELECT 
    'If all tests passed, the database is ready to use!' AS message,
    'Default password for all accounts: password' AS note,
    'Change passwords after installation!' AS warning;

-- ============================================
-- QUICK REFERENCE: DEFAULT CREDENTIALS
-- ============================================

SELECT '=== DEFAULT CREDENTIALS ===' AS info;

SELECT 
    u.username,
    u.role,
    'password' AS default_password,
    CASE
        WHEN u.role = 'admin' THEN a.nama_lengkap
        WHEN u.role = 'dosen' THEN d.nama_lengkap
        WHEN u.role = 'mhs' THEN m.nama_lengkap
    END AS nama_lengkap
FROM users u
LEFT JOIN admin a ON u.id_user = a.user_id
LEFT JOIN dosen d ON u.id_user = d.user_id
LEFT JOIN mahasiswa m ON u.id_user = m.user_id
ORDER BY 
    FIELD(u.role, 'admin', 'dosen', 'mhs'),
    u.username;
