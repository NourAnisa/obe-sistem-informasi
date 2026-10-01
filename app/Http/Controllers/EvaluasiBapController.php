<?php

namespace App\Http\Controllers;

use App\Models\Bap;
use App\Models\MataKuliah;
use Illuminate\Support\Facades\DB;

class EvaluasiBapController extends Controller
{
    /** GET /evaluasi-bap — list MK with comparison summary */
    public function index()
    {
        $mataKuliahs = MataKuliah::orderBy('semester')->orderBy('nama')->get();
        $mkIds = $mataKuliahs->pluck('id');

        // Batch load all BAPs with relationships in ONE query (fixes N+1)
        $baps = Bap::with('bapPertemuans.mahasiswaEvaluasis')
            ->whereIn('mata_kuliah_id', $mkIds)
            ->get()
            ->groupBy('mata_kuliah_id')
            ->map(fn($group) => $group->sortByDesc('id')->first());

        // Batch load RPS counts in ONE query (fixes N+1)
        $rpsCountMap = DB::table('rps_pertemuan')
            ->whereIn('mata_kuliah_id', $mkIds)
            ->selectRaw('mata_kuliah_id, COUNT(*) as cnt')
            ->groupBy('mata_kuliah_id')
            ->pluck('cnt', 'mata_kuliah_id');

        // Build summary from preloaded data
        $summaries = [];
        foreach ($mataKuliahs as $mk) {
            $bap = $baps->get($mk->id);
            $bapCount     = $bap ? $bap->bapPertemuans->count() : 0;
            $allEvals     = $bap ? $bap->bapPertemuans->flatMap->mahasiswaEvaluasis : collect();
            $evalCount    = $allEvals->count();
            $avgKesesuaian = $allEvals->avg('kesesuaian_materi');

            $summaries[$mk->id] = [
                'rps_count'      => $rpsCountMap->get($mk->id, 0),
                'bap_count'      => $bapCount,
                'eval_count'     => $evalCount,
                'avg_kesesuaian' => $avgKesesuaian,
                'bap'            => $bap,
            ];
        }

        return view('evaluasi-bap.index', compact('mataKuliahs', 'summaries'));
    }

    /** GET /evaluasi-bap/{kode} — detailed RPS vs BAP vs Mahasiswa */
    public function show(string $kode)
    {
        $mk = MataKuliah::where('kode', $kode)->firstOrFail();

        // Load RPS pertemuan
        $rpsPertemuans = DB::table('rps_pertemuan')
            ->where('mata_kuliah_id', $mk->id)
            ->orderBy('minggu')
            ->get();

        // Load BAP with evaluasi
        $bap = Bap::with([
            'bapPertemuans.mahasiswaEvaluasis.mahasiswa',
        ])
            ->where('mata_kuliah_id', $mk->id)
            ->latest()
            ->first();

        // Load RPS detail (for CPL/CPMK context)
        $rpsDetail = DB::table('rps_detail')->where('mata_kuliah_id', $mk->id)->first();

        // Build comparison rows
        $rows = [];
        for ($minggu = 1; $minggu <= 16; $minggu++) {
            $rps = $rpsPertemuans->firstWhere('minggu', $minggu);
            $bapPt = $bap ? $bap->bapPertemuans->firstWhere('minggu', $minggu) : null;
            $evals = $bapPt ? $bapPt->mahasiswaEvaluasis : collect();

            $avgMateri = $evals->avg('kesesuaian_materi');
            $avgMetode = $evals->avg('kesesuaian_metode');

            // Kesesuaian logic
            $kesesuaian = 'no_data';
            if ($rps && $bapPt) {
                if ($evals->count() > 0) {
                    $kesesuaian = ($avgMateri >= 3.5 && $avgMetode >= 3.5) ? 'sesuai' : 'tidak_sesuai';
                } else {
                    $kesesuaian = 'no_eval'; // BAP ada tapi belum ada evaluasi mahasiswa
                }
            } elseif ($rps && !$bapPt) {
                $kesesuaian = 'belum_bap';
            }

            $rows[] = [
                'minggu'      => $minggu,
                'rps'         => $rps,
                'bap'         => $bapPt,
                'evals'       => $evals,
                'avg_materi'  => $avgMateri,
                'avg_metode'  => $avgMetode,
                'kesesuaian'  => $kesesuaian,
                'is_uts'      => $minggu === 8,
                'is_uas'      => $minggu === 16,
            ];
        }

        // Overall stats
        $sesuaiCount    = collect($rows)->where('kesesuaian', 'sesuai')->count();
        $tidakCount     = collect($rows)->where('kesesuaian', 'tidak_sesuai')->count();
        $noEvalCount    = collect($rows)->where('kesesuaian', 'no_eval')->count();
        $belumBapCount  = collect($rows)->where('kesesuaian', 'belum_bap')->count();
        $noDataCount    = collect($rows)->where('kesesuaian', 'no_data')->count();

        $totalResponden = $bap
            ? $bap->bapPertemuans->flatMap->mahasiswaEvaluasis->pluck('mahasiswa_id')->unique()->count()
            : 0;

        return view('evaluasi-bap.show', compact(
            'mk',
            'rpsDetail',
            'bap',
            'rows',
            'sesuaiCount',
            'tidakCount',
            'noEvalCount',
            'belumBapCount',
            'noDataCount',
            'totalResponden'
        ));
    }
}
