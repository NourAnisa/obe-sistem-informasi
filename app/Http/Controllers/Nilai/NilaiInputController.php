<?php

namespace App\Http\Controllers\Nilai;

use App\Http\Controllers\Controller;
use App\Models\DosenMataKuliah;
use App\Models\MataKuliah;
use App\Models\NilaiSubCpmk;
use App\Models\SubCpmk;
use App\Models\SubCpmkBobot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * NilaiInputController
 *
 * Tanggung jawab:
 *   - index()  → daftar MK + progress input nilai
 *   - show()   → form input nilai (SubCPMK atau komponen)
 */
class NilaiInputController extends Controller
{
    private function getTaAktif(): string
    {
        return config('obe.tahun_akademik', '2025/2026');
    }

    /**
     * GET /nilai-mahasiswa
     * Daftar MK aktif beserta progress input nilai.
     */
    public function index(Request $request)
    {
        $ta   = $request->input('ta', $this->getTaAktif());
        $role = Auth::user()->role ?? '';

        $mkIds = DB::table('mahasiswa_mk')
            ->where('semester_aktif', $ta)
            ->where('status', 'disetujui')
            ->distinct()
            ->pluck('mata_kuliah_id');

        if ($role === 'dosen') {
            $assignedIds = DosenMataKuliah::mkIdsForDosen(Auth::id(), $ta);
            $mkIds       = $mkIds->intersect($assignedIds);
        }

        // Apply program scoping on the course IDs retrieved via raw SQL
        $scopedMkIds = MataKuliah::pluck('id');
        $mkIds = $mkIds->intersect($scopedMkIds);

        // Load progress counts in two queries instead of N×2
        $totalMap = DB::table('mahasiswa_mk')
            ->where('semester_aktif', $ta)->where('status', 'disetujui')
            ->whereIn('mata_kuliah_id', $mkIds)
            ->selectRaw('mata_kuliah_id, COUNT(*) as cnt')
            ->groupBy('mata_kuliah_id')
            ->pluck('cnt', 'mata_kuliah_id');

        $inputMap = DB::table('nilai_mahasiswa')
            ->where('semester_aktif', $ta)->whereIn('mata_kuliah_id', $mkIds)
            ->selectRaw('mata_kuliah_id, COUNT(*) as cnt')
            ->groupBy('mata_kuliah_id')
            ->pluck('cnt', 'mata_kuliah_id');

        $mataKuliahs = MataKuliah::whereIn('id', $mkIds)
            ->orderBy('semester')->orderBy('kode')->get()
            ->each(function ($mk) use ($totalMap, $inputMap) {
                $total             = $totalMap[$mk->id] ?? 0;
                $sudahInput        = $inputMap[$mk->id] ?? 0;
                $mk->total_mahasiswa = $total;
                $mk->sudah_input     = $sudahInput;
                $mk->persen_input    = $total > 0 ? round($sudahInput / $total * 100) : 0;
            });

        $taAktif = $this->getTaAktif();
        return view('nilai-mahasiswa.index', compact('mataKuliahs', 'ta', 'taAktif'));
    }

    /**
     * GET /nilai-mahasiswa/{mk}
     * Unified OBE grading page. Eager-loads SubCPMK + bobot in one query.
     */
    public function show(Request $request, int $mkId)
    {
        $ta = $request->input('ta', $this->getTaAktif());
        $mk = MataKuliah::findOrFail($mkId);

        // Dosen hanya boleh lihat MK yang ia ampu
        $role = Auth::user()->role ?? '';
        if ($role === 'dosen') {
            $assigned = DosenMataKuliah::mkIdsForDosen(Auth::id(), $ta);
            if (!$assigned->contains((int) $mkId)) {
                abort(403, 'Anda tidak mengampu mata kuliah ini.');
            }
        }

        // ── CPMK list for this MK ─────────────────────────────────────────
        $cpmkList = DB::table('cpmk')
            ->join('mata_kuliah_cpmk', 'cpmk.id', '=', 'mata_kuliah_cpmk.cpmk_id')
            ->where('mata_kuliah_cpmk.mata_kuliah_id', $mkId)
            ->select('cpmk.id', 'cpmk.kode', 'cpmk.deskripsi')
            ->orderBy('cpmk.kode')->get();

        $cpmkIds = $cpmkList->pluck('id');

        // ── SubCPMK eager-loaded with bobot (fixes N+1) ───────────────────
        $subCpmks = SubCpmk::with('bobot')
            ->whereIn('cpmk_id', $cpmkIds)
            ->orderBy('cpmk_id')->orderBy('id')->get();

        $hasSubCpmk = $subCpmks->count() > 0;

        // Build cpmksWithSubs (for view template compatibility)
        $cpmksWithSubs = $cpmkList->map(function ($c) use ($subCpmks) {
            $c->sub_cpmks = $subCpmks->where('cpmk_id', $c->id)->values();
            return $c;
        });

        // ── subBobotMap from eager relationship (no extra query) ──────────
        $subBobotMap = $subCpmks->keyBy('id')->map(fn($sc) => $sc->bobot)->filter();

        // ── Reference bobot for display ───────────────────────────────────
        $bobotRef = $subBobotMap->first()
            ?? DB::table('bobot_penilaian')->where('mata_kuliah_id', $mkId)->first();
        $bobot = [
            'tugas'        => (int) ($bobotRef->bobot_tugas        ?? 20),
            'uts'          => (int) ($bobotRef->bobot_uts          ?? 20),
            'uas'          => (int) ($bobotRef->bobot_uas          ?? 20),
            'partisipatif' => (int) ($bobotRef->bobot_partisipatif ?? 20),
            'proyek'       => (int) ($bobotRef->bobot_proyek       ?? 20),
        ];

        // ── Enrollments — hanya mahasiswa yang KRS-nya disetujui ─────────
        $enrollments = DB::table('mahasiswa_mk')
            ->where('mata_kuliah_id', $mkId)->where('semester_aktif', $ta)
            ->where('mahasiswa_mk.status', 'disetujui')
            ->join('mahasiswas', 'mahasiswa_mk.mahasiswa_id', '=', 'mahasiswas.id')
            ->select('mahasiswas.id as mahasiswa_id', 'mahasiswas.nim', 'mahasiswas.nama',
                     'mahasiswa_mk.is_pjmk', 'mahasiswa_mk.status as status_krs')
            ->orderBy('mahasiswas.nim')->get();

        // ── nilai_sub_cpmk: single query, grouped ─────────────────────────
        $nilaiSubMap = NilaiSubCpmk::whereIn('sub_cpmk_id', $subCpmks->pluck('id'))
            ->whereIn('mahasiswa_id', $enrollments->pluck('mahasiswa_id'))
            ->get()
            ->groupBy('mahasiswa_id')
            ->map(fn($rows) => $rows->keyBy('sub_cpmk_id'));

        // ── Summary nilai_mahasiswa ───────────────────────────────────────
        $nilaiMap = DB::table('nilai_mahasiswa')
            ->where('mata_kuliah_id', $mkId)->where('semester_aktif', $ta)
            ->get()->keyBy('mahasiswa_id');

        $taAktif = $this->getTaAktif();

        return view('nilai-mahasiswa.show', compact(
            'mk', 'ta', 'taAktif', 'cpmksWithSubs', 'subCpmks',
            'bobot', 'enrollments', 'nilaiSubMap', 'nilaiMap',
            'hasSubCpmk', 'subBobotMap'
        ));
    }
}
