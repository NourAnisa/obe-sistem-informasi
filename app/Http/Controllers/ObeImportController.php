<?php

namespace App\Http\Controllers;

use App\Models\BobotPenilaian;
use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObeImportController extends Controller
{
    /**
     * POST /obe/import-nilai-cpmk
     * Import bobot CPMK from .xlsx file and auto-map CPL → CPMK.
     */
    public function importNilaiCpmk(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $file        = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, true);

        // Expect row 1 as header
        $header = array_map('trim', $rows[1] ?? []);
        // Map column letters to header names
        $colMap = array_flip($header);

        $errors   = [];
        $imported = 0;

        for ($i = 2; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? null;
            if (!$row) continue;

            $getValue = function (string $name) use ($row, $colMap) {
                $col = $colMap[$name] ?? null;
                return $col ? trim($row[$col] ?? '') : null;
            };

            $mkKode   = $getValue('MK');
            $cpmkKode = $getValue('CPMK');
            $cplRaw   = $getValue('CPL');

            // Support both "NAMA MK" and "Nama MK" header variants
            // (no action needed here — MK kode is the lookup key)

            if (!$mkKode || !$cpmkKode) continue; // skip empty rows

            $mk   = MataKuliah::where('kode', $mkKode)->first();
            $cpmk = Cpmk::where('kode', $cpmkKode)->first();

            if (!$mk) {
                $errors[] = "Baris {$i}: MK '{$mkKode}' tidak ditemukan";
                continue;
            }
            if (!$cpmk) {
                $errors[] = "Baris {$i}: CPMK '{$cpmkKode}' tidak ditemukan";
                continue;
            }

            $tugas  = (int)($getValue('Tugas') ?? 0);
            $uts    = (int)($getValue('UTS') ?? 0);
            $uas    = (int)($getValue('UAS') ?? 0);
            $partis = (int)($getValue('Aktifitas Partisipatif') ?? 0);
            $proyek = (int)($getValue('Hasil Proyek') ?? 0);
            $total  = $tugas + $uts + $uas + $partis + $proyek;

            if ($total !== 100) {
                $errors[] = "Baris {$i}: CPMK {$cpmkKode} total bobot {$total} (harus 100)";
                continue;
            }

            // Update bobot_penilaian
            BobotPenilaian::updateOrCreate(
                ['mata_kuliah_id' => $mk->id, 'cpmk_id' => $cpmk->id],
                [
                    'bobot_tugas'        => $tugas,
                    'bobot_uts'          => $uts,
                    'bobot_uas'          => $uas,
                    'bobot_partisipatif' => $partis,
                    'bobot_proyek'       => $proyek,
                    'bobot'              => $total,
                ]
            );

            // Auto CPL mapping (supports comma-separated CPL)
            if ($cplRaw) {
                $cplList = explode(',', $cplRaw);
                // Clear old mapping for this CPMK
                DB::table('cpmk_cpl')->where('cpmk_id', $cpmk->id)->delete();

                foreach ($cplList as $kode) {
                    $kode = trim($kode);
                    if (!$kode) continue;
                    $cpl = Cpl::where('kode', $kode)->first();
                    if (!$cpl) {
                        $errors[] = "Baris {$i}: CPL '{$kode}' tidak ditemukan";
                        continue;
                    }
                    DB::table('cpmk_cpl')->updateOrInsert(
                        ['cpmk_id' => $cpmk->id, 'cpl_id' => $cpl->id],
                        []
                    );
                }
            }

            $imported++;
        }

        $message = "Import selesai: {$imported} baris berhasil.";
        if (!empty($errors)) {
            $message .= ' Errors: ' . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) $message .= ' (dan ' . (count($errors) - 5) . ' lainnya)';
        }

        return back()->with(empty($errors) ? 'success' : 'warning', $message);
    }
}
