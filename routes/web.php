<?php

use App\Http\Controllers\{
    AkademikDashboardController,
    BahanKajianController,
    BapController,
    BapEvaluasiController,
    BobotPenilaianController,
    CplController,
    CplSndiktiController,
    DekanDashboardController,
    DosenDashboardController,
    EvaluasiBapController,
    EvaluasiController,
    ImportNilaiController,
    KaprodiDashboardController,
    KemahasiswaanDashboardController,
    KurikulumController,
    LandingController,
    MahasiswaDashboardController,
    MahasiswaController,
    MataKuliahController,
    MbkmController,
    // NilaiMahasiswaController dihapus — sudah dipecah ke Nilai/NilaiInputController,
    // Nilai/NilaiKalkulasiController, Nilai/NilaiExportController, Nilai/NilaiImportController
    PemetaanController,
    ProfilLulusanController,
    PublikasiDosenController,
    RpsController,
    // StudentPortfolioController dihapus — digantikan sepenuhnya oleh PortfolioController
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;



// ── API: SKKM cascading dropdowns (auth required) ─────────────
Route::middleware('auth')->group(function () {
    Route::get('/api/skkm/activity-types', function () {
        try {
            $types = DB::table('skkm_activity_types')
                ->orderBy('id')
                ->get();
            return response()->json($types);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('api.skkm.types');

    Route::get('/api/skkm/roles/{activity_type_id}', function ($atId) {
        $roles = DB::table('skkm_point_rules')
            ->where('activity_type_id', $atId)
            ->distinct()->orderBy('role')->pluck('role');
        return response()->json($roles);
    })->name('api.skkm.roles');

    Route::get('/api/skkm/levels/{activity_type_id}/{role}', function ($atId, $role) {
        $levels = DB::table('skkm_point_rules')
            ->where('activity_type_id', $atId)
            ->where('role', $role)
            ->orderBy('points', 'desc')
            ->get(['id', 'level', 'points']);
        return response()->json($levels);
    })->name('api.skkm.levels');
});

// ── PUBLIC ROUTES (no auth required) ─────────────────────────
Route::get('/', LandingController::class)->name('home');
Route::get('/profil-lulusan', [ProfilLulusanController::class, 'index'])->name('profil-lulusan.index');
Route::get('/cpl', [CplController::class, 'index'])->name('cpl.index');
Route::get('/bahan-kajian', [BahanKajianController::class, 'index'])->name('bahan-kajian.index');
Route::get('/mata-kuliah', [MataKuliahController::class, 'index'])->name('mata-kuliah.index');
Route::get('/mata-kuliah/{kode}', [MataKuliahController::class, 'show'])->name('mata-kuliah.show')->where('kode', '.+');
Route::get('/kurikulum', [KurikulumController::class, 'index'])->name('kurikulum.index');
Route::get('/pemetaan', [PemetaanController::class, 'index'])->name('pemetaan.index');
Route::get('/mbkm', [MbkmController::class, 'index'])->name('mbkm.index');


// ── API ENDPOINTS (AJAX) ──────────────────────────────────────
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/mata-kuliah', [MataKuliahController::class, 'apiIndex'])->name('mk.index');
    Route::get('/cpl/{id}/mk', [CplController::class, 'getMataKuliah'])->name('cpl.mk');
    Route::get('/semester/{n}/mk', [KurikulumController::class, 'getMkBySemester'])->name('semester.mk');
    Route::get('/jadwal-generator', [RpsController::class, 'jadwalGenerator'])->name('jadwal-generator');
});

// ── AUTH ──────────────────────────────────────────────────────
Route::get('/login', fn() => view('auth.login'))->name('login')->middleware('guest');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect(match (\Illuminate\Support\Facades\Auth::user()->role) {
            'kaprodi'       => route('kaprodi.dashboard'),
            'dekan'         => route('dekan.dashboard'),
            'wakildekan'    => route('dekan.dashboard'),
            'dosen'         => route('dosen.dashboard'),
            'akademik'      => route('akademik.dashboard'),
            'kemahasiswaan' => route('kemahasiswaan.dashboard'),
            'mahasiswa'     => route('mahasiswa.dashboard'),
            default         => route('kaprodi.dashboard'),
        });
    }
    return back()->withErrors(['email' => 'Email atau password salah.']);
})->middleware('guest');

// ── STUDENT PORTFOLIO SYSTEM ──────────────────────────────────
// NOTE: Route group lama (StudentPortfolioController) dihapus — 2026-06-09
// Semua route portfolio kini ditangani oleh PortfolioController (lihat bawah)
// yang mendukung CPL-linked evidence dan review panel dosen/kaprodi.

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout')->middleware('web');

// ── DASHBOARD: ALL AUTHENTICATED USERS ───────────────────────
Route::middleware('auth')->group(function () {

    // Redirect /dashboard to role-specific dashboard
    Route::get('/dashboard', function () {
        return redirect(match (\Illuminate\Support\Facades\Auth::user()->role) {
            'kaprodi'       => route('kaprodi.dashboard'),
            'dekan'         => route('dekan.dashboard'),
            'wakildekan'    => route('dekan.dashboard'),
            'dosen'         => route('dosen.dashboard'),
            'akademik'      => route('akademik.dashboard'),
            'kemahasiswaan' => route('kemahasiswaan.dashboard'),
            'mahasiswa'     => route('mahasiswa.dashboard'),
            default         => route('kaprodi.dashboard'),
        });
    })->name('dashboard');

    // ── EVALUASI MAHASISWA (CPL/CPMK Achievement per NIM/Angkatan) ───
    Route::middleware('role:kaprodi,admin,dosen,akademik,kemahasiswaan,dekan,wakildekan')->group(function () {
        Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
        Route::post('/evaluasi/cari', [EvaluasiController::class, 'cari'])->name('evaluasi.cari');
    });
    // ── BOBOT PENILAIAN — dibatasi role operasional (#fix audit #3/#7) ────
    Route::middleware('role:kaprodi,admin,dosen,akademik,dekan,wakildekan')->group(function () {
        Route::get('/bobot-penilaian', [BobotPenilaianController::class, 'index'])->name('bobot-penilaian.index');
        Route::post('/bobot-penilaian/save', [BobotPenilaianController::class, 'save'])->name('bobot-penilaian.save');
        Route::get('/bobot-penilaian/{mkId}', [BobotPenilaianController::class, 'show'])->name('bobot-penilaian.show');
    });

    // ── CPL SN-DIKTI ──────────────────────────────────────────────
    Route::get('/cpl-sndikti', [CplSndiktiController::class, 'index'])->name('cpl-sndikti.index');
    Route::get('/cpl-sndikti/export', [CplSndiktiController::class, 'export'])->name('cpl-sndikti.export');

    // ── PORTFOLIO MAHASISWA (CPL-linked evidence) ──────────────────────────
    // Mahasiswa: kelola evidence
    Route::middleware('role:mahasiswa')->prefix('portfolio')->name('portfolio.')->group(function () {
        Route::get('/',                        [\App\Http\Controllers\PortfolioController::class, 'index'])->name('index');
        Route::post('/evidence',               [\App\Http\Controllers\PortfolioController::class, 'storeEvidence'])->name('evidence.store');
        Route::post('/evidence/{id}/submit',   [\App\Http\Controllers\PortfolioController::class, 'submitEvidence'])->name('evidence.submit');
        Route::delete('/evidence/{id}',        [\App\Http\Controllers\PortfolioController::class, 'destroyEvidence'])->name('evidence.destroy');
        Route::get('/evidence/{id}/download',  [\App\Http\Controllers\PortfolioController::class, 'downloadFile'])->name('evidence.download');
    });
    // Dosen / Kaprodi: review panel
    Route::middleware('role:dosen,kaprodi,admin,dekan,wakildekan')->prefix('portfolio')->name('portfolio.')->group(function () {
        Route::get('/review',                  [\App\Http\Controllers\PortfolioController::class, 'reviewIndex'])->name('review.index');
        Route::post('/evidence/{id}/approve',  [\App\Http\Controllers\PortfolioController::class, 'approve'])->name('evidence.approve');
        Route::post('/evidence/{id}/reject',   [\App\Http\Controllers\PortfolioController::class, 'reject'])->name('evidence.reject');
        Route::get('/evidence/{id}/download',  [\App\Http\Controllers\PortfolioController::class, 'downloadFile'])->name('evidence.download.dosen');
    });

    // ── NILAI MAHASISWA ───────────────────────────────────────────────────
    Route::middleware('role:kaprodi,admin,akademik,dosen,dekan,wakildekan')->prefix('nilai-mahasiswa')->name('nilai-mahasiswa.')->group(function () {
        // Input & tampilan
        Route::get('/',                       [\App\Http\Controllers\Nilai\NilaiInputController::class,     'index'])->name('index');
        Route::get('/{mk}',                   [\App\Http\Controllers\Nilai\NilaiInputController::class,     'show'])->name('show');
        // Simpan & kalkulasi
        Route::post('/{mk}/save',             [\App\Http\Controllers\Nilai\NilaiKalkulasiController::class, 'save'])->name('save');
        // Export Excel
        Route::get('/{mk}/export',            [\App\Http\Controllers\Nilai\NilaiExportController::class,    'export'])->name('export');
        // Import
        Route::get('/{mk}/export-template',   [\App\Http\Controllers\Nilai\NilaiImportController::class,    'exportTemplate'])->name('export-template');
        Route::post('/{mk}/import',           [\App\Http\Controllers\Nilai\NilaiImportController::class,    'import'])->name('import');
    });

    // ── API: Kalkulasi statistik nilai per MK (JSON) ─────────────────────
    Route::middleware('role:kaprodi,admin,akademik,dosen,dekan,wakildekan')
        ->get('/api/nilai-mahasiswa/{mkId}/kalkulasi', [\App\Http\Controllers\Nilai\NilaiKalkulasiController::class, 'kalkulasi'])
        ->name('api.nilai.kalkulasi');

    // ── LAPORAN EVALUASI ──────────────────────────────────────────
    Route::middleware('role:kaprodi,admin,akademik,dosen,dekan,wakildekan')->prefix('laporan-evaluasi')->name('laporan-evaluasi.')->group(function () {
        Route::get('/', [\App\Http\Controllers\LaporanEvaluasiController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\LaporanEvaluasiController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\LaporanEvaluasiController::class, 'store'])->name('store');
        Route::get('/{laporan}', [\App\Http\Controllers\LaporanEvaluasiController::class, 'show'])->name('show');
        Route::get('/{laporan}/edit', [\App\Http\Controllers\LaporanEvaluasiController::class, 'edit'])->name('edit');
        Route::put('/{laporan}', [\App\Http\Controllers\LaporanEvaluasiController::class, 'update'])->name('update');
        Route::delete('/{laporan}', [\App\Http\Controllers\LaporanEvaluasiController::class, 'destroy'])->name('destroy');
        Route::get('/{laporan}/export-word', [\App\Http\Controllers\LaporanEvaluasiController::class, 'exportWord'])->name('export-word');
    });

    // ── OBE CALCULATION ──────────────────────────────────────────────
    Route::middleware('role:kaprodi,admin,akademik,dosen,dekan,wakildekan')->group(function () {

        // ── Dashboard & Grafik ──────────────────────────────────────
        Route::get('/obe-dashboard', [\App\Http\Controllers\Obe\ObeDashboardController::class, 'dashboard'])->name('obe.dashboard');
        Route::get('/grafik-cpl',    [\App\Http\Controllers\Obe\ObeDashboardController::class, 'grafikCpl'])->name('obe.grafik-cpl');

        // ── Evaluasi (CPMK, CPL, Angkatan, Student, Monitoring) ────
        Route::get('/cpmk-evaluasi',           [\App\Http\Controllers\Obe\ObeEvaluasiController::class, 'cpmkEvaluasi'])->name('obe.cpmk-evaluasi');
        Route::get('/cpl-evaluasi',            [\App\Http\Controllers\Obe\ObeEvaluasiController::class, 'cplEvaluasi'])->name('obe.cpl-evaluasi');
        Route::get('/rekap-angkatan',          [\App\Http\Controllers\Obe\ObeEvaluasiController::class, 'rekapAngkatan'])->name('obe.rekap-angkatan');
        Route::get('/student-cpl',             [\App\Http\Controllers\Obe\ObeEvaluasiController::class, 'studentEvaluasi'])->name('obe.student-evaluasi');
        Route::get('/monitoring-kelulusan-cpl', [\App\Http\Controllers\Obe\ObeEvaluasiController::class, 'monitorKelulusanCpl'])->name('obe.monitoring-kelulusan-cpl');

        // ── Early Warning ───────────────────────────────────────────
        Route::get('/early-warning',               [\App\Http\Controllers\Obe\ObeEarlyWarningController::class, 'index'])->name('obe.early-warning');
        Route::get('/early-warning/export-csv',    [\App\Http\Controllers\Obe\ObeEarlyWarningController::class, 'exportCsv'])->name('obe.early-warning.csv');
        Route::post('/early-warning/send-notifications', [\App\Http\Controllers\Obe\ObeEarlyWarningController::class, 'sendNotifications'])->name('obe.early-warning.notify');

        // ── Laporan & Report PDF ────────────────────────────────────
        Route::get('/obe/export-prodi',            [\App\Http\Controllers\Obe\ObeReportController::class, 'exportProdi'])->name('obe.export-prodi');
        Route::get('/obe/laporan-evaluasi-prodi',  [\App\Http\Controllers\Obe\ObeReportController::class, 'laporanEvaluasiProdi'])->name('obe.laporan-evaluasi-prodi');
        Route::get('/obe/laporan-evaluasi-dosen',  [\App\Http\Controllers\Obe\ObeReportController::class, 'laporanEvaluasiDosen'])->name('obe.laporan-evaluasi-dosen');
        Route::get('/obe/rps-bap-consistency',     [\App\Http\Controllers\Obe\ObeReportController::class, 'rpsBapConsistency'])->name('obe.rps-bap-consistency');
        Route::get('/obe/problematic-courses',     [\App\Http\Controllers\Obe\ObeReportController::class, 'problematicCourses'])->name('obe.problematic-courses');

        // ── CQI Monitoring ──────────────────────────────────────────
        Route::get('/obe/cqi-monitoring',          [\App\Http\Controllers\Obe\ObeCqiController::class, 'index'])->name('obe.cqi-monitoring');
        Route::post('/obe/cqi-actions/{id}',       [\App\Http\Controllers\Obe\ObeCqiController::class, 'updateAction'])->name('obe.cqi-update');

        // ── API Endpoints (JSON) ────────────────────────────────────
        Route::post('/api/obe/hitung/{mkId}',    [\App\Http\Controllers\Obe\ObeApiController::class, 'hitung'])->name('api.obe.hitung')->middleware('throttle:10,1');
        Route::post('/api/obe/hitung-semua',     [\App\Http\Controllers\Obe\ObeApiController::class, 'hitungSemua'])->name('api.obe.hitung-semua')->middleware('throttle:5,1');
        Route::post('/api/obe/sync-obe/{mkId}',  [\App\Http\Controllers\Obe\ObeApiController::class, 'syncObe'])->name('api.obe.sync-obe')->middleware('throttle:10,1');
        Route::get('/api/obe/dashboard-data',    [\App\Http\Controllers\Obe\ObeApiController::class, 'dashboardData'])->name('api.obe.dashboard-data');
        Route::get('/api/obe/traffic-light',     [\App\Http\Controllers\Obe\ObeApiController::class, 'trafficLight'])->name('api.obe.traffic-light');
        Route::get('/api/obe/grafik-cpl',        [\App\Http\Controllers\Obe\ObeApiController::class, 'grafikCpl'])->name('api.obe.grafik-cpl');

        // ── Export / Import Nilai CPMK ──────────────────────────────
        Route::get('/obe/export-nilai-cpmk',  [\App\Http\Controllers\ObeExportController::class, 'exportNilaiCpmk'])->name('obe.export-nilai-cpmk');
        Route::post('/obe/import-nilai-cpmk', [\App\Http\Controllers\ObeImportController::class, 'importNilaiCpmk'])->name('obe.import-nilai-cpmk');

        // ── Import Nilai Mahasiswa (Excel → nilai_sub_cpmk) ─────────
        Route::get('/import-nilai',                  [ImportNilaiController::class, 'index'])->name('import-nilai.index');
        Route::post('/import-nilai',                 [ImportNilaiController::class, 'store'])->name('import-nilai.store');
        Route::get('/import-nilai/template/{mkId}',  [\App\Http\Controllers\Nilai\NilaiImportController::class, 'exportTemplate'])->name('import-nilai.template');
        Route::post('/sync-obe/{mkId}',              [ImportNilaiController::class, 'syncObe'])->name('sync-obe')->middleware('throttle:10,1');

        // ── Legacy URL redirects (backward compatibility) ───────────
        Route::redirect('/monitoring-cpl',          '/obe-dashboard', 301);
        Route::redirect('/evaluasi-cpl-mahasiswa',  '/student-cpl',   301);
    });

    // ── DISTRIBUSI DOSEN MATA KULIAH ─────────────────────────
    Route::middleware('role:kaprodi,admin,akademik,dosen,dekan,wakildekan')->group(function () {
        // Export must come before resource to avoid route model binding conflict
        Route::get('/distribusi-dosen/export', [\App\Http\Controllers\DistribusiDosenController::class, 'export'])
            ->name('distribusi-dosen.export');
        Route::resource('/distribusi-dosen', \App\Http\Controllers\DistribusiDosenController::class)
            ->names('distribusi-dosen');
    });

    // ── KRS Mahasiswa (Fix #9: Hanya kaprodi, admin, akademik) ─────────
    Route::middleware('role:kaprodi,admin,akademik,dekan,wakildekan')->group(function () {
        Route::post('/krs-mahasiswa/import-bulk', [\App\Http\Controllers\KrsMahasiswaController::class, 'importBulk'])
            ->name('krs-mahasiswa.import-bulk');
        Route::resource('/krs-mahasiswa', \App\Http\Controllers\KrsMahasiswaController::class)
            ->names('krs-mahasiswa');
    });

    // ── AUTO SCHEDULER & ROOM ANALYTICS ──────────────────────
    Route::middleware('role:kaprodi,admin,akademik,dekan,wakildekan')->group(function () {
        // Room Utilization Dashboard
        Route::get('/rooms/dashboard', [\App\Http\Controllers\RoomAnalyticsController::class, 'dashboard'])->name('rooms.analytics.dashboard');
        // Auto Scheduler
        Route::post('/schedule/auto-generate', [\App\Http\Controllers\ScheduleController::class, 'autoGenerate'])->name('schedule.auto-generate');
        Route::post('/schedule/auto-generate/preview', [\App\Http\Controllers\ScheduleController::class, 'previewSchedule'])->name('schedule.preview');
        Route::post('/schedule/sync-student-count', [\App\Http\Controllers\ScheduleController::class, 'syncStudentCount'])->name('schedule.sync-student-count');
        Route::post('/schedule/sync-student-count/{scheduleId}', [\App\Http\Controllers\ScheduleController::class, 'syncOne'])->name('schedule.sync-one');
        Route::get('/schedule/summary', [\App\Http\Controllers\ScheduleController::class, 'summary'])->name('schedule.summary');
        // ── Schedule Locking ─────────────────────────────────
        Route::post('/schedule/lock/{id}', [\App\Http\Controllers\ScheduleLockController::class, 'lock'])->name('schedule.lock');
        Route::post('/schedule/unlock/{id}', [\App\Http\Controllers\ScheduleLockController::class, 'unlock'])->name('schedule.unlock');
        Route::post('/schedule/lock-all', [\App\Http\Controllers\ScheduleLockController::class, 'lockAll'])->name('schedule.lock-all');
        Route::post('/schedule/unlock-all', [\App\Http\Controllers\ScheduleLockController::class, 'unlockAll'])->name('schedule.unlock-all');
        Route::get('/schedule/lock-status', [\App\Http\Controllers\ScheduleLockController::class, 'status'])->name('schedule.lock-status');
        // ── Conflict Detection ────────────────────────────────
        Route::get('/schedule/conflicts', [\App\Http\Controllers\ScheduleController::class, 'detectConflicts'])->name('schedule.conflicts');
        // ── PDF Export ────────────────────────────────────────
        Route::get('/schedule/export/semester', [\App\Http\Controllers\ScheduleController::class, 'exportSemester'])->name('schedule.export.semester');
        Route::get('/schedule/export/room/{roomId}', [\App\Http\Controllers\ScheduleController::class, 'exportRoom'])->name('schedule.export.room');
        Route::get('/schedule/export/lecturer/{dosenId}', [\App\Http\Controllers\ScheduleController::class, 'exportLecturer'])->name('schedule.export.lecturer');
        // Analytics API
        Route::get('/api/rooms/analytics', [\App\Http\Controllers\RoomAnalyticsController::class, 'utilization'])->name('api.rooms.analytics');
        Route::get('/api/rooms/analytics/correlation', [\App\Http\Controllers\RoomAnalyticsController::class, 'obeCorrelation'])->name('api.rooms.analytics.correlation');
    });
    // ── Calendar API (dosen + kaprodi can view) ───────────────
    Route::middleware('role:kaprodi,admin,akademik,dosen,dekan,wakildekan')->group(function () {
        Route::get('/api/schedules/calendar', [\App\Http\Controllers\ScheduleController::class, 'calendar'])->name('api.schedules.calendar');
    });

    // ── ROOM MANAGEMENT & COURSE SCHEDULING ──────────────────
    Route::middleware('role:kaprodi,admin,akademik,dekan,wakildekan')->group(function () {
        // Rooms CRUD
        Route::get('/rooms', [\App\Http\Controllers\RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/create', [\App\Http\Controllers\RoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [\App\Http\Controllers\RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit', [\App\Http\Controllers\RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/rooms/{room}', [\App\Http\Controllers\RoomController::class, 'destroy'])->name('rooms.destroy');
        // Block Rules
        Route::post('/rooms/{room}/block-rules', [\App\Http\Controllers\RoomController::class, 'storeBlockRule'])->name('rooms.block-rules.store');
        Route::delete('/rooms/{room}/block-rules/{rule}', [\App\Http\Controllers\RoomController::class, 'destroyBlockRule'])->name('rooms.block-rules.destroy');
        // Course Schedules CRUD
        Route::get('/course-schedules', [\App\Http\Controllers\CourseScheduleController::class, 'index'])->name('course-schedules.index');
        Route::post('/course-schedules', [\App\Http\Controllers\CourseScheduleController::class, 'store'])->name('course-schedules.store');
        Route::get('/course-schedules/{courseSchedule}/edit', [\App\Http\Controllers\CourseScheduleController::class, 'edit'])->name('course-schedules.edit');
        Route::put('/course-schedules/{courseSchedule}', [\App\Http\Controllers\CourseScheduleController::class, 'update'])->name('course-schedules.update');
        Route::delete('/course-schedules/{courseSchedule}', [\App\Http\Controllers\CourseScheduleController::class, 'destroy'])->name('course-schedules.destroy');
    });
    // API endpoints (dosen can also call available/auto-assign)
    Route::middleware('role:kaprodi,admin,akademik,dosen,dekan,wakildekan')->group(function () {
        Route::get('/api/rooms/available', [\App\Http\Controllers\RoomController::class, 'available'])->name('api.rooms.available');
        Route::get('/api/rooms/utilization', [\App\Http\Controllers\RoomController::class, 'utilization'])->name('api.rooms.utilization');
        Route::post('/api/rooms/auto-assign', [\App\Http\Controllers\CourseScheduleController::class, 'autoAssign'])->name('api.rooms.auto-assign');
    });

    // ── KAPRODI ──────────────────────────────────────────────
    Route::middleware('role:kaprodi,admin')->prefix('kaprodi')->name('kaprodi.')->group(function () {
        Route::get('/dashboard', [KaprodiDashboardController::class, 'index'])->name('dashboard');
        Route::get('/settings', [\App\Http\Controllers\KaprodiDashboardController::class, 'settingsIndex'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\KaprodiDashboardController::class, 'settingsUpdate'])->name('settings.update');
    });

    // ── DEKAN / WAKIL DEKAN ───────────────────────────────────
    Route::middleware('role:dekan,wakildekan,admin')->prefix('dekan')->name('dekan.')->group(function () {
        Route::get('/dashboard', [DekanDashboardController::class, 'index'])->name('dashboard');
    });

    // ── SYSTEM SETTINGS (admin, dekan, wakildekan) ───────────
    Route::middleware('role:admin,dekan,wakildekan')->group(function () {
        Route::get('/admin/settings', [\App\Http\Controllers\SystemSettingController::class, 'index'])->name('admin.settings.index');
        Route::put('/admin/settings', [\App\Http\Controllers\SystemSettingController::class, 'update'])->name('admin.settings.update');
    });

    // ── USER MANAGEMENT (admin only) ─────────────────────────
    Route::middleware('role:admin,kaprodi,dekan,wakildekan')->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class);

        Route::post('/admin/switch-program', function (\Illuminate\Http\Request $request) {
            $request->validate(['program_id' => 'required|exists:programs,id']);
            $user = Auth::user();
            if (in_array($user->role, ['admin', 'dekan', 'wakildekan'])) {
                $user->update(['program_id' => $request->program_id]);
            }
            return back()->with('success', 'Program studi aktif berhasil dipindah.');
        })->name('admin.switch-program');

        // ── CRUD KURIKULUM ────────────────────────────────────
        Route::prefix('admin')->name('admin.')->group(function () {
            // ── Manajemen Fakultas & Program Studi ───────────
            Route::get('faculties', [\App\Http\Controllers\FacultyController::class, 'index'])->name('faculties.index');
            Route::post('faculties', [\App\Http\Controllers\FacultyController::class, 'store'])->name('faculties.store');
            Route::put('faculties/{faculty}', [\App\Http\Controllers\FacultyController::class, 'update'])->name('faculties.update');
            Route::delete('faculties/{faculty}', [\App\Http\Controllers\FacultyController::class, 'destroy'])->name('faculties.destroy');

            Route::get('programs', [\App\Http\Controllers\FacultyController::class, 'programs'])->name('programs.index');
            Route::post('programs', [\App\Http\Controllers\FacultyController::class, 'storeProgram'])->name('programs.store');
            Route::put('programs/{program}', [\App\Http\Controllers\FacultyController::class, 'updateProgram'])->name('programs.update');
            Route::delete('programs/{program}', [\App\Http\Controllers\FacultyController::class, 'destroyProgram'])->name('programs.destroy');
            Route::get('faculties/{faculty}/programs', [\App\Http\Controllers\FacultyController::class, 'programsByFaculty'])->name('faculties.programs');

            // CPL
            Route::get('cpl', [\App\Http\Controllers\CplController::class, 'adminIndex'])->name('cpl.index');
            Route::get('cpl/create', [\App\Http\Controllers\CplController::class, 'create'])->name('cpl.create');
            Route::post('cpl', [\App\Http\Controllers\CplController::class, 'store'])->name('cpl.store');
            Route::get('cpl/{cpl}/edit', [\App\Http\Controllers\CplController::class, 'edit'])->name('cpl.edit');
            Route::put('cpl/{cpl}', [\App\Http\Controllers\CplController::class, 'update'])->name('cpl.update');
            Route::delete('cpl/{cpl}', [\App\Http\Controllers\CplController::class, 'destroy'])->name('cpl.destroy');

            // CPMK
            Route::resource('cpmk', \App\Http\Controllers\CpmkController::class)->names('cpmk');

            // Sub-CPMK
            Route::resource('sub-cpmk', \App\Http\Controllers\SubCpmkController::class)
                ->parameters(['sub-cpmk' => 'subCpmk'])
                ->names('sub-cpmk');

            // Bahan Kajian
            Route::get('bahan-kajian', [\App\Http\Controllers\BahanKajianController::class, 'adminIndex'])->name('bahan-kajian.index');
            Route::get('bahan-kajian/create', [\App\Http\Controllers\BahanKajianController::class, 'create'])->name('bahan-kajian.create');
            Route::post('bahan-kajian', [\App\Http\Controllers\BahanKajianController::class, 'store'])->name('bahan-kajian.store');
            Route::get('bahan-kajian/{bahanKajian}/edit', [\App\Http\Controllers\BahanKajianController::class, 'edit'])->name('bahan-kajian.edit');
            Route::put('bahan-kajian/{bahanKajian}', [\App\Http\Controllers\BahanKajianController::class, 'update'])->name('bahan-kajian.update');
            Route::delete('bahan-kajian/{bahanKajian}', [\App\Http\Controllers\BahanKajianController::class, 'destroy'])->name('bahan-kajian.destroy');

            // Mata Kuliah
            Route::get('mk', [\App\Http\Controllers\MataKuliahController::class, 'adminIndex'])->name('mk.index');
            Route::get('mk/create', [\App\Http\Controllers\MataKuliahController::class, 'create'])->name('mk.create');
            Route::post('mk', [\App\Http\Controllers\MataKuliahController::class, 'store'])->name('mk.store');
            Route::get('mk/{mataKuliah}/edit', [\App\Http\Controllers\MataKuliahController::class, 'edit'])->name('mk.edit');
            Route::put('mk/{mataKuliah}', [\App\Http\Controllers\MataKuliahController::class, 'update'])->name('mk.update');
            Route::delete('mk/{mataKuliah}', [\App\Http\Controllers\MataKuliahController::class, 'destroy'])->name('mk.destroy');
        });
    });

    // ── DOSEN ─────────────────────────────────────────────────
    Route::middleware('role:dosen,admin,kaprodi,dekan,wakildekan')->prefix('dosen')->name('dosen.')->group(function () {
        Route::get('/dashboard', [DosenDashboardController::class, 'index'])->name('dashboard');
        // PA (Pembimbing Akademik) — approve KRS mahasiswa bimbingan
        Route::get('/pa', [DosenDashboardController::class, 'paIndex'])->name('pa.index');
        Route::get('/pa/{mahasiswaId}/krs', [DosenDashboardController::class, 'paKrs'])->name('pa.krs');
        Route::post('/pa/krs/{enrollmentId}/approve', [DosenDashboardController::class, 'paApprove'])->name('pa.approve');
        Route::post('/pa/krs/{enrollmentId}/reject', [DosenDashboardController::class, 'paReject'])->name('pa.reject');
        Route::post('/pa/krs/{mahasiswaId}/approve-all', [DosenDashboardController::class, 'paApproveAll'])->name('pa.approve-all');
    });

    // ── AKADEMIK ──────────────────────────────────────────────
    Route::middleware('role:akademik,admin,dekan,wakildekan')->prefix('akademik')->name('akademik.')->group(function () {
        Route::get('/dashboard', [AkademikDashboardController::class, 'index'])->name('dashboard');
    });

    // ── KEMAHASISWAAN ─────────────────────────────────────────
    Route::middleware('role:kemahasiswaan,admin,dekan,wakildekan')->prefix('kemahasiswaan')->name('kemahasiswaan.')->group(function () {
        Route::get('/dashboard', [KemahasiswaanDashboardController::class, 'index'])->name('dashboard');
        // SKKM Approval
        Route::get('/skkm', [KemahasiswaanDashboardController::class, 'skkmIndex'])->name('skkm.index');
        Route::post('/skkm/{id}/approve', [KemahasiswaanDashboardController::class, 'skkmApprove'])->name('skkm.approve');
        Route::post('/skkm/{id}/reject', [KemahasiswaanDashboardController::class, 'skkmReject'])->name('skkm.reject');
        Route::post('/skkm/{id}/reset', [KemahasiswaanDashboardController::class, 'skkmReset'])->name('skkm.reset');
    });

    // ── RPS Generator ─────────────────────────────────────────
    Route::middleware('role:dosen,admin,kaprodi,dekan,wakildekan')->group(function () {
        Route::get('/rps', [RpsController::class, 'index'])->name('rps.index');
        Route::get('/rps/{kode}/pdf', [RpsController::class, 'pdf'])->name('rps.pdf')->where('kode', '.+');
        Route::get('/rps/{kode}/docx', [RpsController::class, 'docx'])->name('rps.docx')->where('kode', '.+');
        Route::get('/rps/{kode}/edit', [RpsController::class, 'edit'])->name('rps.edit')->where('kode', '.+');
        Route::post('/rps/{kode}/edit', [RpsController::class, 'update'])->name('rps.update')->where('kode', '.+');
        Route::get('/rps/{kode}/kontrak', [RpsController::class, 'kontrak'])->name('rps.kontrak')->where('kode', '.+');
        Route::get('/rps/{kode}', [RpsController::class, 'show'])->name('rps.show')->where('kode', '.+');
    });

    // ── BAP (Berita Acara Perkuliahan) ─────────────────────────
    Route::middleware('role:dosen,admin,kaprodi,akademik,dekan,wakildekan')->group(function () {
        Route::get('/bap/enrollment-info', [BapController::class, 'enrollmentInfo'])->name('bap.enrollment-info');
        Route::get('/bap', [BapController::class, 'index'])->name('bap.index');
        Route::get('/bap/{kode}/print', [BapController::class, 'printView'])->name('bap.print')->where('kode', '.+');
        Route::get('/bap/{kode}/word', [BapController::class, 'exportWord'])->name('bap.word')->where('kode', '.+');
        Route::put('/bap/{kode}', [BapController::class, 'update'])->name('bap.update')->where('kode', '.+');
        Route::get('/bap/{kode}', [BapController::class, 'show'])->name('bap.show')->where('kode', '.+');
    });

    // ── BAP Token Evaluation (dosen generates token + summary) ──
    Route::middleware('role:dosen,admin,kaprodi,akademik,dekan,wakildekan')->group(function () {
        Route::post('/bap/token/generate', [\App\Http\Controllers\BapPenilaianController::class, 'generateToken'])->name('bap.token.generate');
        Route::get('/bap/token/status',   [\App\Http\Controllers\BapPenilaianController::class, 'tokenStatus'])->name('bap.token.status');
        Route::get('/bap/{kode}/penilaian-summary', [\App\Http\Controllers\BapPenilaianController::class, 'summary'])->name('bap.penilaian.summary')->where('kode', '.+');
    });

    // ── BAP Comparison (dosen/kaprodi view) ───────────────────
    Route::middleware('role:dosen,admin,kaprodi,akademik,dekan,wakildekan')->group(function () {
        Route::get('/bap/{kode}/evaluasi-compare', [BapEvaluasiController::class, 'compare'])->name('bap.evaluasi.compare')->where('kode', '.+');
    });

    // ── Evaluasi Berita Acara (RPS vs BAP vs Mahasiswa) ───────
    Route::middleware('role:kaprodi,admin,dosen,akademik,dekan,wakildekan')->group(function () {
        Route::get('/evaluasi-bap', [EvaluasiBapController::class, 'index'])->name('evaluasi-bap.index');
        Route::get('/evaluasi-bap/{kode}', [EvaluasiBapController::class, 'show'])->name('evaluasi-bap.show')->where('kode', '.+');
    });

    // ── PUBLIKASI DOSEN ───────────────────────────────────────
    Route::middleware('role:kaprodi,admin,dosen,akademik,dekan,wakildekan')->group(function () {
        // Profil dosen + daftar publikasi
        Route::get('/dosen/{dosenId}/publikasi', [PublikasiDosenController::class, 'index'])->name('dosen.publikasi.index');
        Route::patch('/dosen/{dosenId}/publikasi/scholar-id', [PublikasiDosenController::class, 'updateScholarId'])->name('dosen.publikasi.update-scholar-id');
        Route::post('/dosen/{dosenId}/sync-publikasi', [PublikasiDosenController::class, 'syncScholar'])->name('dosen.publikasi.sync');
        Route::delete('/dosen/publikasi/{id}', [PublikasiDosenController::class, 'destroy'])->name('dosen.publikasi.destroy');
        Route::post('/dosen/publikasi/{id}/update', [PublikasiDosenController::class, 'update'])->name('dosen.publikasi.update');
    });
    Route::middleware('role:kaprodi,admin,akademik,dekan,wakildekan')->group(function () {
        // Laporan publikasi
        Route::get('/laporan/publikasi', [PublikasiDosenController::class, 'laporanPublikasi'])->name('laporan.publikasi');
        Route::get('/laporan/publikasi/export-excel', [PublikasiDosenController::class, 'exportExcel'])->name('laporan.publikasi.export-excel');
        Route::get('/laporan/publikasi/export-pdf', [PublikasiDosenController::class, 'exportPdf'])->name('laporan.publikasi.export-pdf');
    });
    Route::middleware('role:kaprodi,admin,dosen,dekan,wakildekan')->group(function () {
        // RPS referensi publikasi
        Route::post('/rps/{kode}/publikasi', [PublikasiDosenController::class, 'saveRpsPublikasi'])->name('rps.publikasi.save')->where('kode', '.+');
    });

    // ── Mahasiswa Management (admin/kaprodi) ───────────────────
    Route::middleware('role:admin,kaprodi,dekan,wakildekan')->group(function () {
        Route::get('/mahasiswa', [MahasiswaController::class, 'index'])->name('mahasiswa.index');
        Route::post('/mahasiswa', [MahasiswaController::class, 'store'])->name('mahasiswa.store');
        Route::delete('/mahasiswa/enrollment/{id}', [MahasiswaController::class, 'unenroll'])->name('mahasiswa.unenroll');
        Route::post('/mahasiswa/enrollment/{id}/toggle-pjmk', [MahasiswaController::class, 'togglePjmk'])->name('mahasiswa.toggle-pjmk');
        Route::post('/mahasiswa/{id}/enroll', [MahasiswaController::class, 'enroll'])->name('mahasiswa.enroll');
        Route::post('/mahasiswa/{id}/assign-pa', [MahasiswaController::class, 'assignPa'])->name('mahasiswa.assign-pa');
        Route::patch('/mahasiswa/{id}/ipk-ips', [MahasiswaController::class, 'updateIpkIps'])->name('mahasiswa.ipk-ips');
        Route::delete('/mahasiswa/{id}', [MahasiswaController::class, 'destroy'])->name('mahasiswa.destroy');
    });

    // ── Mahasiswa Dashboard ────────────────────────────────────
    Route::middleware('role:mahasiswa')->group(function () {
        Route::get('/mahasiswa-dashboard', [MahasiswaDashboardController::class, 'index'])->name('mahasiswa.dashboard');
        Route::get('/mahasiswa-dashboard/cpl', [MahasiswaDashboardController::class, 'cplIndex'])->name('mahasiswa.cpl');
        Route::get('/mahasiswa-dashboard/cpl/export', [MahasiswaDashboardController::class, 'cplExport'])->name('mahasiswa.cpl.export');
        Route::get('/mahasiswa-dashboard/cpl/import-template', [MahasiswaDashboardController::class, 'cplImportTemplate'])->name('mahasiswa.cpl.import.template');
        Route::get('/mahasiswa-dashboard/skkm', [MahasiswaDashboardController::class, 'skkmIndex'])->name('mahasiswa.skkm');
        Route::post('/mahasiswa-dashboard/skkm', [MahasiswaDashboardController::class, 'skkmStore'])->name('mahasiswa.skkm.store');
        Route::delete('/mahasiswa-dashboard/skkm/{id}', [MahasiswaDashboardController::class, 'skkmDestroy'])->name('mahasiswa.skkm.destroy');

        // KRS (Pengambilan Mata Kuliah)
        Route::get('/mahasiswa-dashboard/krs', [MahasiswaDashboardController::class, 'krsIndex'])->name('mahasiswa.krs');
        Route::post('/mahasiswa-dashboard/krs/enroll', [MahasiswaDashboardController::class, 'krsEnroll'])->name('mahasiswa.krs.enroll');
        Route::delete('/mahasiswa-dashboard/krs/{id}', [MahasiswaDashboardController::class, 'krsUnenroll'])->name('mahasiswa.krs.unenroll');
        Route::post('/mahasiswa-dashboard/krs/semester', [MahasiswaDashboardController::class, 'krsUpdateSemester'])->name('mahasiswa.krs.semester');
        Route::get('/mahasiswa-dashboard/nilai', [MahasiswaDashboardController::class, 'nilaiIndex'])->name('mahasiswa.nilai');
        Route::post('/mahasiswa-dashboard/krs/ajukan', [MahasiswaDashboardController::class, 'krsAjukan'])->name('mahasiswa.krs.ajukan');

        // Evaluasi BAP
        Route::get('/bap-evaluasi', [BapEvaluasiController::class, 'index'])->name('bap-evaluasi.index');
        Route::get('/bap-evaluasi/{kode}', [BapEvaluasiController::class, 'show'])->name('bap-evaluasi.show')->where('kode', '.+');
        Route::put('/bap-evaluasi/{kode}', [BapEvaluasiController::class, 'update'])->name('bap-evaluasi.update')->where('kode', '.+');

        // Penilaian Dosen (token-based evaluation by mahasiswa)
        Route::get('/bap-penilaian',  [\App\Http\Controllers\BapPenilaianController::class, 'index'])->name('bap-penilaian.index');
        Route::post('/bap-penilaian', [\App\Http\Controllers\BapPenilaianController::class, 'store'])->name('bap-penilaian.store');
    });
});

