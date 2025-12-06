# 📚 Project Sistem Presensi

> Sistem Presensi Dua Arah untuk Dosen dan Mahasiswa

## 🎯 Overview

Sistem presensi modern yang memungkinkan dosen dan mahasiswa melakukan presensi secara digital. Dosen membuka sesi perkuliahan dengan check-in dan mendapatkan token unik, kemudian mahasiswa melakukan presensi menggunakan token tersebut.

### ✨ Key Features

- **🔐 Multi-role Authentication** - Admin, Dosen, dan Mahasiswa
- **📝 Token-based Attendance** - Sistem token 6 karakter untuk keamanan
- **👨‍🏫 Dosen Check-in/out** - Dosen membuka dan menutup sesi perkuliahan
- **🎓 Student Attendance** - Mahasiswa presensi dengan token
- **📊 Attendance Reports** - Rekap kehadiran otomatis
- **🕐 Session Management** - Kontrol sesi Open/Closed
- **📈 Views & Procedures** - Query optimization dengan stored procedures

## 📁 Project Structure

```
Project-Sstem-Presensi/
├── database/
│   ├── db_presensi_uas.sql    # Complete database schema with seeding
│   ├── README.md              # Database documentation
│   ├── QUICK_START.md         # Quick setup guide
│   └── ERD.md                 # Entity Relationship Diagram
└── README.md                  # This file
```

## 🚀 Quick Start

### 1. Setup Database

```bash
# Clone repository
git clone https://github.com/Yazechi/Project-Sstem-Presensi.git
cd Project-Sstem-Presensi

# Execute SQL script
mysql -u root -p < database/db_presensi_uas.sql
```

### 2. Verify Installation

```sql
USE db_presensi_uas;
SHOW TABLES;
```

### 3. Test Login

Default credentials:
- **Admin:** username: `admin`, password: `password`
- **Dosen:** username: `dosen001`, password: `password`
- **Mahasiswa:** username: `mhs001`, password: `password`

⚠️ **Important:** Change default passwords after installation!

## 📊 Database Schema

### Tables Overview

| Table | Description |
|-------|-------------|
| `users` | Central authentication table |
| `admin` | Admin profile data |
| `dosen` | Lecturer profile data |
| `mahasiswa` | Student profile data |
| `matakuliah` | Course master data |
| `jadwal_kuliah` | Class schedule |
| `jurnal_perkuliahan` | Lecture journal (session log) |
| `presensi_mahasiswa` | Student attendance records |

### Key Relationships

- One user can be Admin, Dosen, or Mahasiswa (1:1)
- One schedule links Dosen + Course + Time
- One journal session has one token for students
- One student can attend once per session

For detailed ERD, see [database/ERD.md](database/ERD.md)

## 📖 Documentation

- **[Database Documentation](database/README.md)** - Complete database guide
- **[Quick Start Guide](database/QUICK_START.md)** - 5-minute setup
- **[ERD Diagram](database/ERD.md)** - Entity relationships
- **SQL Script** - [db_presensi_uas.sql](database/db_presensi_uas.sql)

## 🎯 Use Cases

### Use Case 1: Dosen Opens Session
```sql
-- Dosen checks in and opens session
CALL sp_buka_sesi_kuliah(1, '2024-01-15', '08:00:00', 'Pengenalan PHP');
-- Returns: id_jurnal and token_presensi
```

### Use Case 2: Student Attends
```sql
-- Student uses token to attend
CALL sp_presensi_mahasiswa(1, 'ABC123');
-- Returns: Success message or error
```

### Use Case 3: Dosen Closes Session
```sql
-- Dosen checks out and closes session
CALL sp_tutup_sesi_kuliah(1, '10:30:00');
-- Session closed, no more attendance allowed
```

### Use Case 4: View Attendance Report
```sql
-- View student attendance summary
SELECT * FROM view_rekap_presensi WHERE nim = '21001';
```

## 🛠️ Technology Stack

- **Database:** MySQL 5.7+ / MariaDB 10.3+
- **Charset:** UTF-8 (utf8mb4)
- **Storage Engine:** InnoDB
- **Features:** Foreign Keys, Views, Stored Procedures, Triggers

## 📦 What's Included

### ✅ Phase 1: Authentication & Users (Completed)
- [x] Database creation
- [x] Users table with role-based access
- [x] Admin profile table

### ✅ Phase 2: Master Data (Completed)
- [x] Dosen table with NIDN
- [x] Mahasiswa table with NIM
- [x] Matakuliah table
- [x] Jadwal Kuliah table

### ✅ Phase 3: Transaction Data (Completed)
- [x] Jurnal Perkuliahan (check-in dosen)
- [x] Presensi Mahasiswa (attendance log)
- [x] Token-based system

### ✅ Phase 4: Relations & Constraints (Completed)
- [x] Foreign key relationships
- [x] Cascade and restrict rules
- [x] Unique constraints
- [x] Check constraints

### ✅ Phase 5: Seeding Data (Completed)
- [x] 1 Admin account
- [x] 2 Dosen accounts
- [x] 5 Mahasiswa accounts
- [x] 3 Mata Kuliah
- [x] 3 Jadwal Kuliah
- [x] Sample attendance records

### 🎁 Bonus Features (Included)
- [x] 3 Database views for reporting
- [x] 4 Stored procedures for common operations
- [x] Optimized indexes
- [x] Comprehensive documentation
- [x] ERD diagrams
- [x] Quick start guide

## 🔒 Security Features

1. **Password Hashing:** Bcrypt with cost factor 10+
2. **Token System:** 6-character random tokens
3. **Session Control:** Open/Closed status prevents late attendance
4. **Unique Constraints:** Prevents duplicate attendance
5. **Role-based Access:** Admin, Dosen, Mahasiswa roles

## 🧪 Testing

### Test Database Installation
```sql
-- Count tables
SELECT COUNT(*) FROM information_schema.tables 
WHERE table_schema = 'db_presensi_uas';
-- Should return: 8

-- Check seeded data
SELECT COUNT(*) FROM users;           -- Should be 8
SELECT COUNT(*) FROM mahasiswa;       -- Should be 5
SELECT COUNT(*) FROM dosen;          -- Should be 2
SELECT COUNT(*) FROM matakuliah;     -- Should be 3
```

### Test Stored Procedures
```sql
-- Test token generation
CALL sp_generate_token();

-- Test attendance with existing token
CALL sp_presensi_mahasiswa(5, 'GHI789');
```

## 🤝 Contributing

This is an academic project for UAS (Final Exam). Contributions and suggestions are welcome!

## 📄 License

This project is created for educational purposes.

## 👥 Team

- **Project:** Sistem Presensi Dua Arah
- **Course:** Database Design (UAS)
- **Year:** 2024

## 📞 Support

For questions or issues:
1. Check [database/README.md](database/README.md)
2. Review [database/QUICK_START.md](database/QUICK_START.md)
3. See [database/ERD.md](database/ERD.md) for relationships

## 🎓 Learning Resources

- MySQL Documentation: https://dev.mysql.com/doc/
- SQL Tutorial: https://www.w3schools.com/sql/
- Database Design: https://www.geeksforgeeks.org/database-design/

---

**Status:** ✅ Database Design Completed  
**Version:** 1.0.0  
**Last Updated:** 2024-01-05

Made with ❤️ for learning purposes