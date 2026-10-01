<?php

namespace App\Http\Controllers\Nilai;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\NilaiMahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * NilaiExportController
 *
 * Tanggung jawab:
 *   - export() → Export Excel "Daftar Nilai" format UNISM (PhpSpreadsheet)
 */
class NilaiExportController extends Controller
{
    private function getTaAktif(): string
    {
        return config('obe.tahun_akademik', '2025/2026');
    }

    /**
     * GET /nilai-mahasiswa/{mk}/export
     * Export nilai mahasiswa to Excel (.xlsx) using PhpSpreadsheet.
     */
    public function export(Request $request, int $mkId)
    {
        $ta = $request->input('ta', $this->getTaAktif());
        $mk = MataKuliah::findOrFail($mkId);

        // Fetch enrollments (mahasiswa yang KRS-nya disetujui)
        $enrollments = DB::table('mahasiswa_mk')
            ->where('mata_kuliah_id', $mkId)
            ->where('semester_aktif', $ta)
            ->where('mahasiswa_mk.status', 'disetujui')
            ->join('mahasiswas', 'mahasiswa_mk.mahasiswa_id', '=', 'mahasiswas.id')
            ->select('mahasiswas.id as mahasiswa_id', 'mahasiswas.nim', 'mahasiswas.nama')
            ->orderBy('mahasiswas.nim')
            ->get();

        // Fetch grades
        $nilais = NilaiMahasiswa::where('mata_kuliah_id', $mkId)
            ->where('semester_aktif', $ta)
            ->get()
            ->keyBy('mahasiswa_id');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Nilai');

        // Header row
        $headers = [
            'NIM',
            'Nama Mahasiswa',
            'Tugas',
            'UTS',
            'UAS',
            'Partisipatif',
            'Proyek',
            'Nilai Akhir',
            'Grade',
            'Status'
        ];

        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $sheet->getStyle($col . '1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E3A5F');
            $sheet->getStyle($col . '1')->getFont()->getColor()->setARGB('FFFFFFFF');
        }

        // Data rows
        foreach ($enrollments as $idx => $mhs) {
            $r = $idx + 2;
            $nilai = $nilais->get($mhs->mahasiswa_id);

            $sheet->setCellValue('A' . $r, $mhs->nim);
            $sheet->setCellValue('B' . $r, $mhs->nama);
            $sheet->setCellValue('C' . $r, $nilai ? $nilai->nilai_tugas : 0);
            $sheet->setCellValue('D' . $r, $nilai ? $nilai->nilai_uts : 0);
            $sheet->setCellValue('E' . $r, $nilai ? $nilai->nilai_uas : 0);
            $sheet->setCellValue('F' . $r, $nilai ? $nilai->nilai_partisipatif : 0);
            $sheet->setCellValue('G' . $r, $nilai ? $nilai->nilai_proyek : 0);
            $sheet->setCellValue('H' . $r, $nilai ? $nilai->nilai_akhir : 0);
            $sheet->setCellValue('I' . $r, $nilai ? $nilai->grade : 'E');
            $sheet->setCellValue('J' . $r, ($nilai && $nilai->lulus) ? 'Lulus' : 'Tidak Lulus');
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $kodeClean = preg_replace('/[^A-Za-z0-9_]/', '_', $mk->kode ?? $mkId);
        $filename = 'daftar_nilai_' . $kodeClean . '_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
