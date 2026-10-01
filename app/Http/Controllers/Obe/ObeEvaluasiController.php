<?php

namespace App\Http\Controllers\Obe;

use App\Http\Controllers\Controller;
use App\Services\ObeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ObeEvaluasiController extends Controller
{
    public function __construct(private ObeCalculationService $svc) {}

    /** GET /cpmk-evaluasi */
    public function cpmkEvaluasi(Request $request)
    {
        $ta   = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $mkId = $request->input('mk_id');

        $mkQuery = DB::table('mata_kuliah');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $mkQuery->where('program_id', auth()->user()->program_id);
        }
        $mataKuliahs = $mkQuery->orderBy('kode')->get();
        $rows        = collect();
        $mk          = null;

        if ($mkId) {
            $mkSingleQuery = DB::table('mata_kuliah')->where('id', $mkId);
            if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
                $mkSingleQuery->where('program_id', auth()->user()->program_id);
            }
            $mk = $mkSingleQuery->first();

            $rowsQuery = DB::table('cpmk_achievement')
                ->join('cpmk', 'cpmk_achievement.cpmk_id', '=', 'cpmk.id')
                ->join('cpl', 'cpmk.cpl_id', '=', 'cpl.id')
                ->join('mahasiswas', 'cpmk_achievement.mahasiswa_id', '=', 'mahasiswas.id')
                ->where('cpmk_achievement.mata_kuliah_id', $mkId)
                ->where('cpmk_achievement.semester_aktif', $ta);

            if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
                $rowsQuery->where('cpl.program_id', auth()->user()->program_id);
            }

            $rows = $rowsQuery->select('cpmk_achievement.*', 'cpmk.kode as cpmk_kode', 'cpmk.deskripsi as cpmk_deskripsi',
                    'cpl.kode as cpl_kode', 'mahasiswas.nim', 'mahasiswas.nama')
                ->orderBy('mahasiswas.nim')->orderBy('cpmk.kode')
                ->get();
        }

        $summary = collect();
        if ($mkId) {
            $sumQuery = DB::table('cpmk_achievement')
                ->join('cpmk', 'cpmk_achievement.cpmk_id', '=', 'cpmk.id')
                ->join('cpl', 'cpmk.cpl_id', '=', 'cpl.id')
                ->where('cpmk_achievement.mata_kuliah_id', $mkId)
                ->where('cpmk_achievement.semester_aktif', $ta);

            if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
                $sumQuery->where('cpl.program_id', auth()->user()->program_id);
            }

            $summary = $sumQuery->select('cpmk.id', 'cpmk.kode', 'cpmk.deskripsi', 'cpl.kode as cpl_kode',
                    DB::raw('COUNT(*) as total'), DB::raw('SUM(achieved) as tercapai'),
                    DB::raw('ROUND(AVG(nilai_cpmk),2) as rata_nilai'),
                    DB::raw('ROUND(SUM(achieved)/COUNT(*)*100,2) as pct_achieved'))
                ->groupBy('cpmk.id', 'cpmk.kode', 'cpmk.deskripsi', 'cpl.kode')
                ->orderBy('cpmk.kode')->get();
        }

        return view('obe.cpmk-evaluasi', compact('rows', 'summary', 'mataKuliahs', 'mk', 'ta'));
    }

    /** GET /cpl-evaluasi */
    public function cplEvaluasi(Request $request)
    {
        $ta   = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $rows = $this->fetchCplRows($ta);

        if ($rows->isEmpty()) {
            try {
                $this->svc->runAllPipeline($ta);
                $rows = $this->fetchCplRows($ta);
            } catch (\Throwable $e) {
                Log::warning('CPL evaluasi auto-recalc failed: ' . $e->getMessage());
            }
        }

        $sumQuery = DB::table('cpl_achievement')
            ->join('cpl', 'cpl_achievement.cpl_id', '=', 'cpl.id')
            ->where('cpl_achievement.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $sumQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $summary = $sumQuery->select('cpl.id', 'cpl.kode', 'cpl.deskripsi', 'cpl.kategori',
                DB::raw('COUNT(*) as total'), DB::raw('SUM(cpl_achievement.achieved) as tercapai'),
                DB::raw('ROUND(AVG(cpl_achievement.nilai_cpl),2) as rata_nilai'),
                DB::raw('ROUND(SUM(cpl_achievement.achieved)/COUNT(*)*100,2) as pct_achieved'))
            ->groupBy('cpl.id', 'cpl.kode', 'cpl.deskripsi', 'cpl.kategori')
            ->orderBy('cpl.kode')->get();

        $taListQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id');

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $taListQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $taList = $taListQuery->distinct()->pluck('ca.semester_aktif')->sort()->reverse()->values();

        return view('obe.cpl-evaluasi', compact('rows', 'summary', 'ta', 'taList'));
    }

    /** GET /rekap-angkatan */
    public function rekapAngkatan(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $cohortQuery = DB::table('evaluasi_cohort')
            ->join('cpl', 'evaluasi_cohort.cpl_id', '=', 'cpl.id')
            ->where('evaluasi_cohort.tahun_akademik', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cohortQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $cohort = $cohortQuery->select('evaluasi_cohort.*', 'cpl.kode as cpl_kode', 'cpl.deskripsi as cpl_deskripsi')
            ->orderBy('angkatan')->orderBy('cpl.kode')->get();

        $trendQuery = DB::table('evaluasi_cohort')
            ->join('cpl', 'evaluasi_cohort.cpl_id', '=', 'cpl.id');

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $trendQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $trend = $trendQuery->select('evaluasi_cohort.*', 'cpl.kode as cpl_kode')
            ->orderBy('cpl.kode')->orderBy('angkatan')->get()->groupBy('cpl_kode');

        $taListQuery = DB::table('evaluasi_cohort as ec')
            ->join('cpl', 'ec.cpl_id', '=', 'cpl.id');

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $taListQuery->where('cpl.program_id', auth()->user()->program_id);
        }
        $taList = $taListQuery->distinct()->pluck('ec.tahun_akademik')->sort()->reverse()->values();

        $cohortByAngkatan = $cohort->groupBy('angkatan');

        $cplListQuery = DB::table('cpl');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplListQuery->where('program_id', auth()->user()->program_id);
        }
        $cplList = $cplListQuery->orderBy('kode')->get();

        $angkatanList     = $cohort->pluck('angkatan')->unique()->sort()->values();

        return view('obe.rekap-angkatan', compact(
            'ta', 'taList', 'trend', 'cohortByAngkatan', 'cplList', 'angkatanList'
        ));
    }

    /** GET /student-cpl */
    public function studentEvaluasi(Request $request)
    {
        $nim      = $request->input('nim');
        $angkatan = $request->input('angkatan');
        $ta       = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $mahasiswa    = null;
        $cplData      = collect();

        $angkQuery = DB::table('mahasiswas');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $angkQuery->where('program_id', auth()->user()->program_id);
        }
        $angkatanList = $angkQuery->distinct()->orderBy('angkatan')->pluck('angkatan')->filter();

        if ($nim && $angkatan) {
            $mhsQuery = DB::table('mahasiswas')->where('nim', $nim);
            if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
                $mhsQuery->where('program_id', auth()->user()->program_id);
            }
            $mahasiswa = $mhsQuery->first();

            if ($mahasiswa) {
                $cplDataQuery = DB::table('cpl_achievement')
                    ->join('cpl', 'cpl.id', '=', 'cpl_achievement.cpl_id')
                    ->join('mahasiswas', 'mahasiswas.id', '=', 'cpl_achievement.mahasiswa_id')
                    ->where('mahasiswas.nim', $nim)
                    ->where('cpl_achievement.angkatan', $angkatan);

                if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
                    $cplDataQuery->where('cpl.program_id', auth()->user()->program_id);
                }

                $cplData = $cplDataQuery->select('cpl.kode', 'cpl.deskripsi', 'cpl.kategori', 'cpl.total_skor_maks',
                        'cpl_achievement.nilai_cpl', 'cpl_achievement.achieved',
                        'cpl_achievement.threshold', 'cpl_achievement.jumlah_cpmk',
                        'cpl_achievement.jumlah_achieved', 'cpl_achievement.tahun_akademik')
                    ->orderBy('cpl.kode')->get()
                    ->map(function ($row) {
                        $nilai = (float) $row->nilai_cpl;
                        $row->status = match (true) {
                            $nilai >= 80 => 'Sangat Baik',
                            $nilai >= 60 => 'Perlu Peningkatan',
                            default      => 'Perlu Intervensi',
                        };
                        $row->status_color = match ($row->status) {
                            'Sangat Baik'       => 'green',
                            'Perlu Peningkatan' => 'yellow',
                            default             => 'red',
                        };
                        $row->rekomendasi = match (true) {
                            $nilai < (float) $row->threshold => 'Mahasiswa perlu penguatan pada CPL ini melalui tugas tambahan atau remedial.',
                            $nilai < 80                      => 'Mahasiswa cukup baik namun masih perlu peningkatan.',
                            default                          => 'Mahasiswa telah mencapai CPL dengan sangat baik.',
                        };
                        $row->progress_pct = min(100, round($nilai));
                        return $row;
                    });
            }
        }

        $chartLabels   = $cplData->pluck('kode')->values();
        $chartValues   = $cplData->pluck('nilai_cpl')->map(fn($v) => (float) $v)->values();
        $chartMax      = $cplData->pluck('total_skor_maks')->map(fn($v) => (float) $v)->values();
        $totalCpl      = $cplData->count();
        $tercapai      = $cplData->where('achieved', 1)->count();
        $rataRata      = $totalCpl ? round($cplData->avg('nilai_cpl'), 2) : 0;
        $belumTercapai = $totalCpl - $tercapai;

        return view('obe.student-evaluasi', compact(
            'mahasiswa', 'cplData', 'nim', 'angkatan', 'ta', 'angkatanList',
            'chartLabels', 'chartValues', 'chartMax',
            'totalCpl', 'tercapai', 'belumTercapai', 'rataRata'
        ));
    }

    /** GET /monitoring-kelulusan-cpl */
    public function monitorKelulusanCpl(Request $request)
    {
        $angkatan          = $request->input('angkatan');
        $ta                = $request->input('ta');

        $sksQuery = DB::table('mata_kuliah');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $sksQuery->where('program_id', auth()->user()->program_id);
        }
        $totalSksKurikulum = (int) $sksQuery->sum('sks') ?: 144;

        $cplCountQuery = DB::table('cpl');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplCountQuery->where('program_id', auth()->user()->program_id);
        }
        $totalCplSystem    = $cplCountQuery->count();

        $sksLQuery = DB::table('nilai_mahasiswa as nm')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'nm.mata_kuliah_id')
            ->where('nm.lulus', 1);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $sksLQuery->where('mk.program_id', auth()->user()->program_id);
        }

        $sksLulus = $sksLQuery->selectRaw('nm.mahasiswa_id, SUM(mk.sks) as total_sks_lulus')
            ->groupBy('nm.mahasiswa_id')->get()->keyBy('mahasiswa_id');

        $cplQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id')
            ->selectRaw('ca.mahasiswa_id, COUNT(*) as total_cpl, SUM(ca.achieved) as cpl_tercapai,
                         ROUND(AVG(ca.nilai_cpl), 2) as rata_nilai_cpl, MAX(ca.updated_at) as last_updated')
            ->groupBy('ca.mahasiswa_id');

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        if ($ta) $cplQuery->where('ca.semester_aktif', $ta);
        $cplByMhs = $cplQuery->get()->keyBy('mahasiswa_id');

        $mhsQuery = DB::table('mahasiswas')->select('id', 'nim', 'nama', 'angkatan', 'tahun_akademik', 'aktif');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $mhsQuery->where('program_id', auth()->user()->program_id);
        }
        $mhsQuery->orderBy('angkatan')->orderBy('nim');
        if ($angkatan) $mhsQuery->where('angkatan', $angkatan);

        $rows = $mhsQuery->get()->map(function ($m) use ($sksLulus, $cplByMhs, $totalSksKurikulum, $totalCplSystem) {
            $sks         = $sksLulus->get($m->id);
            $cpl         = $cplByMhs->get($m->id);
            $sksVal      = (int) ($sks->total_sks_lulus ?? 0);
            $sudahLulus  = $sksVal >= $totalSksKurikulum;
            $totalCpl    = (int) ($cpl->total_cpl ?? 0);
            $cplTercapai = (int) ($cpl->cpl_tercapai ?? 0);
            $cplSemua    = $totalCpl > 0 && $cplTercapai >= $totalCpl;
            $statusCpl   = match (true) {
                $totalCpl === 0  => 'Belum Dihitung',
                $cplSemua        => 'CPL Tercapai',
                $cplTercapai > 0 => 'Sebagian Tercapai',
                default          => 'Belum Tercapai',
            };
            return (object) [
                'id'            => $m->id, 'nim' => $m->nim, 'nama' => $m->nama,
                'angkatan'      => $m->angkatan, 'total_sks' => $sksVal,
                'sks_kurikulum' => $totalSksKurikulum, 'sudah_lulus' => $sudahLulus,
                'total_cpl'     => $totalCpl > 0 ? $totalCpl : $totalCplSystem,
                'cpl_tercapai'  => $cplTercapai,
                'avg_nilai_cpl' => (float) ($cpl->rata_nilai_cpl ?? 0),
                'status_cpl'    => $statusCpl,
                'needs_alert'   => $sudahLulus && !$cplSemua,
            ];
        });

        $lulusSks      = $rows->where('sudah_lulus', true)->count();
        $cplTercapai   = $rows->where('status_cpl', 'CPL Tercapai')->count();
        $belumTercapai = $rows->where('sudah_lulus', true)->where('status_cpl', '!=', 'CPL Tercapai')->count();
        $pctCpl        = $lulusSks > 0 ? round($cplTercapai / $lulusSks * 100, 1) : 0;
        $totalMhs      = $rows->count();
        $alertRows     = $rows->where('needs_alert', true)->count();

        $angkQuery = DB::table('mahasiswas');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $angkQuery->where('program_id', auth()->user()->program_id);
        }
        $angkatanList = $angkQuery->distinct()->orderBy('angkatan')->pluck('angkatan')->filter()->values();

        $taQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id');

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $taQuery->where('cpl.program_id', auth()->user()->program_id);
        }
        $taList = $taQuery->distinct()->pluck('ca.semester_aktif')->sort()->reverse()->values();

        return view('obe.monitoring-kelulusan-cpl', compact(
            'rows', 'totalSksKurikulum', 'totalCplSystem', 'angkatan', 'ta', 'angkatanList', 'taList',
            'totalMhs', 'lulusSks', 'cplTercapai', 'belumTercapai', 'pctCpl', 'alertRows'
        ));
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function fetchCplRows(string $ta)
    {
        $cplQuery = DB::table('cpl_achievement')
            ->join('cpl', 'cpl_achievement.cpl_id', '=', 'cpl.id')
            ->join('mahasiswas', 'cpl_achievement.mahasiswa_id', '=', 'mahasiswas.id')
            ->where('cpl_achievement.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        return $cplQuery->select('cpl_achievement.*', 'cpl.kode as cpl_kode', 'cpl.deskripsi as cpl_deskripsi',
                'cpl.kategori', 'mahasiswas.nim', 'mahasiswas.nama', 'mahasiswas.angkatan')
            ->orderBy('mahasiswas.nim')->orderBy('cpl.kode')->get();
    }
}
