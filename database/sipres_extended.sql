-- Extended Schema for SIPRES - Dosen Functionality
-- This extends the basic sipres.sql with necessary tables for attendance management

USE sipres;

-- Table: dosen (Lecturer profile data)
CREATE TABLE IF NOT EXISTS dosen (
    id_dosen INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nidn VARCHAR(20) NOT NULL UNIQUE COMMENT 'NIDN/NIP Dosen',
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    no_telp VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_nidn (nidn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: mahasiswa (Student profile data)
CREATE TABLE IF NOT EXISTS mahasiswa (
    id_mahasiswa INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(100) NOT NULL,
    jurusan VARCHAR(100) NOT NULL,
    angkatan VARCHAR(4) NOT NULL,
    email VARCHAR(100),
    no_telp VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id),
    INDEX idx_nim (nim),
    INDEX idx_jurusan (jurusan),
    INDEX idx_angkatan (angkatan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: matakuliah (Course master data)
CREATE TABLE IF NOT EXISTS matakuliah (
    id_mk INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk VARCHAR(20) NOT NULL UNIQUE,
    nama_mk VARCHAR(100) NOT NULL,
    sks INT NOT NULL,
    semester INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_kode_mk (kode_mk),
    INDEX idx_semester (semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: jadwal_kuliah (Class schedule)
CREATE TABLE IF NOT EXISTS jadwal_kuliah (
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

-- Table: jurnal_perkuliahan (Lecture journal - Dosen check-in/check-out)
CREATE TABLE IF NOT EXISTS jurnal_perkuliahan (
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

-- Table: presensi_mahasiswa (Student attendance records)
CREATE TABLE IF NOT EXISTS presensi_mahasiswa (
    id_presensi INT AUTO_INCREMENT PRIMARY KEY,
    id_jurnal INT NOT NULL,
    id_mahasiswa INT NOT NULL,
    waktu_scan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Hadir', 'Izin', 'Sakit', 'Alpha') DEFAULT 'Hadir',
    keterangan TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jurnal) REFERENCES jurnal_perkuliahan(id_jurnal) ON DELETE CASCADE,
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE RESTRICT,
    UNIQUE KEY unique_presensi (id_jurnal, id_mahasiswa) COMMENT 'One student can only attend once per session',
    INDEX idx_mahasiswa (id_mahasiswa),
    INDEX idx_jurnal (id_jurnal),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample dosen data linked to existing user
INSERT INTO dosen (user_id, nidn, nama_lengkap, email, no_telp) 
SELECT id, username, nama, CONCAT(username, '@univ.ac.id'), '081234567890'
FROM users WHERE role = 'dosen' AND username = '198001012005011001'
ON DUPLICATE KEY UPDATE nidn = nidn;

-- Insert sample mahasiswa data linked to existing user
INSERT INTO mahasiswa (user_id, nim, nama_lengkap, jurusan, angkatan, email, no_telp) 
SELECT id, username, nama, 'Teknik Informatika', '2021', CONCAT(username, '@student.univ.ac.id'), '081234560001'
FROM users WHERE role = 'mahasiswa' AND username = '210001001'
ON DUPLICATE KEY UPDATE nim = nim;

-- Insert sample mata kuliah
INSERT INTO matakuliah (kode_mk, nama_mk, sks, semester) VALUES 
('IF101', 'Pemrograman Web', 3, 3),
('IF102', 'Basis Data', 3, 3),
('SI201', 'Sistem Informasi Manajemen', 3, 4)
ON DUPLICATE KEY UPDATE nama_mk = nama_mk;

-- Insert sample jadwal kuliah for the dosen
INSERT INTO jadwal_kuliah (id_mk, id_dosen, hari, jam_mulai, jam_selesai, ruangan)
SELECT mk.id_mk, d.id_dosen, 'Senin', '08:00:00', '10:30:00', 'Lab Komputer 1'
FROM matakuliah mk, dosen d
WHERE mk.kode_mk = 'IF101' AND d.nidn = '198001012005011001'
AND NOT EXISTS (
    SELECT 1 FROM jadwal_kuliah jk 
    WHERE jk.id_mk = mk.id_mk AND jk.id_dosen = d.id_dosen
);
