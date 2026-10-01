<?php

namespace App\Http\Controllers\Obe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ObeCqiController extends Controller
{
    /** GET /obe/cqi-monitoring */
    public function index(Request $request)
    {
        $ta       = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $angkatan = $request->input('angkatan');
        $autoGen  = $request->boolean('auto_generate');

        $cplQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'cpl.id', '=', 'ca.cpl_id');

        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $cplQuery->where('cpl.program_id', auth()->user()->program_id);
        }

        $cplQuery->selectRaw('
                ca.cpl_id, ca.angkatan, ca.semester_aktif as tahun_akademik,
                cpl.kode as cpl_kode, cpl.deskripsi as cpl_deskripsi,
                ROUND(AVG(ca.nilai_cpl),2) as avg_nilai,
                MAX(ca.threshold) as threshold,
                COUNT(*) as total_mhs, SUM(ca.achieved) as total_achieved
            ')
            ->where('ca.semester_aktif', $ta)
            ->groupBy('ca.cpl_id', 'ca.angkatan', 'ca.semester_aktif', 'cpl.kode', 'cpl.deskripsi')
            ->orderBy('cpl.kode')->orderBy('ca.angkatan');

        if ($angkatan) $cplQuery->where('ca.angkatan', $angkatan);

        $belowThreshold = $cplQuery->get()->filter(
            fn($r) => (float)$r->avg_nilai < (float)$r->threshold
        );

        if ($autoGen && Schema::hasTable('cqi_actions')) {
            foreach ($belowThreshold as $r) {
                $nextSem = $this->nextSemester($ta);
                DB::table('cqi_actions')->updateOrInsert(
                    ['cpl_id' => $r->cpl_id, 'angkatan' => $r->angkatan, 'tahun_akademik' => $ta],
                    [
                        'nilai_cpl'         => $r->avg_nilai,
                        'threshold'         => $r->threshold,
                        'masalah'           => "CPL {$r->cpl_kode} belum tercapai (rata-rata {$r->avg_nilai} < threshold {$r->threshold})",
                        'rencana_perbaikan' => 'Perlu perbaikan metode pembelajaran dan review soal asesmen',
                        'pic'               => 'Kaprodi',
                        'target_semester'   => $nextSem,
                        'updated_at'        => now(),
                    ]
                );
                DB::table('cqi_actions')
                    ->where('cpl_id', $r->cpl_id)->where('angkatan', $r->angkatan)
                    ->where('tahun_akademik', $ta)->whereNull('created_at')
                    ->update(['created_at' => now()]);
            }
            return redirect()->route('obe.cqi-monitoring', ['ta' => $ta, 'angkatan' => $angkatan])
                ->with('success', $belowThreshold->count() . ' CQI action berhasil dibuat/diperbarui.');
        }

        $actions     = collect();
        $openCount   = $inProgCount = $doneCount = 0;
        $cqiTableExists = Schema::hasTable('cqi_actions');

        if ($cqiTableExists) {
            $actQuery = DB::table('cqi_actions as c')
                ->join('cpl', 'cpl.id', '=', 'c.cpl_id');

            if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
                $actQuery->where('cpl.program_id', auth()->user()->program_id);
            }

            $actQuery->select('c.*', 'cpl.kode as cpl_kode', 'cpl.deskripsi as cpl_deskripsi')
                ->orderByRaw("FIELD(c.status,'open','in_progress','done')")
                ->orderBy('cpl.kode');

            if ($ta)       $actQuery->where('c.tahun_akademik', $ta);
            if ($angkatan) $actQuery->where('c.angkatan', $angkatan);

            $actions     = $actQuery->get();
            $openCount   = $actions->where('status', 'open')->count();
            $inProgCount = $actions->where('status', 'in_progress')->count();
            $doneCount   = $actions->where('status', 'done')->count();
        }

        $angkatanQuery = DB::table('mahasiswas');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $angkatanQuery->where('program_id', auth()->user()->program_id);
        }
        $angkatanList = $angkatanQuery->distinct()->orderBy('angkatan')->pluck('angkatan')->filter()->values();

        $taQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id');
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $taQuery->where('cpl.program_id', auth()->user()->program_id);
        }
        $taList = $taQuery->distinct()->pluck('ca.semester_aktif')->sort()->reverse()->values();

        return view('obe.cqi-monitoring', compact(
            'actions', 'belowThreshold', 'openCount', 'inProgCount', 'doneCount',
            'ta', 'angkatan', 'angkatanList', 'taList', 'cqiTableExists'
        ));
    }

    /** POST /obe/cqi-actions/{id} */
    public function updateAction(Request $request, int $id)
    {
        $data = $request->validate([
            'status'            => 'required|in:open,in_progress,done',
            'masalah'           => 'required|string',
            'rencana_perbaikan' => 'required|string',
            'pic'               => 'required|string|max:100',
            'target_semester'   => 'nullable|string|max:20',
        ]);

        $query = DB::table('cqi_actions')->where('id', $id);
        if (auth()->check() && auth()->user()->role !== 'admin' && auth()->user()->program_id) {
            $query->whereExists(fn($q) => $q->from('cpl')
                ->whereColumn('cpl.id', 'cqi_actions.cpl_id')
                ->where('cpl.program_id', auth()->user()->program_id)
            );
        }

        $query->update(array_merge($data, ['updated_at' => now()]));

        return back()->with('success', 'CQI Action diperbarui.');
    }

    private function nextSemester(string $ta): string
    {
        if (preg_match('/(\d{4})\/(\d{4})/', $ta, $m)) {
            return 'Ganjil ' . ((int)$m[1] + 1) . '/' . ((int)$m[2] + 1);
        }
        return 'Semester Berikutnya';
    }
}
