# Admin Module Documentation

## Overview

The Admin module provides comprehensive management capabilities for the SIPRES (Sistem Informasi Presensi) system. Admin has the highest hierarchy role and full access to all system features.

## Features

### 1. Dashboard (`dashboard.php`)
- Welcome screen with user information
- Quick access to all admin features
- Security information display
- Role badge display

### 2. Attendance Monitoring (`absensi.php`)
**Description**: Monitor all attendance activities in the system

**Features**:
- View dosen attendance sessions (jurnal_perkuliahan)
- View mahasiswa attendance records (presensi_mahasiswa)
- Toggle between dosen and mahasiswa views
- Filter by date range (start date to end date)
- Search by name, NIM, or course name
- Statistics dashboard showing:
  - Total sessions/attendance
  - Active sessions
  - Completed sessions
  - Total student attendance

**Access**: `http://localhost:8000/modules/admin/absensi.php`

### 3. Forum Management (`kelola_forum.php`)
**Description**: Create and manage attendance forums (sessions)

**Features**:
- Create new attendance forum with:
  - Schedule selection
  - Date and time
  - Course material description
  - Automatic 6-character token generation
- View all forums with details
- Close active forums
- Statistics dashboard showing:
  - Total forums
  - Active forums
  - Completed forums
  - Total attendance count

**Access**: `http://localhost:8000/modules/admin/kelola_forum.php`

**Token Security**: Tokens are generated using cryptographically secure `random_int()` function and validated for uniqueness.

### 4. User Management (`kelola_pengguna.php`)
**Description**: Complete CRUD operations for dosen and mahasiswa

**Features**:
- Toggle between Dosen and Mahasiswa management
- **Add New User**:
  - Create user account with hashed password
  - Create profile (dosen/mahasiswa)
  - Transaction support ensures data integrity
- **Edit User**:
  - Update profile information
  - Update username
  - Password remains unchanged (security measure)
- **Delete User**:
  - Cascade delete removes user and profile
  - Confirmation dialog for safety
- Statistics dashboard showing:
  - Total dosen
  - Total mahasiswa
  - Total users

**Access**: `http://localhost:8000/modules/admin/kelola_pengguna.php`

**Security Notes**:
- Passwords are hashed using PASSWORD_BCRYPT
- All operations use database transactions
- Delete operations include confirmation

### 5. Activity Logs (`log_aktivitas.php`)
**Description**: View system activity history

**Features**:
- View recent activities:
  - Dosen check-in/check-out
  - Mahasiswa presensi
- Filter by date range
- Search by username or activity
- Statistics showing activity breakdown

**Access**: `http://localhost:8000/modules/admin/log_aktivitas.php`

**Note**: Currently shows activities from jurnal_perkuliahan and presensi_mahasiswa tables. Can be extended with a dedicated activity_log table.

## Admin Functions Library

Location: `includes/admin_functions.php`

### Attendance Functions

```php
getAllAttendanceRecords($start_date, $end_date, $search)
// Returns all dosen attendance sessions with filtering

getAllStudentAttendance($start_date, $end_date, $search)
// Returns all mahasiswa attendance records with filtering
```

### User Management Functions

```php
getAllDosen()
// Returns list of all dosen with user info

getAllMahasiswa()
// Returns list of all mahasiswa with user info

getDosenById($id_dosen)
// Returns dosen details by ID

getMahasiswaById($id_mahasiswa)
// Returns mahasiswa details by ID

addDosen($nidn, $nama_lengkap, $email, $no_telp, $username, $password)
// Creates new dosen with user account

addMahasiswa($nim, $nama_lengkap, $jurusan, $angkatan, $email, $no_telp, $username, $password)
// Creates new mahasiswa with user account

updateDosen($id_dosen, $nidn, $nama_lengkap, $email, $no_telp, $username)
// Updates dosen information

updateMahasiswa($id_mahasiswa, $nim, $nama_lengkap, $jurusan, $angkatan, $email, $no_telp, $username)
// Updates mahasiswa information

deleteDosen($id_dosen)
// Deletes dosen and cascade deletes user account

deleteMahasiswa($id_mahasiswa)
// Deletes mahasiswa and cascade deletes user account
```

### Forum Management Functions

```php
getAllMatakuliah()
// Returns list of all courses

getAllJadwal()
// Returns list of all schedules with course and dosen info

createForumAbsensi($id_jadwal, $tanggal, $jam_masuk, $materi_kuliah)
// Creates new forum and generates unique token

closeForumAbsensi($id_jurnal, $jam_keluar)
// Closes forum and prevents further attendance

generateToken()
// Generates cryptographically secure 6-character token
```

### Activity Log Functions

```php
getActivityLogs($start_date, $end_date, $search)
// Returns activity logs with filtering
```

## Security Features

### Authentication & Authorization
- All pages check for admin role: `if ($_SESSION['role'] != 'admin')`
- Unauthorized access redirects to login page
- Session timeout: 30 minutes

### SQL Injection Prevention
- All queries use prepared statements
- Parameter binding with `bind_param()`
- No string concatenation in SQL queries

### XSS Prevention
- All user outputs escaped with `htmlspecialchars()`
- Example: `<?php echo htmlspecialchars($nama); ?>`

### Password Security
- PASSWORD_BCRYPT algorithm
- Minimum 6 characters
- Never stored in plain text

### Transaction Support
- Add/edit/delete operations use transactions
- Automatic rollback on failure
- Ensures data integrity

### Token Security
- Cryptographically secure `random_int()`
- 6-character alphanumeric tokens
- Uniqueness validation

### Error Handling
- Try-catch blocks on all database operations
- User-friendly error messages
- Sensitive information not exposed
- Errors logged with `error_log()`

## Usage Examples

### Create New Dosen
1. Navigate to "Kelola Pengguna"
2. Select "Dosen" tab
3. Fill in the form:
   - NIDN: 0012345678
   - Nama Lengkap: Dr. John Doe
   - Email: john.doe@univ.ac.id
   - No. Telepon: 081234567890
   - Username: dosen003
   - Password: securepass123
4. Click "Tambah Dosen"

### Create Attendance Forum
1. Navigate to "Kelola Forum Absensi"
2. Fill in the form:
   - Select schedule from dropdown
   - Set date (default: today)
   - Set time (default: current time)
   - Enter course material description
3. Click "Buat Forum & Generate Token"
4. Token will be displayed - share with students

### Monitor Attendance
1. Navigate to "Cek Absensi"
2. Toggle between "Absensi Dosen" or "Absensi Mahasiswa"
3. Set date range filter
4. Enter search term (optional)
5. Click "Filter Data"

## Database Schema Requirements

### Tables Used
- `users` - User accounts
- `admin` - Admin profiles
- `dosen` - Dosen profiles
- `mahasiswa` - Mahasiswa profiles
- `matakuliah` - Course master data
- `jadwal_kuliah` - Class schedules
- `jurnal_perkuliahan` - Lecture sessions
- `presensi_mahasiswa` - Student attendance

### Foreign Key Relationships
- `dosen.user_id` → `users.id_user` (CASCADE DELETE)
- `mahasiswa.user_id` → `users.id_user` (CASCADE DELETE)
- `jadwal_kuliah.id_mk` → `matakuliah.id_mk` (RESTRICT)
- `jadwal_kuliah.id_dosen` → `dosen.id_dosen` (RESTRICT)
- `jurnal_perkuliahan.id_jadwal` → `jadwal_kuliah.id_jadwal` (RESTRICT)
- `presensi_mahasiswa.id_jurnal` → `jurnal_perkuliahan.id_jurnal` (CASCADE)

## User Interface

### Design Principles
- Clean, modern interface
- Responsive design (mobile-friendly)
- Consistent color scheme with role badges
- Statistics cards for quick insights
- Filter forms for data exploration
- Action buttons with confirmation dialogs

### Color Coding
- Admin Badge: Purple/Blue gradient
- Status Open: Green
- Status Closed: Gray
- Hadir: Green
- Izin: Yellow
- Sakit: Blue
- Alpha: Red

## Best Practices

### When Adding New Features
1. Add functions to `includes/admin_functions.php`
2. Use prepared statements for all queries
3. Add try-catch error handling
4. Escape all outputs with `htmlspecialchars()`
5. Check admin role on all pages
6. Use transactions for multi-table operations
7. Add statistics cards for quick insights
8. Provide user feedback (success/error messages)

### Testing Checklist
- [ ] Role authorization works
- [ ] All CRUD operations function correctly
- [ ] Filters and search work properly
- [ ] Error messages are user-friendly
- [ ] No PHP syntax errors
- [ ] No SQL injection vulnerabilities
- [ ] XSS prevention works
- [ ] Transaction rollback works on failure

## Troubleshooting

### Common Issues

**Issue**: "Access Denied" when trying to access admin pages
- **Solution**: Ensure you're logged in with admin role

**Issue**: Database connection errors
- **Solution**: Check `config/database.php` settings

**Issue**: Token not generating
- **Solution**: Check `random_int()` function availability (PHP 7.0+)

**Issue**: User not created
- **Solution**: Check error logs for duplicate username/NIDN/NIM

## Future Enhancements

### Recommended Additions
1. **CSRF Protection**: Add CSRF tokens to all forms
2. **Rate Limiting**: Limit requests per user per timeframe
3. **Enhanced Logging**: Dedicated activity_log table with IP addresses
4. **Bulk Operations**: Import users from CSV/Excel
5. **Export Features**: Export reports to PDF/Excel
6. **Dashboard Charts**: Visualizations for statistics
7. **Email Notifications**: Notify users of account changes
8. **Password Reset**: Allow admins to reset user passwords
9. **User Status**: Active/inactive user management
10. **Backup/Restore**: Database backup functionality

## Support

For issues or questions:
1. Check this documentation
2. Review `includes/admin_functions.php` for function details
3. Check PHP error logs
4. Review database schema in `database/db_presensi_uas.sql`

## Version History

- **v1.0.0** (2025-12-09)
  - Initial admin panel implementation
  - Attendance monitoring
  - User management (CRUD)
  - Forum management
  - Activity logs
  - Security improvements

---

**Developed for SIPRES** - Sistem Informasi Presensi  
**Role**: Administrator (Highest Hierarchy)  
**Status**: Production Ready ✅
