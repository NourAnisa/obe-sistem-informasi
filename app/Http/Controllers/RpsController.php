<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\DosenMataKuliah;
use App\Models\KrsMahasiswa;
use App\Services\RpsGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RpsController extends Controller
{
    public function __construct(private RpsGeneratorService $generator) {}

    /**
     * GET /api/jadwal-generator
     * Returns 16 kuliah dates based on day_of_week, start_date, time, skipping Indonesian holidays.
     */
    public function jadwalGenerator(Request $request)
    {
        $dayOfWeek  = (int) $request->input('hari', 1);   // 0=Sun…6=Sat (JS convention)
        $startDate  = $request->input('mulai');            // Y-m-d
        $waktu      = $request->input('waktu', '08.00–09.40 WITA');
        $dosen      = $request->input('dosen', '');
        $year       = $request->input('tahun', date('Y'));

        // Indonesian national holidays (approximate for 2025 & 2026)
        $holidays = $this->indonesianHolidays((int)$year);
        // Also add next year holidays if start date is near year-end
        if ((int)$year < 2030) {
            $holidays = array_merge($holidays, $this->indonesianHolidays((int)$year + 1));
        }
        $holidaySet = array_flip($holidays); // O(1) lookup

        if (!$startDate) {
            return response()->json(['error' => 'Tanggal mulai wajib diisi.'], 422);
        }

        $results = [];
        $current = new \DateTime($startDate);

        // Advance to first occurrence of the selected day-of-week
        // JS: 0=Sun,1=Mon…6=Sat → PHP: 0=Sun,1=Mon…6=Sat (same)
        $phpDow = (int)$current->format('w'); // 0=Sun
        $diff   = ($dayOfWeek - $phpDow + 7) % 7;
        if ($diff > 0) {
            $current->modify("+{$diff} days");
        }

        $minggu = 1;
        $attempts = 0;
        while ($minggu <= 16 && $attempts < 200) {
            $attempts++;
            $dateStr = $current->format('Y-m-d');
            $label   = $this->formatDateIndonesian($current);
            $hari    = $this->dayNameIndonesian((int)$current->format('w')) . ', ' . $label;

            if (!isset($holidaySet[$dateStr])) {
                // Not a holiday
                if ($minggu == 8) {
                    $results[] = ['hari' => $hari, 'waktu' => 'UTS', 'dosen' => $dosen, 'info' => 'UTS'];
                } elseif ($minggu == 16) {
                    $results[] = ['hari' => $hari, 'waktu' => 'UAS', 'dosen' => $dosen, 'info' => 'UAS'];
                } else {
                    $results[] = ['hari' => $hari, 'waktu' => $waktu, 'dosen' => $dosen, 'info' => ''];
                }
                $minggu++;
            } else {
                // Holiday — add to skipped list but don't count as meeting
                $results[] = [
                    'hari'  => $hari,
                    'waktu' => '— Libur: ' . ($holidaySet[$dateStr] ?? 'Hari Libur'),
                    'dosen' => '',
                    'info'  => 'libur',
                ];
                // We still add this to results so user can see it, but it doesn't count as a meeting week
            }
            $current->modify('+7 days');
        }

        return response()->json([
            'jadwal'   => $results,
            'holidays' => $holidays,
        ]);
    }

    /**
     * Indonesian national holidays for a given year.
     * Returns array of 'Y-m-d' strings.
     */
    private function indonesianHolidays(int $year): array
    {
        // Fixed holidays
        $fixed = [
            "$year-01-01", // Tahun Baru Masehi
            "$year-05-01", // Hari Buruh
            "$year-06-01", // Hari Pancasila
            "$year-08-17", // Kemerdekaan RI
            "$year-12-25", // Natal
            "$year-12-26", // Cuti Bersama Natal
        ];

        // Variable holidays (approximate — based on government decree patterns)
        $variable = match ($year) {
            2025 => [
                '2025-01-27', // Isra Mi'raj
                '2025-01-28', // Imlek
                '2025-01-29', // Imlek cuti
                '2025-03-28', // Idul Fitri
                '2025-03-29', // Hari Raya Nyepi
                '2025-03-31', // Idul Fitri
                '2025-04-01', // Idul Fitri
                '2025-04-02', // Idul Fitri cuti
                '2025-04-03', // Idul Fitri cuti
                '2025-04-04', // Idul Fitri cuti
                '2025-04-18', // Jumat Agung
                '2025-05-12', // Waisak
                '2025-05-29', // Kenaikan Isa Almasih
                '2025-06-06', // Idul Adha
                '2025-06-27', // Tahun Baru Islam 1447H
                '2025-09-05', // Maulid Nabi
                '2025-12-25', // Natal
            ],
            2026 => [
                '2026-01-01', // Tahun Baru
                '2026-01-16', // Isra Mi'raj
                '2026-01-17', // Tahun Baru Imlek
                '2026-03-19', // Nyepi
                '2026-03-20', // Idul Fitri (est.)
                '2026-03-21', // Idul Fitri (est.)
                '2026-03-23', // Cuti Bersama
                '2026-04-03', // Jumat Agung
                '2026-05-01', // Hari Buruh
                '2026-05-14', // Kenaikan Isa Almasih
                '2026-05-26', // Waisak
                '2026-05-27', // Idul Adha (est.)
                '2026-06-17', // Tahun Baru Islam
                '2026-08-17', // Kemerdekaan RI
                '2026-08-25', // Maulid Nabi (est.)
                '2026-12-25', // Natal
                '2026-12-28', // Cuti Bersama Natal
            ],
            default => [],
        };

        return array_unique(array_merge($fixed, $variable));
    }

    private function dayNameIndonesian(int $dow): string
    {
        return ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'][$dow] ?? '';
    }

    private function formatDateIndonesian(\DateTime $d): string
    {
        $months = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        return $d->format('d') . ' ' . $months[(int)$d->format('n')] . ' ' . $d->format('Y');
    }

    /**
     * GET /rps/{kode}/kontrak — Kontrak Pembelajaran separate page
     */
    public function kontrak(string $kode)
    {
        $mk  = $this->findMk($kode);
        $this->authorizeDosenMk($mk);
        $mk->loadMissing(['rpsDetail', 'rpsPertemuans']);
        $rps = $this->generator->generate($mk);

        $ta = config('obe.tahun_akademik', '2025/2026');
        $mahasiswaList = KrsMahasiswa::getAktif($mk->id, $ta);

        return view('rps.kontrak', compact('rps', 'mk', 'mahasiswaList', 'ta'));
    }

    /**
     * GET /rps/{kode} — HTML preview
     */
    public function show(string $kode)
    {
        $mk = $this->findMk($kode);
        $this->authorizeDosenMk($mk);
        $mk->loadMissing(['rpsDetail', 'rpsPertemuans', 'rpsReferensis', 'publikasiDosen.dosen']);
        $rps = $this->generator->generate($mk);

        // Merge manual blueprint overrides (deskripsi & metode) into generated rows
        $overrides = $mk->rpsDetail?->blueprint_overrides ?? [];
        if (!empty($overrides) && !empty($rps['blueprint']['rows'])) {
            $rps['blueprint']['rows'] = array_map(function ($row, $idx) use ($overrides) {
                $key = $row['basis'] . '||' . $row['komponen'];
                if (isset($overrides[$key])) {
                    $row['deskripsi'] = $overrides[$key]['deskripsi'] ?? $row['deskripsi'];
                    $row['metode']    = $overrides[$key]['metode']    ?? $row['metode'];
                }
                return $row;
            }, $rps['blueprint']['rows'], array_keys($rps['blueprint']['rows']));
        }

        $publikasiPendukung = $mk->publikasiDosen->sortByDesc('tahun');

        return view('rps.show', compact('rps', 'mk', 'publikasiPendukung'));
    }

    /**
     * GET /rps/{kode}/edit — Edit form
     */
    public function edit(string $kode)
    {
        $mk = $this->findMk($kode);
        $this->authorizeDosenMk($mk);
        $mk->load([
            'cpls',
            'cpmks.cpl',
            'cpmks.subCpmks',
            'bahanKajians',
            'bobotPenilaians.cpmk',
            'teknikPenilaians.cpmk',
            'rpsReferensis',
            'rpsDetail',
            'rpsPertemuans',
        ]);

        $detail     = $mk->rpsDetail ?? new \App\Models\RpsDetail(['mata_kuliah_id' => $mk->id]);
        $pertemuan  = $mk->rpsPertemuans;

        // Auto-seed 16 pertemuan rows from generated jadwal if none exist yet
        if ($pertemuan->isEmpty()) {
            $rpsForSeed = $this->generator->generate($mk);
            foreach ($rpsForSeed['jadwalMingguan'] as $j) {
                $subIds = collect($j['subCpmks'])->pluck('id')->values()->all();
                $mk->rpsPertemuans()->create([
                    'minggu'              => $j['minggu'],
                    'sub_cpmk_ids'        => $subIds,
                    'cpmk_label'          => '',
                    'indikator'           => $j['type'] === 'content' ? ($j['indikator'] ?? '') : '',
                    'indikator_en'        => '',
                    'teknik_penilaian'    => $j['type'] === 'content' ? ($j['teknik'] ?? '') : '',
                    'teknik_penilaian_en' => '',
                    'kreteria'            => '',
                    'kreteria_en'         => '',
                    'metode_sinkron'      => $j['type'] === 'content' ? ($j['metode'] ?? '') : '',
                    'metode_sinkron_en'   => '',
                    'metode_asinkron'     => '',
                    'metode_asinkron_en'  => '',
                    'tugas'               => '',
                    'tugas_en'            => '',
                    'materi'              => $j['type'] === 'content' ? ($j['materi'] ?? '') : '',
                    'materi_en'           => '',
                    'bobot'               => is_numeric($j['bobot'] ?? null) ? $j['bobot'] : 0,
                    'dosen'               => '',
                ]);
            }
            $mk->load('rpsPertemuans');
            $pertemuan = $mk->rpsPertemuans;
        }

        // Build blueprint rows for editing (auto-generated + saved overrides)
        $rpsGenerated    = $this->generator->generate($mk);
        $blueprintRows   = $rpsGenerated['blueprint']['rows'] ?? [];
        $bpOverrides     = $detail->blueprint_overrides ?? [];
        foreach ($blueprintRows as &$row) {
            $key = $row['basis'] . '||' . $row['komponen'];
            $row['override_deskripsi'] = $bpOverrides[$key]['deskripsi'] ?? '';
            $row['override_metode']    = $bpOverrides[$key]['metode']    ?? $row['metode'];
            $row['override_key']       = $key;
        }
        unset($row);

        $allSubCpmks = $mk->cpmks->sortBy('kode')->flatMap(function ($cpmk) {
            return $cpmk->subCpmks->sortBy('kode')->map(fn($s) => [
                'id'       => $s->id,
                'kode'     => $s->kode,
                'deskripsi' => $s->deskripsi,
                'cpmk_kode' => $cpmk->kode,
                'cpmk_id'  => $cpmk->id,
            ]);
        })->values();

        return view('rps.edit', compact('mk', 'detail', 'pertemuan', 'allSubCpmks', 'blueprintRows'));
    }

    /**
     * POST /rps/{kode}/edit — Save manual data
     */
    public function update(Request $request, string $kode)
    {
        $mk = $this->findMk($kode);
        $this->authorizeDosenMk($mk);

        // Save deskripsi to mata_kuliah table
        if ($request->filled('mk_deskripsi') || $request->has('mk_deskripsi_en')) {
            $mk->update(array_filter([
                'deskripsi'    => $request->mk_deskripsi,
                'deskripsi_en' => $request->mk_deskripsi_en,
            ], fn($v) => !is_null($v)));
        }

        // Save rps_detail
        $dosenPengampu     = array_values(array_filter($request->dosen_pengampu ?? []));
        $ketentuanTambahan = array_values(array_filter($request->ketentuan_tambahan ?? []));
        $jadwalKuliah      = array_values(array_filter($request->jadwal_kuliah ?? [], fn($j) => !empty($j['hari'])));

        // Save blueprint overrides (deskripsi & metode per row keyed by basis||komponen)
        $blueprintOverrides = [];
        foreach ($request->blueprint_override ?? [] as $key => $vals) {
            $key = urldecode($key);
            if (!empty($vals['deskripsi']) || !empty($vals['metode'])) {
                $blueprintOverrides[$key] = [
                    'deskripsi' => $vals['deskripsi'] ?? '',
                    'metode'    => $vals['metode'] ?? '',
                ];
            }
        }

        $mk->rpsDetail()->updateOrCreate(
            ['mata_kuliah_id' => $mk->id],
            [
                'tautan_kelas_daring' => $request->tautan_kelas_daring,
                'dosen_pengampu'      => $dosenPengampu,
                'ketentuan_tambahan'  => $ketentuanTambahan,
                'jadwal_kuliah'       => $jadwalKuliah,
                'catatan_blueprint'   => $request->catatan_blueprint,
                'blueprint_overrides' => $blueprintOverrides ?: null,
            ]
        );

        // Save Pustaka Utama → rps_referensi
        $mk->rpsReferensis()->where('jenis', 'utama')->delete();
        foreach ($request->referensi_utama ?? [] as $urutan => $ref) {
            if (empty(trim($ref['judul'] ?? ''))) continue;
            $mk->rpsReferensis()->create([
                'judul'    => $ref['judul'],
                'penulis'  => $ref['penulis']  ?? null,
                'penerbit' => $ref['penerbit'] ?? null,
                'tahun'    => $ref['tahun']    ?? null,
                'url'      => $ref['url']      ?? null,
                'jenis'    => 'utama',
                'urutan'   => $urutan + 1,
            ]);
        }

        // Save pertemuan (delete old, insert new)
        $mk->rpsPertemuans()->delete();
        foreach ($request->pertemuan ?? [] as $p) {
            if (empty($p['minggu'])) continue;
            $subIds = array_values(array_filter(
                is_array($p['sub_cpmk_ids'] ?? null) ? $p['sub_cpmk_ids'] : []
            ));
            $mk->rpsPertemuans()->create([
                'minggu'              => $p['minggu'],
                'sub_cpmk_ids'        => $subIds,
                'cpmk_label'          => $p['cpmk_label'] ?? '',
                'indikator'           => $p['indikator'] ?? '',
                'indikator_en'        => $p['indikator_en'] ?? '',
                'teknik_penilaian'    => $p['teknik_penilaian'] ?? '',
                'teknik_penilaian_en' => $p['teknik_penilaian_en'] ?? '',
                'kreteria'            => $p['kreteria'] ?? '',
                'kreteria_en'         => $p['kreteria_en'] ?? '',
                'metode_sinkron'      => $p['metode_sinkron'] ?? '',
                'metode_sinkron_en'   => $p['metode_sinkron_en'] ?? '',
                'metode_asinkron'     => $p['metode_asinkron'] ?? '',
                'metode_asinkron_en'  => $p['metode_asinkron_en'] ?? '',
                'tugas'               => $p['tugas'] ?? '',
                'tugas_en'            => $p['tugas_en'] ?? '',
                'materi'              => $p['materi'] ?? '',
                'materi_en'           => $p['materi_en'] ?? '',
                'bobot'               => $p['bobot'] ?? 0,
                'dosen'               => $p['dosen'] ?? '',
            ]);
        }

        return redirect()->route('rps.show', $kode)->with('success', 'RPS berhasil disimpan!');
    }

    /**
     * GET /rps/{kode}/pdf — Export PDF via DomPDF (fallback: browser print)
     */
    public function pdf(string $kode)
    {
        $mk  = $this->findMk($kode);
        $this->authorizeDosenMk($mk);
        $mk->loadMissing(['rpsDetail', 'rpsPertemuans']);
        $rps = $this->generator->generate($mk);

        // Use DomPDF if available, otherwise serve print-ready HTML
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $html = view('rps.pdf', compact('rps', 'mk'))->render();
            $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                ->setPaper('A4', 'landscape')
                ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => false, 'defaultFont' => 'times']);
            $filename = 'RPS_' . str_replace(['/', '\\', ' '], '_', $mk->kode) . '_' . date('Ymd') . '.pdf';
            return $pdf->download($filename);
        }

        // Fallback: serve PDF-formatted HTML (user prints via browser)
        return view('rps.pdf', compact('rps', 'mk'))
            ->with('printMode', true);
    }

    /**
     * GET /rps/{kode}/docx — Export DOCX via ZipArchive OOXML (no extra packages)
     */
    public function docx(string $kode)
    {
        $mk  = $this->findMk($kode);
        $this->authorizeDosenMk($mk);
        $rps = $this->generator->generate($mk);

        // Render standalone HTML view — identical layout to show.blade.php
        $html = view('rps.word', compact('rps', 'mk'))->render();

        $filename = 'RPS_' . str_replace(['/', '\\', ' '], '_', $mk->kode) . '_' . date('Ymd') . '.doc';

        return response($html, 200, [
            'Content-Type'        => 'application/msword',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * GET /rps — List all MK with RPS generate buttons
     */
    public function index()
    {
        $query = MataKuliah::orderBy('semester')->orderBy('kode')
            ->withCount(['cpls', 'cpmks']);

        // Dosen only sees courses they are assigned to
        $role = Auth::user()->role ?? '';
        if ($role === 'dosen') {
            $assignedIds = DosenMataKuliah::where('dosen_id', Auth::id())->pluck('mata_kuliah_id');
            $userName = Auth::user()->name;
            $pjmkIds = MataKuliah::where('pjmk', 'like', '%' . explode(',', $userName)[0] . '%')
                ->orWhere('pjmk', 'like', '%' . $userName . '%')
                ->pluck('id');
            $combinedIds = $assignedIds->merge($pjmkIds)->unique();
            $query->whereIn('id', $combinedIds);
        }

        $mataKuliahs = $query->get()->groupBy('semester');

        return view('rps.index', compact('mataKuliahs'));
    }

    // ──────────────────────────────────────────────
    private function findMk(string $kode): MataKuliah
    {
        return MataKuliah::where('kode', $kode)->firstOrFail();
    }

    /** Abort 403 if authenticated dosen is not assigned to this MK. */
    private function authorizeDosenMk(MataKuliah $mk): void
    {
        if ((Auth::user()->role ?? '') === 'dosen') {
            $assignedIds = DosenMataKuliah::where('dosen_id', Auth::id())->pluck('mata_kuliah_id');
            $userName = Auth::user()->name;
            $pjmkIds = MataKuliah::where('pjmk', 'like', '%' . explode(',', $userName)[0] . '%')
                ->orWhere('pjmk', 'like', '%' . $userName . '%')
                ->pluck('id');
            $combinedIds = $assignedIds->merge($pjmkIds)->unique();
            if (!$combinedIds->contains($mk->id)) {
                abort(403, 'Anda tidak mengampu mata kuliah ini.');
            }
        }
    }

    /** Helper: XML-escape a string */
    private function xe(mixed $s): string
    {
        return htmlspecialchars((string)($s ?? ''), ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    /** Build a table row with given cells: [text, width_dxa, bold?, alignment?] */
    private function tRow(array $cells, string $rowStyle = '', bool $isHeader = false): string
    {
        $tcs = '';
        foreach ($cells as $c) {
            [$text, $w] = $c;
            $bold  = !empty($c[2]);
            $align = $c[3] ?? 'left';
            $shd   = !empty($c[4]) ? '<w:shd w:val="clear" w:color="auto" w:fill="' . $c[4] . '"/>' : '';
            $color = !empty($c[5]) ? '<w:color w:val="' . $c[5] . '"/>' : '';
            $tcs  .= '<w:tc><w:tcPr><w:tcW w:w="' . $w . '" w:type="dxa"/>' . $shd . '</w:tcPr>'
                . '<w:p><w:pPr><w:jc w:val="' . $align . '"/><w:spacing w:before="0" w:after="60"/></w:pPr>'
                . '<w:r><w:rPr>' . ($bold ? '<w:b/>' : '') . '<w:sz w:val="20"/><w:szCs w:val="20"/>' . $color . '</w:rPr>'
                . '<w:t xml:space="preserve">' . $this->xe($text) . '</w:t></w:r></w:p></w:tc>';
        }
        return '<w:tr>' . $tcs . '</w:tr>';
    }

    /** Build a paragraph with optional bold/size */
    private function para(string $text, bool $bold = false, int $size = 22, string $align = 'left', string $spacingBefore = '120', string $spacingAfter = '60'): string
    {
        return '<w:p><w:pPr><w:jc w:val="' . $align . '"/>'
            . '<w:spacing w:before="' . $spacingBefore . '" w:after="' . $spacingAfter . '"/></w:pPr>'
            . '<w:r><w:rPr>' . ($bold ? '<w:b/>' : '') . '<w:sz w:val="' . $size . '"/><w:szCs w:val="' . $size . '"/></w:rPr>'
            . '<w:t xml:space="preserve">' . $this->xe($text) . '</w:t></w:r></w:p>';
    }

    /** Page break paragraph */
    private function pageBreak(): string
    {
        return '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    /** Section heading paragraph */
    private function sectionHead(string $text): string
    {
        return '<w:p><w:pPr><w:pStyle w:val="Heading1"/><w:spacing w:before="200" w:after="100"/></w:pPr>'
            . '<w:r><w:rPr><w:b/><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr>'
            . '<w:t>' . $this->xe($text) . '</w:t></w:r></w:p>';
    }

    private function buildDocxXml(array $rps): string
    {
        $h  = $rps['header'];
        $ns = 'xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas" '
            . 'xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" '
            . 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"';

        $body = '';

        // ── KOP TABLE ───────────────────────────────────────────
        $body .= '<w:tbl>'
            . '<w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="6" w:space="0" w:color="1e3a8a"/>'
            . '<w:left w:val="single" w:sz="6" w:space="0" w:color="1e3a8a"/>'
            . '<w:bottom w:val="single" w:sz="6" w:space="0" w:color="1e3a8a"/>'
            . '<w:right w:val="single" w:sz="6" w:space="0" w:color="1e3a8a"/>'
            . '<w:insideH w:val="single" w:sz="6" w:space="0" w:color="1e3a8a"/>'
            . '<w:insideV w:val="single" w:sz="6" w:space="0" w:color="1e3a8a"/>'
            . '</w:tblBorders></w:tblPr>';

        // Header row: Logo | Identitas Prodi | RPS label
        $body .= '<w:tr>'
            . '<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/></w:tcPr>'
            . $this->para('[LOGO UNISM]', true, 18, 'center') . '</w:tc>'
            . '<w:tc><w:tcPr><w:tcW w:w="5572" w:type="dxa"/></w:tcPr>'
            . $this->para(strtoupper($this->xe($h['universitas'])), true, 26, 'center', '80', '40')
            . $this->para('FAKULTAS ' . strtoupper($this->xe($h['fakultas'])), true, 22, 'center', '40', '40')
            . $this->para('PROGRAM STUDI ' . strtoupper($this->xe($h['prodi'])), true, 22, 'center', '40', '40')
            . $this->para('TAHUN AKADEMIK ' . $this->xe($h['tahun_akademik']), false, 20, 'center', '40', '80')
            . '</w:tc>'
            . '<w:tc><w:tcPr><w:tcW w:w="2000" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="1e3a8a"/></w:tcPr>'
            . $this->para('RENCANA PEMBELAJARAN SEMESTER (RPS)', true, 22, 'center', '120', '80')
            . '</w:tc>'
            . '</w:tr></w:tbl>';

        // ── IDENTITAS TABLE ──────────────────────────────────────
        $body .= '<w:p><w:pPr><w:spacing w:before="120" w:after="0"/></w:pPr></w:p>';
        $body .= '<w:tbl><w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/>'
            . '<w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/>'
            . '<w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>'
            . '</w:tblBorders></w:tblPr>';

        $sks_str = $h['sks'] . ' SKS (' . ($h['sks_teori'] ?? $h['sks']) . 'T + ' . ($h['sks_praktikum'] ?? 0) . 'P)';
        $identitasRows = [
            ['Nama Mata Kuliah',   $h['nama_mk'] . ($h['nama_mk_en'] ? ' / ' . $h['nama_mk_en'] : '')],
            ['Kode Mata Kuliah',   $h['kode_mk']],
            ['Bobot SKS',          $sks_str],
            ['Semester',           'Semester ' . $h['semester']],
            ['Tahun Akademik',     $h['tahun_akademik']],
            ['Program Studi',      $h['prodi'] . ' (' . ($h['jenjang'] ?? 'S1') . ')'],
            ['Dosen PJMK',         $h['pjmk'] ?? '-'],
            ['Kaprodi',            $h['kaprodi']],
            ['Tanggal Penyusunan', $h['tanggal'] ?? date('d F Y')],
        ];
        foreach ($identitasRows as [$label, $val]) {
            $body .= $this->tRow([[$label, 2500, true], [':', 200], [$val, 6372]], '');
        }
        $body .= '</w:tbl>';

        // ── OTORISASI TABLE ──────────────────────────────────────
        $body .= $this->sectionHead('OTORISASI / PENGESAHAN');
        $body .= '<w:tbl><w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/>'
            . '<w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/>'
            . '<w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>'
            . '</w:tblBorders></w:tblPr>';
        $body .= $this->tRow([
            ['Dosen Pengembang RPS', 4536, true, 'center', 'dbeafe', '1e3a8a'],
            ['Ketua Program Studi',  4536, true, 'center', 'dbeafe', '1e3a8a'],
        ]);
        $body .= $this->tRow([
            [($h['pjmk'] ?? 'Dosen PJMK') . "\n\nNIK: …", 4536],
            [($h['kaprodi']) . "\n\nNIK: " . ($h['nik_kaprodi'] ?? '…'), 4536],
        ]);
        $body .= '</w:tbl>';

        // ── CPL SECTION ─────────────────────────────────────────
        $body .= $this->sectionHead('A. CAPAIAN PEMBELAJARAN LULUSAN (CPL)');
        $body .= '<w:tbl><w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/>'
            . '<w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/>'
            . '<w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>'
            . '</w:tblBorders></w:tblPr>';
        $body .= $this->tRow([['Kode CPL', 1000, true, 'center', 'dbeafe'], ['Kategori', 1200, true, 'center', 'dbeafe'], ['Deskripsi (Indonesia / English)', 6872, true, 'center', 'dbeafe']]);
        foreach ($rps['cpls'] as $cpl) {
            $desc = $this->xe($cpl->deskripsi);
            if (!empty($cpl->deskripsi_en)) $desc .= ' / ' . $this->xe($cpl->deskripsi_en);
            $body .= $this->tRow([[$cpl->kode, 1000, false, 'center'], [$cpl->kategori ?? '-', 1200, false, 'center'], [$desc, 6872]]);
        }
        $body .= '</w:tbl>';

        // ── CPMK SECTION ─────────────────────────────────────────
        $body .= $this->sectionHead('B. CAPAIAN PEMBELAJARAN MATA KULIAH (CPMK)');
        $body .= '<w:tbl><w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/>'
            . '<w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/>'
            . '<w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>'
            . '</w:tblBorders></w:tblPr>';
        $body .= $this->tRow([['Kode', 900, true, 'center', 'dbeafe'], ['Deskripsi CPMK', 4936, true, 'center', 'dbeafe'], ['CPL', 800, true, 'center', 'dbeafe'], ['Sub-CPMK', 2436, true, 'center', 'dbeafe']]);
        foreach ($rps['cpmks'] as $cpmk) {
            $subs = $cpmk->subCpmks->pluck('kode')->implode(', ');
            $body .= $this->tRow([[$cpmk->kode, 900, false, 'center'], [$cpmk->deskripsi, 4936], [$cpmk->cpl?->kode ?? '-', 800, false, 'center'], [$subs ?: '-', 2436]]);
        }
        $body .= '</w:tbl>';

        // ── BAHAN KAJIAN ─────────────────────────────────────────
        if ($rps['bahanKajians']->count()) {
            $body .= $this->sectionHead('C. BAHAN KAJIAN');
            $body .= '<w:tbl><w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
                . '<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/>'
                . '<w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/>'
                . '<w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>'
                . '</w:tblBorders></w:tblPr>';
            $body .= $this->tRow([['Kode', 900, true, 'center', 'dbeafe'], ['Nama Bahan Kajian', 8172, true, 'center', 'dbeafe']]);
            foreach ($rps['bahanKajians'] as $bk) {
                $body .= $this->tRow([[$bk->kode, 900, false, 'center'], [$bk->nama, 8172]]);
            }
            $body .= '</w:tbl>';
        }

        // ── PUSTAKA ──────────────────────────────────────────────
        $body .= $this->sectionHead('D. PUSTAKA');
        if ($rps['referensiUtama']->count()) {
            $body .= $this->para('Referensi Utama:', true, 22, 'left', '120', '40');
            foreach ($rps['referensiUtama'] as $i => $ref) {
                $body .= $this->para(($i + 1) . '. ' . $ref->referensi, false, 20, 'left', '40', '40');
            }
        }
        if ($rps['referensiPendukung']->count()) {
            $body .= $this->para('Referensi Pendukung:', true, 22, 'left', '120', '40');
            foreach ($rps['referensiPendukung'] as $i => $ref) {
                $body .= $this->para(($i + 1) . '. ' . $ref->referensi, false, 20, 'left', '40', '40');
            }
        }

        // ── BLUEPRINT / BOBOT PENILAIAN ──────────────────────────
        $body .= $this->sectionHead('E. KRITERIA PENILAIAN & BLUEPRINT ASESMEN');
        $blueprint = $rps['blueprint'];
        if (!empty($blueprint)) {
            $types = ['Tugas', 'UTS', 'UAS', 'Partisipatif', 'Proyek'];
            $body .= '<w:tbl><w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
                . '<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/>'
                . '<w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/>'
                . '<w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>'
                . '</w:tblBorders></w:tblPr>';
            $hcells = [['CPMK', 2000, true, 'center', 'dbeafe']];
            foreach ($types as $t) $hcells[] = [$t, 900, true, 'center', 'dbeafe'];
            $hcells[] = ['Total', 1072, true, 'center', 'dbeafe'];
            $body .= $this->tRow($hcells);
            foreach ($blueprint as $row) {
                $cells = [[$row['kode'] ?? '-', 2000, true]];
                foreach ($types as $t) $cells[] = [($row[strtolower($t)] ?? 0) . '%', 900, false, 'center'];
                $cells[] = [($row['total'] ?? 0) . '%', 1072, true, 'center'];
                $body .= $this->tRow($cells);
            }
            $body .= '</w:tbl>';
        }

        // ── JADWAL MINGGUAN ──────────────────────────────────────
        $body .= $this->pageBreak();
        $body .= $this->sectionHead('F. RENCANA KEGIATAN BELAJAR MENGAJAR (16 MINGGU)');
        $body .= '<w:tbl><w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
            . '<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/>'
            . '<w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/>'
            . '<w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>'
            . '</w:tblBorders></w:tblPr>';
        $body .= $this->tRow([
            ['Mg', 350, true, 'center', 'dbeafe'],
            ['Kemampuan Akhir (Sub-CPMK)', 1400, true, 'center', 'dbeafe'],
            ['Indikator', 1600, true, 'center', 'dbeafe'],
            ['Teknik Penilaian', 1000, true, 'center', 'dbeafe'],
            ['Metode Pembelajaran', 1200, true, 'center', 'dbeafe'],
            ['Materi Pokok', 1800, true, 'center', 'dbeafe'],
            ['Waktu', 800, true, 'center', 'dbeafe'],
            ['Bobot', 722, true, 'center', 'dbeafe'],
        ]);
        foreach ($rps['jadwalMingguan'] as $row) {
            $isSpecial = in_array($row['type'], ['uts', 'uas']);
            $fillColor = $isSpecial ? 'fef3c7' : '';
            $subLabel  = collect($row['subCpmks'])->pluck('kode')->implode(', ') ?: strtoupper($row['type']);
            $bobot     = is_numeric($row['bobot']) ? $row['bobot'] . '%' : ($row['bobot'] ?? '-');
            $body .= $this->tRow([
                [(string)$row['minggu'],     350,  $isSpecial, 'center', $fillColor],
                [$subLabel,                  1400, $isSpecial, 'center', $fillColor],
                [$row['indikator'] ?? '',    1600, false,      'left',   $fillColor],
                [$row['teknik'] ?? '',       1000, false,      'left',   $fillColor],
                [$row['metode'] ?? '',       1200, false,      'left',   $fillColor],
                [$row['materi'] ?? '',       1800, false,      'left',   $fillColor],
                [$row['waktu'] ?? '100 mnt', 800,  false,      'center', $fillColor],
                [$bobot,                     722,  $isSpecial, 'center', $fillColor],
            ]);
        }
        $body .= '</w:tbl>';

        // ── KONTRAK PEMBELAJARAN ─────────────────────────────────
        $body .= $this->pageBreak();
        $body .= $this->sectionHead('G. KONTRAK PEMBELAJARAN');
        $kontrak = $rps['kontrak'] ?? [];
        $kontrakItems = [
            'Tata Tertib'         => $kontrak['tata_tertib'] ?? [],
            'Komponen Penilaian'  => $kontrak['komponen_penilaian'] ?? [],
            'Teknik Penilaian'    => $kontrak['teknik_penilaian'] ?? [],
            'Kriteria Kelulusan'  => $kontrak['kriteria_kelulusan'] ?? [],
        ];
        foreach ($kontrakItems as $title => $items) {
            $body .= $this->para($title . ':', true, 22, 'left', '120', '40');
            $items = (array)$items;
            if (is_array($items) && count($items)) {
                foreach ($items as $i => $item) {
                    $body .= $this->para(($i + 1) . '. ' . (is_array($item) ? implode(' – ', $item) : $item), false, 20, 'left', '40', '40');
                }
            } else {
                $body .= $this->para('-', false, 20, 'left', '40', '40');
            }
        }

        // ── RENCANA TUGAS ────────────────────────────────────────
        $body .= $this->sectionHead('H. RENCANA TUGAS MAHASISWA');
        $tugas = $rps['rencanaTugas'] ?? [];
        if (!empty($tugas)) {
            $body .= '<w:tbl><w:tblPr><w:tblW w:w="9072" w:type="dxa"/><w:tblBorders>'
                . '<w:top w:val="single" w:sz="4"/><w:left w:val="single" w:sz="4"/>'
                . '<w:bottom w:val="single" w:sz="4"/><w:right w:val="single" w:sz="4"/>'
                . '<w:insideH w:val="single" w:sz="4"/><w:insideV w:val="single" w:sz="4"/>'
                . '</w:tblBorders></w:tblPr>';
            $body .= $this->tRow([['Tugas', 1000, true, 'center', 'dbeafe'], ['Deskripsi', 5072, true, 'center', 'dbeafe'], ['Metode', 1500, true, 'center', 'dbeafe'], ['Bobot', 1500, true, 'center', 'dbeafe']]);
            foreach ($tugas as $t) {
                $body .= $this->tRow([
                    [$t['nama'] ?? '-',       1000, false, 'center'],
                    [$t['deskripsi'] ?? '-',  5072],
                    [$t['metode'] ?? '-',     1500, false, 'center'],
                    [($t['bobot'] ?? '-') . (isset($t['bobot']) ? '%' : ''), 1500, false, 'center'],
                ]);
            }
            $body .= '</w:tbl>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document ' . $ns . '>'
            . '<w:body>'
            . '<w:sectPr>'
            . '<w:pgSz w:w="12240" w:h="15840"/>'
            . '<w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1701" w:header="709" w:footer="709" w:gutter="0"/>'
            . '</w:sectPr>'
            . $body
            . '</w:body></w:document>';
    }

    private function buildDocxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults>
    <w:rPrDefault>
      <w:rPr>
        <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/>
        <w:sz w:val="22"/>
        <w:szCs w:val="22"/>
        <w:lang w:val="id-ID"/>
      </w:rPr>
    </w:rPrDefault>
    <w:pPrDefault>
      <w:pPr>
        <w:spacing w:after="120" w:line="276" w:lineRule="auto"/>
      </w:pPr>
    </w:pPrDefault>
  </w:docDefaults>
  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/>
    <w:pPr>
      <w:spacing w:before="240" w:after="120"/>
      <w:shd w:val="clear" w:color="auto" w:fill="1e3a8a"/>
    </w:pPr>
    <w:rPr>
      <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/>
      <w:b/>
      <w:color w:val="FFFFFF"/>
      <w:sz w:val="22"/>
      <w:szCs w:val="22"/>
    </w:rPr>
  </w:style>
</w:styles>';
    }
}
