<?php

namespace App\Http\Controllers;

use App\Models\BobotPenilaian;
use App\Models\Cpl;
use App\Models\DosenMataKuliah;
use App\Models\Mahasiswa;
use App\Models\MahasiswaMk;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DosenDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $ta   = config('obe.tahun_akademik', '2025/2026');

        // Use dosen_mata_kuliah FK lookup instead of LIKE on pjmk text (indexed, exact match)
        $mkIds = DosenMataKuliah::mkIdsForDosen($user->id, $ta);

        // Fallback: try text-based lookup on pjmk field if dosen_mata_kuliah is empty
        if ($mkIds->isEmpty()) {
            $mkIds = MataKuliah::where('pjmk', 'like', '%' . explode(',', $user->name)[0] . '%')
                ->orWhere('pjmk', 'like', '%' . $user->name . '%')
                ->pluck('id');
        }

        $mkAmpu = MataKuliah::whereIn('id', $mkIds)
            ->with(['cpls', 'cpmks.subCpmks', 'bobotPenilaians'])
            ->orderBy('semester')
            ->get();

        if ($mkAmpu->isEmpty()) {
            $mkAmpu = MataKuliah::with(['cpls', 'cpmks.subCpmks', 'bobotPenilaians'])
                ->orderBy('semester')->take(10)->get();
        }

        $stats = [
            'total_mk_ampu'  => $mkAmpu->count(),
            'total_sks_ampu' => $mkAmpu->sum('sks'),
            'total_cpmk'     => $mkAmpu->sum(fn($mk) => $mk->cpmks->count()),
            'total_subcpmk'  => $mkAmpu->sum(fn($mk) => $mk->cpmks->sum(fn($c) => $c->subCpmks->count())),
        ];

        // Compute bobot validity in SQL instead of PHP loop
        $bobotValidity = DB::table('bobot_penilaian')
            ->whereIn('mata_kuliah_id', $mkAmpu->pluck('id'))
            ->selectRaw('mata_kuliah_id, SUM(bobot_tugas + bobot_uts + bobot_uas + bobot_partisipatif + bobot_proyek) as total')
            ->groupBy('mata_kuliah_id')
            ->pluck('total', 'mata_kuliah_id');

        // Re-key by kode for view
        $bobotValidity = $mkAmpu->mapWithKeys(fn($mk) => [$mk->kode => (int) ($bobotValidity[$mk->id] ?? 0)]);

        $allCpl = Cpl::orderBy('kode')->get();

        // Count PA mahasiswa with pending KRS
        $paMahasiswaCount = Mahasiswa::where('dosen_pa_id', $user->id)->count();
        $paKrsPending = MahasiswaMk::whereHas('mahasiswa', fn($q) => $q->where('dosen_pa_id', $user->id))
            ->where('status', 'diajukan')->count();

        return view('dashboard.dosen', compact(
            'user',
            'mkAmpu',
            'stats',
            'bobotValidity',
            'allCpl',
            'paMahasiswaCount',
            'paKrsPending'
        ));
    }

    /** GET /dosen/pa — list mahasiswa bimbingan */
    public function paIndex(): View
    {
        $user = Auth::user();
        $mahasiswas = Mahasiswa::where('dosen_pa_id', $user->id)
            ->with(['enrollments' => fn($q) => $q->where('status', 'diajukan')->with('mataKuliah')])
            ->orderBy('nama')
            ->get();

        return view('dosen.pa.index', compact('mahasiswas'));
    }

    /** GET /dosen/pa/{mahasiswaId}/krs — view student's KRS */
    public function paKrs(int $mahasiswaId): View
    {
        $user = Auth::user();
        $mahasiswa = Mahasiswa::where('id', $mahasiswaId)
            ->where('dosen_pa_id', $user->id)
            ->firstOrFail();

        $enrollments = MahasiswaMk::with('mataKuliah')
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('semester_aktif', $mahasiswa->tahun_akademik ?? '2025/2026')
            ->orderBy('status')
            ->get();

        return view('dosen.pa.krs', compact('mahasiswa', 'enrollments'));
    }

    /** POST /dosen/pa/krs/{enrollmentId}/approve */
    public function paApprove(int $enrollmentId)
    {
        $user = Auth::user();
        $enrollment = MahasiswaMk::with('mahasiswa')
            ->where('id', $enrollmentId)
            ->whereHas('mahasiswa', fn($q) => $q->where('dosen_pa_id', $user->id))
            ->firstOrFail();

        $enrollment->update(['status' => 'disetujui', 'catatan_pa' => null]);

        return back()->with('success', 'Mata kuliah disetujui.');
    }

    /** POST /dosen/pa/krs/{enrollmentId}/reject */
    public function paReject(Request $request, int $enrollmentId)
    {
        $request->validate(['catatan_pa' => 'nullable|string|max:500']);
        $user = Auth::user();
        $enrollment = MahasiswaMk::with('mahasiswa')
            ->where('id', $enrollmentId)
            ->whereHas('mahasiswa', fn($q) => $q->where('dosen_pa_id', $user->id))
            ->firstOrFail();

        $enrollment->update(['status' => 'ditolak', 'catatan_pa' => $request->catatan_pa]);

        return back()->with('success', 'Mata kuliah ditolak.');
    }

    /** POST /dosen/pa/krs/{mahasiswaId}/approve-all */
    public function paApproveAll(int $mahasiswaId)
    {
        $user = Auth::user();
        $mahasiswa = Mahasiswa::where('id', $mahasiswaId)
            ->where('dosen_pa_id', $user->id)->firstOrFail();

        MahasiswaMk::where('mahasiswa_id', $mahasiswaId)
            ->where('semester_aktif', $mahasiswa->tahun_akademik ?? '2025/2026')
            ->where('status', 'diajukan')
            ->update(['status' => 'disetujui', 'catatan_pa' => null]);

        return back()->with('success', 'Semua KRS disetujui.');
    }
}
