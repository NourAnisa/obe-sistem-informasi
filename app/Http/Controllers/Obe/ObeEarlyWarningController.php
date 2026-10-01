<?php

namespace App\Http\Controllers\Obe;

use App\Http\Controllers\Controller;
use App\Services\EarlyWarningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObeEarlyWarningController extends Controller
{
    public function __construct(private EarlyWarningService $svc) {}

    /** GET /early-warning */
    public function index(Request $request)
    {
        $ta       = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $angkatan = $request->input('angkatan');

        $query = DB::table('cpl_achievement as ca')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'ca.mahasiswa_id')
            ->join('cpl', 'cpl.id', '=', 'ca.cpl_id')
            ->where('ca.semester_aktif', $ta)
            ->where('ca.achieved', 0);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $query->where('cpl.program_id', auth()->user()->program_id);
        }

        $query->select(
                'mahasiswas.nim', 'mahasiswas.nama', 'mahasiswas.angkatan',
                'cpl.kode as cpl_kode', 'cpl.deskripsi as cpl_deskripsi',
                'ca.nilai_cpl', 'ca.threshold', 'ca.jumlah_cpmk', 'ca.jumlah_achieved',
                DB::raw('ROUND(ca.threshold - ca.nilai_cpl, 2) as gap')
            )
            ->orderBy('mahasiswas.nim')->orderBy('cpl.kode');

        if ($angkatan) {
            $query->where('ca.angkatan', $angkatan);
        }

        $warningRows = $query->get()->map(function ($r) {
            $r->status = match (true) {
                (float)$r->nilai_cpl < (float)$r->threshold * 0.5 => 'Kritis',
                (float)$r->nilai_cpl < (float)$r->threshold        => 'Perlu Intervensi',
                default                                             => 'Perhatian',
            };
            return $r;
        });

        $totalWarningMhs = $warningRows->pluck('nim')->unique()->count();
        $cplTerburuk     = $warningRows->sortBy('nilai_cpl')->first();
        $perCpl          = $warningRows->groupBy('cpl_kode')->map(fn($g) => [
            'kode'       => $g->first()->cpl_kode,
            'deskripsi'  => $g->first()->cpl_deskripsi,
            'jumlah_mhs' => $g->pluck('nim')->unique()->count(),
            'rata_nilai' => round($g->avg('nilai_cpl'), 2),
            'avg_gap'    => round($g->avg('gap'), 2),
        ])->values()->sortByDesc('jumlah_mhs');

        $angkatanQuery = DB::table('mahasiswas');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $angkatanQuery->where('program_id', auth()->user()->program_id);
        }
        $angkatanList = $angkatanQuery->distinct()->orderBy('angkatan')->pluck('angkatan')->filter();

        $taQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $taQuery->where('cpl.program_id', auth()->user()->program_id);
        }
        $taList = $taQuery->distinct()->pluck('ca.semester_aktif')->sort()->reverse()->values();

        return view('obe.warning-mahasiswa', compact(
            'warningRows', 'totalWarningMhs', 'cplTerburuk', 'perCpl',
            'ta', 'angkatan', 'angkatanList', 'taList'
        ));
    }

    /** GET /early-warning/export-csv */
    public function exportCsv(Request $request)
    {
        $ta       = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $angkatan = $request->input('angkatan');

        $query = DB::table('cpl_achievement as ca')
            ->join('mahasiswas', 'mahasiswas.id', '=', 'ca.mahasiswa_id')
            ->join('cpl', 'cpl.id', '=', 'ca.cpl_id')
            ->where('ca.semester_aktif', $ta)
            ->where('ca.achieved', 0);

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $query->where('cpl.program_id', auth()->user()->program_id);
        }

        $query->select(
                'mahasiswas.nim', 'mahasiswas.nama', 'mahasiswas.angkatan',
                'cpl.kode as cpl_kode', 'ca.nilai_cpl', 'ca.threshold',
                DB::raw('ROUND(ca.threshold - ca.nilai_cpl, 2) as gap')
            )
            ->orderBy('mahasiswas.nim')->orderBy('cpl.kode');

        if ($angkatan) $query->where('ca.angkatan', $angkatan);

        $rows     = $query->get();
        $filename = 'early-warning-' . str_replace('/', '-', $ta) . ($angkatan ? '-' . $angkatan : '') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($rows) {
            $f = fopen('php://output', 'w');
            fputcsv($f, ['NIM', 'Nama', 'Angkatan', 'CPL', 'Nilai CPL', 'Threshold', 'Gap']);
            foreach ($rows as $r) {
                fputcsv($f, [$r->nim, $r->nama, $r->angkatan, $r->cpl_kode, $r->nilai_cpl, $r->threshold, $r->gap]);
            }
            fclose($f);
        }, 200, $headers);
    }

    /**
     * POST /early-warning/send-notifications
     * Kirim email ke semua dosen PA yang punya mahasiswa early warning.
     */
    public function sendNotifications(Request $request)
    {
        $ta       = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $angkatan = $request->input('angkatan');

        $result = $this->svc->sendNotifications($ta, $angkatan);

        $msg = "Email terkirim: {$result['sent']} dosen PA.";
        if (!empty($result['skipped'])) {
            $msg .= " Dilewati (email kosong): {$result['skipped']}.";
        }
        if (!empty($result['errors'])) {
            $msg .= ' Error: ' . implode('; ', $result['errors']);
        }

        return back()->with($result['sent'] > 0 ? 'success' : 'warning', $msg);
    }
}
