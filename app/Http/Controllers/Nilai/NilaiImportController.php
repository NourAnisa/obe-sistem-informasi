<?php

namespace App\Http\Controllers\Nilai;

use App\Http\Controllers\Controller;
use App\Models\DosenMataKuliah;
use App\Models\MataKuliah;
use App\Models\NilaiMahasiswa;
use App\Models\NilaiSubCpmk;
use App\Models\SubCpmk;
use App\Exports\NilaiMahasiswaImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * NilaiImportController
 *
 * Tanggung jawab:
 *   - import()         → import nilai dari file Excel/CSV
 *   - exportTemplate() → download template CSV kosong
 *
 * Merge target: sebagian fungsi ImportNilaiController (sub_cpmk-based)
 * juga bisa dipindah ke sini jika ingin fully consolidated.
 */
class NilaiImportController extends Controller
{
    private function getTaAktif(): string
    {
        return config('obe.tahun_akademik', '2025/2026');
    }

    /**
     * POST /nilai-mahasiswa/{mk}/import
     * Import nilai dari file Excel atau CSV.
     * Mendukung format SubCPMK (hasSubCpmk = true) maupun komponen langsung.
     */
    public function import(Request $request, int $mkId)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $ta = $request->input('ta', $this->getTaAktif());
        MataKuliah::findOrFail($mkId);

        $cpmkIds = DB::table('cpmk')
            ->join('mata_kuliah_cpmk', 'cpmk.id', '=', 'mata_kuliah_cpmk.cpmk_id')
            ->where('mata_kuliah_cpmk.mata_kuliah_id', $mkId)
            ->pluck('cpmk.id');

        $subCpmks      = SubCpmk::with('bobot')->whereIn('cpmk_id', $cpmkIds)->get();
        $hasSubCpmk    = $subCpmks->count() > 0;
        $subCpmkByKode = $subCpmks->keyBy(fn($s) => strtoupper($s->kode));
        $subBobotMap   = $subCpmks->keyBy('id')->map(fn($s) => $s->bobot)->filter();

        $bobotRow = DB::table('bobot_penilaian')->where('mata_kuliah_id', $mkId)->first();
        $bobot    = [
            'tugas'        => (int) ($bobotRow->bobot_tugas        ?? 20),
            'uts'          => (int) ($bobotRow->bobot_uts          ?? 20),
            'uas'          => (int) ($bobotRow->bobot_uas          ?? 20),
            'partisipatif' => (int) ($bobotRow->bobot_partisipatif ?? 20),
            'proyek'       => (int) ($bobotRow->bobot_proyek       ?? 20),
        ];

        $importer = new NilaiMahasiswaImport($mkId, $ta, $hasSubCpmk, $subCpmkByKode, $subBobotMap, $bobot);
        Excel::import($importer, $request->file('file'));

        $msg  = "Import selesai: {$importer->imported} mahasiswa berhasil diproses";
        if ($importer->skipped) {
            $msg .= ", {$importer->skipped} dilewati";
        }
        if (!empty($importer->errors)) {
            $msg .= '. ⚠ ' . implode(' | ', array_slice($importer->errors, 0, 5));
        }

        $type = empty($importer->errors) ? 'success' : 'warning';
        return redirect()->route('nilai-mahasiswa.show', ['mk' => $mkId, 'ta' => $ta])
            ->with($type, $msg);
    }

    /**
     * GET /nilai-mahasiswa/{mk}/export-template
     * Download template CSV kosong sesuai format SubCPMK atau komponen.
     */
    public function exportTemplate(Request $request, int $mkId)
    {
        $ta = $request->input('ta', $this->getTaAktif());
        $mk = MataKuliah::findOrFail($mkId);

        $cpmkIds = DB::table('cpmk')
            ->join('mata_kuliah_cpmk', 'cpmk.id', '=', 'mata_kuliah_cpmk.cpmk_id')
            ->where('mata_kuliah_cpmk.mata_kuliah_id', $mkId)
            ->pluck('cpmk.id');

        $hasSubCpmk = SubCpmk::whereIn('cpmk_id', $cpmkIds)->exists();

        $enrollments = DB::table('mahasiswa_mk')
            ->where('mata_kuliah_id', $mkId)->where('semester_aktif', $ta)
            ->join('mahasiswas', 'mahasiswa_mk.mahasiswa_id', '=', 'mahasiswas.id')
            ->select('mahasiswas.nim', 'mahasiswas.nama')
            ->orderBy('mahasiswas.nim')->get();

        $kode     = preg_replace('/[^A-Za-z0-9_]/', '_', $mk->kode ?? $mkId);
        $filename = "Template_Import_Nilai_{$kode}.csv";

        return response()->streamDownload(function () use ($enrollments, $hasSubCpmk, $cpmkIds) {
            $out = fopen('php://output', 'w');
            if ($hasSubCpmk) {
                $subs = SubCpmk::whereIn('cpmk_id', $cpmkIds)->orderBy('cpmk_id')->orderBy('id')->get();
                fputcsv($out, ['nim', 'nama', 'sub_cpmk_kode', 'tugas', 'uts', 'uas', 'partisipatif', 'proyek']);
                foreach ($enrollments as $mhs) {
                    foreach ($subs as $sub) {
                        fputcsv($out, [$mhs->nim, $mhs->nama, $sub->kode, 0, 0, 0, 0, 0]);
                    }
                }
            } else {
                fputcsv($out, ['nim', 'nama', 'tugas', 'uts', 'uas', 'partisipatif', 'proyek']);
                foreach ($enrollments as $mhs) {
                    fputcsv($out, [$mhs->nim, $mhs->nama, 0, 0, 0, 0, 0]);
                }
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }
}
