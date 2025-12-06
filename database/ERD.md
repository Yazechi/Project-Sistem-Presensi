# 📐 Entity Relationship Diagram (ERD)

## Database: db_presensi_uas

### ERD Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    SISTEM PRESENSI DUA ARAH                             │
│                         ERD DIAGRAM                                     │
└─────────────────────────────────────────────────────────────────────────┘

                            ┌─────────────┐
                            │   users     │
                            │─────────────│
                            │ id_user (PK)│
                            │ username    │
                            │ password    │
                            │ role        │
                            │ created_at  │
                            └──────┬──────┘
                                   │
                                   │ 1:1
                 ┌─────────────────┼─────────────────┐
                 │                 │                 │
                 │                 │                 │
        ┌────────▼─────┐  ┌────────▼─────┐  ┌───────▼────────┐
        │    admin     │  │    dosen     │  │  mahasiswa     │
        │──────────────│  │──────────────│  │────────────────│
        │ id_admin(PK) │  │ id_dosen(PK) │  │ id_mahasiswa(PK│
        │ user_id (FK) │  │ nidn (UNQ)   │  │ nim (UNQ)      │
        │ nama_lengkap │  │ nama_lengkap │  │ nama_lengkap   │
        │ email        │  │ email        │  │ jurusan        │
        │ created_at   │  │ no_telp      │  │ angkatan       │
        └──────────────┘  │ user_id (FK) │  │ email          │
                          │ created_at   │  │ no_telp        │
                          └──────┬───────┘  │ user_id (FK)   │
                                 │          │ created_at     │
                                 │          └────────┬───────┘
                                 │                   │
                                 │                   │
┌────────────────┐               │                   │
│  matakuliah    │               │                   │
│────────────────│               │                   │
│ id_mk (PK)     │               │                   │
│ kode_mk (UNQ)  │               │                   │
│ nama_mk        │               │                   │
│ sks            │               │                   │
│ semester       │               │                   │
│ created_at     │               │                   │
└────────┬───────┘               │                   │
         │                       │                   │
         │ N:1                   │ N:1               │
         │                       │                   │
         │         ┌─────────────┴─────────┐         │
         └────────►│   jadwal_kuliah       │         │
                   │───────────────────────│         │
                   │ id_jadwal (PK)        │         │
                   │ id_mk (FK)            │         │
                   │ id_dosen (FK)         │         │
                   │ hari                  │         │
                   │ jam_mulai             │         │
                   │ jam_selesai           │         │
                   │ ruangan               │         │
                   │ created_at            │         │
                   └───────────┬───────────┘         │
                               │                     │
                               │ 1:N                 │
                               │                     │
                   ┌───────────▼───────────┐         │
                   │ jurnal_perkuliahan    │         │
                   │───────────────────────│         │
                   │ id_jurnal (PK)        │         │
                   │ id_jadwal (FK)        │         │
                   │ tanggal               │         │
                   │ jam_masuk             │         │
                   │ jam_keluar            │         │
                   │ materi_kuliah         │         │
                   │ token_presensi (6chr) │         │
                   │ status_sesi           │         │
                   │ created_at            │         │
                   │ updated_at            │         │
                   └───────────┬───────────┘         │
                               │                     │
                               │ 1:N                 │
                               │                     │
                   ┌───────────▼─────────────────────┼──┐
                   │      presensi_mahasiswa             │
                   │─────────────────────────────────────│
                   │ id_presensi (PK)                    │
                   │ id_jurnal (FK) ─────────────────────┘
                   │ id_mahasiswa (FK) ◄──────────────────
                   │ waktu_scan                          │
                   │ status                              │
                   │ keterangan                          │
                   │ created_at                          │
                   └─────────────────────────────────────┘
```

## Detailed Entity Descriptions

### 1️⃣ AUTHENTICATION LAYER

#### Entity: users
**Purpose:** Central authentication table untuk semua user

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| id_user | INT | PK, Auto Inc | Unique identifier |
| username | VARCHAR(50) | UNIQUE, NOT NULL | Login username |
| password | VARCHAR(255) | NOT NULL | Hashed password (bcrypt) |
| role | ENUM | NOT NULL | 'admin', 'dosen', 'mhs' |
| created_at | TIMESTAMP | DEFAULT NOW | Registration time |

**Business Rules:**
- Username harus unique di seluruh sistem
- Password harus di-hash sebelum disimpan
- Satu user hanya punya satu role

---

#### Entity: admin
**Purpose:** Profile data untuk administrator

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| id_admin | INT | PK, Auto Inc | Unique identifier |
| user_id | INT | FK, UNIQUE, NOT NULL | Link ke users |
| nama_lengkap | VARCHAR(100) | NOT NULL | Full name |
| email | VARCHAR(100) | | Email address |
| created_at | TIMESTAMP | DEFAULT NOW | Created time |

**Relationships:**
- **users** (1:1) - One admin profile per user account

**Business Rules:**
- Satu user_id hanya boleh punya satu profile admin
- Cascade delete: Jika user dihapus, admin profile ikut terhapus

---

### 2️⃣ MASTER DATA LAYER

#### Entity: dosen
**Purpose:** Profile dan data dosen pengajar

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| id_dosen | INT | PK, Auto Inc | Unique identifier |
| nidn | VARCHAR(20) | UNIQUE, NOT NULL | NIDN/NIP dosen |
| nama_lengkap | VARCHAR(100) | NOT NULL | Full name |
| email | VARCHAR(100) | | Email address |
| no_telp | VARCHAR(15) | | Phone number |
| user_id | INT | FK, UNIQUE, NOT NULL | Link ke users |
| created_at | TIMESTAMP | DEFAULT NOW | Created time |

**Relationships:**
- **users** (1:1) - One dosen profile per user account
- **jadwal_kuliah** (1:N) - One dosen can teach multiple schedules

**Business Rules:**
- NIDN harus unique (tidak boleh duplikat)
- Satu user_id hanya boleh punya satu profile dosen
- Cascade delete: Jika user dihapus, dosen profile ikut terhapus
- Restrict delete: Tidak bisa hapus dosen jika masih punya jadwal

---

#### Entity: mahasiswa
**Purpose:** Profile dan data mahasiswa

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| id_mahasiswa | INT | PK, Auto Inc | Unique identifier |
| nim | VARCHAR(20) | UNIQUE, NOT NULL | Nomor Induk Mahasiswa |
| nama_lengkap | VARCHAR(100) | NOT NULL | Full name |
| jurusan | VARCHAR(100) | NOT NULL | Department/Major |
| angkatan | VARCHAR(4) | NOT NULL | Year of entry |
| email | VARCHAR(100) | | Email address |
| no_telp | VARCHAR(15) | | Phone number |
| user_id | INT | FK, UNIQUE, NOT NULL | Link ke users |
| created_at | TIMESTAMP | DEFAULT NOW | Created time |

**Relationships:**
- **users** (1:1) - One mahasiswa profile per user account
- **presensi_mahasiswa** (1:N) - One student can have multiple attendance records

**Business Rules:**
- NIM harus unique (tidak boleh duplikat)
- Satu user_id hanya boleh punya satu profile mahasiswa
- Cascade delete: Jika user dihapus, mahasiswa profile ikut terhapus
- Restrict delete: Tidak bisa hapus mahasiswa jika masih punya presensi

---

#### Entity: matakuliah
**Purpose:** Master data mata kuliah

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| id_mk | INT | PK, Auto Inc | Unique identifier |
| kode_mk | VARCHAR(20) | UNIQUE, NOT NULL | Course code |
| nama_mk | VARCHAR(100) | NOT NULL | Course name |
| sks | INT | NOT NULL, > 0 | Credit hours |
| semester | INT | NOT NULL | Semester number |
| created_at | TIMESTAMP | DEFAULT NOW | Created time |

**Relationships:**
- **jadwal_kuliah** (1:N) - One course can have multiple schedules

**Business Rules:**
- Kode mata kuliah harus unique
- SKS harus lebih dari 0
- Restrict delete: Tidak bisa hapus mata kuliah jika masih punya jadwal

---

#### Entity: jadwal_kuliah
**Purpose:** Schedule pertemuan kuliah (link dosen + mata kuliah + waktu)

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| id_jadwal | INT | PK, Auto Inc | Unique identifier |
| id_mk | INT | FK, NOT NULL | Link ke matakuliah |
| id_dosen | INT | FK, NOT NULL | Link ke dosen |
| hari | ENUM | NOT NULL | Senin-Minggu |
| jam_mulai | TIME | NOT NULL | Start time |
| jam_selesai | TIME | NOT NULL | End time |
| ruangan | VARCHAR(50) | NOT NULL | Room/location |
| created_at | TIMESTAMP | DEFAULT NOW | Created time |

**Relationships:**
- **matakuliah** (N:1) - Multiple schedules can use same course
- **dosen** (N:1) - Multiple schedules can have same lecturer
- **jurnal_perkuliahan** (1:N) - One schedule can have multiple sessions

**Business Rules:**
- Satu jadwal hanya untuk satu mata kuliah dan satu dosen
- jam_selesai harus lebih besar dari jam_mulai (validasi di app)
- Restrict delete: Tidak bisa hapus jadwal jika sudah ada jurnal

---

### 3️⃣ TRANSACTION LAYER

#### Entity: jurnal_perkuliahan
**Purpose:** Log perkuliahan (dosen check-in/out) - PARENT of student attendance

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| id_jurnal | INT | PK, Auto Inc | Unique identifier |
| id_jadwal | INT | FK, NOT NULL | Link ke jadwal_kuliah |
| tanggal | DATE | NOT NULL | Date of session |
| jam_masuk | TIME | NOT NULL | Dosen check-in time |
| jam_keluar | TIME | NULL | Dosen check-out time |
| materi_kuliah | TEXT | NULL | Course material/topic |
| token_presensi | VARCHAR(6) | NOT NULL | 6-char token for students |
| status_sesi | ENUM | DEFAULT 'Open' | 'Open' or 'Closed' |
| created_at | TIMESTAMP | DEFAULT NOW | Created time |
| updated_at | TIMESTAMP | ON UPDATE NOW | Last updated time |

**Relationships:**
- **jadwal_kuliah** (N:1) - Multiple sessions from same schedule
- **presensi_mahasiswa** (1:N) - One session has multiple student attendances

**Business Rules:**
- Token presensi di-generate otomatis (6 karakter random)
- Status 'Open' = mahasiswa bisa presensi
- Status 'Closed' = mahasiswa tidak bisa presensi lagi
- Dosen bisa check-in tanpa check-out (jam_keluar nullable)
- Restrict delete: Tidak bisa hapus jurnal (keep audit trail)

---

#### Entity: presensi_mahasiswa
**Purpose:** Log kehadiran mahasiswa per sesi kuliah

| Attribute | Type | Constraints | Description |
|-----------|------|-------------|-------------|
| id_presensi | INT | PK, Auto Inc | Unique identifier |
| id_jurnal | INT | FK, NOT NULL | Link ke jurnal_perkuliahan |
| id_mahasiswa | INT | FK, NOT NULL | Link ke mahasiswa |
| waktu_scan | TIMESTAMP | DEFAULT NOW | Attendance timestamp |
| status | ENUM | DEFAULT 'Hadir' | Hadir/Izin/Sakit/Alpha |
| keterangan | TEXT | NULL | Additional notes |
| created_at | TIMESTAMP | DEFAULT NOW | Created time |

**Relationships:**
- **jurnal_perkuliahan** (N:1) - Multiple attendances per session
- **mahasiswa** (N:1) - Multiple attendances per student

**Business Rules:**
- UNIQUE constraint: (id_jurnal, id_mahasiswa) - Satu mahasiswa hanya bisa presensi sekali per sesi
- Cascade delete: Jika jurnal dihapus, presensi ikut terhapus
- Restrict delete: Tidak bisa hapus mahasiswa yang punya presensi (keep audit trail)
- Status default: 'Hadir' (untuk presensi via token)

---

## Cardinality Summary

| From | To | Relationship | Description |
|------|-----|--------------|-------------|
| users | admin | 1:1 | One user, one admin profile |
| users | dosen | 1:1 | One user, one dosen profile |
| users | mahasiswa | 1:1 | One user, one mahasiswa profile |
| matakuliah | jadwal_kuliah | 1:N | One course, many schedules |
| dosen | jadwal_kuliah | 1:N | One lecturer, many schedules |
| jadwal_kuliah | jurnal_perkuliahan | 1:N | One schedule, many sessions |
| jurnal_perkuliahan | presensi_mahasiswa | 1:N | One session, many attendances |
| mahasiswa | presensi_mahasiswa | 1:N | One student, many attendances |

## Cascade Rules

### ON DELETE CASCADE
- `users` → `admin` (delete profile when user deleted)
- `users` → `dosen` (delete profile when user deleted)
- `users` → `mahasiswa` (delete profile when user deleted)
- `jurnal_perkuliahan` → `presensi_mahasiswa` (delete attendances when session deleted)

### ON DELETE RESTRICT
- `matakuliah` → `jadwal_kuliah` (cannot delete course with schedules)
- `dosen` → `jadwal_kuliah` (cannot delete lecturer with schedules)
- `jadwal_kuliah` → `jurnal_perkuliahan` (cannot delete schedule with sessions)
- `mahasiswa` → `presensi_mahasiswa` (cannot delete student with attendances)

## Indexes Strategy

### Primary Keys (Auto-indexed)
- All `id_*` columns are primary keys with auto-increment

### Foreign Keys (Indexed)
- All FK columns automatically indexed for JOIN performance

### Business Logic Indexes
- `username` (UNIQUE) - Fast login lookup
- `nidn` (UNIQUE) - Fast dosen lookup
- `nim` (UNIQUE) - Fast mahasiswa lookup
- `kode_mk` (UNIQUE) - Fast course lookup
- `token_presensi` - Fast token validation
- `tanggal` - Fast date-based queries

### Composite Indexes
- `(tanggal, status_sesi)` - Optimasi query jurnal by date and status
- `(status, id_jurnal)` - Optimasi query presensi by status
- `(id_jurnal, id_mahasiswa)` - UNIQUE constraint untuk prevent duplicate attendance

## Data Flow

### Flow 1: User Registration
```
1. INSERT INTO users (username, password, role)
2. Based on role:
   - IF 'admin': INSERT INTO admin (user_id, ...)
   - IF 'dosen': INSERT INTO dosen (user_id, nidn, ...)
   - IF 'mhs': INSERT INTO mahasiswa (user_id, nim, ...)
```

### Flow 2: Dosen Check-in (Open Session)
```
1. Dosen login (query users + dosen)
2. Pilih jadwal_kuliah
3. CALL sp_buka_sesi_kuliah(id_jadwal, tanggal, jam, materi)
4. System generates token_presensi
5. INSERT INTO jurnal_perkuliahan (status='Open')
6. Return token to dosen
```

### Flow 3: Mahasiswa Presensi
```
1. Mahasiswa login (query users + mahasiswa)
2. Input token_presensi
3. CALL sp_presensi_mahasiswa(id_mahasiswa, token)
4. System validates:
   - Token exists?
   - Session still Open?
   - Already attended?
5. INSERT INTO presensi_mahasiswa
```

### Flow 4: Dosen Check-out (Close Session)
```
1. Dosen pilih jurnal aktif
2. CALL sp_tutup_sesi_kuliah(id_jurnal, jam_keluar)
3. UPDATE jurnal_perkuliahan SET status='Closed', jam_keluar=...
4. Mahasiswa tidak bisa presensi lagi
```

### Flow 5: View Attendance Report
```
1. Query view_rekap_presensi
2. Filter by nim/kode_mk
3. Display attendance statistics
```

---

## 🎨 ERD Notation Legend

```
PK  = Primary Key
FK  = Foreign Key
UNQ = Unique Constraint
1:1 = One-to-One Relationship
1:N = One-to-Many Relationship
N:1 = Many-to-One Relationship
◄   = Foreign Key Reference Direction
```

## 📐 Tools for Visualization

Anda bisa visualisasi ERD ini dengan tools:
1. **MySQL Workbench** - Reverse engineer dari database
2. **dbdiagram.io** - Free online ERD tool
3. **Draw.io** - Free diagramming tool
4. **Lucidchart** - Professional diagramming

### Import ke MySQL Workbench
```
1. Database → Reverse Engineer
2. Select connection dan database 'db_presensi_uas'
3. Auto-generate ERD diagram
```

---

**Last Updated:** 2024-01-05  
**Version:** 1.0.0  
**Maintainer:** Development Team
