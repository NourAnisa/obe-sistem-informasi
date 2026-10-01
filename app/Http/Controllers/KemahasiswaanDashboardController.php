<?php

namespace App\Http\Controllers;

use App\Models\Cpl;
use App\Models\MataKuliah;
use App\Models\MbkmBkp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KemahasiswaanDashboardController extends Controller
{
    public function index(): View
    {
        $mbkmActivities = MbkmBkp::all();
        $mbkmMk = MataKuliah::where('is_mbkm', true)->with('cpls')->orderBy('semester')->get();

        $stats = [
            'total_mbkm_mk'     => $mbkmMk->count(),
            'total_mbkm_sks'    => $mbkmMk->sum('sks'),
            'total_bentuk_mbkm' => $mbkmActivities->count(),
            'max_sks_mbkm'      => $mbkmActivities->max('sks_mbkm_maks') ?? 0,
        ];

        $mbkmByBentuk = $mbkmActivities->groupBy('bentuk_kegiatan');
        $cplList = Cpl::withCount('mataKuliahs')->orderBy('kode')->get();

        // SKKM pending count for badge
        $skkmPending = 0;
        try {
            $skkmPending = DB::table('skkm_mahasiswas')->where('status', 'pending')->count();
        } catch (\Exception $e) {}

        return view('dashboard.kemahasiswaan', compact(
            'mbkmActivities', 'mbkmMk', 'stats', 'mbkmByBentuk', 'cplList', 'skkmPending'
        ));
    }

    /** GET /kemahasiswaan/skkm — list all SKKM submissions */
    public function skkmIndex(Request $request): View
    {
        $status = $request->query('status', 'pending');
        $search = $request->query('search', '');

        $query = DB::table('skkm_mahasiswas as sm')
            ->join('mahasiswas as mhs', 'sm.mahasiswa_id', '=', 'mhs.id')
            ->join('users as u', 'mhs.user_id', '=', 'u.id')
            ->select(
                'sm.*',
                'mhs.nim', 'mhs.nama as nama_mhs', 'mhs.program_studi',
                'u.email'
            )
            ->orderBy('sm.created_at', 'desc');

        if ($status !== 'all') {
            $query->where('sm.status', $status);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('mhs.nim', 'like', "%$search%")
                  ->orWhere('mhs.nama', 'like', "%$search%")
                  ->orWhere('sm.nama_kegiatan', 'like', "%$search%");
            });
        }

        $skkmList = $query->paginate(20)->withQueryString();

        // Counts per status
        $counts = DB::table('skkm_mahasiswas')
            ->selectRaw("status, COUNT(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        $skkmPending = $counts['pending'] ?? 0;

        return view('kemahasiswaan.skkm', compact('skkmList', 'status', 'search', 'counts', 'skkmPending'));
    }

    /** POST /kemahasiswaan/skkm/{id}/approve */
    public function skkmApprove(int $id)
    {
        $skkm = DB::table('skkm_mahasiswas')->where('id', $id)->first();
        if (!$skkm) {
            return back()->with('error', 'Data SKKM tidak ditemukan.');
        }

        DB::table('skkm_mahasiswas')->where('id', $id)->update([
            'status'     => 'disetujui',
            'updated_at' => now(),
        ]);

        return back()->with('success', 'SKKM telah disetujui.');
    }

    /** POST /kemahasiswaan/skkm/{id}/reject */
    public function skkmReject(Request $request, int $id)
    {
        $request->validate(['catatan_penolakan' => 'nullable|string|max:500']);

        $skkm = DB::table('skkm_mahasiswas')->where('id', $id)->first();
        if (!$skkm) {
            return back()->with('error', 'Data SKKM tidak ditemukan.');
        }

        $update = ['status' => 'ditolak', 'updated_at' => now()];
        // Store rejection note in keterangan if column exists, otherwise ignore
        if ($request->catatan_penolakan) {
            try {
                $update['keterangan'] = $request->catatan_penolakan;
            } catch (\Exception $e) {}
        }

        DB::table('skkm_mahasiswas')->where('id', $id)->update($update);

        return back()->with('success', 'SKKM telah ditolak.');
    }

    /** POST /kemahasiswaan/skkm/{id}/reset — reset to pending */
    public function skkmReset(int $id)
    {
        DB::table('skkm_mahasiswas')->where('id', $id)->update([
            'status'     => 'pending',
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Status SKKM dikembalikan ke pending.');
    }
}
