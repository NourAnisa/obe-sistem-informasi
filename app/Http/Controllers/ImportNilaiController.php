<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Services\ObeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportNilaiController extends Controller
{
    public function __construct(private ObeCalculationService $obeService) {}

    // ─────────────────────────────────────────────────────────────────
    // GET /import-nilai
    // ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $ta          = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $mkQuery = DB::table('mata_kuliah');
        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mkQuery->where('program_id', Auth::user()->program_id);
        }
        $mataKuliahs = $mkQuery->orderBy('kode')->get(['id', 'kode', 'nama', 'semester']);

        // Bobot validation: check which MK have total bobot ≠ 100 per CPMK
        $bobotQuery = DB::table('bobot_penilaian as bp')
            ->join('mata_kuliah as mk', 'bp.mata_kuliah_id', '=', 'mk.id')
            ->join('cpmk', 'bp.cpmk_id', '=', 'cpmk.id');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $bobotQuery->where('mk.program_id', Auth::user()->program_id);
        }

        $bobotIssues = $bobotQuery->select(
                'mk.kode as mk_kode',
                'mk.nama as mk_nama',
                'cpmk.kode as cpmk_kode',
                DB::raw('(bp.bobot_tugas + bp.bobot_uts + bp.bobot_uas + bp.bobot_partisipatif + bp.bobot_proyek) as total_bobot')
            )
            ->having(DB::raw('total_bobot'), '!=', 100)
            ->orderBy('mk.kode')
            ->get();

        // CPMK grouped by MK for JS preview: { mk_id: ['CPMK031', 'CPMK061', ...] }
        $cpmkQuery = DB::table('mata_kuliah_cpmk as mkc')
            ->join('cpmk', 'mkc.cpmk_id', '=', 'cpmk.id')
            ->join('mata_kuliah as mk', 'mkc.mata_kuliah_id', '=', 'mk.id');

        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $cpmkQuery->where('mk.program_id', Auth::user()->program_id);
        }

        $cpmkRows = $cpmkQuery->orderBy('cpmk.kode')
            ->get(['mkc.mata_kuliah_id', 'cpmk.kode']);

        $cpmkByMk = [];
        foreach ($cpmkRows as $row) {
            $cpmkByMk[$row->mata_kuliah_id][] = $row->kode;
        }

        return view('import-nilai.index', compact('mataKuliahs', 'ta', 'bobotIssues', 'cpmkByMk'));
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /import-nilai/template/{mkId}
    // Download template Excel for a MK
    // ─────────────────────────────────────────────────────────────────
    public function downloadTemplate(int $mkId)
    {
        $mkQuery = DB::table('mata_kuliah')->where('id', $mkId);
        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mkQuery->where('program_id', Auth::user()->program_id);
        }
        $mk = $mkQuery->first();
        if (!$mk) abort(404);

        // Get CPMKs linked to this MK
        $cpmks = DB::table('mata_kuliah_cpmk as mkc')
            ->join('cpmk', 'mkc.cpmk_id', '=', 'cpmk.id')
            ->where('mkc.mata_kuliah_id', $mkId)
            ->orderBy('cpmk.kode')
            ->pluck('cpmk.kode')
            ->toArray();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Nilai');

        // Header row
        $headers = array_merge(['NIM', 'Nama'], $cpmks);
        foreach ($headers as $col => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E3A5F');
            $sheet->getStyle($cell)->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        // Sample rows
        $samples = [['11203462310001', 'Nama Mahasiswa 1'], ['11203462310002', 'Nama Mahasiswa 2']];
        foreach ($samples as $rowIdx => $sample) {
            $row = $rowIdx + 2;
            $sheet->setCellValue('A' . $row, $sample[0]);
            $sheet->setCellValue('B' . $row, $sample[1]);
            foreach ($cpmks as $col => $cpmk) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 3) . $row;
                $sheet->setCellValue($cell, 80);
            }
        }

        // Auto width
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $writer   = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'template_nilai_' . $mk->kode . '_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /import-nilai
    // Process Excel upload
    // ─────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'mk_id'     => 'required|integer|exists:mata_kuliah,id',
            'file'      => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'komponen'  => 'nullable|in:tugas,uts,uas,partisipatif,proyek',
            'ta'        => 'nullable|string|max:20',
        ]);

        $mkId      = (int) $request->mk_id;
        $komponen  = $request->komponen ?? 'partisipatif';
        $ta        = $request->ta ?? config('obe.tahun_akademik', '2025/2026');

        $mkQuery = DB::table('mata_kuliah')->where('id', $mkId);
        if (Auth::check() && Auth::user()->role !== 'admin' && Auth::user()->program_id) {
            $mkQuery->where('program_id', Auth::user()->program_id);
        }
        $mkCheck = $mkQuery->first();
        if (!$mkCheck) {
            return back()->withInput()->withErrors(['mk_id' => 'Mata kuliah tidak valid atau tidak sesuai program studi Anda.']);
        }

        // ── Validate bobot sums to 100 ────────────────────────────────
        $bobotErrors = $this->validateBobot($mkId);
        if (!empty($bobotErrors)) {
            return back()
                ->withInput()
                ->withErrors(['bobot' => 'Bobot penilaian tidak valid: ' . implode('; ', $bobotErrors)])
                ->with('warning', 'Bobot beberapa CPMK tidak berjumlah 100. Silakan perbaiki dulu di Konfigurasi Bobot.');
        }

        // ── Read CPMK list for this MK ────────────────────────────────
        $cpmkMap = DB::table('mata_kuliah_cpmk as mkc')
            ->join('cpmk', 'mkc.cpmk_id', '=', 'cpmk.id')
            ->where('mkc.mata_kuliah_id', $mkId)
            ->pluck('cpmk.id', 'cpmk.kode')   // ['CPMK031' => 5, ...]
            ->toArray();

        // Build sub_cpmk map: cpmk_id → [sub_cpmk_id, ...]
        $subCpmkMap = [];
        if (!empty($cpmkMap)) {
            $subs = DB::table('sub_cpmk as sc')
                ->join('mata_kuliah_sub_cpmk as msc', function ($j) use ($mkId) {
                    $j->on('msc.sub_cpmk_id', '=', 'sc.id')
                        ->where('msc.mata_kuliah_id', '=', $mkId);
                })
                ->whereIn('sc.cpmk_id', array_values($cpmkMap))
                ->select('sc.id', 'sc.cpmk_id')
                ->get();
            foreach ($subs as $s) {
                $subCpmkMap[$s->cpmk_id][] = $s->id;
            }
        }

        // ── Parse Excel ───────────────────────────────────────────────
        try {
            $path        = $request->file('file')->getRealPath();
            $spreadsheet = IOFactory::load($path);
            $sheet       = $spreadsheet->getActiveSheet();
            $rows        = $sheet->toArray(null, true, true, false);
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors(['file' => 'Gagal membaca file: ' . $e->getMessage()]);
        }

        if (empty($rows)) {
            return back()->withInput()->withErrors(['file' => 'File kosong atau tidak valid.']);
        }

        // ── Identify header row (first row) ──────────────────────────
        $header = array_map('trim', (array)$rows[0]);

        // Find column indices
        $nimCol  = $this->findColIndex($header, ['nim', 'no induk', 'nrp', 'student id']);
        $namaCol = $this->findColIndex($header, ['nama', 'name', 'nama mahasiswa']);

        if ($nimCol === null) {
            return back()->withInput()->withErrors(['file' => 'Kolom NIM tidak ditemukan. Pastikan header mengandung "NIM".']);
        }

        // Map CPMK columns: col_index → cpmk_kode
        $cpmkCols = [];
        foreach ($header as $idx => $cell) {
            $kode = strtoupper(trim((string)$cell));
            if (isset($cpmkMap[$kode])) {
                $cpmkCols[$idx] = $kode;
            }
        }

        if (empty($cpmkCols)) {
            return back()->withInput()->withErrors([
                'file' => 'Tidak ada kolom CPMK yang cocok. Gunakan kode CPMK sebagai header (contoh: CPMK031). MK ini punya: ' . implode(', ', array_keys($cpmkMap))
            ]);
        }

        // ── Process rows ──────────────────────────────────────────────
        $stats = ['inserted' => 0, 'updated' => 0, 'created_user' => 0, 'skipped' => 0, 'errors' => []];

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIdx => $row) {
                if ($rowIdx === 0) continue; // skip header
                $row = (array)$row;

                $nim  = trim((string)($row[$nimCol] ?? ''));
                $nama = $namaCol !== null ? trim((string)($row[$namaCol] ?? '')) : $nim;

                if (empty($nim) || !is_numeric(str_replace('-', '', $nim))) {
                    $stats['skipped']++;
                    continue;
                }

                // ── Auto-create mahasiswa if not exists ────────────────
                $mhsId = $this->findOrCreateMahasiswa($nim, $nama, $ta, $stats);
                if (!$mhsId) {
                    $stats['errors'][] = "Baris " . ($rowIdx + 1) . ": Gagal membuat mahasiswa NIM $nim";
                    continue;
                }

                // ── Enroll mahasiswa ke MK ─────────────────────────────
                DB::table('mahasiswa_mk')->updateOrInsert(
                    ['mahasiswa_id' => $mhsId, 'mata_kuliah_id' => $mkId, 'semester_aktif' => $ta],
                    ['status' => 'disetujui', 'updated_at' => now(), 'created_at' => now()]
                );

                // ── Insert nilai per CPMK → sub_cpmk ──────────────────
                foreach ($cpmkCols as $colIdx => $cpmkKode) {
                    $nilaiRaw = $row[$colIdx] ?? null;
                    if ($nilaiRaw === null || $nilaiRaw === '') continue;

                    $nilai   = min(100, max(0, (float)$nilaiRaw));
                    $cpmkId  = $cpmkMap[$cpmkKode];
                    $subIds  = $subCpmkMap[$cpmkId] ?? [];

                    if (empty($subIds)) {
                        // Fallback: no sub_cpmk linked, skip
                        $stats['errors'][] = "CPMK $cpmkKode tidak punya sub_cpmk di MK ini — skip mhs $nim";
                        continue;
                    }

                    foreach ($subIds as $subCpmkId) {
                        $existing = DB::table('nilai_sub_cpmk')
                            ->where('mahasiswa_id', $mhsId)
                            ->where('sub_cpmk_id', $subCpmkId)
                            ->first();

                        if ($existing) {
                            DB::table('nilai_sub_cpmk')
                                ->where('id', $existing->id)
                                ->update([
                                    $komponen    => $nilai,
                                    'updated_at' => now(),
                                ]);
                            $stats['updated']++;
                        } else {
                            DB::table('nilai_sub_cpmk')->insert([
                                'mahasiswa_id' => $mhsId,
                                'sub_cpmk_id'  => $subCpmkId,
                                $komponen      => $nilai,
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]);
                            $stats['inserted']++;
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ImportNilai error: ' . $e->getMessage());
            return back()->withInput()->withErrors(['file' => 'Terjadi kesalahan saat menyimpan: ' . $e->getMessage()]);
        }

        // ── Auto sync OBE after import ────────────────────────────────
        $syncResult = null;
        try {
            $syncResult = $this->obeService->syncFromSubCpmk($mkId, $ta);
        } catch (\Throwable $e) {
            Log::warning('Auto-sync OBE setelah import gagal: ' . $e->getMessage());
        }

        $msg = sprintf(
            'Import selesai: %d baris diinsert, %d diupdate, %d user baru dibuat.',
            $stats['inserted'],
            $stats['updated'],
            $stats['created_user']
        );
        if ($syncResult) {
            $msg .= sprintf(' OBE disinkronkan: %d CPMK, %d CPL records.', $syncResult['cpmk_records'] ?? 0, $syncResult['cpl_records'] ?? 0);
        }
        if (!empty($stats['errors'])) {
            $msg .= ' Peringatan: ' . count($stats['errors']) . ' baris bermasalah.';
        }

        return redirect()
            ->route('import-nilai.index', ['ta' => $ta])
            ->with('success', $msg)
            ->with('import_errors', $stats['errors']);
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /sync-obe/{mkId}
    // Standalone sync trigger
    // ─────────────────────────────────────────────────────────────────
    public function syncObe(Request $request, int $mkId)
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        // Validate bobot first
        $errors = $this->validateBobot($mkId);
        if (!empty($errors)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Bobot tidak valid: ' . implode('; ', $errors),
            ], 422);
        }

        try {
            $result = $this->obeService->syncFromSubCpmk($mkId, $ta);
            return response()->json($result);
        } catch (\Throwable $e) {
            Log::error("syncObe MK#$mkId: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────

    /**
     * Validate that bobot_penilaian for each CPMK in MK sums to 100.
     * Returns array of error messages (empty = valid).
     */
    private function validateBobot(int $mkId): array
    {
        $rows = DB::table('bobot_penilaian as bp')
            ->join('cpmk', 'bp.cpmk_id', '=', 'cpmk.id')
            ->where('bp.mata_kuliah_id', $mkId)
            ->select(
                'cpmk.kode',
                DB::raw('(bp.bobot_tugas + bp.bobot_uts + bp.bobot_uas + bp.bobot_partisipatif + bp.bobot_proyek) as total')
            )
            ->get();

        $errors = [];
        foreach ($rows as $r) {
            if ((int)$r->total !== 100) {
                $errors[] = "CPMK {$r->kode}: total bobot = {$r->total} (harus 100)";
            }
        }
        return $errors;
    }

    /**
     * Find case-insensitive column index from header array.
     */
    private function findColIndex(array $header, array $candidates): ?int
    {
        foreach ($header as $idx => $cell) {
            if (in_array(strtolower(trim((string)$cell)), $candidates, true)) {
                return $idx;
            }
        }
        return null;
    }

    /**
     * Find existing mahasiswas record or create user + mahasiswas.
     * Returns mahasiswas.id or null on failure.
     */
    private function findOrCreateMahasiswa(string $nim, string $nama, string $ta, array &$stats): ?int
    {
        // 1. Check mahasiswas by nim
        $mhs = DB::table('mahasiswas')->where('nim', $nim)->first();
        if ($mhs) return (int)$mhs->id;

        // 2. Check users by nim (column added by our previous script)
        $user = DB::table('users')->where('nim', $nim)->first()
            ?? DB::table('users')->where('email', "$nim@dummy.com")->first();

        $userId = null;
        if ($user) {
            $userId = $user->id;
        } else {
            // 3. Auto-create user
            try {
                // Ensure role enum has 'mahasiswa'
                $enumDef = DB::selectOne("SHOW COLUMNS FROM users LIKE 'role'")?->Type ?? '';
                if (!str_contains($enumDef, 'mahasiswa')) {
                    $new = str_replace(')', ",'mahasiswa')", $enumDef);
                    DB::statement("ALTER TABLE users MODIFY COLUMN role $new");
                }

                // Add nim column if missing
                $hasCols = collect(DB::select("SHOW COLUMNS FROM users"))->pluck('Field')->toArray();
                if (!in_array('nim', $hasCols)) {
                    DB::statement("ALTER TABLE users ADD COLUMN nim VARCHAR(20) NULL AFTER name");
                }

                $progId = Auth::check() ? Auth::user()->program_id : null;
                $userId = DB::table('users')->insertGetId([
                    'name'       => $nama ?: $nim,
                    'nim'        => $nim,
                    'email'      => "$nim@dummy.com",
                    'password'   => Hash::make('123'),
                    'role'       => 'mahasiswa',
                    'program_id' => $progId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $stats['created_user']++;
            } catch (\Throwable $e) {
                Log::warning("Auto-create user NIM $nim: " . $e->getMessage());
                return null;
            }
        }

        // 4. Auto-create mahasiswas record
        try {
            $angkatan = '20' . substr($nim, 4, 2);
            $progId = Auth::check() ? Auth::user()->program_id : null;
            $mhsId = DB::table('mahasiswas')->insertGetId([
                'user_id'        => $userId,
                'nim'            => $nim,
                'nama'           => $nama ?: $nim,
                'angkatan'       => $angkatan,
                'prodi'          => 'Sistem Informasi',
                'aktif'          => 1,
                'semester'       => 3,
                'tahun_akademik' => $ta,
                'program_id'     => $progId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
            return $mhsId;
        } catch (\Throwable $e) {
            Log::warning("Auto-create mahasiswas NIM $nim: " . $e->getMessage());
            return null;
        }
    }
}

