<?php

namespace App\Http\Controllers\Obe;

use App\Http\Controllers\Controller;
use App\Services\ObeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ObeDashboardController extends Controller
{
    public function __construct(private ObeCalculationService $svc) {}

    /** GET /obe-dashboard */
    public function dashboard(Request $request)
    {
        $ta      = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $metrics = $this->svc->getDashboardMetrics($ta);

        // Auto-recalculate if dashboard has no CPL data yet
        if ($metrics['cplStats']->isEmpty()) {
            try {
                $this->svc->runAllPipeline($ta);
                $metrics = $this->svc->getDashboardMetrics($ta);
            } catch (\Throwable $e) {
                Log::warning('OBE dashboard auto-recalc failed: ' . $e->getMessage());
            }
        }

        $taQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $taQuery->where('cpl.program_id', auth()->user()->program_id);
        }
        $taList = $taQuery->distinct()->pluck('ca.semester_aktif')->sort()->reverse()->values();

        $cplQuery = DB::table('cpl');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplQuery->where('program_id', auth()->user()->program_id);
        }
        $cpls = $cplQuery->orderBy('kode')->get(['id', 'kode', 'total_skor_maks']);

        $achQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id')
            ->where('ca.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $achQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $achievements = $achQuery
            ->select('ca.cpl_id', DB::raw('ROUND(AVG(ca.nilai_cpl),2) as avg_nilai'))
            ->groupBy('ca.cpl_id')
            ->get()->keyBy('cpl_id');

        $cplChartData = $cpls->map(fn($c) => [
            'kode'           => $c->kode,
            'skor_maksimal'  => (float) $c->total_skor_maks,
            'skor_sementara' => (float) ($achievements->get($c->id)?->avg_nilai ?? 0),
        ])->values();

        return view('obe.dashboard', compact('metrics', 'ta', 'taList', 'cplChartData'));
    }

    /**
     * GET /grafik-cpl
     * Bar chart: Skor Maksimal vs Skor Sementara per CPL.
     */
    public function grafikCpl(Request $request)
    {
        $ta     = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $taQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $taQuery->where('cpl.program_id', auth()->user()->program_id);
        }
        $taList = $taQuery->distinct()->pluck('ca.semester_aktif')->sort()->reverse()->values();

        $cplQuery = DB::table('cpl');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplQuery->where('program_id', auth()->user()->program_id);
        }
        $cpls = $cplQuery->orderBy('kode')->get(['id', 'kode', 'deskripsi', 'kategori', 'total_skor_maks']);

        $achQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id')
            ->where('ca.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $achQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $achievements = $achQuery
            ->select(
                'ca.cpl_id',
                DB::raw('ROUND(AVG(ca.nilai_cpl), 2) as avg_nilai'),
                DB::raw('ROUND(AVG(CASE WHEN ca.achieved=1 THEN 100 ELSE 0 END), 2) as pct_achieved'),
                DB::raw('COUNT(*) as total_mahasiswa'),
                DB::raw('SUM(ca.achieved) as jumlah_tercapai')
            )
            ->groupBy('ca.cpl_id')
            ->get()->keyBy('cpl_id');

        $chartData = $cpls->map(function ($cpl) use ($achievements) {
            $ach = $achievements->get($cpl->id);
            return [
                'kode'            => $cpl->kode,
                'deskripsi'       => $cpl->deskripsi,
                'kategori'        => $cpl->kategori,
                'skor_maksimal'   => (float) $cpl->total_skor_maks,
                'skor_sementara'  => $ach ? (float) $ach->avg_nilai : 0.0,
                'pct_achieved'    => $ach ? (float) $ach->pct_achieved : 0.0,
                'total_mahasiswa' => $ach?->total_mahasiswa ?? 0,
                'jumlah_tercapai' => $ach?->jumlah_tercapai ?? 0,
                'gap'             => $ach
                    ? round((float) $cpl->total_skor_maks - (float) $ach->avg_nilai, 2)
                    : (float) $cpl->total_skor_maks,
            ];
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success'   => true,
                'ta'        => $ta,
                'chartData' => $chartData->values(),
            ]);
        }

        return view('obe.grafik-cpl', compact('chartData', 'ta', 'taList', 'cpls'));
    }

    /** GET /api/obe/dashboard-data — JSON untuk chart */
    public function dashboardData(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        return response()->json($this->svc->getDashboardMetrics($ta));
    }

    /**
     * GET /api/obe/traffic-light
     * Traffic Light grid data: CPL × Angkatan dengan pct_lulus thresholds.
     */
    public function trafficLight(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $rowsQuery = DB::table('evaluasi_cohort')
            ->join('cpl', 'cpl.id', '=', 'evaluasi_cohort.cpl_id')
            ->where('evaluasi_cohort.tahun_akademik', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $rowsQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $rows = $rowsQuery->select(
                'evaluasi_cohort.angkatan',
                'evaluasi_cohort.cpl_id',
                'cpl.kode as cpl_kode',
                'evaluasi_cohort.pct_lulus',
                'evaluasi_cohort.total_mahasiswa',
                'evaluasi_cohort.jumlah_tercapai',
                'evaluasi_cohort.target_capaian',
                'evaluasi_cohort.status_target'
            )
            ->orderBy('evaluasi_cohort.angkatan')
            ->orderBy('cpl.kode')
            ->get()
            ->map(function ($r) {
                $pct = (float) $r->pct_lulus;
                $r->traffic_color = $pct >= 80 ? 'green' : ($pct >= 60 ? 'yellow' : 'red');
                return $r;
            });

        $cplListQuery = DB::table('cpl');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplListQuery->where('program_id', auth()->user()->program_id);
        }
        $cplList = $cplListQuery->orderBy('kode')->get(['id', 'kode', 'deskripsi']);

        return response()->json([
            'ta'      => $ta,
            'cplList' => $cplList,
            'grid'    => $rows->groupBy('angkatan'),
            'legend'  => [
                'green'  => 'Tercapai (≥ 80%)',
                'yellow' => 'Perlu Monitoring (60–79%)',
                'red'    => 'Belum Tercapai (< 60%)',
            ],
        ]);
    }
}
