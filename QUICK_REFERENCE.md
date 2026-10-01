# 🚀 QUICK REFERENCE GUIDE - OBE_SISTEM_INFORMASI

**Panduan cepat untuk developers & system administrators**

---

## 📋 QUICK START

### Installation

```bash
# Clone & Setup
composer install
npm install
npm run build

# Database
php artisan migrate
php artisan db:seed  # Optional

# Serve
php artisan serve
# Visit http://localhost:8000
```

### Default Admin Access

```
Role: admin
Access: Filament Admin Panel at /admin
(Configure in .env as needed)
```

---

## 🎯 MAIN FEATURES AT A GLANCE

| Feature                | Controller                | Key Model                 | URL Route    |
| ---------------------- | ------------------------- | ------------------------- | ------------ |
| **RPS Management**     | RpsController             | RpsPertemuan, RpsDetail   | /rps         |
| **BAP Documentation**  | BapController             | Bap, BapPertemuan         | /bap         |
| **CPL Tracking**       | CplController             | Cpl, CplAchievement       | /cpl         |
| **CPMK Management**    | CpmkController            | Cpmk, CpmkAchievement     | /cpmk        |
| **Grade Entry**        | NilaiMahasiswaController  | NilaiMahasiswa            | /nilai       |
| **Evaluation**         | EvaluasiController        | EvaluasiCpmk, EvaluasiCpl | /evaluasi    |
| **Dashboards**         | \*DashboardController     | Various                   | /dashboard   |
| **Student Enrollment** | KrsMahasiswaController    | KrsMahasiswa              | /krs         |
| **Course Management**  | MataKuliahController      | MataKuliah                | /mata-kuliah |
| **Faculty Assignment** | DistribusiDosenController | DosenMataKuliah           | /distribusi  |

---

## 🔑 KEY MODELS & RELATIONSHIPS

### Core Learning Chain

```
ProfilLulusan
    ↓ (1:many)
Cpl (Capaian Pembelajaran Lulusan)
    ↓ (many:many via mata_kuliah_cpl)
MataKuliah
    ↓ (1:many)
Cpmk (Capaian Pembelajaran Mata Kuliah)
    ↓ (1:many)
SubCpmk (Sub-breakdown)
    ↓
Bap/RpsDetail (Actual Implementation)
    ↓
NilaiMahasiswa / NilaiSubCpmk (Student Grades)
```

### Key Model Methods to Know

```php
// Get CPL achievement for a cohort
$achievement = EvaluasiCpl::where('cohort_id', $id)->first();

// Get CPMK distribution (% achieving)
$stats = EvaluasiCpmk::where('cpmk_id', $id)
    ->with('distribusi_nilai')
    ->get();

// Auto-generate RPS schedule
$service = new RpsGeneratorService();
$dates = $service->generateSchedule($startDate, $dayOfWeek, $year);

// Student CPMK achievement
$student_cpmk = NilaiSubCpmk::where('mahasiswa_id', $id)->get();
```

---

## 🗂️ DATABASE SCHEMA QUICK LOOKUP

### Users & Roles

```sql
users (id, name, email, password, role, jabatan)
-- Roles: admin, kaprodi, dosen, akademik, kemahasiswaan, mahasiswa
```

### Learning Design

```sql
mata_kuliahs (id, kode, nama, semester, sks, ...)
cpls (id, nama, deskripsi, profil_lulusan_id, ...)
cpmks (id, nama, mata_kuliah_id, ...)
sub_cpmks (id, nama, cpmk_id, bobot)

-- Linking tables
mata_kuliah_cpl (mata_kuliah_id, cpl_id)
mata_kuliah_cpmk (mata_kuliah_id, cpmk_id)
mata_kuliah_sub_cpmk (mata_kuliah_id, sub_cpmk_id)
```

### Teaching Implementation

```sql
rps_pertemuan (id, mata_kuliah_id, minggu, tanggal, ...)
rps_detail (id, rps_pertemuan_id, materi, metode, ...)
bap (id, mata_kuliah_id, semester, tahun, ...)
bap_pertemuan (id, bap_id, minggu, metode, ...)
```

### Grading

```sql
nilai_mahasiswas (id, mahasiswa_id, mata_kuliah_id, nilai_akhir, ...)
nilai_sub_cpmks (id, mahasiswa_id, sub_cpmk_id, nilai, ...)
bobot_penilaian (id, mata_kuliah_id, komponen, bobot, ...)
```

### Evaluation

```sql
evaluasi_cpmks (id, cpmk_id, minggu, capaian, hambatan, ...)
evaluasi_cpls (id, cpl_id, cohort_id, target, capaian, ...)
laporan_evaluasi (id, semester, analisis, rekomendasi, ...)
```

### Student & Enrollment

```sql
mahasiswas (id, nim, nama, dosen_pa_id, ...)
mahasiswa_mk (id, mahasiswa_id, mata_kuliah_id, status, ...)
krs_mahasiswas (id, mahasiswa_id, semester, status, ...)
```

---

## 🛠️ COMMON DEVELOPER TASKS

### Add New Course

```php
// In Controller
$mk = MataKuliah::create([
    'kode' => 'SI101',
    'nama' => 'Pemrograman Dasar',
    'semester' => 1,
    'sks' => 3,
]);

// Link CPL
$mk->cpls()->attach($cpl_ids);

// Link CPMK
$mk->cpmks()->attach($cpmk_ids);
```

### Record Student Grade

```php
$nilai = NilaiMahasiswa::create([
    'mahasiswa_id' => $mahasiswa_id,
    'mata_kuliah_id' => $mk_id,
    'uts' => 75,
    'uas' => 80,
    'tugas' => 85,
    'nilai_akhir' => 80,  // auto-calculated
]);

// Also record sub-CPMK scores
NilaiSubCpmk::create([
    'mahasiswa_id' => $mahasiswa_id,
    'sub_cpmk_id' => $sub_cpmk_id,
    'nilai' => 85,
]);
```

### Generate RPS Schedule

```php
$service = new RpsGeneratorService();
$hasil = $service->generateSchedule(
    startDate: '2025-02-10',
    dayOfWeek: 1,  // Monday
    tahun: 2025
);
// Returns array of 16 weeks with dates
```

### Create Evaluation Report

```php
$evaluasi = LaporanEvaluasi::create([
    'semester' => '2024/2025 Ganjil',
    'analisis' => 'CPMK achievement: 85%...',
    'rekomendasi' => 'Improve PBL method...',
]);

// Export to PDF
return PDF::generate($evaluasi);
```

---

## 📊 COMMON QUERIES

### Get All Students in a Course

```php
$students = MahasiswaMk::where('mata_kuliah_id', $mk_id)
    ->with('mahasiswa')
    ->get();
```

### Get CPMK Achievement Stats

```php
$stats = EvaluasiCpmk::where('cpmk_id', $cpmk_id)
    ->with('distribusi_nilai')
    ->first();

// Access: $stats->capaian, $stats->hambatan
```

### Get Student's CPMK Grades

```php
$grades = NilaiSubCpmk::where('mahasiswa_id', $mhs_id)
    ->with('subCpmk.cpmk.mataKuliah')
    ->get();
```

### Get CPL Target Achievement

```php
$cpl = EvaluasiCpl::where('cpl_id', $cpl_id)
    ->where('cohort_id', $cohort_id)
    ->first();

// Check: ($cpl->capaian >= $cpl->target) ? 'Achieved' : 'Gap';
```

### Get Dosen's Assigned Courses

```php
$courses = DosenMataKuliah::where('dosen_id', $dosen_id)
    ->with('mataKuliah')
    ->get();
```

---

## 🔐 ROLE-BASED FEATURES

### Admin

- ✅ View all features
- ✅ User management
- ✅ System configuration

### Kaprodi

- ✅ Oversee curriculum
- ✅ Review & approve BAP
- ✅ View evaluations
- ✅ Generate reports
- ❌ Edit individual grades

### Dosen

- ✅ Create/edit RPS
- ✅ Submit BAP per meeting
- ✅ Input grades (nilai)
- ✅ View own courses
- ❌ Modify other dosen's courses

### Akademik

- ✅ Register students
- ✅ Process KRS
- ✅ Import grades
- ✅ Manage enrollments
- ❌ Input grades directly

### Kemahasiswaan

- ✅ Student development tracking
- ✅ View MBKM
- ✅ Student services
- ❌ Academic data modification

### Mahasiswa (Student)

- ✅ View own grades
- ✅ View CPMK achievement
- ✅ Submit KRS
- ❌ Modify grades

---

## 🐛 COMMON DEBUGGING

### Check Migrations Status

```bash
php artisan migrate:status
php artisan migrate --step  # Run specific migration
php artisan migrate:rollback --step=1  # Undo
```

### Clear Caches

```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Check DB Connections

```bash
php artisan tinker
# Then: DB::connection()->getPdo()
```

### View Logs

```bash
# Real-time
php artisan pail

# Or check file
tail -f storage/logs/laravel.log
```

### Test Database

```bash
php artisan tinker
>>> User::count()
>>> MataKuliah::with('cpls')->first()
>>> Cpl::all()
```

---

## 📦 ADDING NEW FEATURES

### Adding a New Management Module (e.g., Student Portfolio)

1. **Create Migration**

```bash
php artisan make:migration create_student_portfolios_table
```

2. **Create Model**

```bash
php artisan make:model StudentPortfolio
```

3. **Create Controller**

```bash
php artisan make:controller StudentPortfolioController --resource
```

4. **Add Routes** (in routes/web.php)

```php
Route::resource('portfolio', StudentPortfolioController);
```

5. **Create Views** (in resources/views/portfolio/)

```
index.blade.php
show.blade.php
create.blade.php
edit.blade.php
```

6. **Link Relationships** (in StudentPortfolio model)

```php
public function mahasiswa() {
    return $this->belongsTo(Mahasiswa::class);
}
```

---

## 🔄 DEPLOYMENT CHECKLIST

- [ ] Update .env for production
- [ ] Set APP_DEBUG=false
- [ ] Set APP_ENV=production
- [ ] Run php artisan config:cache
- [ ] Run migrations: php artisan migrate --force
- [ ] Set proper file permissions (storage/, bootstrap/)
- [ ] Configure web server (nginx/Apache)
- [ ] Set up backups
- [ ] Configure email/SMTP
- [ ] Test all major features
- [ ] Set up monitoring & logs

---

## 📞 SUPPORT RESOURCES

### Built-in Tools

- **Filament Docs**: https://filamentphp.com
- **Laravel Docs**: https://laravel.com/docs/12.x
- **Tailwind Docs**: https://tailwindcss.com/docs
- **PHPUnit Docs**: https://phpunit.readthedocs.io

### Local Help

```bash
# List all routes
php artisan route:list

# List all models
php artisan tinker
# Then: get_class_methods(User::class)

# Model relationships
php artisan tinker
# Then: User::first()->load('roles')
```

---

## 📝 FILE LOCATIONS FOR KEY FILES

| What             | Where                   |
| ---------------- | ----------------------- |
| Main Config      | `config/obe.php`        |
| Database Queries | `app/Models/`           |
| Request Handlers | `app/Http/Controllers/` |
| Views/Templates  | `resources/views/`      |
| CSS/Tailwind     | `resources/css/app.css` |
| Routes           | `routes/web.php`        |
| Database Schema  | `database/migrations/`  |
| Tests            | `tests/`                |
| Logs             | `storage/logs/`         |
| Uploaded Files   | `storage/app/`          |

---

## 🎓 EDUCATIONAL TERMS GLOSSARY

| Term        | Indonesian                       | Definition                              |
| ----------- | -------------------------------- | --------------------------------------- |
| **CPL**     | Capaian Pembelajaran Lulusan     | Program-level learning outcomes         |
| **CPMK**    | Capaian Pembelajaran Mata Kuliah | Course-level learning outcomes          |
| **RPS**     | Rencana Pembelajaran Semester    | Semester learning plan/syllabus         |
| **BAP**     | Bukti Autentik Pelaksanaan       | Authentic proof of implementation       |
| **OBE**     | Outcome-Based Education          | Education approach focusing on outcomes |
| **SNDIKTI** | Standar Nasional Dikti           | National higher ed standards            |
| **KRS**     | Kartu Rencana Studi              | Study plan registration                 |
| **PA**      | Pembimbing Akademik              | Academic advisor                        |
| **CPMK**    | Capaian Pembelajaran Mata Kuliah | Learning objectives per course          |
| **MBKM**    | Magang, Belajar, Kuliah, Magang  | Practical work/internship program       |
| **CQI**     | Continuous Quality Improvement   | Ongoing quality enhancement             |

---

_Last Updated: 2025_  
_For detailed docs, see DOKUMENTASI_TEKNOLOGI_FITUR.md & ARSITEKTUR_SISTEM.md_
