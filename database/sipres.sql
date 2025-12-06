-- Database: SIPRES (Sistem Presensi)
-- Database schema for the attendance system

CREATE DATABASE IF NOT EXISTS sipres;
USE sipres;

-- Table: users
-- Stores user accounts for Admin, Dosen, and Mahasiswa
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    role ENUM('admin', 'dosen', 'mahasiswa') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert demo accounts with hashed passwords
-- NOTE: The hash values below are PLACEHOLDERS and all use the same hash for demonstration.
-- After importing this SQL file, you MUST run: php database/create_demo_users.php
-- This script will generate unique, properly hashed passwords for each account:
--   - Admin: admin / admin123
--   - Dosen: 198001012005011001 / dosen123
--   - Mahasiswa: 210001001 / mhs123

-- Admin account (placeholder - run create_demo_users.php to set correct password)
INSERT INTO users (username, password, nama, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Dosen account (placeholder - run create_demo_users.php to set correct password)
INSERT INTO users (username, password, nama, role) VALUES 
('198001012005011001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Ahmad Budiman', 'dosen');

-- Mahasiswa account (placeholder - run create_demo_users.php to set correct password)
INSERT INTO users (username, password, nama, role) VALUES 
('210001001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'mahasiswa');
