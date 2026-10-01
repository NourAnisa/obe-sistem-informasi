<?php

namespace App\Http\Controllers\Obe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ObeReportController — semua laporan PDF/export dari OBE.
 */
class ObeReportController extends Controller
{
    /** GET /obe/export-prodi?ta= — Quick PDF export (ringkas) */
    public function exportProdi(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $cplStats = $this->fetchCplStats($ta);
        $cpmkStats = $this->fetchCpmkStats($ta);

        $cohortQuery = DB::table('evaluasi_cohort as ec')
            ->join('cpl', 'cpl.id', '=', 'ec.cpl_id')
            ->where('ec.tahun_akademik', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cohortQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $cohort = $cohortQuery->select('ec.*', 'cpl.kode as cpl_kode')
            ->orderBy('ec.angkatan')->orderBy('cpl.kode')->get();

        $cohortByAngkatan = $cohort->groupBy('angkatan');

        $cplListQuery = DB::table('cpl');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplListQuery->where('program_id', auth()->user()->program_id);
        }
        $cplList = $cplListQuery->orderBy('kode')->get();
        $totalCpl         = $cplStats->count();
        $jumlahTercapai   = $cplStats->where('status', 'Tercapai')->count();
        $generatedAt      = now()->format('d F Y, H:i');

        $jumlahBelum = $totalCpl - $jumlahTercapai;
        $rataCapaian = $totalCpl ? round($cplStats->avg('rata_nilai'), 2) : 0;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('obe.export-prodi', compact(
            'ta', 'cplStats', 'cpmkStats', 'cohortByAngkatan', 'cplList',
            'totalCpl', 'jumlahTercapai', 'jumlahBelum', 'rataCapaian', 'generatedAt'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('laporan-evaluasi-prodi-' . str_replace('/', '-', $ta) . '.pdf');
    }

    /** GET /obe/laporan-evaluasi-prodi?ta= — Full multi-chapter PDF */
    public function laporanEvaluasiProdi(Request $request)
    {
        $ta          = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $cplListQuery = DB::table('cpl');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplListQuery->where('program_id', auth()->user()->program_id);
        }
        $cplList = $cplListQuery->orderBy('kode')->get();

        $profilListQuery = DB::table('profil_lulusan');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $profilListQuery->where('program_id', auth()->user()->program_id);
        }
        $profilList = $profilListQuery->orderBy('kode')->get();

        $mataKuliahsQuery = DB::table('mata_kuliah');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $mataKuliahsQuery->where('program_id', auth()->user()->program_id);
        }
        $mataKuliahs = $mataKuliahsQuery->orderBy('semester')->orderBy('kode')->get();

        $distQuery = DB::table('dosen_mata_kuliah as dmk')
            ->join('users', 'users.id', '=', 'dmk.dosen_id')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'dmk.mata_kuliah_id')
            ->where('dmk.tahun_akademik', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $distQuery->where('mk.program_id', auth()->user()->program_id);
        }

        $distribusiDosen = $distQuery->select('users.name as nama_dosen', 'users.nik', 'mk.kode as mk_kode', 'mk.nama as mk_nama',
                'mk.sks', 'dmk.kelas', 'dmk.semester', 'dmk.peran', 'dmk.jumlah_sks', 'dmk.status')
            ->orderBy('dmk.semester')->orderBy('mk.kode')->get();

        $bapDataQuery = DB::table('bap as b')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'b.mata_kuliah_id')
            ->leftJoin('bap_pertemuan as bp', 'bp.bap_id', '=', 'b.id')
            ->where('b.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $bapDataQuery->where('mk.program_id', auth()->user()->program_id);
        }

        $bapData = $bapDataQuery->select('mk.kode as mk_kode', 'mk.nama as mk_nama', 'b.kelas', 'b.jumlah_mahasiswa',
                DB::raw('COUNT(bp.id) as total_pertemuan'),
                DB::raw('ROUND(AVG(bp.jumlah_hadir), 1) as rata_hadir'),
                DB::raw('SUM(bp.jumlah_hadir) as total_hadir'),
                DB::raw('SUM(COALESCE(bp.jumlah_tk,0)) as total_tk'))
            ->groupBy('mk.kode', 'mk.nama', 'b.kelas', 'b.jumlah_mahasiswa')
            ->orderBy('mk.kode')->get()
            ->map(function ($r) {
                $r->pct_hadir = $r->jumlah_mahasiswa && $r->total_pertemuan
                    ? round(($r->rata_hadir / max($r->jumlah_mahasiswa, 1)) * 100, 1)
                    : 0;
                return $r;
            });

        $cplStats  = $this->fetchCplStats($ta);
        $cpmkStats = $this->fetchCpmkStats($ta);

        $ewQuery = DB::table('cpl_achievement as ca')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'ca.mahasiswa_id')
            ->join('cpl', 'cpl.id', '=', 'ca.cpl_id')
            ->where('ca.semester_aktif', $ta)->where('ca.achieved', 0);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $ewQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $earlyWarning = $ewQuery->select('mahasiswas.nim', 'mahasiswas.nama', 'mahasiswas.angkatan', 'cpl.kode as cpl_kode',
                'ca.nilai_cpl', 'ca.threshold', DB::raw('ROUND(ca.threshold - ca.nilai_cpl, 2) as gap'))
            ->orderBy('ca.nilai_cpl')->orderBy('mahasiswas.nim')->limit(50)->get()
            ->map(fn($r) => tap($r, fn($r) => $r->status = (float)$r->nilai_cpl < (float)$r->threshold * 0.5
                ? 'Kritis' : 'Perlu Intervensi'));

        $masukanQuery = DB::table('bap_evaluasi_mahasiswa as bem')
            ->join('bap_pertemuan as bp', 'bp.id', '=', 'bem.bap_pertemuan_id')
            ->join('bap as b', 'b.id', '=', 'bp.bap_id')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'b.mata_kuliah_id')
            ->where('bp.minggu', 16)->where('b.semester_aktif', $ta)
            ->where(fn($q) => $q->whereNotNull('bem.kritik')->orWhereNotNull('bem.saran'));

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $masukanQuery->where('mk.program_id', auth()->user()->program_id);
        }

        $masukanMahasiswa = $masukanQuery->select('mk.kode as mk_kode', 'mk.nama as mk_nama', 'bem.kritik', 'bem.saran')
            ->orderBy('mk.kode')->get()
            ->filter(fn($r) => !empty($r->kritik) || !empty($r->saran))
            ->groupBy('mk_kode');

        $kaprodiQuery = DB::table('users')->where('role', 'kaprodi');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $kaprodiQuery->where('program_id', auth()->user()->program_id);
        }
        $kaprodi = $kaprodiQuery->first();

        $dosenQuery = DB::table('users')->where('role', 'dosen');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $dosenQuery->where('program_id', auth()->user()->program_id);
        }
        $timDosen = $dosenQuery->orderBy('name')->limit(10)->get();
        $summary  = [
            'total_cpl'         => $cplStats->count(),
            'cpl_tercapai'      => $cplStats->where('status', 'Tercapai')->count(),
            'cpl_monitoring'    => $cplStats->where('status', 'Perlu Monitoring')->count(),
            'cpl_belum'         => $cplStats->where('status', 'Belum Tercapai')->count(),
            'rata_capaian'      => $cplStats->count() ? round($cplStats->avg('rata_nilai'), 2) : 0,
            'total_mhs_warning' => $earlyWarning->pluck('nim')->unique()->count(),
            'total_mk'          => $mataKuliahs->count(),
            'total_dosen'       => $distribusiDosen->pluck('nama_dosen')->unique()->count(),
        ];
        $generatedAt = now()->format('d F Y, H:i');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('obe.laporan-evaluasi-prodi', compact(
            'ta', 'cplList', 'profilList', 'mataKuliahs', 'distribusiDosen', 'bapData',
            'cplStats', 'cpmkStats', 'earlyWarning', 'masukanMahasiswa',
            'kaprodi', 'timDosen', 'summary', 'generatedAt'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-evaluasi-prodi-' . str_replace('/', '-', $ta) . '.pdf');
    }

    /** GET /obe/laporan-evaluasi-dosen?ta= */
    public function laporanEvaluasiDosen(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $kategoriFn = function (float $avg): array {
            $pct = round(($avg - 1) / 2 * 100, 1);
            return ['pct' => $pct, 'kategori' => match (true) {
                $pct >= 84 => 'Sangat Kompeten',
                $pct >= 66 => 'Kompeten',
                $pct >= 48 => 'Cukup',
                default    => 'Kurang',
            }];
        };

        $joinDmk = function ($j) {
            $j->on('dmk.mata_kuliah_id', '=', 'mk.id')
              ->on('dmk.tahun_akademik', '=', 'b.semester_aktif')
              ->where('dmk.peran', 'pengampu');
        };

        $rekapQuery = DB::table('bap_evaluasi_mahasiswa as bem')
            ->join('bap_pertemuan as bp', 'bp.id', '=', 'bem.bap_pertemuan_id')
            ->join('bap as b', 'b.id', '=', 'bp.bap_id')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'b.mata_kuliah_id')
            ->join('dosen_mata_kuliah as dmk', $joinDmk)
            ->join('users', 'users.id', '=', 'dmk.dosen_id')
            ->where('b.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $rekapQuery->where('mk.program_id', auth()->user()->program_id);
        }

        $rekapRaw = $rekapQuery->select(
                'users.id as dosen_id', 'users.name as dosen_nama',
                DB::raw('COALESCE(users.nik, "") as dosen_nik'),
                'mk.id as mk_id', 'mk.kode as mk_kode', 'mk.nama as mk_nama', 'mk.sks',
                DB::raw('COUNT(bem.id) as total_responden'),
                DB::raw('ROUND(AVG(bem.pedagogik), 2) as avg_pedagogik'),
                DB::raw('ROUND(AVG(bem.profesional), 2) as avg_profesional'),
                DB::raw('ROUND(AVG(bem.kepribadian), 2) as avg_kepribadian'),
                DB::raw('ROUND(AVG(bem.sosial), 2) as avg_sosial'),
                DB::raw('ROUND(AVG((bem.pedagogik+bem.profesional+bem.kepribadian+bem.sosial)/4), 2) as avg_total')
            )
            ->groupBy('users.id', 'users.name', 'users.nik', 'mk.id', 'mk.kode', 'mk.nama', 'mk.sks')
            ->orderBy('users.name')->orderBy('mk.kode')->get();

        $rekap = $rekapRaw->map(function ($r) use ($kategoriFn) {
            $r->detail_pedagogik   = $kategoriFn((float)$r->avg_pedagogik);
            $r->detail_profesional = $kategoriFn((float)$r->avg_profesional);
            $r->detail_kepribadian = $kategoriFn((float)$r->avg_kepribadian);
            $r->detail_sosial      = $kategoriFn((float)$r->avg_sosial);
            $r->detail_total       = $kategoriFn((float)$r->avg_total);
            return $r;
        });

        $rekapByDosen = $rekap->groupBy('dosen_id');
        $totalCount   = $rekap->count();
        $overallAvg   = [
            'pedagogik'   => $totalCount ? round($rekap->avg('avg_pedagogik'),   2) : 0,
            'profesional' => $totalCount ? round($rekap->avg('avg_profesional'), 2) : 0,
            'kepribadian' => $totalCount ? round($rekap->avg('avg_kepribadian'), 2) : 0,
            'sosial'      => $totalCount ? round($rekap->avg('avg_sosial'),      2) : 0,
        ];
        $overallAvg['total'] = round(array_sum($overallAvg) / 4, 2);
        $overallKat          = $kategoriFn($overallAvg['total']);

        $feedbackQuery = DB::table('bap_evaluasi_mahasiswa as bem')
            ->join('bap_pertemuan as bp', 'bp.id', '=', 'bem.bap_pertemuan_id')
            ->join('bap as b', 'b.id', '=', 'bp.bap_id')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'b.mata_kuliah_id')
            ->join('dosen_mata_kuliah as dmk', $joinDmk)
            ->join('users', 'users.id', '=', 'dmk.dosen_id')
            ->where('b.semester_aktif', $ta)->where('bp.minggu', 16)
            ->where(fn($q) => $q->whereNotNull('bem.kritik')->orWhereNotNull('bem.saran'));

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $feedbackQuery->where('mk.program_id', auth()->user()->program_id);
        }

        $feedbackRaw = $feedbackQuery->select('users.id as dosen_id', 'users.name as dosen_nama',
                'mk.kode as mk_kode', 'mk.nama as mk_nama', 'bem.kritik', 'bem.saran')
            ->orderBy('users.name')->orderBy('mk.kode')->get()
            ->filter(fn($r) => !empty($r->kritik) || !empty($r->saran));

        $feedbackByDosen = $feedbackRaw->groupBy('dosen_id')
            ->map(fn($rows) => $rows->groupBy('mk_kode'));

        $generatedAt = now()->format('d F Y, H:i');
        
        $kaprodiQuery = DB::table('users')->where('role', 'kaprodi');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $kaprodiQuery->where('program_id', auth()->user()->program_id);
        }
        $kaprodi = $kaprodiQuery->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('obe.laporan-evaluasi-dosen', compact(
            'ta', 'rekap', 'rekapByDosen', 'overallAvg', 'overallKat',
            'feedbackByDosen', 'generatedAt', 'kaprodi'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('laporan-evaluasi-dosen-' . str_replace('/', '-', $ta) . '.pdf');
    }

    /** GET /obe/rps-bap-consistency */
    public function rpsBapConsistency(Request $request)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $mkListQuery = DB::table('mata_kuliah as mk')
            ->whereExists(fn($q) => $q->from('rps_pertemuan')->whereColumn('rps_pertemuan.mata_kuliah_id', 'mk.id'))
            ->whereExists(fn($q) => $q->from('bap as b')
                ->join('bap_pertemuan as bp', 'bp.bap_id', '=', 'b.id')
                ->whereColumn('b.mata_kuliah_id', 'mk.id')->where('b.semester_aktif', $ta));

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $mkListQuery->where('mk.program_id', auth()->user()->program_id);
        }

        $mkList = $mkListQuery->select('mk.id', 'mk.kode', 'mk.nama')->orderBy('mk.kode')->get();

        $rows = $mkList->map(function ($mk) use ($ta) {
            $total = DB::table('rps_pertemuan as rp')
                ->join('bap as b', 'b.mata_kuliah_id', '=', 'rp.mata_kuliah_id')
                ->join('bap_pertemuan as bp', fn($j) => $j->on('bp.bap_id', '=', 'b.id')->on('bp.minggu', '=', 'rp.minggu'))
                ->where('rp.mata_kuliah_id', $mk->id)->where('b.semester_aktif', $ta)
                ->whereNotNull('rp.materi')->whereNotNull('bp.materi')->count();

            if ($total === 0) {
                return (object)['mk_kode' => $mk->kode, 'mk_nama' => $mk->nama, 'total' => 0, 'cocok' => 0, 'persentase' => 0, 'status' => 'Tidak Ada Data'];
            }

            $cocok = DB::table('rps_pertemuan as rp')
                ->join('bap as b', 'b.mata_kuliah_id', '=', 'rp.mata_kuliah_id')
                ->join('bap_pertemuan as bp', fn($j) => $j->on('bp.bap_id', '=', 'b.id')->on('bp.minggu', '=', 'rp.minggu'))
                ->where('rp.mata_kuliah_id', $mk->id)->where('b.semester_aktif', $ta)
                ->whereNotNull('rp.materi')->whereNotNull('bp.materi')
                ->whereRaw("(rp.materi LIKE CONCAT('%', SUBSTRING(bp.materi, 1, 20), '%') OR bp.materi LIKE CONCAT('%', SUBSTRING(rp.materi, 1, 20), '%'))")
                ->count();

            $pct = round($cocok / $total * 100, 1);
            return (object)['mk_kode' => $mk->kode, 'mk_nama' => $mk->nama, 'total' => $total, 'cocok' => $cocok, 'persentase' => $pct, 'status' => $pct >= 80 ? 'OK' : 'Warning'];
        })->sortByDesc('persentase')->values();

        $taQuery = DB::table('bap as b')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'b.mata_kuliah_id');

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $taQuery->where('mk.program_id', auth()->user()->program_id);
        }
        $taList = $taQuery->distinct()->pluck('b.semester_aktif')->sort()->reverse()->values();

        $okCount      = $rows->where('status', 'OK')->count();
        $warningCount = $rows->where('status', 'Warning')->count();
        $avgPct       = $rows->where('total', '>', 0)->avg('persentase') ?? 0;

        return view('obe.rps-bap-consistency', compact(
            'rows', 'ta', 'taList', 'okCount', 'warningCount', 'avgPct'
        ));
    }

    /** GET /obe/problematic-courses */
    public function problematicCourses(Request $request)
    {
        $ta       = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $angkatan = $request->input('angkatan');

        $cplQ = DB::table('cpmk_achievement as ca')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'ca.mata_kuliah_id')
            ->where('ca.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplQ->where('mk.program_id', auth()->user()->program_id);
        }

        $cplQ->selectRaw('ca.mata_kuliah_id, mk.kode as mk_kode, mk.nama as mk_nama, ROUND(AVG(ca.nilai_cpmk),2) as avg_cpl')
            ->groupBy('ca.mata_kuliah_id', 'mk.kode', 'mk.nama');
        if ($angkatan) $cplQ->where('ca.angkatan', $angkatan);
        $cplByMk = $cplQ->get()->keyBy('mata_kuliah_id');

        $evalQuery = DB::table('bap_evaluasi_mahasiswa as bem')
            ->join('bap_pertemuan as bp', 'bp.id', '=', 'bem.bap_pertemuan_id')
            ->join('bap as b', 'b.id', '=', 'bp.bap_id')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'b.mata_kuliah_id')
            ->where('b.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $evalQuery->where('mk.program_id', auth()->user()->program_id);
        }

        $evalByMk = $evalQuery->selectRaw('b.mata_kuliah_id, ROUND(AVG((bem.pedagogik+bem.profesional+bem.kepribadian+bem.sosial)/4.0),2) as avg_eval')
            ->groupBy('b.mata_kuliah_id')->get()->keyBy('mata_kuliah_id');

        $passQuery = DB::table('nilai_mahasiswa as nm')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'nm.mata_kuliah_id')
            ->where('nm.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $passQuery->where('mk.program_id', auth()->user()->program_id);
        }

        $passQ = $passQuery->selectRaw('nm.mata_kuliah_id, ROUND(SUM(nm.lulus)/COUNT(*)*100,1) as pass_rate')
            ->groupBy('nm.mata_kuliah_id')->get()->keyBy('mata_kuliah_id');

        $allMkIds = collect()->merge($cplByMk->keys())->merge($evalByMk->keys())->merge($passQ->keys())->unique();

        $mkNamesQuery = DB::table('mata_kuliah')->whereIn('id', $allMkIds);
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $mkNamesQuery->where('program_id', auth()->user()->program_id);
        }
        $mkNames = $mkNamesQuery->get(['id', 'kode', 'nama'])->keyBy('id');

        $rows = $allMkIds->map(function ($mkId) use ($cplByMk, $evalByMk, $passQ, $mkNames) {
            $mk       = $mkNames->get($mkId);
            $avgCpl   = $cplByMk->get($mkId)  ? (float)$cplByMk->get($mkId)->avg_cpl    : null;
            $avgEval  = $evalByMk->get($mkId) ? (float)$evalByMk->get($mkId)->avg_eval   : null;
            $passRate = $passQ->get($mkId)     ? (float)$passQ->get($mkId)->pass_rate     : null;
            $issues   = [];
            if ($avgCpl !== null && $avgCpl < 70)    $issues[] = 'CPL Rendah';
            if ($avgEval !== null && $avgEval < 2.0)  $issues[] = 'Eval Dosen Rendah';
            if ($passRate !== null && $passRate < 70) $issues[] = 'Kelulusan Rendah';
            return (object)[
                'mk_id' => $mkId, 'mk_kode' => $mk?->kode ?? '—', 'mk_nama' => $mk?->nama ?? "MK #{$mkId}",
                'avg_cpl' => $avgCpl, 'avg_eval' => $avgEval, 'pass_rate' => $passRate,
                'issues' => $issues, 'perlu_review' => count($issues) > 0,
            ];
        })->sortByDesc('perlu_review')->values();

        $angkatanQuery = DB::table('mahasiswas');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $angkatanQuery->where('program_id', auth()->user()->program_id);
        }
        $angkatanList = $angkatanQuery->distinct()->orderBy('angkatan')->pluck('angkatan')->filter()->values();

        $taQuery = DB::table('cpmk_achievement as ca')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'ca.mata_kuliah_id');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $taQuery->where('mk.program_id', auth()->user()->program_id);
        }
        $taList = $taQuery->distinct()->pluck('ca.semester_aktif')->sort()->reverse()->values();

        $perluReview  = $rows->where('perlu_review', true)->count();
        $normal       = $rows->where('perlu_review', false)->count();

        return view('obe.problematic-courses', compact(
            'rows', 'ta', 'angkatan', 'angkatanList', 'taList', 'perluReview', 'normal'
        ));
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function fetchCplStats(string $ta)
    {
        $cplStatsQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'cpl.id', '=', 'ca.cpl_id')
            ->where('ca.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplStatsQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        return $cplStatsQuery->select('cpl.id', 'cpl.kode', 'cpl.deskripsi', 'cpl.kategori', 'cpl.total_skor_maks',
                DB::raw('COUNT(*) as total_mahasiswa'), DB::raw('SUM(ca.achieved) as jumlah_tercapai'),
                DB::raw('ROUND(AVG(ca.nilai_cpl), 2) as rata_nilai'),
                DB::raw('ROUND(SUM(ca.achieved)/COUNT(*)*100, 2) as pct_tercapai'))
            ->groupBy('cpl.id', 'cpl.kode', 'cpl.deskripsi', 'cpl.kategori', 'cpl.total_skor_maks')
            ->orderBy('cpl.kode')->get()
            ->map(function ($row) {
                $row->status = match (true) {
                    (float)$row->pct_tercapai >= 80 => 'Tercapai',
                    (float)$row->pct_tercapai >= 60 => 'Perlu Monitoring',
                    default                         => 'Belum Tercapai',
                };
                $row->rekomendasi = match ($row->status) {
                    'Tercapai'         => 'Capaian CPL sudah memenuhi target. Pertahankan kualitas pembelajaran.',
                    'Perlu Monitoring' => 'Capaian CPL mendekati target. Lakukan monitoring berkala.',
                    default            => 'Capaian CPL belum memenuhi target. Diperlukan evaluasi dan intervensi segera.',
                };
                return $row;
            });
    }

    private function fetchCpmkStats(string $ta)
    {
        $cpmkStatsQuery = DB::table('cpmk_achievement as ca')
            ->join('cpmk', 'cpmk.id', '=', 'ca.cpmk_id')
            ->join('cpl', 'cpl.id', '=', 'cpmk.cpl_id')
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'ca.mata_kuliah_id')
            ->where('ca.semester_aktif', $ta);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cpmkStatsQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        return $cpmkStatsQuery->select('mata_kuliah.kode as mk_kode', 'mata_kuliah.nama as mk_nama',
                'cpl.kode as cpl_kode', 'cpmk.kode as cpmk_kode',
                DB::raw('COUNT(*) as total'), DB::raw('SUM(ca.achieved) as tercapai'),
                DB::raw('ROUND(AVG(ca.nilai_cpmk), 2) as rata_nilai'),
                DB::raw('ROUND(SUM(ca.achieved)/COUNT(*)*100, 2) as pct_lulus'))
            ->groupBy('mata_kuliah.kode', 'mata_kuliah.nama', 'cpl.kode', 'cpmk.kode')
            ->orderBy('cpl.kode')->orderBy('cpmk.kode')->get();
    }
}
