<?php
namespace App\Http\Controllers;

use App\Models\BobotPenilaian;
use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\MataKuliah;
use App\Models\SubCpmk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KaprodiDashboardController extends Controller
{
    public function index(): View
    {
        $progId = auth()->check() ? auth()->user()->program_id : null;

        // Single query for MK stats
        $mkStatsQuery = DB::table('mata_kuliah');
        if ($progId) {
            $mkStatsQuery->where('program_id', $progId);
        }
        $mkStats = $mkStatsQuery->selectRaw("
                COUNT(*) as total_mk,
                COALESCE(SUM(sks), 0) as total_sks,
                COUNT(DISTINCT pjmk) as total_dosen,
                SUM(CASE WHEN is_mbkm = 1 THEN 1 ELSE 0 END) as mk_mbkm
            ")
            ->first();

        $cplQuery = Cpl::query();
        if ($progId) {
            $cplQuery->where('program_id', $progId);
        }

        $cpmkQuery = Cpmk::query();
        if ($progId) {
            $cpmkQuery->whereHas('cpl', fn($q) => $q->where('program_id', $progId));
        }

        $subCpmkQuery = SubCpmk::query();
        if ($progId) {
            $subCpmkQuery->whereHas('cpmk.cpl', fn($q) => $q->where('program_id', $progId));
        }

        $stats = [
            'total_mk'      => $mkStats->total_mk ?? 0,
            'total_sks'     => $mkStats->total_sks ?? 0,
            'total_cpl'     => $cplQuery->count(),
            'total_cpmk'    => $cpmkQuery->count(),
            'total_subcpmk' => $subCpmkQuery->count(),
            'total_dosen'   => $mkStats->total_dosen ?? 0,
            'mk_mbkm'       => $mkStats->mk_mbkm ?? 0,
        ];

        // OBE health checks
        $mkTanpaCplQuery = MataKuliah::doesntHave('cpls');
        $mkTanpaCpmkQuery = MataKuliah::doesntHave('cpmks');
        if ($progId) {
            $mkTanpaCplQuery->where('program_id', $progId);
            $mkTanpaCpmkQuery->where('program_id', $progId);
        }

        $mkTanpaCpl  = $mkTanpaCplQuery->count();
        $mkTanpaCpmk = $mkTanpaCpmkQuery->count();

        $bobotQuery = BobotPenilaian::query();
        if ($progId) {
            $bobotQuery->whereHas('mataKuliah', fn($q) => $q->where('program_id', $progId));
        }

        $bobotInvalid = $bobotQuery->selectRaw('mata_kuliah_id,
            SUM(bobot_tugas + bobot_uts + bobot_uas + bobot_partisipatif + bobot_proyek) as total')
            ->groupBy('mata_kuliah_id')
            ->havingRaw('total != 100 AND total != 0')
            ->count();

        $obeHealth = [
            'mk_tanpa_cpl'  => $mkTanpaCpl,
            'mk_tanpa_cpmk' => $mkTanpaCpmk,
            'bobot_invalid' => $bobotInvalid,
        ];

        $sksBySemester = MataKuliah::when($progId, fn($q) => $q->where('program_id', $progId))
            ->selectRaw('semester, SUM(sks) as total_sks, COUNT(*) as total_mk')
            ->groupBy('semester')->orderBy('semester')
            ->get();

        $cplCoverage = Cpl::when($progId, fn($q) => $q->where('program_id', $progId))
            ->withCount(['mataKuliahs' => function($q) use ($progId) {
                if ($progId) $q->where('program_id', $progId);
            }])
            ->orderBy('kode')->get();

        $dosenWorkload = MataKuliah::when($progId, fn($q) => $q->where('program_id', $progId))
            ->whereNotNull('pjmk')
            ->where('pjmk', '!=', '')
            ->selectRaw('pjmk, COUNT(*) as total_mk, SUM(sks) as total_sks')
            ->groupBy('pjmk')->orderByDesc('total_mk')->get();

        $mkByKategori = MataKuliah::when($progId, fn($q) => $q->where('program_id', $progId))
            ->selectRaw('kategori, COUNT(*) as total, SUM(sks) as total_sks')
            ->groupBy('kategori')->get();

        $cplTidakTercover = Cpl::when($progId, fn($q) => $q->where('program_id', $progId))
            ->doesntHave('mataKuliahs')->count();

        return view('dashboard.kaprodi', compact(
            'stats', 'obeHealth', 'sksBySemester', 'cplCoverage',
            'dosenWorkload', 'mkByKategori', 'cplTidakTercover'
        ));
    }

    public function settingsIndex()
    {
        $user = Auth::user();
        $program = $user->program;

        if (!$program) {
            return redirect()->route('kaprodi.dashboard')
                ->with('error', 'Anda belum terdaftar di Program Studi manapun.');
        }

        return view('kaprodi.settings.index', compact('program'));
    }

    public function settingsUpdate(\Illuminate\Http\Request $request)
    {
        $user = Auth::user();
        $program = $user->program;

        if (!$program) {
            return redirect()->route('kaprodi.dashboard')
                ->with('error', 'Anda belum terdaftar di Program Studi manapun.');
        }

        $request->validate([
            'logo_prodi_path' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'nama'            => 'required|string|max:150|unique:programs,nama,' . $program->id,
            'jenjang'         => 'required|string|max:10',
            'kaprodi'         => 'required|string|max:150',
            'nik_kaprodi'     => 'nullable|string|max:30',
            'akreditasi'      => 'nullable|string|max:50',
            'sks_total'       => 'required|integer|min:1',
            'total_semester'  => 'required|integer|min:1',
            'visi'            => 'nullable|string|max:1000',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo_prodi_path')) {
            if ($program->logo_prodi_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($program->logo_prodi_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($program->logo_prodi_path);
            }
            $path = $request->file('logo_prodi_path')->store('logos', 'public');
            $program->logo_prodi_path = $path;
        }

        // Handle logo deletion checkbox
        if ($request->boolean('delete_logo_prodi_path')) {
            if ($program->logo_prodi_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($program->logo_prodi_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($program->logo_prodi_path);
            }
            $program->logo_prodi_path = null;
        }

        // Update all fields
        $program->update([
            'nama'           => $request->input('nama'),
            'jenjang'        => $request->input('jenjang'),
            'kaprodi'        => $request->input('kaprodi'),
            'nik_kaprodi'    => $request->input('nik_kaprodi'),
            'akreditasi'     => $request->input('akreditasi'),
            'sks_total'      => $request->input('sks_total'),
            'total_semester' => $request->input('total_semester'),
            'visi'           => $request->input('visi'),
        ]);

        // Clear settings cache to force reload
        \Illuminate\Support\Facades\Cache::forget('settings.all');

        return back()->with('success', 'Konfigurasi program studi berhasil disimpan.');
    }
}

