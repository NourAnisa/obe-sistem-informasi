<?php

namespace App\Exports;

use App\Models\NilaiMahasiswa;
use App\Models\NilaiSubCpmk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

/**
 * Import nilai mahasiswa dari Excel.
 *
 * Format kolom (SubCPMK mode):
 *   nim | sub_cpmk_kode | tugas | uts | uas | partisipatif | proyek
 *   — Satu baris per NIM per Sub-CPMK
 *
 * Format kolom (Komponen mode / tanpa SubCPMK):
 *   nim | tugas | uts | uas | partisipatif | proyek
 *   — Satu baris per NIM
 */
class NilaiMahasiswaImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public array $errors   = [];
    public int   $imported = 0;
    public int   $skipped  = 0;

    public function __construct(
        protected int        $mkId,
        protected string     $ta,
        protected bool       $hasSubCpmk,
        protected Collection $subCpmkByKode,
        protected Collection $subBobotMap,
        protected array      $bobot,
    ) {}

    public function collection(Collection $rows): void
    {
        $now = now();

        $nims   = $rows->pluck('nim')->filter()->map(fn($v) => strtoupper(trim((string)$v)))->unique()->values()->all();
        $mhsMap = DB::table('mahasiswas')->whereIn('nim', $nims)->pluck('id', 'nim');

        $byNim        = $rows->groupBy(fn($r) => strtoupper(trim((string)($r['nim'] ?? ''))));
        $nilaiSubData = [];
        $nilaiMhsData = [];
        $cpmkData     = [];

        foreach ($byNim as $nim => $nimRows) {
            if (blank($nim)) {
                $this->skipped++;
                continue;
            }

            $mhsId = $mhsMap[$nim] ?? null;
            if (!$mhsId) {
                $this->errors[] = "NIM {$nim} tidak ditemukan.";
                $this->skipped++;
                continue;
            }

            if ($this->hasSubCpmk) {
                $cpmkVals = [];
                foreach ($nimRows as $row) {
                    $subKode = strtoupper(trim((string)($row['sub_cpmk_kode'] ?? '')));
                    $sub     = $this->subCpmkByKode->get($subKode);
                    if (!$sub) {
                        $this->errors[] = "NIM {$nim}: Sub-CPMK '{$subKode}' tidak ditemukan.";
                        continue;
                    }
                    $b = $this->subBobotMap->get($sub->id);
                    if (!$b) {
                        $this->errors[] = "NIM {$nim}: Bobot Sub-CPMK '{$subKode}' belum di-set.";
                        continue;
                    }

                    $t = max(0, min(100, (float)($row['tugas']        ?? 0)));
                    $u = max(0, min(100, (float)($row['uts']          ?? 0)));
                    $a = max(0, min(100, (float)($row['uas']          ?? 0)));
                    $p = max(0, min(100, (float)($row['partisipatif'] ?? 0)));
                    $r = max(0, min(100, (float)($row['proyek']       ?? 0)));

                    $nilaiSub = round(
                        ($t * $b->bobot_tugas + $u * $b->bobot_uts + $a * $b->bobot_uas
                            + $p * $b->bobot_partisipatif + $r * $b->bobot_proyek) / 100,
                        2
                    );
                    $nilaiSubData[] = [
                        'mahasiswa_id' => $mhsId,
                        'sub_cpmk_id' => $sub->id,
                        'tugas' => $t,
                        'uts' => $u,
                        'uas' => $a,
                        'partisipatif' => $p,
                        'proyek' => $r,
                        'nilai_subcpmk' => $nilaiSub,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $cpmkVals[$sub->cpmk_id][] = $nilaiSub;
                }
                $allAvgs = [];
                foreach ($cpmkVals as $cpmkId => $vals) {
                    $avg       = round(array_sum($vals) / count($vals), 2);
                    $allAvgs[] = $avg;
                    $cpmkData[] = [
                        'mahasiswa_id' => $mhsId,
                        'mata_kuliah_id' => $this->mkId,
                        'cpmk_id' => $cpmkId,
                        'semester_aktif' => $this->ta,
                        'nilai_cpmk' => $avg,
                        'threshold' => 56,
                        'achieved' => $avg >= 56 ? 1 : 0,
                        'rubric_level' => $avg < 60 ? 'novice' : ($avg < 80 ? 'developing' : 'proficient'),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if (!empty($allAvgs)) {
                    $akhir = round(array_sum($allAvgs) / count($allAvgs), 2);
                    $nilaiMhsData[] = $this->buildRow($mhsId, $akhir, $now);
                    $this->imported++;
                }
            } else {
                $row   = $nimRows->first();
                $n     = [
                    'tugas'        => max(0, min(100, (float)($row['tugas']        ?? 0))),
                    'uts'          => max(0, min(100, (float)($row['uts']          ?? 0))),
                    'uas'          => max(0, min(100, (float)($row['uas']          ?? 0))),
                    'partisipatif' => max(0, min(100, (float)($row['partisipatif'] ?? 0))),
                    'proyek'       => max(0, min(100, (float)($row['proyek']       ?? 0))),
                ];
                $akhir  = NilaiMahasiswa::hitungNilaiAkhir($n, $this->bobot);
                $rec    = $this->buildRow($mhsId, $akhir, $now);
                $rec    = array_merge($rec, [
                    'nilai_tugas' => $n['tugas'],
                    'nilai_uts' => $n['uts'],
                    'nilai_uas' => $n['uas'],
                    'nilai_partisipatif' => $n['partisipatif'],
                    'nilai_proyek' => $n['proyek'],
                ]);
                $nilaiMhsData[] = $rec;
                $this->imported++;
            }
        }

        DB::transaction(function () use ($nilaiSubData, $cpmkData, $nilaiMhsData) {
            if (!empty($nilaiSubData)) {
                NilaiSubCpmk::upsert(
                    $nilaiSubData,
                    ['mahasiswa_id', 'sub_cpmk_id'],
                    ['tugas', 'uts', 'uas', 'partisipatif', 'proyek', 'nilai_subcpmk', 'updated_at']
                );
            }
            if (!empty($cpmkData)) {
                DB::table('cpmk_achievement')->upsert(
                    $cpmkData,
                    ['mahasiswa_id', 'mata_kuliah_id', 'cpmk_id', 'semester_aktif'],
                    ['nilai_cpmk', 'threshold', 'achieved', 'rubric_level', 'updated_at']
                );
            }
            if (!empty($nilaiMhsData)) {
                NilaiMahasiswa::upsert(
                    $nilaiMhsData,
                    ['mahasiswa_id', 'mata_kuliah_id', 'semester_aktif'],
                    [
                        'nilai_akhir',
                        'grade',
                        'lulus',
                        'nilai_tugas',
                        'nilai_uts',
                        'nilai_uas',
                        'nilai_partisipatif',
                        'nilai_proyek',
                        'updated_at'
                    ]
                );
            }
        });
    }

    private function buildRow(int $mhsId, float $akhir, $now): array
    {
        return [
            'mahasiswa_id' => $mhsId,
            'mata_kuliah_id' => $this->mkId,
            'semester_aktif' => $this->ta,
            'nilai_akhir' => $akhir,
            'grade' => NilaiMahasiswa::toGrade($akhir),
            'lulus' => $akhir >= 56 ? 1 : 0,
            'nilai_tugas' => 0,
            'nilai_uts' => 0,
            'nilai_uas' => 0,
            'nilai_partisipatif' => 0,
            'nilai_proyek' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
}
