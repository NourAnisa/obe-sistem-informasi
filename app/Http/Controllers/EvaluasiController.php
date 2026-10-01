<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\CplAchievement;
use App\Models\CpmkAchievement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EvaluasiController extends Controller
{
    public function index(): View
    {
        $mahasiswas = Mahasiswa::orderBy('nim')
            ->select('id', 'nim', 'nama', 'angkatan', 'semester')
            ->get();

        return view('evaluasi.index', compact('mahasiswas'));
    }

    public function cari(Request $request): View
    {
        $request->validate([
            'nim'      => 'nullable|string|max:20',
            'angkatan' => 'nullable|integer|min:2018|max:2030',
        ]);

        $nim      = $request->nim;
        $angkatan = $request->angkatan;
        $hasil    = null;

        $query = Mahasiswa::query();
        if ($nim) {
            $query->where('nim', 'like', "%{$nim}%");
        }
        if ($angkatan) {
            $query->where('angkatan', $angkatan);
        }

        $mahasiswas = $query->orderBy('nim')->get();
        $mhsIds = $mahasiswas->pluck('id');

        // Batch load all CPL & CPMK achievements in 2 queries (fixes N+1)
        $allCpl = CplAchievement::whereIn('cpl_achievement.mahasiswa_id', $mhsIds)
            ->join('cpl', 'cpl.id', '=', 'cpl_achievement.cpl_id')
            ->select('cpl_achievement.mahasiswa_id', 'cpl.kode', 'cpl_achievement.nilai', 'cpl_achievement.lulus')
            ->get()
            ->groupBy('mahasiswa_id');

        $allCpmk = CpmkAchievement::whereIn('cpmk_achievement.mahasiswa_id', $mhsIds)
            ->join('cpmk', 'cpmk.id', '=', 'cpmk_achievement.cpmk_id')
            ->select('cpmk_achievement.mahasiswa_id', 'cpmk.kode', 'cpmk_achievement.nilai', 'cpmk_achievement.lulus')
            ->get()
            ->groupBy('mahasiswa_id');

        $hasil = [];
        foreach ($mahasiswas as $mhs) {
            $cplAchievements  = $allCpl->get($mhs->id, collect());
            $cpmkAchievements = $allCpmk->get($mhs->id, collect());

            $hasil[] = [
                'mahasiswa' => $mhs,
                'cpl'       => $cplAchievements,
                'cpmk'      => $cpmkAchievements,
                'avg_cpl'   => $cplAchievements->avg('nilai'),
                'lulus_cpl' => $cplAchievements->isEmpty() ? null : $cplAchievements->every(fn($c) => $c->lulus),
            ];
        }

        return view('evaluasi.index', compact('nim', 'angkatan', 'hasil', 'mahasiswas'));
    }
}
