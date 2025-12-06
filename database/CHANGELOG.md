# Changelog

All notable changes to the database design will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-05

### Added - Phase 1: Authentication & Users
- ✅ Created `db_presensi_uas` database
- ✅ Created `users` table with role-based authentication
  - Columns: id_user, username, password, role, created_at
  - Support for 3 roles: admin, dosen, mhs
  - Unique constraint on username
  - Indexes on username and role
- ✅ Created `admin` table for administrator profiles
  - Foreign key to users table
  - CASCADE delete when user is deleted
  - Unique constraint on user_id

### Added - Phase 2: Master Data
- ✅ Created `dosen` table for lecturer data
  - Columns: id_dosen, nidn, nama_lengkap, email, no_telp, user_id
  - Unique constraint on NIDN and user_id
  - Foreign key to users with CASCADE delete
- ✅ Created `mahasiswa` table for student data
  - Columns: id_mahasiswa, nim, nama_lengkap, jurusan, angkatan, email, no_telp, user_id
  - Unique constraint on NIM and user_id
  - Indexes on nim, jurusan, angkatan
  - Foreign key to users with CASCADE delete
- ✅ Created `matakuliah` table for course master data
  - Columns: id_mk, kode_mk, nama_mk, sks, semester
  - Unique constraint on kode_mk
  - CHECK constraint: sks > 0
- ✅ Created `jadwal_kuliah` table for class schedules
  - Columns: id_jadwal, id_mk, id_dosen, hari, jam_mulai, jam_selesai, ruangan
  - Foreign keys to matakuliah and dosen with RESTRICT delete
  - Enum for hari (days of week)

### Added - Phase 3: Transaction Data
- ✅ Created `jurnal_perkuliahan` table for lecture sessions
  - Columns: id_jurnal, id_jadwal, tanggal, jam_masuk, jam_keluar, materi_kuliah, token_presensi, status_sesi
  - 6-character token system for student attendance
  - Enum status: Open/Closed
  - Foreign key to jadwal_kuliah with RESTRICT delete
  - Indexes on tanggal, token_presensi, status_sesi
- ✅ Created `presensi_mahasiswa` table for student attendance
  - Columns: id_presensi, id_jurnal, id_mahasiswa, waktu_scan, status, keterangan
  - Enum status: Hadir/Izin/Sakit/Alpha
  - Foreign key to jurnal_perkuliahan with CASCADE delete
  - Foreign key to mahasiswa with RESTRICT delete
  - UNIQUE constraint on (id_jurnal, id_mahasiswa) to prevent duplicate attendance

### Added - Phase 4: Relations & Constraints
- ✅ Implemented all foreign key relationships
  - users → admin/dosen/mahasiswa (1:1 with CASCADE)
  - matakuliah → jadwal_kuliah (1:N with RESTRICT)
  - dosen → jadwal_kuliah (1:N with RESTRICT)
  - jadwal_kuliah → jurnal_perkuliahan (1:N with RESTRICT)
  - jurnal_perkuliahan → presensi_mahasiswa (1:N with CASCADE)
  - mahasiswa → presensi_mahasiswa (1:N with RESTRICT)
- ✅ Added composite indexes for optimization
  - idx_jurnal_tanggal_status on jurnal_perkuliahan
  - idx_presensi_status_jurnal on presensi_mahasiswa
- ✅ Set appropriate CASCADE and RESTRICT rules

### Added - Phase 5: Seeding Data
- ✅ Inserted 1 admin account (username: admin)
- ✅ Inserted 2 dosen accounts (dosen001, dosen002)
- ✅ Inserted 5 mahasiswa accounts (mhs001-mhs005)
- ✅ Inserted 3 mata kuliah (Pemrograman Web, Basis Data, SIM)
- ✅ Inserted 3 jadwal kuliah across different days
- ✅ Inserted 3 jurnal perkuliahan (2 closed, 1 open)
- ✅ Inserted 8 presensi records as examples
- ✅ All accounts use default password: "password" (bcrypt hash)

### Added - Bonus Features
- ✅ Created 3 database views for reporting:
  - `view_rekap_presensi` - Student attendance summary per course
  - `view_jadwal_lengkap` - Complete schedule with lecturer info
  - `view_jurnal_lengkap` - Complete journal with attendance count
- ✅ Created 4 stored procedures:
  - `sp_generate_token()` - Generate 6-character random token
  - `sp_buka_sesi_kuliah()` - Open lecture session (lecturer check-in)
  - `sp_tutup_sesi_kuliah()` - Close lecture session (lecturer check-out)
  - `sp_presensi_mahasiswa()` - Student attendance with token validation
- ✅ Optimized indexes for common queries
- ✅ UTF-8 (utf8mb4) charset for international support
- ✅ InnoDB engine for ACID compliance

### Documentation
- ✅ Created comprehensive database documentation (README.md)
- ✅ Created Entity Relationship Diagram (ERD.md)
- ✅ Created Quick Start Guide (QUICK_START.md)
- ✅ Created Test Validation Script (TEST_VALIDATION.sql)
- ✅ Updated main project README
- ✅ Added .gitignore file
- ✅ Created CHANGELOG.md (this file)

### Security
- ✅ Password hashing with bcrypt (cost factor 10+)
- ✅ Token-based attendance system
- ✅ Session control (Open/Closed status)
- ✅ Unique constraints to prevent data duplication
- ✅ Role-based access structure

### Database Statistics (Version 1.0.0)
- **Total Tables:** 8
- **Total Views:** 3
- **Total Stored Procedures:** 4
- **Total Foreign Keys:** 8
- **Total Indexes:** 20+
- **Seeded Users:** 8 (1 admin, 2 dosen, 5 mahasiswa)
- **Seeded Courses:** 3
- **Seeded Schedules:** 3

## Future Enhancements (Planned)

### [1.1.0] - To Be Determined
- [ ] Add trigger for automatic alpha status
- [ ] Add table for semester/academic year management
- [ ] Add table for announcement/notifications
- [ ] Add audit log table for tracking changes
- [ ] Add student enrollment table (many-to-many with jadwal)

### [1.2.0] - To Be Determined
- [ ] Add file attachment support for assignments
- [ ] Add grading/scoring system
- [ ] Add course prerequisites management
- [ ] Add room capacity and availability checking

### [2.0.0] - To Be Determined
- [ ] Add support for online classes (Zoom/Meet integration metadata)
- [ ] Add biometric attendance support
- [ ] Add geolocation-based attendance
- [ ] Add parent/guardian accounts and notifications
- [ ] Add multi-campus support

## Notes

### Database Design Decisions

1. **Why CASCADE for users → profiles:**
   - When a user account is deleted, the profile should also be deleted
   - Prevents orphaned profile records

2. **Why RESTRICT for master data:**
   - Prevent accidental deletion of courses/lecturers that are in use
   - Maintains referential integrity for historical data

3. **Why token_presensi is VARCHAR(6):**
   - Easy to type and communicate
   - Short enough for QR code or manual entry
   - Sufficient entropy for session-based use (36^6 = 2 billion combinations)

4. **Why status_sesi (Open/Closed):**
   - Prevents late attendance after class ends
   - Gives lecturer control over attendance window
   - Prevents retroactive attendance fraud

5. **Why unique constraint on (id_jurnal, id_mahasiswa):**
   - One student can only attend once per session
   - Prevents duplicate attendance records
   - Maintains data integrity

### Migration Guide

**From Empty Database:**
```bash
mysql -u root -p < database/db_presensi_uas.sql
```

**Verification:**
```bash
mysql -u root -p < database/TEST_VALIDATION.sql
```

### Breaking Changes

None yet (this is the initial release).

### Deprecations

None yet (this is the initial release).

---

**Maintained By:** Development Team  
**Contact:** See main README.md  
**License:** Educational Use
