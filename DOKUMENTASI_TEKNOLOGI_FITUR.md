# 📊 LAPORAN KOMPREHENSIF: TEKNOLOGI DAN FITUR OBE_SISTEM_INFORMASI

**Dokumen Analisis**: Universitas Sari Mulia - Program Studi Sistem Informasi  
**Framework**: Laravel 12 dengan Filament Admin Panel  
**Database**: SQLite / MySQL  
**PHP Version**: ^8.2

---

## 🏗️ TEKNOLOGI YANG DIGUNAKAN (TECH STACK)

### Backend

| Komponen        | Teknologi      | Versi      | Keterangan                            |
| --------------- | -------------- | ---------- | ------------------------------------- |
| **Framework**   | Laravel        | ^12.0      | Full-stack PHP framework modern       |
| **PHP**         | PHP            | ^8.2       | Dengan fitur OOP dan Typed Properties |
| **Database**    | SQLite / MySQL | -          | SQLite default, MySQL optional        |
| **Admin Panel** | Filament       | ^3.2       | Modern admin UI dengan Laravel        |
| **ORM**         | Eloquent       | (built-in) | Model-based database abstraction      |

### Frontend

| Komponen            | Teknologi      | Versi      | Keterangan                          |
| ------------------- | -------------- | ---------- | ----------------------------------- |
| **Template Engine** | Blade          | (built-in) | Laravel's powerful templating       |
| **CSS Framework**   | Tailwind CSS   | ^4.0       | Utility-first CSS framework         |
| **Build Tool**      | Vite           | ^7.0.7     | Lightning-fast build tool           |
| **HTTP Client**     | Axios          | ^1.11.0    | Promise-based HTTP client           |
| **Package Manager** | npm / Composer | -          | Node.js & PHP dependency management |

### Tools & Services

| Tool                        | Versi    | Fungsi                                 |
| --------------------------- | -------- | -------------------------------------- |
| **barryvdh/laravel-dompdf** | ^3.1     | PDF generation untuk laporan & dokumen |
| **maatwebsite/excel**       | ^3.1     | Excel import/export (RPS, BAP, Nilai)  |
| **laravel-tinker**          | ^2.10.1  | Interactive REPL untuk debugging       |
| **laravel-pail**            | ^1.2.2   | Real-time log viewer                   |
| **laravel-sail**            | ^1.41    | Docker development environment         |
| **phpunit**                 | ^11.5.50 | Testing framework                      |
| **laravel-pint**            | ^1.24    | Code style fixer                       |

---

## 📈 FITUR YANG SUDAH DIIMPLEMENTASIKAN

### 1. **MANAJEMEN KURIKULUM & PEMBELAJARAN**

#### a) **RPS (Rencana Pembelajaran Semester)**

- **Controller**: `RpsController`
- **Models**: `RpsPertemuan`, `RpsDetail`, `RpsReferensi`
- **Fitur**:
    - Automatic schedule generation berdasarkan hari, tanggal mulai (dengan skip holidays)
    - Support untuk 16 minggu perkuliahan
    - Integrasi dengan kalender akademik Indonesia
    - PDF export untuk dokumentasi resmi
    - Detail pertemuan dengan materi, metode, dan estimasi waktu

**Contoh**: Dosen input tanggal mulai & hari → Sistem otomatis generate jadwal 16 minggu, skip hari libur nasional

#### b) **CPL (Capaian Pembelajaran Lulusan)**

- **Controller**: `CplController`
- **Models**: `Cpl`, `CplAchievement`, `CplTarget`, `CplSndikti`
- **Fitur**:
    - Mapping ke Profil Lulusan (Standar SNDIKTI)
    - Tracking capaian lulusan per cohort
    - Evaluasi pencapaian target CPL
    - Linkage ke mata kuliah & CPMK

#### c) **CPMK (Capaian Pembelajaran Mata Kuliah)**

- **Controller**: `CpmkController`
- **Models**: `Cpmk`, `CpmkAchievement`, `CpmkRubric`
- **Fitur**:
    - Breakdown dari CPL ke mata kuliah
    - Sub-CPMK untuk detail granular
    - Penilaian menggunakan rubrik/scoring
    - Tracking achievement rate per mahasiswa

#### d) **Profil Lulusan & Standar SNDIKTI**

- **Controller**: `ProfilLulusanController`, `CplSndiktiController`
- **Models**: `ProfilLulusan`, `CplSndikti`
- **Fitur**:
    - Mapping Profil Lulusan ke CPL SNDIKTI
    - Standard alignment checking
    - Dokumentasi per profil lulusan

---

### 2. **MANAJEMEN MATA KULIAH & PEMBELAJARAN**

#### a) **Mata Kuliah**

- **Controller**: `MataKuliahController`
- **Models**: `MataKuliah`, `BahanKajian`, `TeknikPenilaian`
- **Fitur**:
    - Database semua mata kuliah per semester
    - Linking ke CPL dan CPMK
    - Teknik penilaian per MK (UTS, UAS, Tugas, dll)
    - Bahan kajian terstruktur

#### b) **Distribusi Dosen**

- **Controller**: `DistribusiDosenController`
- **Models**: `DosenMataKuliah`
- **Fitur**:
    - Assign dosen ke mata kuliah
    - Tracking SKS (Satuan Kredit Semester)
    - Status assignment (aktif/non-aktif)

---

### 3. **BAP (BUKTI AUTENTIK PELAKSANAAN)**

#### a) **BAP Management**

- **Controller**: `BapController`
- **Models**: `Bap`, `BapPertemuan`, `BapToken`
- **Fitur**:
    - Pencatatan detail setiap pertemuan
    - Metode pembelajaran (Ceramah, Diskusi, PBL, PjBL, dll)
    - Pencapaian CPMK per pertemuan
    - PDF export untuk dokumentasi

**Metode yang didukung**:

- Ceramah, Diskusi, Praktikum, Demonstrasi
- PBL (Problem-Based Learning)
- PjBL (Project-Based Learning)
- Discovery Learning, Case Study
- Role Playing & Simulation

#### b) **BAP Penilaian & Evaluasi**

- **Controller**: `BapPenilaianController`, `EvaluasiBapController`
- **Models**: `BapPertemuan`, `BapMahasiswaEvaluasi`, `BapToken`
- **Fitur**:
    - Penilaian pencapaian CPMK per mahasiswa
    - Token-based evaluation system
    - Feedback untuk mahasiswa
    - Attendance tracking

---

### 4. **PENILAIAN & NILAI MAHASISWA**

#### a) **Nilai Mahasiswa**

- **Controller**: `NilaiMahasiswaController`
- **Models**: `NilaiMahasiswa`, `NilaiSubCpmk`
- **Fitur**:
    - Pencatatan nilai per komponen (UTS, UAS, Tugas, dll)
    - Bobot penilaian yang fleksibel
    - Sub-CPMK scoring
    - Import nilai dari file Excel

#### b) **Bobot Penilaian**

- **Controller**: `BobotPenilaianController`
- **Models**: `BobotPenilaian`, `RumusanNilaiMk`
- **Fitur**:
    - Definisi bobot per komponen penilaian
    - Rumus perhitungan nilai akhir
    - Konfigurasi per mata kuliah

#### c) **Import Nilai**

- **Controller**: `ImportNilaiController`
- **Fitur**:
    - Bulk import dari file Excel
    - Validasi otomatis
    - Error reporting & correction

---

### 5. **MANAJEMEN MAHASISWA**

#### a) **Data Mahasiswa**

- **Controller**: `MahasiswaController`
- **Models**: `Mahasiswa`, `MahasiswaMk`, `KrsMahasiswa`
- **Fitur**:
    - Database lengkap mahasiswa
    - Tracking status akademik
    - PA (Pembimbing Akademik) assignment
    - KRS (Kartu Rencana Studi) management

#### b) **KRS & Enrollment**

- **Controller**: `KrsMahasiswaController`
- **Models**: `KrsMahasiswa`
- **Fitur**:
    - Pendaftaran mata kuliah per semester
    - PA approval workflow
    - SKS validation
    - Tracking enrollment status

---

### 6. **EVALUASI & ANALISIS**

#### a) **Evaluasi CPMK**

- **Controller**: `EvaluasiController`
- **Models**: `EvaluasiCpmk`, `EvaluasiDistribusiNilai`
- **Fitur**:
    - Analysis capaian CPMK per cohort
    - Distribution analysis (berapa % mencapai, belum mencapai)
    - Hambatan & penyebab ketidakcapaian
    - Tindak lanjut improvement

#### b) **Evaluasi CPL**

- **Models**: `EvaluasiCpl`, `EvaluasiCohort`
- **Fitur**:
    - Tracking CPL achievement per cohort
    - Target vs actual comparison
    - Gap analysis & recommendations

#### c) **Laporan Evaluasi**

- **Controller**: `LaporanEvaluasiController`
- **Models**: `LaporanEvaluasi`
- **Fitur**:
    - Generated reports dengan analisis mendalam
    - Export ke PDF format
    - Action items untuk improvement

---

### 7. **DASHBOARD & ANALYTICS**

#### a) **Dashboard Berbasis Role**

- **Dosen Dashboard**: `DosenDashboardController`
    - Mata kuliah yang diampu
    - BAP/RPS status
    - Nilai mahasiswa
    - PA workload
- **Kaprodi Dashboard**: `KaprodiDashboardController`
    - Overview semua mata kuliah
    - CPMK achievement tracking
    - BAP completion status
    - Evaluasi CPL summary
- **Akademik Dashboard**: `AkademikDashboardController`
    - System-wide analytics
    - Kurasi data untuk laporan
- **Kemahasiswaan Dashboard**: `KemahasiswaanDashboardController`
    - Mahasiswa analytics
    - KRS status tracking
- **Mahasiswa Dashboard**: `MahasiswaDashboardController`
    - Personal grades
    - CPMK achievement tracker
    - Enrollment status

#### b) **Analytics & Reporting**

- **Room Analytics**: `RoomAnalyticsController`
    - Classroom usage analytics
    - Schedule optimization

---

### 8. **TAMBAHAN FEATURES**

#### a) **Publikasi Dosen**

- **Controller**: `PublikasiDosenController`
- **Models**: `PublikasiDosen`
- **Fitur**: Tracking publikasi ilmiah dosen

#### b) **MBKM (Magang, Belajar, Kuliah Magang)**

- **Controller**: `MbkmController`
- **Models**: `MbkmBkp`
- **Fitur**: Management kegiatan MBKM mahasiswa

#### c) **Manajemen Ruang & Jadwal**

- **Controller**: `RoomController`, `ScheduleController`, `CourseScheduleController`
- **Models**: `Room`, `CourseSchedule`, `RoomBlockRule`
- **Fitur**:
    - Room scheduling & conflict detection
    - Block rule untuk resource reservation
    - Schedule locking untuk periode tertentu

#### d) **CQI (Continuous Quality Improvement)**

- **Models**: `CqiAction`
- **Fitur**: Action items untuk quality improvement

---

### 9. **SISTEM USER & AUTHENTICATION**

#### a) **User Management**

- **Controller**: `UserController`
- **Models**: `User`
- **Roles yang didukung**:
    - **Admin**: Full system access
    - **Kaprodi**: Program director level
    - **Dosen**: Faculty level
    - **Akademik**: Academic staff
    - **Kemahasiswaan**: Student affairs
    - **Mahasiswa**: Student level

#### b) **Filament Admin Panel**

- Admin interface untuk quick access
- Role-based resource management
- User & permission management

---

## 🎯 FITUR YANG MUNGKIN KURANG / REKOMENDASI PENGEMBANGAN

### **HIGH PRIORITY** 🔴

#### 1. **Student Portfolio System**

**Mengapa diperlukan**:

- Mahasiswa perlu track pencapaian CPMK mereka sepanjang program
- Evidence collection untuk akreditasi & portfolio

**Rekomendasi**:

```
Models: StudentPortfolio, PortfolioEvidence
- Portfolio per mahasiswa dengan progress tracking
- Upload bukti pencapaian (assignment, project, reflection)
- Mapping ke sub-CPMK achievements
- Visual progress indicator
```

#### 2. **Advanced Reporting & Analytics**

**Mengapa diperlukan**:

- Akreditasi memerlukan trend analysis & predictive insights
- Belum ada comparative analysis antar cohort/semester

**Rekomendasi**:

```
Models: AnalyticsDashboard, TrendAnalysis
- Trend analysis CPMK achievement across cohorts
- Heatmap untuk identify weakness areas
- Predictive analytics untuk CPL achievement
- Export ke format BAN-PT
```

#### 3. **Automated Improvement Plan (AIP) Generator**

**Mengapa diperlukan**:

- Tindak lanjut evaluasi memerlukan structured planning
- Automated suggestions berdasarkan data analysis

**Rekomendasi**:

```
Models: ImprovementPlan, AIRecommendation
- AI-powered recommendations based on CPMK gaps
- Auto-generate action items
- Timeline & responsibility assignment
- Progress tracking
```

---

### **MEDIUM PRIORITY** 🟡

#### 4. **Integration dengan External Systems**

**Mengapa diperlukan**:

- Integrasi dengan SIAKAD untuk data sync otomatis
- API untuk reporting ke BAN-PT

**Rekomendasi**:

```
Models: ExternalSync, SyncLog
- SIAKAD integration untuk real-time data
- BAN-PT API compliance
- Data validation & error handling
```

#### 5. **Student Learning Experience (SLX) Tracking**

**Mengapa diperlukan**:

- Track student satisfaction & engagement
- Feedback loop untuk continuous improvement

**Rekomendasi**:

```
Models: StudentFeedback, SLXSurvey
- Periodic surveys untuk evaluasi pengajaran
- Course evaluation forms
- Engagement metrics
- Analysis & reporting
```

#### 6. **Industry Advisory Board (IAB) Management**

**Mengapa diperlukan**:

- Stakeholder engagement untuk curriculum relevance
- Feedback dari industri untuk program alignment

**Rekomendasi**:

```
Models: IndustryPartner, IABMeeting, FeedbackLog
- Manage industry partners
- Meeting minutes & feedback tracking
- Curriculum review based on industry input
```

---

### **NICE TO HAVE** 🟢

#### 7. **Mobile App / Mobile-Responsive Interface**

**Untuk**: Quick access ke nilai, jadwal, CPMK status

- Mobile-optimized dashboard
- Notification system

#### 8. **Gamification Elements**

**Untuk**: Student engagement & motivation

- Achievement badges
- Leaderboard (opsional)
- Progress visualization

#### 9. **Collaboration Tools**

**Untuk**: Group projects & peer learning

- Discussion forum per mata kuliah
- File sharing & submission
- Peer review system

#### 10. **Calendar & Notification System**

**Untuk**: Better communication

- Integrated calendar (RPS dates, BAP schedule, deadline)
- Email/SMS notifications
- Mobile push notifications

---

## 🔄 WORKFLOW UTAMA SISTEM

### **Semester Flow**

```
1. PERSIAPAN SEMESTER
   ├─ Profil Lulusan & CPL Definition
   ├─ Kurikulum Design (Mata Kuliah, CPMK)
   ├─ Dosen Assignment
   └─ Room & Schedule Planning

2. PERKULIAHAN
   ├─ RPS Creation (Auto-generated schedule)
   ├─ BAP Documentation (Per Pertemuan)
   ├─ Nilai Recording (UTS, UAS, Tugas)
   └─ KRS Management (Student Enrollment)

3. EVALUASI
   ├─ CPMK Achievement Analysis
   ├─ CPL Target Compliance Check
   ├─ Gap Analysis
   └─ Improvement Plan Generation

4. IMPROVEMENT
   ├─ Action Items Implementation
   ├─ Progress Tracking
   └─ Next Semester Planning
```

---

## 📊 DATABASE ARCHITECTURE OVERVIEW

### **Core Tables** (45+ tables)

**Pembelajaran**:

- `mata_kuliahs`, `cpls`, `cpmks`, `sub_cpmks`
- `rps_meetings`, `rps_details`, `rps_references`
- `bap`, `bap_meetings`, `bap_evaluations`

**Penilaian**:

- `nilai_mahasiswas`, `nilai_sub_cpmks`
- `bobot_penilaians`, `teknik_penilaians`
- `rumusan_nilai_mks`

**Mahasiswa**:

- `mahasiswas`, `mahasiswa_mk`, `krs_mahasiswas`

**Evaluasi**:

- `evaluasi_cpmks`, `evaluasi_cpls`, `evaluasi_cohorts`
- `evaluasi_hambatans`, `evaluasi_distribusi_nilais`

**Infrastruktur**:

- `users`, `rooms`, `course_schedules`, `dosen_mata_kuliah`
- `publications`, `mbkm_bkp`, `cqi_actions`

---

## ⚙️ TECHNICAL STACK SUMMARY

| Layer            | Technology           | Purpose                        |
| ---------------- | -------------------- | ------------------------------ |
| **Web Server**   | Laravel 12           | Backend API & Blade rendering  |
| **Frontend**     | Blade + Tailwind CSS | Server-side rendered templates |
| **Admin Panel**  | Filament 3.2         | Admin dashboard interface      |
| **Build**        | Vite 7.0             | Fast module bundling           |
| **Database**     | SQLite/MySQL         | Data persistence               |
| **Testing**      | PHPUnit 11           | Automated testing              |
| **Code Quality** | Laravel Pint         | Code style & formatting        |
| **Logging**      | Laravel Pail         | Real-time log viewing          |

---

## 🎓 KESIMPULAN

**OBE_SISTEM_INFORMASI** adalah sistem informasi terintegrasi untuk **Outcome-Based Education (OBE)** yang komprehensif. Sistem ini sudah mencakup:

✅ **Manajemen kurikulum** (CPL, CPMK, sub-CPMK)
✅ **Dokumentasi pembelajaran** (RPS, BAP)
✅ **Penilaian & nilai** (Bobot, Komponen, Import)
✅ **Evaluasi terstruktur** (Achievement analysis, Gap analysis)
✅ **Dashboard analytics** per role
✅ **Multi-user system** dengan role-based access
✅ **Export capabilities** (PDF, Excel)

**Saran pengembangan utama** fokus pada:

1. Student portfolio & evidence collection
2. Advanced analytics & predictive insights
3. Automated improvement plan generation
4. External system integration
5. Student feedback & engagement tracking

Sistem ini telah siap untuk mendukung **akreditasi program studi** dengan dokumentasi komprehensif dan analytics yang kuat.

---

_Generated: 2025 | Framework: Laravel 12 | Database: SQLite/MySQL_
