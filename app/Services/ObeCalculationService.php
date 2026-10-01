<?php

namespace App\Services;

use App\Models\{MataKuliah, NilaiMahasiswa, CpmkAchievement, CplAchievement, EvaluasiCohort, CplTarget, Cpl};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class ObeCalculationService
{
    const THRESHOLD_DEFAULT = 56.0;
    const TARGET_PCT_DEFAULT = 60.0;

    /**
     * STEP 1 — Hitung nilai_cpmk per mahasiswa per MK.
     *
     * Nilai CPMK = Σ(nilai_komponen × bobot_komponen) / total_bobot
     *
     * Sumber data:
     *   - nilai_mahasiswa → nilai per komponen per mahasiswa per MK
     *   - mata_kuliah_cpmk → daftar CPMK yang ada di MK ini
     *   - bobot_penilaian → bobot per CPMK per MK (join mata_kuliah_id + cpmk_id)
     *
     * Fallback: jika total bobot = 0, gunakan nilai_akhir dari nilai_mahasiswa.
     */
    public function hitungCpmkAchievement(int $mkId, string $semesterAktif): int
    {
        // Mahasiswa yang KRS-nya disetujui di MK ini
        $mahasiswaIdsQuery = DB::table('mahasiswa_mk')
            ->join('mahasiswas', 'mahasiswa_mk.mahasiswa_id', '=', 'mahasiswas.id')
            ->where('mahasiswa_mk.mata_kuliah_id', $mkId)
            ->where('mahasiswa_mk.semester_aktif', $semesterAktif)
            ->where('mahasiswa_mk.status', 'disetujui');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mahasiswaIdsQuery->where('mahasiswas.program_id', Auth::user()->program_id);
        }

        $mahasiswaIds = $mahasiswaIdsQuery->pluck('mahasiswa_mk.mahasiswa_id');

        if ($mahasiswaIds->isEmpty()) return 0;

        // CPMK + bobot untuk MK ini. LEFT JOIN karena bobot_penilaian mungkin belum diisi.
        $cpmkBobotQuery = DB::table('mata_kuliah_cpmk as mkc')
            ->leftJoin('bobot_penilaian as bp', function ($join) use ($mkId) {
                $join->on('mkc.cpmk_id', '=', 'bp.cpmk_id')
                    ->where('bp.mata_kuliah_id', '=', $mkId);
            })
            ->join('cpmk', 'mkc.cpmk_id', '=', 'cpmk.id')
            ->join('cpl', 'cpmk.cpl_id', '=', 'cpl.id')
            ->where('mkc.mata_kuliah_id', $mkId);

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $cpmkBobotQuery->where('cpl.program_id', Auth::user()->program_id);
        }

        $cpmkBobot = $cpmkBobotQuery->select(
            'mkc.cpmk_id',
            'cpmk.cpl_id', // CPL langsung dari CPMK (FK langsung)
            DB::raw('COALESCE(bp.bobot_tugas, 0) as bobot_tugas'),
            DB::raw('COALESCE(bp.bobot_uts, 0) as bobot_uts'),
            DB::raw('COALESCE(bp.bobot_uas, 0) as bobot_uas'),
            DB::raw('COALESCE(bp.bobot_partisipatif, 0) as bobot_partisipatif'),
            DB::raw('COALESCE(bp.bobot_proyek, 0) as bobot_proyek')
        )
            ->get();

        if ($cpmkBobot->isEmpty()) {
            Log::warning("OBE: Tidak ada CPMK untuk MK id={$mkId}");
            return 0;
        }

        // Pre-load semua nilai dan angkatan untuk eleminasi N+1 query
        $nilaiMap = NilaiMahasiswa::where('mata_kuliah_id', $mkId)
            ->where('semester_aktif', $semesterAktif)
            ->whereIn('mahasiswa_id', $mahasiswaIds)
            ->get()
            ->keyBy('mahasiswa_id');

        $angkatanMap = DB::table('mahasiswas')
            ->whereIn('id', $mahasiswaIds)
            ->pluck('angkatan', 'id');

        $count = 0;
        foreach ($mahasiswaIds as $mhsId) {
            $nilai = $nilaiMap->get($mhsId);

            if (!$nilai) continue; // Nilai belum diinput → skip

            $angkatan = $angkatanMap->get($mhsId);

            foreach ($cpmkBobot as $b) {
                $totalBobot = (float)($b->bobot_tugas + $b->bobot_uts + $b->bobot_uas
                    + $b->bobot_partisipatif + $b->bobot_proyek);

                if ($totalBobot > 0) {
                    $nilaiCpmk = (
                        ($nilai->nilai_tugas        * $b->bobot_tugas) +
                        ($nilai->nilai_uts           * $b->bobot_uts) +
                        ($nilai->nilai_uas           * $b->bobot_uas) +
                        ($nilai->nilai_partisipatif  * $b->bobot_partisipatif) +
                        ($nilai->nilai_proyek        * $b->bobot_proyek)
                    ) / $totalBobot;
                } else {
                    // Fallback: pakai nilai_akhir — bobot belum dikonfigurasi
                    $nilaiCpmk = (float)($nilai->nilai_akhir ?? 0);
                }

                $threshold = self::THRESHOLD_DEFAULT;

                CpmkAchievement::updateOrCreate(
                    [
                        'mahasiswa_id'   => $mhsId,
                        'mata_kuliah_id' => $mkId,
                        'cpmk_id'        => $b->cpmk_id,
                        'semester_aktif' => $semesterAktif,
                    ],
                    [
                        'angkatan'           => $angkatan,
                        'nilai_tugas'        => $nilai->nilai_tugas        ?? 0,
                        'nilai_uts'          => $nilai->nilai_uts           ?? 0,
                        'nilai_uas'          => $nilai->nilai_uas           ?? 0,
                        'nilai_partisipatif' => $nilai->nilai_partisipatif  ?? 0,
                        'nilai_proyek'       => $nilai->nilai_proyek        ?? 0,
                        'nilai_cpmk'         => round($nilaiCpmk, 2),
                        'threshold'          => $threshold,
                        'achieved'           => $nilaiCpmk >= $threshold ? 1 : 0,
                    ]
                );
                $count++;
            }
        }
        return $count;
    }

    /**
     * STEP 2 — Hitung CPL Achievement per mahasiswa untuk semester aktif.
     *
     * Strategi: gunakan cpmk.cpl_id (FK langsung) untuk memetakan CPMK → CPL.
     * Ini lebih andal dari mata_kuliah_cpl karena CPMK sudah punya cpl_id.
     *
     * nilai_cpl = AVG(nilai_cpmk) dari semua CPMK yang mapped ke CPL ini
     *             yang sudah dihitung di cpmk_achievement untuk mahasiswa ini.
     */
    public function hitungCplAchievement(int $mahasiswaId, string $semesterAktif): int
    {
        $mhsQuery = DB::table('mahasiswas')->where('id', $mahasiswaId);
        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mhsQuery->where('program_id', Auth::user()->program_id);
        }
        $mhs = $mhsQuery->select('angkatan')->first();
        if (!$mhs) return 0;
        $angkatan = $mhs->angkatan;

        // MK yang disetujui oleh mahasiswa ini di semester aktif
        $mkIdsQuery = DB::table('mahasiswa_mk')
            ->join('mata_kuliah', 'mahasiswa_mk.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->where('mahasiswa_mk.mahasiswa_id', $mahasiswaId)
            ->where('mahasiswa_mk.semester_aktif', $semesterAktif)
            ->where('mahasiswa_mk.status', 'disetujui');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mkIdsQuery->where('mata_kuliah.program_id', Auth::user()->program_id);
        }

        $mkIds = $mkIdsQuery->pluck('mahasiswa_mk.mata_kuliah_id');

        if ($mkIds->isEmpty()) return 0;

        // Ambil semua CPMK achievement yang sudah dihitung
        $cpmkAchsQuery = DB::table('cpmk_achievement as ca')
            ->join('cpmk', 'ca.cpmk_id', '=', 'cpmk.id')
            ->join('cpl', 'cpmk.cpl_id', '=', 'cpl.id')
            ->where('ca.mahasiswa_id', $mahasiswaId)
            ->where('ca.semester_aktif', $semesterAktif)
            ->whereIn('ca.mata_kuliah_id', $mkIds);

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $cpmkAchsQuery->where('cpl.program_id', Auth::user()->program_id);
        }

        $cpmkAchs = $cpmkAchsQuery->select(
            'cpmk.cpl_id',  // CPL langsung dari CPMK — sumber kebenaran
            'ca.nilai_cpmk',
            'ca.achieved'
        )
            ->get();

        if ($cpmkAchs->isEmpty()) return 0;

        // Group by CPL → hitung rata-rata nilai_cpmk per CPL
        $byGpl = $cpmkAchs->groupBy('cpl_id');

        $count = 0;
        foreach ($byGpl as $cplId => $rows) {
            $jumlahCpmk     = $rows->count();
            $jumlahAchieved = $rows->where('achieved', 1)->count();
            $rataNilai      = round($rows->avg('nilai_cpmk'), 2);

            $threshold = CplTarget::where('cpl_id', $cplId)->value('threshold')
                ?? self::THRESHOLD_DEFAULT;

            CplAchievement::updateOrCreate(
                [
                    'mahasiswa_id'   => $mahasiswaId,
                    'cpl_id'         => $cplId,
                    'semester_aktif' => $semesterAktif,
                ],
                [
                    'tahun_akademik'  => $semesterAktif,
                    'angkatan'        => $angkatan,
                    'nilai_cpl'       => $rataNilai,
                    'achieved'        => $rataNilai >= $threshold ? 1 : 0,
                    'threshold'       => $threshold,
                    'jumlah_cpmk'     => $jumlahCpmk,
                    'jumlah_achieved' => $jumlahAchieved,
                ]
            );
            $count++;
        }
        return $count;
    }

    /**
     * STEP 3 — Rekap Evaluasi per Angkatan per CPL.
     *
     * Sumber: cpl_achievement (sudah dihitung di Step 2).
     * pct_lulus = jumlah mahasiswa achieved / total × 100
     */
    public function hitungEvaluasiCohort(int $angkatan, string $semesterAktif): void
    {
        // Hanya CPL yang ada data achievement-nya untuk angkatan ini
        $cplIdsQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id')
            ->where('ca.angkatan', $angkatan)
            ->where('ca.semester_aktif', $semesterAktif);

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $cplIdsQuery->where('cpl.program_id', Auth::user()->program_id);
        }

        $cplIds = $cplIdsQuery->distinct()->pluck('ca.cpl_id');

        foreach ($cplIds as $cplId) {
            $rows = CplAchievement::where('cpl_id', $cplId)
                ->where('angkatan', $angkatan)
                ->where('semester_aktif', $semesterAktif)
                ->get();

            if ($rows->isEmpty()) continue;

            $total    = $rows->count();
            $tercapai = $rows->where('achieved', true)->count();
            $rata     = round($rows->avg('nilai_cpl'), 2);
            $pct      = $total > 0 ? round($tercapai / $total * 100, 2) : 0;
            $target   = CplTarget::where('cpl_id', $cplId)->value('target_pct')
                ?? self::TARGET_PCT_DEFAULT;

            $status = match (true) {
                $pct >= $target           => 'tercapai',
                $pct >= ($target * 0.80)  => 'perlu_monitoring',
                default                   => 'belum_tercapai',
            };

            EvaluasiCohort::updateOrCreate(
                [
                    'cpl_id'         => $cplId,
                    'angkatan'       => $angkatan,
                    'tahun_akademik' => $semesterAktif,
                ],
                [
                    'total_mahasiswa' => $total,
                    'jumlah_tercapai' => $tercapai,
                    'rata_nilai_cpl'  => $rata,
                    'pct_lulus'       => $pct,
                    'target_capaian'  => $target,
                    'status_target'   => $status,
                ]
            );
        }
    }

    /**
     * Full pipeline untuk 1 MK: CPMK → CPL → Cohort
     */
    public function runFullPipeline(int $mkId, string $semesterAktif): array
    {
        $cpmkCount = $this->hitungCpmkAchievement($mkId, $semesterAktif);

        $mahasiswaIdsQuery = DB::table('mahasiswa_mk')
            ->join('mahasiswas', 'mahasiswa_mk.mahasiswa_id', '=', 'mahasiswas.id')
            ->where('mahasiswa_mk.mata_kuliah_id', $mkId)
            ->where('mahasiswa_mk.semester_aktif', $semesterAktif)
            ->where('mahasiswa_mk.status', 'disetujui');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mahasiswaIdsQuery->where('mahasiswas.program_id', Auth::user()->program_id);
        }

        $mahasiswaIds = $mahasiswaIdsQuery->pluck('mahasiswa_mk.mahasiswa_id');

        $cplCount = 0;
        foreach ($mahasiswaIds as $mhsId) {
            $cplCount += $this->hitungCplAchievement($mhsId, $semesterAktif);
        }

        // Rekap cohort untuk setiap angkatan yang terlibat
        $angkatans = DB::table('mahasiswas')
            ->whereIn('id', $mahasiswaIds)
            ->whereNotNull('angkatan')
            ->distinct()->pluck('angkatan');

        foreach ($angkatans as $angkatan) {
            $this->hitungEvaluasiCohort($angkatan, $semesterAktif);
        }

        $mkNama = DB::table('mata_kuliah')->where('id', $mkId)->value('nama');

        return [
            'status'          => 'ok',
            'mk'              => $mkNama,
            'mk_id'           => $mkId,
            'semester_aktif'  => $semesterAktif,
            'cpmk_records'    => $cpmkCount,
            'cpl_records'     => $cplCount,
            'mahasiswa_count' => $mahasiswaIds->count(),
        ];
    }

    /**
     * Hitung semua MK yang punya mahasiswa enrolled di semester aktif.
     */
    public function runAllPipeline(string $semesterAktif): array
    {
        $allowedMkIds = MataKuliah::pluck('id');

        $mkIds = DB::table('mahasiswa_mk')
            ->where('semester_aktif', $semesterAktif)
            ->where('status', 'disetujui')
            ->whereIn('mata_kuliah_id', $allowedMkIds)
            ->distinct()
            ->pluck('mata_kuliah_id');

        $totalCpmk = 0;
        $totalCpl  = 0;
        $processed = 0;

        foreach ($mkIds as $mkId) {
            $result = $this->runFullPipeline($mkId, $semesterAktif);
            $totalCpmk += $result['cpmk_records'];
            $totalCpl  += $result['cpl_records'];
            $processed++;
        }

        // Auto-generate CQI action plans for CPLs below threshold
        $cqiGenerated = $this->autoGenerateCqiActions($semesterAktif);

        return [
            'status'         => 'ok',
            'semester_aktif' => $semesterAktif,
            'mk_processed'   => $processed,
            'cpmk_records'   => $totalCpmk,
            'cpl_records'    => $totalCpl,
            'total_records'  => $totalCpmk + $totalCpl,
            'cqi_generated'  => $cqiGenerated,
        ];
    }

    /**
     * Auto-generate CQI action plans for CPLs below threshold.
     * Only inserts if no open/in_progress action already exists for the same
     * (cpl_id, angkatan, tahun_akademik) combination.
     * Called at end of runAllPipeline() — never modifies existing CQI records.
     */
    private function autoGenerateCqiActions(string $semesterAktif): int
    {
        if (!Schema::hasTable('cqi_actions')) {
            return 0;
        }

        // Aggregate CPL achievement per cohort per TA
        $belowThresholdQuery = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id')
            ->where('ca.semester_aktif', $semesterAktif);

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $belowThresholdQuery->where('cpl.program_id', Auth::user()->program_id);
        }

        $belowThreshold = $belowThresholdQuery
            ->selectRaw('ca.cpl_id, ca.angkatan, ca.tahun_akademik, AVG(ca.nilai_cpl) as nilai, AVG(ca.threshold) as threshold')
            ->groupBy('ca.cpl_id', 'ca.angkatan', 'ca.tahun_akademik')
            ->havingRaw('AVG(ca.nilai_cpl) < AVG(ca.threshold)')
            ->get();

        $generated = 0;

        foreach ($belowThreshold as $row) {
            // Skip if open or in_progress action already exists
            $exists = DB::table('cqi_actions')
                ->where('cpl_id', $row->cpl_id)
                ->where('angkatan', $row->angkatan)
                ->where('tahun_akademik', $row->tahun_akademik)
                ->whereIn('status', ['open', 'in_progress'])
                ->exists();

            if ($exists) {
                continue;
            }

            $nextSemester = $this->nextSemesterLabel($semesterAktif);

            DB::table('cqi_actions')->insert([
                'cpl_id'            => $row->cpl_id,
                'angkatan'          => $row->angkatan,
                'tahun_akademik'    => $row->tahun_akademik,
                'nilai_cpl'         => round($row->nilai, 2),
                'threshold'         => round($row->threshold, 2),
                'masalah'           => 'CPL belum mencapai target',
                'rencana_perbaikan' => 'Review metode pembelajaran dan penilaian',
                'pic'               => 'Kaprodi',
                'target_semester'   => $nextSemester,
                'status'            => 'open',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $generated++;
        }

        return $generated;
    }

    // =========================================================================
    // SYNC FROM nilai_sub_cpmk
    // Flow: nilai_sub_cpmk → (sub_cpmk_bobot) → nilai_subcpmk
    //       → nilai_mahasiswa (aggregated per mk) → cpmk_achievement → cpl_achievement
    // =========================================================================

    /**
     * Step 0: Recalculate nilai_subcpmk for all rows in nilai_sub_cpmk
     * that belong to the given MK, using sub_cpmk_bobot weights.
     * Falls back to equal 20% per component when no bobot is configured.
     */
    public function recalcNilaiSubCpmk(int $mkId): int
    {
        return DB::statement("
            UPDATE nilai_sub_cpmk nsc
            JOIN sub_cpmk sc ON sc.id = nsc.sub_cpmk_id
            JOIN mata_kuliah_sub_cpmk msc
                ON msc.sub_cpmk_id = sc.id AND msc.mata_kuliah_id = ?
            LEFT JOIN sub_cpmk_bobot b ON b.sub_cpmk_id = sc.id
            SET nsc.nilai_subcpmk = ROUND(
                (nsc.tugas        * COALESCE(b.bobot_tugas,        20) +
                 nsc.uts          * COALESCE(b.bobot_uts,          20) +
                 nsc.uas          * COALESCE(b.bobot_uas,          20) +
                 nsc.partisipatif * COALESCE(b.bobot_partisipatif, 20) +
                 nsc.proyek       * COALESCE(b.bobot_proyek,       20)) / 100, 2),
            nsc.updated_at = NOW()
        ", [$mkId]) ? 1 : 0;
    }

    /**
     * Step 0b: Aggregate nilai_sub_cpmk → nilai_mahasiswa.
     * Per mahasiswa per MK: average each komponen across all sub_cpmks in MK,
     * then compute nilai_akhir as average of nilai_subcpmk.
     */
    public function aggregateSubCpmkToNilaiMahasiswa(int $mkId, string $semesterAktif): int
    {
        $rows = DB::select("
            SELECT
                nsc.mahasiswa_id,
                ROUND(AVG(nsc.tugas), 2)         AS nilai_tugas,
                ROUND(AVG(nsc.uts), 2)           AS nilai_uts,
                ROUND(AVG(nsc.uas), 2)           AS nilai_uas,
                ROUND(AVG(nsc.partisipatif), 2)  AS nilai_partisipatif,
                ROUND(AVG(nsc.proyek), 2)        AS nilai_proyek,
                ROUND(AVG(nsc.nilai_subcpmk), 2) AS nilai_akhir
            FROM nilai_sub_cpmk nsc
            JOIN sub_cpmk sc ON sc.id = nsc.sub_cpmk_id
            JOIN mata_kuliah_sub_cpmk msc
                ON msc.sub_cpmk_id = sc.id AND msc.mata_kuliah_id = ?
            GROUP BY nsc.mahasiswa_id
        ", [$mkId]);

        $count = 0;
        foreach ($rows as $r) {
            $nilaiAkhir = (float)$r->nilai_akhir;
            $grade = match (true) {
                $nilaiAkhir >= 80 => 'A',
                $nilaiAkhir >= 70 => 'B',
                $nilaiAkhir >= 56 => 'C',
                $nilaiAkhir >= 40 => 'D',
                default           => 'E',
            };

            DB::table('nilai_mahasiswa')->updateOrInsert(
                [
                    'mahasiswa_id'   => $r->mahasiswa_id,
                    'mata_kuliah_id' => $mkId,
                    'semester_aktif' => $semesterAktif,
                ],
                [
                    'nilai_tugas'        => $r->nilai_tugas,
                    'nilai_uts'          => $r->nilai_uts,
                    'nilai_uas'          => $r->nilai_uas,
                    'nilai_partisipatif' => $r->nilai_partisipatif,
                    'nilai_proyek'       => $r->nilai_proyek,
                    'nilai_akhir'        => $nilaiAkhir,
                    'grade'              => $grade,
                    'lulus'              => $nilaiAkhir >= self::THRESHOLD_DEFAULT,
                    'updated_at'         => now(),
                    'created_at'         => now(),
                ]
            );
            $count++;
        }
        return $count;
    }

    /**
     * Full sync pipeline starting from nilai_sub_cpmk.
     *
     * nilai_sub_cpmk
     *   → (sub_cpmk_bobot) → nilai_subcpmk      [recalcNilaiSubCpmk]
     *   → nilai_mahasiswa                        [aggregateSubCpmkToNilaiMahasiswa]
     *   → cpmk_achievement                       [hitungCpmkAchievement]
     *   → cpl_achievement                        [hitungCplAchievement]
     */
    public function syncFromSubCpmk(int $mkId, string $semesterAktif): array
    {
        $this->recalcNilaiSubCpmk($mkId);

        $mhsCount = $this->aggregateSubCpmkToNilaiMahasiswa($mkId, $semesterAktif);

        // Ensure mahasiswa are enrolled (mark disetujui if missing, so pipeline finds them)
        if ($mhsCount > 0) {
            $progId = (Auth::check() && Auth::user()->role !== 'admin') ? Auth::user()->program_id : null;
            if ($progId) {
                $mhsIds = DB::select("
                    SELECT DISTINCT nsc.mahasiswa_id
                    FROM nilai_sub_cpmk nsc
                    JOIN sub_cpmk sc ON sc.id = nsc.sub_cpmk_id
                    JOIN mata_kuliah_sub_cpmk msc ON msc.sub_cpmk_id = sc.id AND msc.mata_kuliah_id = ?
                    JOIN mahasiswas m ON nsc.mahasiswa_id = m.id
                    WHERE m.program_id = ?
                ", [$mkId, $progId]);
            } else {
                $mhsIds = DB::select("
                    SELECT DISTINCT nsc.mahasiswa_id
                    FROM nilai_sub_cpmk nsc
                    JOIN sub_cpmk sc ON sc.id = nsc.sub_cpmk_id
                    JOIN mata_kuliah_sub_cpmk msc ON msc.sub_cpmk_id = sc.id AND msc.mata_kuliah_id = ?
                ", [$mkId]);
            }

            foreach ($mhsIds as $row) {
                DB::table('mahasiswa_mk')->updateOrInsert(
                    [
                        'mahasiswa_id'   => $row->mahasiswa_id,
                        'mata_kuliah_id' => $mkId,
                        'semester_aktif' => $semesterAktif,
                    ],
                    [
                        'status'     => 'disetujui',
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        $result = $this->runFullPipeline($mkId, $semesterAktif);
        $result['source']           = 'nilai_sub_cpmk';
        $result['synced_mahasiswa'] = $mhsCount;

        return $result;
    }

    /** Compute next semester label from a TA string like "2025/2026". */
    private function nextSemesterLabel(string $ta): string
    {
        if (preg_match('/(\d{4})\/(\d{4})/', $ta, $m)) {
            return 'Ganjil ' . ((int)$m[1] + 1) . '/' . ((int)$m[2] + 1);
        }
        return 'Semester Berikutnya';
    }

    /**
     * Dashboard metrics: ringkasan CPL + CPMK + cohort grid.
     * Menggunakan tabel real: cpl, cpmk, cpl_achievement, cpmk_achievement,
     * evaluasi_cohort, bobot_penilaian.
     */
    public function getDashboardMetrics(string $semesterAktif): array
    {
        $programId = (Auth::check() && Auth::user()->role !== 'admin') ? Auth::user()->program_id : null;

        // --- CPL summary ---
        $cplStats = DB::table('cpl_achievement as ca')
            ->join('cpl', 'ca.cpl_id', '=', 'cpl.id')
            ->where('ca.semester_aktif', $semesterAktif)
            ->when($programId, fn($q) => $q->where('cpl.program_id', $programId))
            ->select(
                'cpl.id',
                'cpl.kode',
                'cpl.deskripsi',
                'cpl.kategori',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(ca.achieved) as tercapai'),
                DB::raw('ROUND(AVG(ca.nilai_cpl), 2) as rata_nilai'),
                DB::raw('ROUND(SUM(ca.achieved) / COUNT(*) * 100, 2) as pct_achieved')
            )
            ->groupBy('cpl.id', 'cpl.kode', 'cpl.deskripsi', 'cpl.kategori')
            ->orderBy('cpl.kode')
            ->get();

        // --- CPMK summary (via cpmk.cpl_id — FK langsung, bukan mata_kuliah_cpl) ---
        $cpmkStats = DB::table('cpmk_achievement as ca')
            ->join('cpmk', 'ca.cpmk_id', '=', 'cpmk.id')
            ->join('cpl', 'cpmk.cpl_id', '=', 'cpl.id')   // CPL dari CPMK langsung
            ->join('mata_kuliah', 'ca.mata_kuliah_id', '=', 'mata_kuliah.id')
            ->where('ca.semester_aktif', $semesterAktif)
            ->when($programId, fn($q) => $q->where('cpl.program_id', $programId))
            ->select(
                'cpmk.id',
                'cpmk.kode',
                'cpmk.deskripsi',
                'cpl.kode as cpl_kode',
                'mata_kuliah.nama as mk_nama',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(ca.achieved) as tercapai'),
                DB::raw('ROUND(AVG(ca.nilai_cpmk), 2) as rata_nilai'),
                DB::raw('ROUND(SUM(ca.achieved) / COUNT(*) * 100, 2) as pct_achieved')
            )
            ->groupBy('cpmk.id', 'cpmk.kode', 'cpmk.deskripsi', 'cpl.kode', 'mata_kuliah.id', 'mata_kuliah.nama')
            ->orderBy('cpl.kode')
            ->orderBy('cpmk.kode')
            ->get();

        // --- Cohort grid ---
        $cohortRows = DB::table('evaluasi_cohort as ec')
            ->join('cpl', 'ec.cpl_id', '=', 'cpl.id')
            ->where('ec.tahun_akademik', $semesterAktif)
            ->when($programId, fn($q) => $q->where('cpl.program_id', $programId))
            ->select('ec.*', 'cpl.kode as cpl_kode', 'cpl.deskripsi as cpl_deskripsi')
            ->orderBy('angkatan')
            ->orderBy('cpl.kode')
            ->get();

        $cohortByAngkatan = $cohortRows->groupBy('angkatan');

        // --- CPL list (untuk header tabel cohort) ---
        // ProgramScope otomatis filter by program_id
        $cplList = Cpl::orderBy('kode')->get();

        // --- Target per CPL ---
        $cplTargets = DB::table('cpl_target as ct')
            ->join('cpl', 'ct.cpl_id', '=', 'cpl.id')
            ->when($programId, fn($q) => $q->where('cpl.program_id', $programId))
            ->pluck('ct.target_pct', 'ct.cpl_id');

        return compact('cplStats', 'cpmkStats', 'cohortByAngkatan', 'cplList', 'cplTargets');
    }

    /**
     * Convenience wrapper: hitung CPL Achievement untuk semua mahasiswa di 1 MK.
     * Ini dipanggil setelah hitungCpmkAchievement() selesai.
     *
     * @return int jumlah cpl_achievement records yang dibuat/update
     */
    public function hitungCpl(int $mkId, string $semesterAktif): int
    {
        $mahasiswaIdsQuery = DB::table('mahasiswa_mk')
            ->join('mahasiswas', 'mahasiswa_mk.mahasiswa_id', '=', 'mahasiswas.id')
            ->where('mahasiswa_mk.mata_kuliah_id', $mkId)
            ->where('mahasiswa_mk.semester_aktif', $semesterAktif)
            ->where('mahasiswa_mk.status', 'disetujui');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mahasiswaIdsQuery->where('mahasiswas.program_id', Auth::user()->program_id);
        }

        $mahasiswaIds = $mahasiswaIdsQuery->pluck('mahasiswa_mk.mahasiswa_id');

        if ($mahasiswaIds->isEmpty()) return 0;

        $total = 0;
        foreach ($mahasiswaIds as $mhsId) {
            $total += $this->hitungCplAchievement($mhsId, $semesterAktif);
        }
        return $total;
    }

    /**
     * Debug SQL: kembalikan query string lengkap untuk audit dan debugging.
     * Tidak mengeksekusi kalkulasi, hanya mengembalikan SQL.
     */
    public function getDebugSql(string $semesterAktif): array
    {
        $cplAggregationSql = "
-- STEP 2: CPL aggregation (per mahasiswa, per semester)
-- Sumber: cpmk_achievement JOIN cpmk (via cpmk.cpl_id)
SELECT
    cpmk.cpl_id,
    ROUND(AVG(ca.nilai_cpmk), 2)         AS nilai_cpl,
    COUNT(*)                              AS jumlah_cpmk,
    SUM(CASE WHEN ca.achieved = 1 THEN 1 ELSE 0 END) AS jumlah_achieved
FROM cpmk_achievement ca
JOIN cpmk ON ca.cpmk_id = cpmk.id
WHERE ca.mahasiswa_id = :mahasiswa_id
  AND ca.semester_aktif = '{$semesterAktif}'
  AND ca.mata_kuliah_id IN (
    SELECT mata_kuliah_id FROM mahasiswa_mk
    WHERE mahasiswa_id = :mahasiswa_id
      AND semester_aktif = '{$semesterAktif}'
      AND status = 'disetujui'
  )
GROUP BY cpmk.cpl_id;
        ";

        $cohortEvalSql = "
-- STEP 3: Evaluasi Cohort (per angkatan per CPL)
SELECT
    ca.cpl_id,
    ca.angkatan,
    COUNT(*)                                              AS total_mahasiswa,
    SUM(CASE WHEN ca.achieved = 1 THEN 1 ELSE 0 END)     AS jumlah_tercapai,
    ROUND(SUM(CASE WHEN ca.achieved = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) AS pct_lulus,
    ROUND(AVG(ca.nilai_cpl), 2)                           AS rata_nilai_cpl,
    COALESCE(ct.target_pct, 60.0)                         AS target_capaian
FROM cpl_achievement ca
LEFT JOIN cpl_target ct ON ct.cpl_id = ca.cpl_id
WHERE ca.semester_aktif = '{$semesterAktif}'
GROUP BY ca.cpl_id, ca.angkatan;
        ";

        $dashboardSummarySql = "
-- STEP 4: Dashboard summary (CPL stats)
SELECT
    cpl.id,
    cpl.kode,
    cpl.deskripsi,
    cpl.kategori,
    COUNT(*)                                                     AS total_mahasiswa,
    SUM(ca.achieved)                                             AS tercapai,
    ROUND(AVG(ca.nilai_cpl), 2)                                  AS rata_nilai,
    ROUND(SUM(ca.achieved) / COUNT(*) * 100, 2)                  AS pct_achieved
FROM cpl_achievement ca
JOIN cpl ON ca.cpl_id = cpl.id
WHERE ca.semester_aktif = '{$semesterAktif}'
GROUP BY cpl.id, cpl.kode, cpl.deskripsi, cpl.kategori
ORDER BY cpl.kode;

-- STEP 4b: Cohort grid for dashboard
SELECT ec.*, cpl.kode, cpl.deskripsi
FROM evaluasi_cohort ec
JOIN cpl ON ec.cpl_id = cpl.id
WHERE ec.tahun_akademik = '{$semesterAktif}'
ORDER BY angkatan, cpl.kode;

-- STEP 4c: CPL chart data (maks vs sementara)
SELECT
    cpl.kode,
    cpl.total_skor_maks                         AS skor_maksimal,
    ROUND(AVG(ca.nilai_cpl), 2)                 AS skor_sementara,
    ROUND(cpl.total_skor_maks - AVG(ca.nilai_cpl), 2) AS gap
FROM cpl
LEFT JOIN cpl_achievement ca ON ca.cpl_id = cpl.id AND ca.semester_aktif = '{$semesterAktif}'
GROUP BY cpl.id, cpl.kode, cpl.total_skor_maks
ORDER BY cpl.kode;
        ";

        return [
            'semester_aktif'     => $semesterAktif,
            'cpl_aggregation'    => trim($cplAggregationSql),
            'cohort_evaluation'  => trim($cohortEvalSql),
            'dashboard_summary'  => trim($dashboardSummarySql),
        ];
    }

    /**
     * Data trend CPL per angkatan (untuk chart atau tabel rekap).
     */
    public function getCplTrend(int $cplId): array
    {
        return DB::table('evaluasi_cohort')
            ->where('cpl_id', $cplId)
            ->orderBy('angkatan')
            ->select('angkatan', 'tahun_akademik', 'rata_nilai_cpl', 'pct_lulus', 'status_target')
            ->get()
            ->toArray();
    }

    /**
     * Ringkasan per MK: capaian CPMK + link ke laporan evaluasi jika ada.
     */
    public function getMkSummary(int $mkId, string $semesterAktif): array
    {
        $mkQuery = DB::table('mata_kuliah')->where('id', $mkId);
        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mkQuery->where('program_id', Auth::user()->program_id);
        }
        $mk = $mkQuery->first();

        if (!$mk) {
            return [
                'mk' => null,
                'cpmk_summary' => collect(),
                'total_mahasiswa' => 0
            ];
        }

        $cpmkSummaryQuery = DB::table('cpmk_achievement as ca')
            ->join('cpmk', 'ca.cpmk_id', '=', 'cpmk.id')
            ->join('cpl', 'cpmk.cpl_id', '=', 'cpl.id')
            ->where('ca.mata_kuliah_id', $mkId)
            ->where('ca.semester_aktif', $semesterAktif);

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $cpmkSummaryQuery->where('cpl.program_id', Auth::user()->program_id);
        }

        $cpmkSummary = $cpmkSummaryQuery->select(
            'cpmk.id',
            'cpmk.kode',
            'cpmk.deskripsi',
            'cpl.kode as cpl_kode',
            DB::raw('COUNT(*) as total_mahasiswa'),
            DB::raw('SUM(ca.achieved) as jumlah_tercapai'),
            DB::raw('ROUND(AVG(ca.nilai_cpmk), 2) as rata_nilai'),
            DB::raw('ROUND(SUM(ca.achieved)/COUNT(*)*100, 2) as pct_lulus')
        )
            ->groupBy('cpmk.id', 'cpmk.kode', 'cpmk.deskripsi', 'cpl.kode')
            ->orderBy('cpmk.kode')
            ->get();

        $totalMhsQuery = DB::table('mahasiswa_mk')
            ->join('mahasiswas', 'mahasiswa_mk.mahasiswa_id', '=', 'mahasiswas.id')
            ->where('mahasiswa_mk.mata_kuliah_id', $mkId)
            ->where('mahasiswa_mk.semester_aktif', $semesterAktif)
            ->where('mahasiswa_mk.status', 'disetujui');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $totalMhsQuery->where('mahasiswas.program_id', Auth::user()->program_id);
        }

        $totalMhs = $totalMhsQuery->count();

        return [
            'mk'          => $mk,
            'cpmk_summary' => $cpmkSummary,
            'total_mahasiswa' => $totalMhs,
        ];
    }
}
