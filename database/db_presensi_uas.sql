-- ============================================
-- Database: db_presensi_uas
-- Project: Sistem Presensi Dua Arah
-- Description: Database schema untuk sistem presensi dosen dan mahasiswa
-- ============================================

-- ============================================
-- PHASE 1: DATABASE & USERS SETUP
-- ============================================

-- Create Database
DROP DATABASE IF EXISTS db_presensi_uas;
CREATE DATABASE db_presensi_uas;
USE db_presensi_uas;

-- Table: users (Tabel login terpusat)
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Password hash (bcrypt/sha256)',
    role ENUM('admin', 'dosen', 'mhs') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: admin (Data profil admin)
CREATE TABLE admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PHASE 2: MASTER DATA
-- ============================================

-- Table: dosen (Data dosen)
CREATE TABLE dosen (
    id_dosen INT AUTO_INCREMENT PRIMARY KEY,
    nidn VARCHAR(20) NOT NULL UNIQUE COMMENT 'NIDN/NIP Dosen',
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    no_telp VARCHAR(15),
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_nidn (nidn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: mahasiswa (Data mahasiswa)
CREATE TABLE mahasiswa (
    id_mahasiswa INT AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    angkatan VARCHAR(4) NOT NULL,
    email VARCHAR(100),
    no_telp VARCHAR(15),
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id_user) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_nim (nim),
    INDEX idx_jurusan (jurusan),
    INDEX idx_angkatan (angkatan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: matakuliah (Data mata kuliah)
CREATE TABLE matakuliah (
    id_mk INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk VARCHAR(20) NOT NULL UNIQUE,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_kode_mk (kode_mk),
    INDEX idx_semester (semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: jadwal_kuliah (Jadwal perkuliahan)
CREATE TABLE jadwal_kuliah (
    id_jadwal INT AUTO_INCREMENT PRIMARY KEY,
    id_mk INT NOT NULL,
    id_dosen INT NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    ruangan VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_mk) REFERENCES matakuliah(id_mk) ON DELETE RESTRICT,
    FOREIGN KEY (id_dosen) REFERENCES dosen(id_dosen) ON DELETE RESTRICT,
    INDEX idx_hari (hari),
    INDEX idx_dosen (id_dosen),
    INDEX idx_mk (id_mk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PHASE 3: TRANSACTION DATA (Presensi)
-- ============================================

-- Table: jurnal_perkuliahan (Check-in dosen - Parent dari presensi mahasiswa)
CREATE TABLE jurnal_perkuliahan (
    id_jurnal INT AUTO_INCREMENT PRIMARY KEY,
    id_jadwal INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME NOT NULL,
    jam_keluar TIME NULL,
    materi_kuliah TEXT,
    token_presensi VARCHAR(6) NOT NULL COMMENT 'Token unik 6 karakter untuk mahasiswa',
    status_sesi ENUM('Open', 'Closed') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jadwal) REFERENCES jadwal_kuliah(id_jadwal) ON DELETE RESTRICT,
    INDEX idx_tanggal (tanggal),
    INDEX idx_token (token_presensi),
    INDEX idx_status (status_sesi),
    INDEX idx_jadwal (id_jadwal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: presensi_mahasiswa (Log kehadiran mahasiswa)
CREATE TABLE presensi_mahasiswa (
    id_presensi INT AUTO_INCREMENT PRIMARY KEY,
    id_jurnal INT NOT NULL,
    id_mahasiswa INT NOT NULL,
    waktu_scan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Hadir', 'Izin', 'Sakit', 'Alpha') DEFAULT 'Hadir',
    keterangan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jurnal) REFERENCES jurnal_perkuliahan(id_jurnal) ON DELETE CASCADE,
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE RESTRICT,
    UNIQUE KEY unique_presensi (id_jurnal, id_mahasiswa) COMMENT 'Satu mahasiswa hanya bisa presensi sekali per sesi',
    INDEX idx_mahasiswa (id_mahasiswa),
    INDEX idx_jurnal (id_jurnal),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PHASE 5: SEEDING DATA (Data Dummy)
-- ============================================

-- Insert Admin User
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password: password (hash dengan bcrypt)

INSERT INTO admin (user_id, nama_lengkap, email) VALUES 
(1, 'Administrator Sistem', 'admin@presensi.ac.id');

-- Insert 2 Dosen
INSERT INTO users (username, password, role) VALUES 
('dosen001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen'),
('dosen002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dosen');
-- Password: password (hash dengan bcrypt)

INSERT INTO dosen (nidn, nama_lengkap, email, no_telp, user_id) VALUES 
('0012345678', 'Dr. Ahmad Wijaya, M.Kom', 'ahmad.wijaya@univ.ac.id', '081234567890', 2),
('0087654321', 'Prof. Siti Rahayu, M.T', 'siti.rahayu@univ.ac.id', '081234567891', 3);

-- Insert 5 Mahasiswa
INSERT INTO users (username, password, role) VALUES 
('mhs001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mhs'),
('mhs002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mhs'),
('mhs003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mhs'),
('mhs004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mhs'),
('mhs005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mhs');
-- Password: password (hash dengan bcrypt)

INSERT INTO mahasiswa (nim, nama_lengkap, jurusan, angkatan, email, no_telp, user_id) VALUES 
('21001', 'Budi Santoso', 'Teknik Informatika', '2021', 'budi.santoso@student.univ.ac.id', '081234560001', 4),
('21002', 'Ani Lestari', 'Sistem Informasi', '2021', 'ani.lestari@student.univ.ac.id', '081234560002', 5),
('21003', 'Candra Wijaya', 'Teknik Informatika', '2021', 'candra.wijaya@student.univ.ac.id', '081234560003', 6),
('22001', 'Dewi Kusuma', 'Sistem Informasi', '2022', 'dewi.kusuma@student.univ.ac.id', '081234560004', 7),
('22002', 'Eko Prasetyo', 'Teknik Informatika', '2022', 'eko.prasetyo@student.univ.ac.id', '081234560005', 8);

-- Insert 3 Mata Kuliah
INSERT INTO matakuliah (kode_mk, nama_mk, sks, semester) VALUES 
('IF101', 'Pemrograman Web', 3, 3),
('IF102', 'Basis Data', 3, 3),
('SI201', 'Sistem Informasi Manajemen', 3, 4);

-- Insert 3 Jadwal Kuliah
INSERT INTO jadwal_kuliah (id_mk, id_dosen, hari, jam_mulai, jam_selesai, ruangan) VALUES 
(1, 1, 'Senin', '08:00:00', '10:30:00', 'Lab Komputer 1'),
(2, 1, 'Rabu', '13:00:00', '15:30:00', 'Lab Komputer 2'),
(3, 2, 'Jumat', '08:00:00', '10:30:00', 'Ruang Kuliah 301');

-- Insert contoh Jurnal Perkuliahan (Dosen sudah check-in)
INSERT INTO jurnal_perkuliahan (id_jadwal, tanggal, jam_masuk, jam_keluar, materi_kuliah, token_presensi, status_sesi) VALUES 
(1, '2024-01-08', '08:00:00', '10:30:00', 'Pengenalan HTML dan CSS', 'ABC123', 'Closed'),
(2, '2024-01-10', '13:00:00', '15:30:00', 'Normalisasi Database', 'DEF456', 'Closed'),
(3, '2024-01-12', '08:00:00', NULL, 'Analisis Sistem Informasi', 'GHI789', 'Open');

-- Insert contoh Presensi Mahasiswa
INSERT INTO presensi_mahasiswa (id_jurnal, id_mahasiswa, waktu_scan, status, keterangan) VALUES 
-- Presensi untuk jurnal 1
(1, 1, '2024-01-08 08:05:00', 'Hadir', NULL),
(1, 2, '2024-01-08 08:07:00', 'Hadir', NULL),
(1, 3, '2024-01-08 08:30:00', 'Hadir', NULL),
-- Presensi untuk jurnal 2
(2, 1, '2024-01-10 13:05:00', 'Hadir', NULL),
(2, 2, '2024-01-10 13:10:00', 'Hadir', NULL),
(2, 4, '2024-01-10 13:15:00', 'Hadir', NULL),
-- Presensi untuk jurnal 3 (masih open)
(3, 1, '2024-01-12 08:05:00', 'Hadir', NULL),
(3, 3, '2024-01-12 08:10:00', 'Hadir', NULL);

-- ============================================
-- VIEWS (Optional - untuk kemudahan query)
-- ============================================

-- View: Rekap Kehadiran Mahasiswa per Mata Kuliah
CREATE VIEW view_rekap_presensi AS
SELECT 
    m.nim,
    m.nama_lengkap AS nama_mahasiswa,
    mk.kode_mk,
    mk.nama_mk,
    COUNT(pm.id_presensi) AS total_presensi,
    SUM(CASE WHEN pm.status = 'Hadir' THEN 1 ELSE 0 END) AS total_hadir,
    SUM(CASE WHEN pm.status = 'Izin' THEN 1 ELSE 0 END) AS total_izin,
    SUM(CASE WHEN pm.status = 'Sakit' THEN 1 ELSE 0 END) AS total_sakit,
    SUM(CASE WHEN pm.status = 'Alpha' THEN 1 ELSE 0 END) AS total_alpha
FROM mahasiswa m
LEFT JOIN presensi_mahasiswa pm ON m.id_mahasiswa = pm.id_mahasiswa
LEFT JOIN jurnal_perkuliahan jp ON pm.id_jurnal = jp.id_jurnal
LEFT JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
LEFT JOIN matakuliah mk ON jk.id_mk = mk.id_mk
GROUP BY m.id_mahasiswa, mk.id_mk;

-- View: Jadwal Lengkap dengan Info Dosen dan Mata Kuliah
CREATE VIEW view_jadwal_lengkap AS
SELECT 
    jk.id_jadwal,
    mk.kode_mk,
    mk.nama_mk,
    mk.sks,
    d.nama_lengkap AS nama_dosen,
    d.nidn,
    jk.hari,
    jk.jam_mulai,
    jk.jam_selesai,
    jk.ruangan
FROM jadwal_kuliah jk
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
JOIN dosen d ON jk.id_dosen = d.id_dosen
ORDER BY jk.hari, jk.jam_mulai;

-- View: Jurnal Perkuliahan Lengkap
CREATE VIEW view_jurnal_lengkap AS
SELECT 
    jp.id_jurnal,
    jp.tanggal,
    jp.jam_masuk,
    jp.jam_keluar,
    jp.materi_kuliah,
    jp.token_presensi,
    jp.status_sesi,
    mk.kode_mk,
    mk.nama_mk,
    d.nama_lengkap AS nama_dosen,
    jk.hari,
    jk.ruangan,
    COUNT(pm.id_presensi) AS jumlah_mahasiswa_hadir
FROM jurnal_perkuliahan jp
JOIN jadwal_kuliah jk ON jp.id_jadwal = jk.id_jadwal
JOIN matakuliah mk ON jk.id_mk = mk.id_mk
JOIN dosen d ON jk.id_dosen = d.id_dosen
LEFT JOIN presensi_mahasiswa pm ON jp.id_jurnal = pm.id_jurnal AND pm.status = 'Hadir'
GROUP BY jp.id_jurnal;

-- ============================================
-- STORED PROCEDURES (Optional)
-- ============================================

DELIMITER //

-- Procedure: Generate Token Presensi (6 karakter random)
CREATE PROCEDURE sp_generate_token()
BEGIN
    DECLARE token VARCHAR(6);
    SET token = UPPER(SUBSTRING(MD5(RAND()), 1, 6));
    SELECT token;
END //

-- Procedure: Buka Sesi Perkuliahan (Dosen Check-in)
CREATE PROCEDURE sp_buka_sesi_kuliah(
    IN p_id_jadwal INT,
    IN p_tanggal DATE,
    IN p_jam_masuk TIME,
    IN p_materi TEXT
)
BEGIN
    DECLARE v_token VARCHAR(6);
    SET v_token = UPPER(SUBSTRING(MD5(RAND()), 1, 6));
    
    INSERT INTO jurnal_perkuliahan 
        (id_jadwal, tanggal, jam_masuk, materi_kuliah, token_presensi, status_sesi)
    VALUES 
        (p_id_jadwal, p_tanggal, p_jam_masuk, p_materi, v_token, 'Open');
    
    SELECT LAST_INSERT_ID() AS id_jurnal, v_token AS token_presensi;
END //

-- Procedure: Tutup Sesi Perkuliahan (Dosen Check-out)
CREATE PROCEDURE sp_tutup_sesi_kuliah(
    IN p_id_jurnal INT,
    IN p_jam_keluar TIME
)
BEGIN
    UPDATE jurnal_perkuliahan 
    SET jam_keluar = p_jam_keluar, 
        status_sesi = 'Closed'
    WHERE id_jurnal = p_id_jurnal;
    
    SELECT 'Sesi berhasil ditutup' AS message;
END //

-- Procedure: Presensi Mahasiswa dengan Token
CREATE PROCEDURE sp_presensi_mahasiswa(
    IN p_id_mahasiswa INT,
    IN p_token VARCHAR(6)
)
BEGIN
    DECLARE v_id_jurnal INT;
    DECLARE v_status_sesi VARCHAR(10);
    
    -- Cari jurnal yang sesuai dengan token dan masih open
    SELECT id_jurnal, status_sesi 
    INTO v_id_jurnal, v_status_sesi
    FROM jurnal_perkuliahan 
    WHERE token_presensi = p_token 
    LIMIT 1;
    
    IF v_id_jurnal IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Token tidak valid';
    ELSEIF v_status_sesi = 'Closed' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Sesi sudah ditutup';
    ELSE
        -- Insert presensi
        INSERT INTO presensi_mahasiswa (id_jurnal, id_mahasiswa, status)
        VALUES (v_id_jurnal, p_id_mahasiswa, 'Hadir')
        ON DUPLICATE KEY UPDATE waktu_scan = CURRENT_TIMESTAMP;
        
        SELECT 'Presensi berhasil dicatat' AS message;
    END IF;
END //

DELIMITER ;

-- ============================================
-- INDEXES FOR OPTIMIZATION (Tambahan)
-- ============================================

-- Composite indexes untuk query yang sering digunakan
CREATE INDEX idx_jurnal_tanggal_status ON jurnal_perkuliahan(tanggal, status_sesi);
CREATE INDEX idx_presensi_status_jurnal ON presensi_mahasiswa(status, id_jurnal);

-- ============================================
-- CONSTRAINTS VALIDATION
-- ============================================

-- Pastikan SKS tidak negatif
ALTER TABLE matakuliah ADD CONSTRAINT chk_sks_positive CHECK (sks > 0);

-- Pastikan jam selesai lebih besar dari jam mulai (akan divalidasi di aplikasi)
-- ALTER TABLE jadwal_kuliah ADD CONSTRAINT chk_jam_valid CHECK (jam_selesai > jam_mulai);

-- ============================================
-- DOCUMENTATION
-- ============================================

/*
DATABASE SCHEMA SUMMARY:
========================

1. USERS & AUTHENTICATION
   - users: Tabel utama untuk login (username, password, role)
   - admin: Profil admin yang terhubung ke users
   - dosen: Profil dosen yang terhubung ke users
   - mahasiswa: Profil mahasiswa yang terhubung ke users

2. MASTER DATA
   - matakuliah: Data mata kuliah (kode, nama, sks, semester)
   - jadwal_kuliah: Jadwal pertemuan (mata kuliah + dosen + waktu + ruangan)

3. TRANSACTION DATA
   - jurnal_perkuliahan: Log perkuliahan dosen (check-in/check-out + token)
   - presensi_mahasiswa: Log kehadiran mahasiswa per sesi

4. RELATIONSHIPS
   - users 1:1 admin/dosen/mahasiswa (via user_id)
   - jadwal_kuliah N:1 matakuliah (via id_mk)
   - jadwal_kuliah N:1 dosen (via id_dosen)
   - jurnal_perkuliahan N:1 jadwal_kuliah (via id_jadwal)
   - presensi_mahasiswa N:1 jurnal_perkuliahan (via id_jurnal)
   - presensi_mahasiswa N:1 mahasiswa (via id_mahasiswa)

5. KEY FEATURES
   - Cascade delete untuk relasi users -> admin/dosen/mahasiswa
   - Restrict delete untuk data master (matakuliah, jadwal)
   - Unique constraint untuk NIM, NIDN, username
   - Token system untuk presensi mahasiswa
   - Status sesi (Open/Closed) untuk kontrol presensi
   - Views untuk kemudahan reporting
   - Stored procedures untuk operasi umum

6. SEEDED DATA
   - 1 Admin
   - 2 Dosen
   - 5 Mahasiswa
   - 3 Mata Kuliah
   - 3 Jadwal Kuliah
   - 3 Jurnal (dengan berbagai status)
   - 8 Record Presensi Mahasiswa

DEFAULT PASSWORD: "password" (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
*/
