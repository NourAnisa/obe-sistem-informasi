<?php

namespace App\Http\Controllers\Obe;

use App\Http\Controllers\Controller;
use App\Services\ObeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ObeApiController — semua /api/obe/* endpoints (JSON only).
 */
class ObeApiController extends Controller
{
    public function __construct(private ObeCalculationService $svc) {}

    /** POST /api/obe/hitung/{mkId} */
    public function hitung(Request $request, int $mkId)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $mkQuery = DB::table('mata_kuliah')->where('id', $mkId);
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $mkQuery->where('program_id', auth()->user()->program_id);
        }
        if (!$mkQuery->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Mata kuliah tidak valid atau tidak sesuai program studi Anda.'], 403);
        }
        try {
            return response()->json($this->svc->runFullPipeline($mkId, $ta));
        } catch (\Throwable $e) {
            Log::error("OBE hitung MK id={$mkId}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /** POST /api/obe/hitung-semua */
    public function hitungSemua(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        try {
            return response()->json($this->svc->runAllPipeline($ta));
        } catch (\Throwable $e) {
            Log::error('OBE hitungSemua error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /** POST /api/obe/sync-obe/{mkId} */
    public function syncObe(Request $request, int $mkId)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $mkQuery = DB::table('mata_kuliah')->where('id', $mkId);
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $mkQuery->where('program_id', auth()->user()->program_id);
        }
        if (!$mkQuery->exists()) {
            return response()->json(['status' => 'error', 'message' => 'Mata kuliah tidak valid atau tidak sesuai program studi Anda.'], 403);
        }
        try {
            return response()->json($this->svc->syncFromSubCpmk($mkId, $ta));
        } catch (\Throwable $e) {
            Log::error("OBE syncObe MK id={$mkId}: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /** GET /api/obe/dashboard-data */
    public function dashboardData(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        return response()->json($this->svc->getDashboardMetrics($ta));
    }

    /** GET /api/obe/traffic-light */
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
                'evaluasi_cohort.angkatan', 'evaluasi_cohort.cpl_id', 'cpl.kode as cpl_kode',
                'evaluasi_cohort.pct_lulus', 'evaluasi_cohort.total_mahasiswa',
                'evaluasi_cohort.jumlah_tercapai', 'evaluasi_cohort.target_capaian',
                'evaluasi_cohort.status_target'
            )
            ->orderBy('evaluasi_cohort.angkatan')->orderBy('cpl.kode')
            ->get()
            ->map(function ($r) {
                $pct              = (float) $r->pct_lulus;
                $r->traffic_color = $pct >= 80 ? 'green' : ($pct >= 60 ? 'yellow' : 'red');
                return $r;
            });

        return response()->json([
            'ta'      => $ta,
            'cplList' => (function() use ($ta) {
                $cplListQuery = DB::table('cpl');
                if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
                    $cplListQuery->where('program_id', auth()->user()->program_id);
                }
                return $cplListQuery->orderBy('kode')->get(['id', 'kode', 'deskripsi']);
            })(),
            'grid'    => $rows->groupBy('angkatan'),
            'legend'  => [
                'green'  => 'Tercapai (≥ 80%)',
                'yellow' => 'Perlu Monitoring (60–79%)',
                'red'    => 'Belum Tercapai (< 60%)',
            ],
        ]);
    }

    /** GET /api/obe/grafik-cpl */
    public function grafikCpl(Request $request)
    {
        $ta   = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
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
            ->select('ca.cpl_id',
                DB::raw('ROUND(AVG(ca.nilai_cpl), 2) as avg_nilai'),
                DB::raw('ROUND(AVG(CASE WHEN ca.achieved=1 THEN 100 ELSE 0 END), 2) as pct_achieved'),
                DB::raw('COUNT(*) as total_mahasiswa'),
                DB::raw('SUM(ca.achieved) as jumlah_tercapai'))
            ->groupBy('ca.cpl_id')->get()->keyBy('cpl_id');

        $chartData = $cpls->map(function ($cpl) use ($achievements) {
            $ach = $achievements->get($cpl->id);
            return [
                'kode'            => $cpl->kode,
                'deskripsi'       => $cpl->deskripsi,
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

        return response()->json(['success' => true, 'ta' => $ta, 'chartData' => $chartData->values()]);
    }
}
