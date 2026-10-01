# 🏛️ ARSITEKTUR SISTEM OBE_SISTEM_INFORMASI

## Diagram Arsitektur Keseluruhan

```
┌─────────────────────────────────────────────────────────────────┐
│                     PRESENTATION LAYER                          │
│                    (Frontend / User Interface)                   │
├─────────────────────────────────────────────────────────────────┤
│  • Blade Templates      • Filament Admin Panel     • Tailwind   │
│  • Responsive Design    • Role-based Views        • Vite Build  │
│  • AJAX/Axios Calls     • PDF Export              • Excel Imp   │
└────────────────────────────────┬────────────────────────────────┘
                                  │
                          HTTP/JSON API
                                  │
┌────────────────────────────────┴────────────────────────────────┐
│                  APPLICATION LAYER (Controllers)                │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐            │
│  │  Kurikulum   │  │  Penilaian   │  │  Evaluasi    │            │
│  │  Controller  │  │  Controller  │  │  Controller  │            │
│  │  (CPL, CPMK) │  │  (Nilai, BAP)│  │  (Analysis)  │            │
│  └──────────────┘  └──────────────┘  └──────────────┘            │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐            │
│  │  Mahasiswa   │  │  Dashboard   │  │  Manajemen   │            │
│  │  Controller  │  │  Controller  │  │  Resources   │            │
│  │  (KRS, PA)   │  │  (Analytics) │  │  (User, Room)│            │
│  └──────────────┘  └──────────────┘  └──────────────┘            │
│                                                                   │
│  Service Layer:  RpsGeneratorService & Business Logic            │
└────────────────────────────────┬────────────────────────────────┘
                                  │
┌────────────────────────────────┴────────────────────────────────┐
│                    DATA ACCESS LAYER (Models)                   │
├─────────────────────────────────────────────────────────────────┤
│  Eloquent ORM                                                    │
│  └─ Relationships, Queries, Validation                          │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐            │
│  │ Pembelajaran │  │  Penilaian   │  │  Evaluasi    │            │
│  │              │  │              │  │              │            │
│  │• Mata Kuliah │  │• Nilai MHS   │  │• Evaluasi CP │            │
│  │• CPL/CPMK    │  │• Sub-CPMK    │  │• Evaluasi CB │            │
│  │• RPS/BAP     │  │• Bobot       │  │• Gap Analysi │            │
│  │• Bahan Kajian│  │• Rubric      │  │• CQI Actions │            │
│  └──────────────┘  └──────────────┘  └──────────────┘            │
│                                                                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐            │
│  │  Mahasiswa   │  │  Infrastruktur │  │  Integration │           │
│  │              │  │                │  │              │           │
│  │• User (Role) │  │• Room         │  │• DosenMK    │           │
│  │• Mahasiswa   │  │• Schedule     │  │• Publikasi  │           │
│  │• KRS         │  │• CourseSchedl │  │• MBKM       │           │
│  │• MahasiswaMK │  │• BlockRules   │  │• Portal Token│          │
│  └──────────────┘  └──────────────┘  └──────────────┘            │
└────────────────────────────────┬────────────────────────────────┘
                                  │
┌────────────────────────────────┴────────────────────────────────┐
│                  DATABASE LAYER (SQLite/MySQL)                  │
├─────────────────────────────────────────────────────────────────┤
│  45+ Tables dengan Foreign Keys & Relationships                 │
│                                                                   │
│  Core Tables: users, mata_kuliahs, cpls, cpmks, sub_cpmks      │
│  Pembelajaran: rps_pertemuan, rps_detail, bap, bap_pertemuan   │
│  Penilaian: nilai_mahasiswas, nilai_sub_cpmks, bobot_penilaian │
│  Evaluasi: evaluasi_cpmks, evaluasi_cpls, laporan_evaluasi     │
│  Infrastruktur: rooms, course_schedules, dosen_mata_kuliahs     │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## Role-Based Access Control

```
┌────────────────────────────────────────────────────────────────┐
│                        ROLE HIERARCHY                          │
└────────────────────────────────────────────────────────────────┘

┌─────────────┐
│    ADMIN    │ ← Full System Access (Sudah punya semua)
└─────────────┘
      ↓
┌─────────────────────────────────────────────────────────────┐
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   KAPRODI    │  │   DOSEN      │  │  AKADEMIK    │      │
│  │              │  │              │  │              │      │
│  │• Oversee     │  │• Teach       │  │• Register    │      │
│  │• Approve     │  │• Submit RPS  │  │• Process KRS │      │
│  │• Evaluate    │  │• Record BAP  │  │• Import Nilai│      │
│  │• Plan AIP    │  │• Input Nilai │  │• Issue Cert  │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │KEMAHASISWAAN │  │  MAHASISWA   │  │     PA       │      │
│  │              │  │  (STUDENT)   │  │  (ADVISOR)   │      │
│  │• Student Dev │  │• View Grades │  │• Guide       │      │
│  │• Tracking    │  │• View CPMK   │  │• Approve KRS │      │
│  │• Services    │  │• View Schd   │  │• Advising    │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                                                              │
└────────────────────────────────────────────────────────────┘
```

---

## Data Flow untuk Setiap Proses Utama

### 1️⃣ PROSES PERSIAPAN SEMESTER

```
KAPRODI defines
    ├─ CPL (Capaian Pembelajaran Lulusan)
    ├─ Profil Lulusan → Mapping ke CPL SNDIKTI
    └─ Kurikulum: Mata Kuliah + Semester Placement
           ├─ CPMK (per mata kuliah)
           ├─ Sub-CPMK (detail breakdown)
           └─ Teknik Penilaian (UTS, UAS, Tugas, dll)

DOSEN assigned to Mata Kuliah
    └─ Gets notifications → Prepare Course

AKADEMIK plans
    ├─ Room & Class Scheduling
    └─ Course Schedule Setup (lock/unlock periods)
```

### 2️⃣ PROSES PERKULIAHAN

```
DOSEN creates RPS
    ├─ Input: Tanggal mulai, hari, waktu
    ├─ System generates: 16 minggu jadwal (skip holidays)
    └─ Add: Materi, Referensi, Estimasi Waktu

MAHASISWA enroll
    ├─ Submit KRS
    ├─ PA review → Approve
    └─ Akademik finalize

DOSEN documents BAP
    ├─ Per Pertemuan:
    │   ├─ Metode Pembelajaran
    │   ├─ Capaian CPMK
    │   └─ Attendance (Token-based)
    │
    └─ Record Nilai:
        ├─ UTS, UAS, Tugas
        └─ Auto-calculate berdasarkan Bobot
```

### 3️⃣ PROSES EVALUASI

```
SISTEM auto-calculates:
    ├─ CPMK Achievement (% mahasiswa mencapai)
    ├─ Distribution Analysis (Excellent, Good, Fair, Poor)
    ├─ CPL Target Achievement
    └─ Gap Identification

DOSEN reviews & fills Evaluasi:
    ├─ CPMK Gaps (mana yang kurang)
    ├─ Hambatan (obstacles/barriers)
    ├─ Penyebab (root causes)
    └─ Rekomendasi (suggestions)

KAPRODI consolidates:
    ├─ CPL Achievement Review
    ├─ Program-level Gap Analysis
    └─ Generates AIP (Action Improvement Plan)
```

---

## File Structure Organisasi

```
OBE_sistem_informasi/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── RpsController.php              ← RPS Management
│   │   │   ├── BapController.php              ← BAP Documentation
│   │   │   ├── CplController.php              ← CPL Management
│   │   │   ├── CpmkController.php             ← CPMK Management
│   │   │   ├── NilaiMahasiswaController.php   ← Grade Recording
│   │   │   ├── EvaluasiController.php         ← Evaluation Analysis
│   │   │   ├── *DashboardController.php       ← Analytics (7 controllers)
│   │   │   ├── MahasiswaController.php        ← Student Management
│   │   │   ├── KrsMahasiswaController.php     ← Enrollment
│   │   │   ├── MataKuliahController.php       ← Course Management
│   │   │   ├── DistribusiDosenController.php  ← Faculty Assignment
│   │   │   └── ... (41 controllers total)
│   │   │
│   │   └── Middleware/ (Authentication & Authorization)
│   │
│   ├── Models/ (45 models)
│   │   ├── Pembelajaran: MataKuliah, Cpl, Cpmk, SubCpmk,
│   │   │               RpsPertemuan, RpsDetail, Bap, BapPertemuan
│   │   ├── Penilaian: NilaiMahasiswa, NilaiSubCpmk, BobotPenilaian,
│   │   │            TeknikPenilaian, RumusanNilaiMk
│   │   ├── Evaluasi: EvaluasiCpmk, EvaluasiCpl, EvaluasiCohort,
│   │   │            LaporanEvaluasi, CqiAction
│   │   ├── Mahasiswa: Mahasiswa, MahasiswaMk, KrsMahasiswa
│   │   ├── Infrastruktur: User, Room, CourseSchedule, DosenMataKuliah
│   │   └── ...
│   │
│   ├── Services/
│   │   ├── RpsGeneratorService.php           ← Auto-schedule generator
│   │   └── ... (Business logic services)
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── Filament/AdminPanelProvider.php
│   │
│   ├── Exports/ (Excel Export Classes)
│   ├── Imports/ (Excel Import Classes)
│   └── Filament/ (Filament Resources)
│
├── database/
│   ├── migrations/ (45+ migration files)
│   │   ├── 2024_01_01_00xxxx_create_*.php
│   │   ├── 2025_04_0x_00xxxx_create_*.php
│   │   └── Includes: Performance indexes, FK constraints
│   │
│   ├── seeders/ (Optional data seeding)
│   └── factories/ (Model factories for testing)
│
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── dashboard.blade.php          ← Main layout
│   │   │   └── admin.blade.php              ← Admin layout
│   │   ├── components/                       ← Reusable Blade components
│   │   ├── dashboard/                        ← Dashboard views
│   │   └── pages/                            ← Feature pages (RPS, BAP, dll)
│   │
│   └── css/
│       └── app.css                           ← Tailwind compilation
│
├── routes/
│   ├── web.php                               ← Main routes (Auth, Resources)
│   └── api.php                               ← Optional API routes
│
├── config/
│   ├── app.php                               ← App configuration
│   ├── database.php                          ← Database connection (SQLite/MySQL)
│   ├── obe.php                               ← Custom OBE config (Institution info)
│   ├── auth.php, cache.php, session.php, etc.
│   └── (Other Laravel configs)
│
├── public/
│   ├── index.php                             ← Entry point
│   ├── css/, js/                             ← Built assets (Vite)
│   └── uploads/                              ← User uploaded files
│
├── storage/
│   ├── app/                                  ← File storage
│   ├── logs/                                 ← Application logs
│   └── framework/                            ← Cache, sessions
│
├── tests/                                    ← Test files (PHPUnit)
├── vendor/                                   ← Composer dependencies
│
├── composer.json                             ← PHP dependencies
├── package.json                              ← Node.js dependencies
├── vite.config.js                            ← Vite configuration
├── .env                                      ← Environment variables
└── artisan                                   ← Laravel CLI

```

---

## Key Technologies & Why They're Used

| Technology            | Purpose         | Benefits                                     |
| --------------------- | --------------- | -------------------------------------------- |
| **Laravel 12**        | Web Framework   | Modern, secure, OOP-based rapid development  |
| **Filament 3.2**      | Admin Panel     | Beautiful, extensible admin dashboard        |
| **Eloquent ORM**      | Database Access | Type-safe, relationship-aware queries        |
| **Blade Templating**  | View Rendering  | Powerful, PHP-based templating               |
| **Tailwind CSS**      | Styling         | Utility-first, responsive, maintainable CSS  |
| **Vite**              | Build Tool      | Lightning-fast development server            |
| **Laravel Pail**      | Logging         | Real-time log streaming for debugging        |
| **DOMPDF**            | PDF Export      | Generate PDF reports (RPS, BAP, evaluasi)    |
| **Maatwebsite Excel** | Excel I/O       | Import/export for bulk data operations       |
| **SQLite/MySQL**      | Database        | Persistent data storage with ACID guarantees |

---

## Security Architecture

```
┌─────────────────────────────────────────┐
│      AUTHENTICATION & AUTHORIZATION      │
├─────────────────────────────────────────┤
│                                          │
│  • Laravel Auth (sessions-based)        │
│  • Password hashing (bcrypt)            │
│  • CSRF protection (Laravel CSRF token) │
│  • Role-Based Access Control (RBAC)     │
│    - 6 primary roles with permissions   │
│                                          │
│  Query Builders Prevent SQL Injection   │
│  Eloquent escapes all inputs            │
│                                          │
└─────────────────────────────────────────┘
```

---

## Performance Optimizations

```
✅ Database Indexes
   - Foreign keys indexed
   - Query optimizations on large tables (nilai, bap)

✅ Eager Loading
   - Controllers use with() to prevent N+1 queries

✅ Caching
   - Laravel cache for frequently accessed data
   - View cache for dashboard

✅ Pagination
   - Large data sets (nilai, bap) are paginated

✅ Vite Build Optimization
   - CSS/JS minification & bundling
   - Lazy loading of components
```

---

## Integration Points

```
┌──────────────────────────────────────────┐
│  POTENTIAL INTEGRATIONS                  │
├──────────────────────────────────────────┤
│                                          │
│  🔗 SIAKAD (Student Info System)        │
│     └─ Real-time data sync               │
│                                          │
│  🔗 BAN-PT Reporting                     │
│     └─ Automated accreditation reports   │
│                                          │
│  🔗 Email Service (for notifications)    │
│     └─ SMTP or cloud-based (SendGrid)    │
│                                          │
│  🔗 Google Drive / Cloud Storage         │
│     └─ Backup and document storage       │
│                                          │
│  🔗 Mobile App (React Native / Flutter)  │
│     └─ REST API backend ready            │
│                                          │
└──────────────────────────────────────────┘
```

---

## Deployment Architecture

```
Production Setup:

┌─────────────────────────────────────────┐
│        Web Server (nginx/Apache)        │
│  ↓                                       │
│  ├─ Static assets (CSS, JS via Vite)   │
│  ├─ PHP-FPM (PHP execution)            │
│  └─ Session handling                    │
│                                          │
│  Laravel Application (12.0)             │
│  ├─ Routes → Controllers → Models       │
│  ├─ Service providers & middleware      │
│  └─ Logging & error handling            │
│                                          │
│  Database (SQLite or MySQL)             │
│  ├─ 45+ tables with indexes             │
│  ├─ Foreign key constraints             │
│  └─ Automated backups                   │
│                                          │
│  Storage (File system or S3)            │
│  └─ PDF documents, Excel files, etc.    │
│                                          │
│  Logs (storage/logs/)                   │
│  └─ Application & error logging         │
│                                          │
└─────────────────────────────────────────┘
```

---

_Last Updated: 2025_  
_Architecture Version: 1.0_  
_Framework: Laravel 12 | Database: SQLite/MySQL_
