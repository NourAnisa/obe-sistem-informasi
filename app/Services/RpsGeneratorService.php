<?php

namespace App\Services;

use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * RpsGeneratorService
 *
 * Gathers and structures all data needed to render a complete RPS
 * (Rencana Pembelajaran Semester) following the UNISM template.
 */
class RpsGeneratorService
{
    public const TOTAL_WEEKS   = 16;
    public const WEEK_UTS      = 8;
    public const WEEK_UAS      = 16;
    public const CONTENT_WEEKS = 14; // 16 - 2 (UTS + UAS)

    /** Build the complete RPS data structure for a mata kuliah */
    public function generate(MataKuliah $mk): array
    {
        $mk->loadMissing([
            'cpls',
            'cpmks.cpl',
            'cpmks.subCpmks',
            'bahanKajians',
            'bobotPenilaians.cpmk',
            'teknikPenilaians.cpmk',
            'rpsReferensis',
            'rpsDetail',
            'rpsPertemuans',
            'publikasiDosen.dosen',
        ]);

        // Merge publikasi dosen as supplementary references (for PDF/Word)
        $referensiPendukung = $mk->rpsReferensis->where('jenis', 'pendukung')->sortBy('urutan')->values();
        $publikasiPendukung = $mk->publikasiDosen->sortByDesc('tahun')->map(function ($pub) {
            // Fake a citation-compatible object
            $parts = [];
            if ($pub->authors) $parts[] = $pub->authors;
            if ($pub->tahun)   $parts[] = "({$pub->tahun})";
            if ($pub->judul)   $parts[] = "<em>{$pub->judul}</em>";
            if ($pub->sumber)  $parts[] = $pub->sumber;
            if ($pub->link)    $parts[] = "<a href='{$pub->link}'>{$pub->link}</a>";
            $pub->citation = implode('. ', $parts);
            return $pub;
        });

        return [
            'mk'                => $mk,
            'header'            => $this->buildHeader($mk),
            'cpls'              => $mk->cpls->sortBy('kode')->values(),
            'cpmks'             => $mk->cpmks->sortBy('kode')->values(),
            'subCpmks'          => $this->getAllSubCpmks($mk),
            'bahanKajians'      => $mk->bahanKajians->sortBy('kode')->values(),
            'referensiUtama'    => $mk->rpsReferensis->where('jenis', 'utama')->sortBy('urutan')->values(),
            'referensiPendukung' => $referensiPendukung->merge($publikasiPendukung)->values(),
            'blueprint'         => $this->buildBlueprint($mk),
            'jadwalMingguan'    => $this->buildJadwalMingguan($mk),
            'kontrak'           => $this->buildKontrak($mk),
            'rencanaTugas'      => $this->buildRencanaTugas($mk),
            'detail'            => $mk->rpsDetail,
            'pertemuan'         => $mk->rpsPertemuans,
            'generatedAt'       => now(),
        ];
    }

    // ──────────────────────────────────────────────────────
    // BAGIAN 1: HEADER
    // ──────────────────────────────────────────────────────
    private function buildHeader(MataKuliah $mk): array
    {
        $kaprodi = User::where('role', 'kaprodi')->first();

        return [
            'universitas'    => config('obe.universitas', 'Universitas Sari Mulia'),
            'fakultas'       => config('obe.fakultas', 'Sains dan Teknologi'),
            'prodi'          => config('obe.prodi', 'Sistem Informasi'),
            'jenjang'        => config('obe.jenjang', 'S1'),
            'tahun_akademik' => config('obe.tahun_akademik', '2025/2026'),
            'kaprodi'        => $kaprodi?->name ?? config('obe.kaprodi', 'Nor Anisa, S.Kom., M.Kom.'),
            'nik_kaprodi'    => $kaprodi?->nik ?? config('obe.nik_kaprodi', ''),
            'nuptk_kaprodi'  => $kaprodi?->nuptk ?? '',
            'nama_mk'        => $mk->nama,
            'nama_mk_en'     => $mk->nama_en ?? null,
            'kode_mk'        => $mk->kode,
            'sks'            => $mk->sks,
            'sks_teori'      => $mk->sks_teori,
            'sks_praktikum'  => $mk->sks_praktikum,
            'semester'       => $mk->semester,
            'kategori'       => $mk->kategori,
            'pjmk'           => $mk->pjmk,
            'deskripsi'      => $mk->deskripsi,
            'prasyarat'      => $mk->prasyarat ?? '–',
            'tanggal'        => now()->locale('id')->isoFormat('D MMMM YYYY'),
            'tanggal_en'     => now()->locale('en')->isoFormat('MMMM D, YYYY'),
            'is_mbkm'        => $mk->is_mbkm,
        ];
    }

    // ──────────────────────────────────────────────────────
    // HELPER: Flatten all SubCPMKs for a MK
    // ──────────────────────────────────────────────────────
    private function getAllSubCpmks(MataKuliah $mk): Collection
    {
        return $mk->cpmks
            ->sortBy('kode')
            ->flatMap(function ($cpmk) {
                return $cpmk->subCpmks->sortBy('kode')->each(
                    fn($sub) => $sub->setRelation('cpmk', $cpmk)
                );
            })
            ->values();
    }

    // ──────────────────────────────────────────────────────
    // BAGIAN 8: BLUEPRINT ASESMEN CPL–CPMK
    // ──────────────────────────────────────────────────────
    private function buildBlueprint(MataKuliah $mk): array
    {
        $rows       = [];
        $grandTotal = 0;

        foreach ($mk->cpmks->sortBy('kode') as $cpmk) {
            $bobot  = $mk->bobotPenilaians->firstWhere('cpmk_id', $cpmk->id);
            $teknik = $mk->teknikPenilaians->firstWhere('cpmk_id', $cpmk->id);

            if (! $bobot) continue;

            $entries = [
                [
                    'aktif'    => $teknik?->has_partisipatif && $bobot->bobot_partisipatif > 0,
                    'basis'    => 'Aktivitas Partisipatif',
                    'komponen' => 'Partisipasi & Keaktifan Kelas',
                    'metode'   => 'Observasi, Penilaian Diri',
                    'bobot'    => $bobot->bobot_partisipatif,
                ],
                [
                    'aktif'    => $teknik?->has_proyek && $bobot->bobot_proyek > 0,
                    'basis'    => 'Hasil Proyek / Produk',
                    'komponen' => 'Proyek / Case Study',
                    'metode'   => 'Penilaian Produk, Rubrik Holistik',
                    'bobot'    => $bobot->bobot_proyek,
                ],
                [
                    'aktif'    => $teknik?->has_tugas && $bobot->bobot_tugas > 0,
                    'basis'    => 'Tugas Terstruktur',
                    'komponen' => 'Tugas Individu / Kelompok',
                    'metode'   => 'Tes Tertulis / Laporan',
                    'bobot'    => $bobot->bobot_tugas,
                ],
                [
                    'aktif'    => $teknik?->has_uts && $bobot->bobot_uts > 0,
                    'basis'    => 'Ujian Tengah Semester',
                    'komponen' => 'Tes Tertulis UTS',
                    'metode'   => 'Tes Esai / Pilihan Ganda',
                    'bobot'    => $bobot->bobot_uts,
                ],
                [
                    'aktif'    => $teknik?->has_uas && $bobot->bobot_uas > 0,
                    'basis'    => 'Ujian Akhir Semester',
                    'komponen' => 'Tes Tertulis UAS',
                    'metode'   => 'Tes Esai / Pilihan Ganda',
                    'bobot'    => $bobot->bobot_uas,
                ],
            ];

            foreach ($entries as $e) {
                if (! $e['aktif']) continue;
                $key = $e['basis'] . '||' . $e['komponen'];
                if (isset($rows[$key])) {
                    $rows[$key]['cpmks'][]  = $cpmk->kode;
                    $rows[$key]['bobot']   += $e['bobot'];
                } else {
                    $rows[$key] = [
                        'basis'    => $e['basis'],
                        'komponen' => $e['komponen'],
                        'cpmks'    => [$cpmk->kode],
                        'metode'   => $e['metode'],
                        'bobot'    => $e['bobot'],
                    ];
                }
                $grandTotal += $e['bobot'];
            }
        }

        // Normalize cpmks array to string and compute rowspans by basis
        $finalRows  = [];
        $basisCount = [];
        foreach ($rows as $row) {
            $basis = $row['basis'];
            $basisCount[$basis] = ($basisCount[$basis] ?? 0) + 1;
        }
        $basisSeen = [];
        foreach (array_values($rows) as $row) {
            $basis    = $row['basis'];
            $rowspan  = 0;
            if (! isset($basisSeen[$basis])) {
                $rowspan = $basisCount[$basis];
                $basisSeen[$basis] = true;
            }
            $finalRows[] = [
                'basis'    => $basis,
                'rowspan'  => $rowspan,
                'komponen' => $row['komponen'],
                'cpmk'     => implode(', ', array_unique($row['cpmks'])),
                'deskripsi' => '',
                'metode'   => $row['metode'],
                'bobot'    => $row['bobot'],
            ];
        }

        // Build rangkuman per basis
        $rangkuman = [];
        foreach (array_keys($basisCount) as $basis) {
            $bobot = array_sum(array_column(
                array_filter($finalRows, fn($r) => $r['basis'] === $basis),
                'bobot'
            ));
            $rangkuman[] = [
                'kategori'   => $basis,
                'bobot'      => $bobot,
                'keterangan' => '',
            ];
        }

        return [
            'rows'       => $finalRows,
            'rangkuman'  => $rangkuman,
            'grandTotal' => $grandTotal,
        ];
    }

    // ──────────────────────────────────────────────────────
    // BAGIAN 9: RENCANA PEMBELAJARAN MINGGUAN (16 Minggu)
    // ──────────────────────────────────────────────────────
    private function buildJadwalMingguan(MataKuliah $mk): array
    {
        $subCpmks  = $this->getAllSubCpmks($mk);
        $bkList    = $mk->bahanKajians->sortBy('kode')->values();
        $bkCount   = $bkList->count();

        // Content weeks: all except UTS (8) and UAS (16)
        $contentWeeks = collect(range(1, self::TOTAL_WEEKS))
            ->reject(fn($w) => in_array($w, [self::WEEK_UTS, self::WEEK_UAS]))
            ->values(); // 14 weeks

        $weekCount   = $contentWeeks->count();
        $subCount    = $subCpmks->count();

        // Distribute SubCPMKs across content weeks
        $assignments = array_fill(0, $weekCount, []);
        if ($subCount > 0) {
            foreach ($subCpmks as $idx => $sub) {
                $weekIdx = (int) floor(($idx / $subCount) * $weekCount);
                $weekIdx = min($weekIdx, $weekCount - 1);
                $assignments[$weekIdx][] = $sub;
            }
        }

        $defaultMethods = [
            'Ceramah, Tanya Jawab',
            'Ceramah, Diskusi Interaktif',
            'Diskusi Kelompok, Presentasi',
            'Praktikum, Demonstrasi',
            'Studi Kasus, Diskusi',
            'Problem Based Learning (PBL)',
            'Ceramah, Latihan Soal',
            'Project Based Learning (PjBL)',
            'Collaborative Learning',
            'Flipped Classroom',
            'Ceramah, Simulasi',
            'Workshop, Praktikum',
            'Diskusi, Presentasi Kelompok',
            'Ceramah, Review & Evaluasi',
        ];

        $schedule = [];
        foreach ($contentWeeks as $pos => $weekNum) {
            $subs    = $assignments[$pos] ?? [];
            $bkIdx   = $bkCount > 0 ? ($pos % $bkCount) : 0;
            $bk      = $bkCount > 0 ? $bkList[$bkIdx] : null;

            $schedule[] = [
                'minggu'      => $weekNum,
                'type'        => 'content',
                'subCpmks'    => $subs,
                'indikator'   => $this->generateIndikator($subs),
                'teknik'      => $this->generateTeknikForSubs($subs, $mk),
                'metode'      => $defaultMethods[$pos % count($defaultMethods)],
                'materi'      => $bk?->nama ?? 'Sesuai Silabus',
                'materi_kode' => $bk?->kode ?? '',
                'bobot'       => $this->getBobotForSubs($subs, $mk),
            ];
        }

        // UTS row
        $schedule[] = [
            'minggu'      => self::WEEK_UTS,
            'type'        => 'uts',
            'subCpmks'    => [],
            'indikator'   => 'Mahasiswa mampu menguasai materi pertemuan 1–7',
            'teknik'      => 'Tes Tertulis (Esai / Pilihan Ganda)',
            'metode'      => 'Ujian Tengah Semester (UTS)',
            'materi'      => 'Komprehensif Materi Pertemuan 1–7',
            'materi_kode' => '',
            'bobot'       => $mk->bobotPenilaians->sum('bobot_uts') ?: '-',
        ];

        // UAS row
        $schedule[] = [
            'minggu'      => self::WEEK_UAS,
            'type'        => 'uas',
            'subCpmks'    => [],
            'indikator'   => 'Mahasiswa mampu menguasai materi pertemuan 9–15',
            'teknik'      => 'Tes Tertulis (Esai / Pilihan Ganda)',
            'metode'      => 'Ujian Akhir Semester (UAS)',
            'materi'      => 'Komprehensif Materi Pertemuan 9–15',
            'materi_kode' => '',
            'bobot'       => $mk->bobotPenilaians->sum('bobot_uas') ?: '-',
        ];

        usort($schedule, fn($a, $b) => $a['minggu'] <=> $b['minggu']);

        return $schedule;
    }

    private function generateIndikator(array $subs): string
    {
        if (empty($subs)) return '-';
        return collect($subs)
            ->map(fn($s) => 'Mampu ' . Str::lcfirst($s->deskripsi))
            ->implode('; ');
    }

    private function generateTeknikForSubs(array $subs, MataKuliah $mk): string
    {
        if (empty($subs)) return 'Observasi';
        $set = [];
        foreach ($subs as $sub) {
            $t = $mk->teknikPenilaians->firstWhere('cpmk_id', $sub->cpmk_id);
            if ($t?->has_tugas)        $set[] = 'Tugas';
            if ($t?->has_partisipatif) $set[] = 'Partisipasi';
            if ($t?->has_proyek)       $set[] = 'Proyek';
        }
        return implode(', ', array_unique($set)) ?: 'Observasi';
    }

    private function getBobotForSubs(array $subs, MataKuliah $mk): int|string
    {
        if (empty($subs)) return '-';
        $total = 0;
        $cpmkIds = collect($subs)->pluck('cpmk_id')->unique();
        foreach ($cpmkIds as $cpmkId) {
            $b = $mk->bobotPenilaians->firstWhere('cpmk_id', $cpmkId);
            if ($b) {
                $total += $b->bobot_tugas + $b->bobot_partisipatif + $b->bobot_proyek;
            }
        }
        // Divide by sub count per cpmk to avoid double counting
        $subCount = count($subs);
        return $subCount > 0 ? (int) round($total / $subCount) : '-';
    }

    // ──────────────────────────────────────────────────────
    // BAGIAN 12: KONTRAK PEMBELAJARAN
    // ──────────────────────────────────────────────────────
    private function buildKontrak(MataKuliah $mk): array
    {
        $cpmks    = $mk->cpmks->sortBy('kode');
        $bks      = $mk->bahanKajians->sortBy('kode');
        $bobots   = $mk->bobotPenilaians;

        // Average bobots across all CPMK entries
        $avgTugas        = $bobots->avg('bobot_tugas') ?? 0;
        $avgPartisipatif = $bobots->avg('bobot_partisipatif') ?? 0;
        $avgProyek       = $bobots->avg('bobot_proyek') ?? 0;
        $avgUts          = $bobots->avg('bobot_uts') ?? 0;
        $avgUas          = $bobots->avg('bobot_uas') ?? 0;

        return [
            'tujuan'       => 'Setelah menyelesaikan mata kuliah ' . $mk->nama
                . ', mahasiswa diharapkan mampu: '
                . $cpmks->map(fn($c) => '(' . $c->kode . ') ' . Str::lcfirst($c->deskripsi))
                ->implode('; ') . '.',
            'bahan_kajian' => $bks->pluck('nama')->implode(', '),
            'metode'       => 'Ceramah, Diskusi Interaktif, Studi Kasus, Praktikum, Proyek',
            'penilaian'    => [
                'tugas'        => round($avgTugas),
                'partisipatif' => round($avgPartisipatif),
                'proyek'       => round($avgProyek),
                'uts'          => round($avgUts),
                'uas'          => round($avgUas),
            ],
            'tata_tertib'  => [
                'Kehadiran minimal 75% dari total pertemuan tatap muka.',
                'Tugas dikumpulkan sesuai jadwal yang telah ditetapkan dosen.',
                'Tidak diperkenankan melakukan kecurangan/plagiarisme (nilai 0).',
                'Berpakaian rapi dan sopan sesuai aturan Universitas Sari Mulia.',
                'Mahasiswa yang terlambat > 15 menit tidak diizinkan mengisi absensi.',
                'Aktif berpartisipasi dalam setiap sesi diskusi dan praktikum.',
                'Penggunaan perangkat digital hanya untuk keperluan pembelajaran.',
                'Komunikasi dengan dosen melalui media resmi yang disepakati.',
            ],
            'referensi'    => $mk->rpsReferensis,
            'cpls'         => $mk->cpls->sortBy('kode'),
            'cpmks'        => $cpmks,
            'sub_cpmks'    => $this->getAllSubCpmks($mk),
        ];
    }

    // ──────────────────────────────────────────────────────
    // BAGIAN 13: RENCANA TUGAS MAHASISWA
    // ──────────────────────────────────────────────────────
    private function buildRencanaTugas(MataKuliah $mk): Collection
    {
        $subCpmks = $this->getAllSubCpmks($mk);
        $counter  = 0;

        return $subCpmks->map(function ($sub) use ($mk, &$counter) {
            $counter++;
            $bobot  = $mk->bobotPenilaians->firstWhere('cpmk_id', $sub->cpmk_id);
            $teknik = $mk->teknikPenilaians->firstWhere('cpmk_id', $sub->cpmk_id);

            // Determine task type
            $jenis  = 'Tugas Terstruktur';
            $metode = 'Laporan / Makalah Individu';
            if ($teknik?->has_proyek) {
                $jenis  = 'Proyek';
                $metode = 'Proyek Kelompok, Presentasi & Demo';
            } elseif ($teknik?->has_partisipatif) {
                $jenis  = 'Partisipasi Aktif';
                $metode = 'Diskusi Kelas, Refleksi, Presentasi';
            }

            // Bobot per sub-cpmk
            $subCount = $sub->cpmk->subCpmks->count() ?: 1;
            $totalB   = $bobot
                ? ($bobot->bobot_tugas + $bobot->bobot_proyek + $bobot->bobot_partisipatif)
                : 0;
            $bobotSub = $subCount > 0 ? (int) round($totalB / $subCount) : 0;

            return [
                'no'        => $counter,
                'judul'     => "Tugas {$counter}: " . Str::words($sub->deskripsi, 8, '...'),
                'sub_kode'  => $sub->kode,
                'cpmk_kode' => $sub->cpmk->kode ?? '-',
                'deskripsi' => 'Mahasiswa ' . Str::lcfirst($sub->deskripsi)
                    . ($sub->catatan ? ' — Catatan: ' . $sub->catatan : ''),
                'jenis'     => $jenis,
                'metode'    => $metode,
                'bobot'     => $bobotSub > 0 ? $bobotSub . '%' : '-',
                'kriteria'  => 'Ketepatan analisis (40%), kelengkapan isi (30%), kerapian penyajian (20%), ketepatan waktu pengumpulan (10%).',
                'waktu'     => 'Sesuai jadwal yang ditentukan dosen',
                'sub_cpmk'  => $sub,
            ];
        });
    }
}
