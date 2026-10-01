<?php

namespace App\Http\Controllers\Nilai;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\NilaiMahasiswa;
use App\Models\NilaiSubCpmk;
use App\Models\SubCpmk;
use App\Models\SubCpmkBobot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * NilaiKalkulasiController
 *
 * Tanggung jawab:
 *   - save()      → simpan nilai (SubCPMK mode OBE / komponen mode legacy) + trigger kalkulasi CPMK
 *   - kalkulasi() → API JSON statistik distribusi nilai per MK (komponen, CPMK, CPL, grade)
 */
class NilaiKalkulasiController extends Controller
{
    private function getTaAktif(): string
    {
        return config('obe.tahun_akademik', '2025/2026');
    }

    /**
     * POST /nilai-mahasiswa/{mk}/save
     * SubCPMK mode (OBE): validates bobot = 100, bulk upsert 3 tables.
     * Komponen mode (legacy): direct upsert ke nilai_mahasiswa.
     */
    public function save(Request $request, int $mkId)
    {
        $ta   = $request->input('ta', $this->getTaAktif());
        $mode = $request->input('mode', 'subcpmk');
        MataKuliah::findOrFail($mkId);

        $rows = $request->input('nilai', []);

        if ($mode === 'subcpmk') {
            return $this->saveSubCpmk($rows, $mkId, $ta);
        }

        return $this->saveKomponen($rows, $mkId, $ta);
    }

    /**
     * GET /api/nilai/{mk}/kalkulasi
     * Returns JSON: {komponen, cpmk, cpl, distribusi}
     */
    public function kalkulasi(Request $request, int $mkId)
    {
        MataKuliah::findOrFail($mkId);
        $ta = $request->input('ta', $this->getTaAktif());

        $bobotRow = DB::table('bobot_penilaian')
            ->where('mata_kuliah_id', $mkId)
            ->select('bobot_tugas', 'bobot_uts', 'bobot_uas', 'bobot_partisipatif', 'bobot_proyek')
            ->first();

        $bobot = [
            'tugas'        => (int) ($bobotRow->bobot_tugas        ?? 20),
            'uts'          => (int) ($bobotRow->bobot_uts          ?? 20),
            'uas'          => (int) ($bobotRow->bobot_uas          ?? 20),
            'partisipatif' => (int) ($bobotRow->bobot_partisipatif ?? 20),
            'proyek'       => (int) ($bobotRow->bobot_proyek       ?? 20),
        ];

        $nilais = DB::table('nilai_mahasiswa')
            ->where('mata_kuliah_id', $mkId)->where('semester_aktif', $ta)->get();

        $n = $nilais->count();
        if ($n === 0) {
            return response()->json(['error' => 'Belum ada nilai yang diinput untuk MK ini.'], 404);
        }

        $komponen = $this->buildKomponenStats($nilais, $bobot);
        $cpmkRows = $this->buildCpmkStats($nilais, $mkId, $n);
        $cplRows  = $this->buildCplStats($nilais, $mkId);

        $jmlLulus = $nilais->where('lulus', true)->count();
        $distribusi = [
            'jumlah_lulus'       => $jmlLulus,
            'jumlah_tidak_lulus' => $n - $jmlLulus,
            'persen_lulus'       => $n > 0 ? round($jmlLulus / $n * 100, 2) : 0,
            'rata_rata_final'    => round($nilais->avg('nilai_akhir'), 2),
            'jml_a'              => $nilais->where('grade', 'A')->count(),
            'jml_b'              => $nilais->where('grade', 'B')->count(),
            'jml_c'              => $nilais->where('grade', 'C')->count(),
            'jml_d'              => $nilais->where('grade', 'D')->count(),
            'jml_e'              => $nilais->where('grade', 'E')->count(),
        ];

        $mk = MataKuliah::find($mkId);
        return response()->json([
            'mk'               => $mk ? ['nama' => $mk->nama, 'kode' => $mk->kode, 'pjmk' => $mk->pjmk] : null,
            'jumlah_mahasiswa' => $n,
            'komponen'         => $komponen,
            'cpmk'             => $cpmkRows,
            'cpl'              => $cplRows,
            'distribusi'       => $distribusi,
        ]);
    }

    // ── Private: save helpers ─────────────────────────────────────────────

    private function saveSubCpmk(array $rows, int $mkId, string $ta)
    {
        $subCpmkMap = DB::table('sub_cpmk')->get()->keyBy('id');

        $subIds = collect($rows)
            ->flatMap(fn($s) => is_array($s) ? array_keys($s) : [])
            ->unique()->values()->all();

        $subBobotMap = Cache::remember(
            "sub_bobot_{$mkId}",
            3600,
            fn() => SubCpmkBobot::whereIn('sub_cpmk_id', $subIds)->get()->keyBy('sub_cpmk_id')
        );

        // Validate: bobot must exist AND sum to exactly 100
        foreach ($subIds as $subId) {
            $b = $subBobotMap->get($subId);
            if (!$b) {
                return back()->withErrors([
                    'bobot' => "Bobot SubCPMK ID {$subId} tidak ditemukan. Harap set bobot terlebih dahulu.",
                ])->withInput();
            }
            $total = $b->bobot_tugas + $b->bobot_uts + $b->bobot_uas
                + $b->bobot_partisipatif + $b->bobot_proyek;
            if ($total !== 100) {
                $sub  = $subCpmkMap->get($subId);
                $kode = $sub->kode ?? "ID {$subId}";
                return back()->withErrors([
                    'bobot' => "Total bobot SubCPMK {$kode} harus 100, ditemukan: {$total}.",
                ])->withInput();
            }
        }

        $angkatanMap = DB::table('mahasiswas')
            ->whereIn('id', array_keys($rows))
            ->pluck('angkatan', 'id');

        $nilaiSubData = [];
        $cpmkData     = [];
        $nilaiMhsData = [];
        $now          = now();

        foreach ($rows as $mahasiswaId => $subRows) {
            if (!is_array($subRows)) continue;
            $angkatan = $angkatanMap[$mahasiswaId] ?? null;
            $cpmkVals = [];

            foreach ($subRows as $subId => $komponens) {
                $sub = $subCpmkMap->get($subId);
                $b   = $subBobotMap->get($subId);
                if (!$sub || !$b || !is_array($komponens)) continue;

                $t = max(0, min(100, (float) ($komponens['tugas']        ?? 0)));
                $u = max(0, min(100, (float) ($komponens['uts']          ?? 0)));
                $a = max(0, min(100, (float) ($komponens['uas']          ?? 0)));
                $p = max(0, min(100, (float) ($komponens['partisipatif'] ?? 0)));
                $r = max(0, min(100, (float) ($komponens['proyek']       ?? 0)));

                $nilaiSub = round(
                    ($t * $b->bobot_tugas + $u * $b->bobot_uts + $a * $b->bobot_uas
                        + $p * $b->bobot_partisipatif + $r * $b->bobot_proyek) / 100,
                    2
                );

                $nilaiSubData[] = [
                    'mahasiswa_id'  => $mahasiswaId,
                    'sub_cpmk_id'   => $subId,
                    'tugas'         => $t, 'uts' => $u, 'uas' => $a,
                    'partisipatif'  => $p, 'proyek' => $r,
                    'nilai_subcpmk' => $nilaiSub,
                    'created_at'    => $now, 'updated_at' => $now,
                ];

                $cpmkVals[$sub->cpmk_id][] = $nilaiSub;
            }

            $allCpmkAvgs = [];
            foreach ($cpmkVals as $cpmkId => $vals) {
                $avg    = round(array_sum($vals) / count($vals), 2);
                $allCpmkAvgs[] = $avg;
                $rubric = $avg < 60 ? 'novice' : ($avg < 80 ? 'developing' : 'proficient');

                $cpmkData[] = [
                    'mahasiswa_id'   => $mahasiswaId, 'mata_kuliah_id' => $mkId,
                    'cpmk_id'        => $cpmkId,      'semester_aktif' => $ta,
                    'nilai_cpmk'     => $avg,          'threshold'      => 56,
                    'achieved'       => $avg >= 56 ? 1 : 0,
                    'rubric_level'   => $rubric,       'angkatan'       => $angkatan,
                    'created_at'     => $now,          'updated_at'     => $now,
                ];
            }

            if (!empty($allCpmkAvgs)) {
                $akhir = round(array_sum($allCpmkAvgs) / count($allCpmkAvgs), 2);
                $nilaiMhsData[] = [
                    'mahasiswa_id'       => $mahasiswaId, 'mata_kuliah_id'     => $mkId,
                    'semester_aktif'     => $ta,          'nilai_akhir'        => $akhir,
                    'grade'              => NilaiMahasiswa::toGrade($akhir),
                    'lulus'              => $akhir >= 56 ? 1 : 0,
                    'nilai_tugas'        => 0, 'nilai_uts' => 0, 'nilai_uas' => 0,
                    'nilai_partisipatif' => 0, 'nilai_proyek' => 0,
                    'created_at'         => $now, 'updated_at' => $now,
                ];
            }
        }

        DB::transaction(function () use ($nilaiSubData, $cpmkData, $nilaiMhsData) {
            if (!empty($nilaiSubData)) {
                NilaiSubCpmk::upsert($nilaiSubData, ['mahasiswa_id', 'sub_cpmk_id'],
                    ['tugas', 'uts', 'uas', 'partisipatif', 'proyek', 'nilai_subcpmk', 'updated_at']);
            }
            if (!empty($cpmkData)) {
                DB::table('cpmk_achievement')->upsert($cpmkData,
                    ['mahasiswa_id', 'mata_kuliah_id', 'cpmk_id', 'semester_aktif'],
                    ['nilai_cpmk', 'threshold', 'achieved', 'rubric_level', 'angkatan', 'updated_at']);
            }
            if (!empty($nilaiMhsData)) {
                NilaiMahasiswa::upsert($nilaiMhsData,
                    ['mahasiswa_id', 'mata_kuliah_id', 'semester_aktif'],
                    ['nilai_akhir', 'grade', 'lulus', 'nilai_tugas', 'nilai_uts', 'nilai_uas',
                     'nilai_partisipatif', 'nilai_proyek', 'updated_at']);
            }
        });

        return redirect()->route('nilai-mahasiswa.show', ['mk' => $mkId, 'ta' => $ta])
            ->with('success', 'Nilai berhasil disimpan.');
    }

    private function saveKomponen(array $rows, int $mkId, string $ta)
    {
        $bobotRow = DB::table('bobot_penilaian')
            ->where('mata_kuliah_id', $mkId)
            ->select('bobot_tugas', 'bobot_uts', 'bobot_uas', 'bobot_partisipatif', 'bobot_proyek')
            ->first();

        $bobot = [
            'tugas'        => (int) ($bobotRow->bobot_tugas        ?? 20),
            'uts'          => (int) ($bobotRow->bobot_uts          ?? 20),
            'uas'          => (int) ($bobotRow->bobot_uas          ?? 20),
            'partisipatif' => (int) ($bobotRow->bobot_partisipatif ?? 20),
            'proyek'       => (int) ($bobotRow->bobot_proyek       ?? 20),
        ];

        $nilaiMhsData = [];
        $now          = now();

        foreach ($rows as $mahasiswaId => $v) {
            if (!is_array($v)) continue;
            $nilaiArr = [
                'tugas'        => max(0, min(100, (float) ($v['tugas']        ?? 0))),
                'uts'          => max(0, min(100, (float) ($v['uts']          ?? 0))),
                'uas'          => max(0, min(100, (float) ($v['uas']          ?? 0))),
                'partisipatif' => max(0, min(100, (float) ($v['partisipatif'] ?? 0))),
                'proyek'       => max(0, min(100, (float) ($v['proyek']       ?? 0))),
            ];
            $akhir = NilaiMahasiswa::hitungNilaiAkhir($nilaiArr, $bobot);
            $nilaiMhsData[] = [
                'mahasiswa_id'       => $mahasiswaId, 'mata_kuliah_id' => $mkId,
                'semester_aktif'     => $ta,           'nilai_tugas'    => $nilaiArr['tugas'],
                'nilai_uts'          => $nilaiArr['uts'],                'nilai_uas' => $nilaiArr['uas'],
                'nilai_partisipatif' => $nilaiArr['partisipatif'],       'nilai_proyek' => $nilaiArr['proyek'],
                'nilai_akhir'        => $akhir,
                'grade'              => NilaiMahasiswa::toGrade($akhir),
                'lulus'              => $akhir >= 56 ? 1 : 0,
                'created_at'         => $now, 'updated_at' => $now,
            ];
        }

        if (!empty($nilaiMhsData)) {
            NilaiMahasiswa::upsert($nilaiMhsData,
                ['mahasiswa_id', 'mata_kuliah_id', 'semester_aktif'],
                ['nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_partisipatif',
                 'nilai_proyek', 'nilai_akhir', 'grade', 'lulus', 'updated_at']);
        }

        return redirect()->route('nilai-mahasiswa.show', ['mk' => $mkId, 'ta' => $ta])
            ->with('success', 'Nilai berhasil disimpan.');
    }

    // ── Private: kalkulasi helpers ────────────────────────────────────────

    private function buildKomponenStats($nilais, array $bobot): array
    {
        $fields = [
            'tugas' => 'nilai_tugas', 'uts' => 'nilai_uts', 'uas' => 'nilai_uas',
            'partisipatif' => 'nilai_partisipatif', 'proyek' => 'nilai_proyek',
        ];
        $labels = [
            'tugas' => 'Tugas', 'uts' => 'UTS', 'uas' => 'UAS',
            'partisipatif' => 'Partisipatif', 'proyek' => 'Proyek',
        ];

        $result = [];
        foreach ($fields as $key => $col) {
            $vals     = $nilais->pluck($col)->map(fn($v) => (float) $v);
            $avg      = round($vals->avg(), 2);
            $variance = $vals->map(fn($v) => pow($v - $avg, 2))->avg();
            $result[] = [
                'komponen'     => $labels[$key],
                'bobot_persen' => $bobot[$key],
                'rata_rata'    => $avg,
                'nilai_min'    => round($vals->min(), 2),
                'nilai_max'    => round($vals->max(), 2),
                'std_deviasi'  => round(sqrt($variance), 2),
            ];
        }
        return $result;
    }

    private function buildCpmkStats($nilais, int $mkId, int $n): array
    {
        $cpmkList = DB::table('cpmk')
            ->join('mata_kuliah_cpmk', 'cpmk.id', '=', 'mata_kuliah_cpmk.cpmk_id')
            ->where('mata_kuliah_cpmk.mata_kuliah_id', $mkId)
            ->select('cpmk.id', 'cpmk.kode', 'cpmk.deskripsi')
            ->orderBy('cpmk.kode')->get();

        $cpmkBobotMap = DB::table('bobot_penilaian')
            ->where('mata_kuliah_id', $mkId)->get()->keyBy('cpmk_id');

        $result = [];
        foreach ($cpmkList as $cpmk) {
            $cb = $cpmkBobotMap->get($cpmk->id);
            $bt = $cb ? (int) $cb->bobot_tugas        : 20;
            $bu = $cb ? (int) $cb->bobot_uts           : 20;
            $ba = $cb ? (int) $cb->bobot_uas           : 20;
            $bp = $cb ? (int) $cb->bobot_partisipatif  : 20;
            $br = $cb ? (int) $cb->bobot_proyek        : 20;
            $bT = max(1, $bt + $bu + $ba + $bp + $br);

            $nilaiCpmk = $nilais->map(
                fn($r) => ($r->nilai_tugas * $bt + $r->nilai_uts * $bu + $r->nilai_uas * $ba
                    + $r->nilai_partisipatif * $bp + $r->nilai_proyek * $br) / $bT
            );
            $avgCpmk   = round($nilaiCpmk->avg(), 2);
            $lulusCpmk = $nilaiCpmk->filter(fn($v) => $v >= 56)->count();

            $result[] = [
                'kode_cpmk'       => $cpmk->kode,
                'deskripsi_cpmk'  => $cpmk->deskripsi,
                'rata_rata_nilai' => $avgCpmk,
                'persen_lulus'    => $n > 0 ? round($lulusCpmk / $n * 100, 2) : 0,
                'target_capaian'  => 70,
                'keterangan'      => $avgCpmk >= 70 ? 'Tercapai' : 'Belum Tercapai',
            ];
        }
        return $result;
    }

    private function buildCplStats($nilais, int $mkId): array
    {
        $cplList = DB::table('cpl')
            ->join('mata_kuliah_cpl', 'cpl.id', '=', 'mata_kuliah_cpl.cpl_id')
            ->where('mata_kuliah_cpl.mata_kuliah_id', $mkId)
            ->select('cpl.id', 'cpl.kode', 'cpl.deskripsi')
            ->orderBy('cpl.kode')->get();

        $cpmkBobotMap = DB::table('bobot_penilaian')
            ->where('mata_kuliah_id', $mkId)->get()->keyBy('cpmk_id');

        $result = [];
        foreach ($cplList as $cpl) {
            $cpmkIds = DB::table('cpmk')
                ->join('mata_kuliah_cpmk', 'cpmk.id', '=', 'mata_kuliah_cpmk.cpmk_id')
                ->where('mata_kuliah_cpmk.mata_kuliah_id', $mkId)
                ->where('cpmk.cpl_id', $cpl->id)
                ->pluck('cpmk.id');

            if ($cpmkIds->isEmpty()) {
                $avgCpl = round($nilais->avg('nilai_akhir'), 2);
            } else {
                $allVals = [];
                foreach ($cpmkIds as $cid) {
                    $cb2 = $cpmkBobotMap->get($cid);
                    if (!$cb2) continue;
                    $bT2 = max(1, $cb2->bobot_tugas + $cb2->bobot_uts + $cb2->bobot_uas
                        + $cb2->bobot_partisipatif + $cb2->bobot_proyek);
                    foreach ($nilais as $row) {
                        $allVals[] = ($row->nilai_tugas * $cb2->bobot_tugas
                            + $row->nilai_uts * $cb2->bobot_uts
                            + $row->nilai_uas * $cb2->bobot_uas
                            + $row->nilai_partisipatif * $cb2->bobot_partisipatif
                            + $row->nilai_proyek * $cb2->bobot_proyek) / $bT2;
                    }
                }
                $avgCpl = count($allVals) > 0 ? round(array_sum($allVals) / count($allVals), 2) : 0;
            }

            $result[] = [
                'kode_cpl'   => $cpl->kode,
                'nilai_cpl'  => $avgCpl,
                'target_cpl' => 70,
                'keterangan' => $avgCpl >= 70 ? 'Tercapai' : 'Belum Tercapai',
            ];
        }
        return $result;
    }
}
