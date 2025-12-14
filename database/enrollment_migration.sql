-- ============================================
-- Enrollment System Enhancement
-- Add table for mahasiswa to join courses
-- ============================================

USE db_presensi_uas;

-- Table: enrollment (Pendaftaran mahasiswa ke mata kuliah)
CREATE TABLE IF NOT EXISTS enrollment (
    id_enrollment INT AUTO_INCREMENT PRIMARY KEY,
    id_mahasiswa INT NOT NULL,
    id_jadwal INT NOT NULL,
    tanggal_daftar TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Aktif', 'Tidak Aktif') DEFAULT 'Aktif',
    FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa(id_mahasiswa) ON DELETE CASCADE,
    FOREIGN KEY (id_jadwal) REFERENCES jadwal_kuliah(id_jadwal) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (id_mahasiswa, id_jadwal),
    INDEX idx_mahasiswa (id_mahasiswa),
    INDEX idx_jadwal (id_jadwal),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample enrollments for existing mahasiswa
-- Mahasiswa 1-3 enroll in jadwal 1 (IF101 - Pemrograman Web)
INSERT IGNORE INTO enrollment (id_mahasiswa, id_jadwal, status) VALUES 
(1, 1, 'Aktif'),
(2, 1, 'Aktif'),
(3, 1, 'Aktif');

-- Mahasiswa 2-4 enroll in jadwal 2 (IF102 - Basis Data)
INSERT IGNORE INTO enrollment (id_mahasiswa, id_jadwal, status) VALUES 
(2, 2, 'Aktif'),
(3, 2, 'Aktif'),
(4, 2, 'Aktif');

-- Mahasiswa 1,4,5 enroll in jadwal 3 (SI201 - SIM)
INSERT IGNORE INTO enrollment (id_mahasiswa, id_jadwal, status) VALUES 
(1, 3, 'Aktif'),
(4, 3, 'Aktif'),
(5, 3, 'Aktif');
