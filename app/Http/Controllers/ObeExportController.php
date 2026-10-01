<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObeExportController extends Controller
{
    /**
     * GET /obe/export-nilai-cpmk?mkId=&ta=
     * Export bobot CPMK to Excel (.xlsx) using PhpSpreadsheet
     */
    public function exportNilaiCpmk(Request $request)
    {
        $mkId = $request->input('mkId');

        $query = DB::table('bobot_penilaian as bp')
            ->join('cpmk', 'cpmk.id', '=', 'bp.cpmk_id')
            ->join('mata_kuliah', 'mata_kuliah.id', '=', 'bp.mata_kuliah_id')
            ->leftJoin('cpl', 'cpl.id', '=', 'cpmk.cpl_id')
            ->select(
                'mata_kuliah.kode as MK',
                'mata_kuliah.nama as NAMA_MK',
                DB::raw('COALESCE(cpl.kode, "") as CPL'),
                'cpmk.kode as CPMK',
                DB::raw('0 as MBKM'),
                DB::raw('0 as Quiz'),
                'bp.bobot_tugas as Tugas',
                'bp.bobot_uts as UTS',
                'bp.bobot_uas as UAS',
                'bp.bobot_partisipatif as Aktifitas_Partisipatif',
                'bp.bobot_proyek as Hasil_Proyek',
                DB::raw('(bp.bobot_tugas + bp.bobot_uts + bp.bobot_uas + bp.bobot_partisipatif + bp.bobot_proyek) as Total')
            )
            ->groupBy(
                'bp.id',
                'mata_kuliah.kode',
                'mata_kuliah.nama',
                'cpmk.kode',
                'cpl.kode',
                'bp.bobot_tugas',
                'bp.bobot_uts',
                'bp.bobot_uas',
                'bp.bobot_partisipatif',
                'bp.bobot_proyek'
            );

        if ($mkId) {
            $query->where('bp.mata_kuliah_id', $mkId);
        }

        $rows = $query->orderBy('mata_kuliah.kode')->orderBy('cpmk.kode')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bobot CPMK');

        // Header row — order: MK | Nama MK | CPL | CPMK | MBKM | Quiz | Tugas | UTS | UAS | Aktifitas Partisipatif | Hasil Proyek | Total
        $headers = [
            'MK',
            'Nama MK',
            'CPL',
            'CPMK',
            'MBKM',
            'Quiz',
            'Tugas',
            'UTS',
            'UAS',
            'Aktifitas Partisipatif',
            'Hasil Proyek',
            'Total'
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
        foreach ($rows as $idx => $row) {
            $r = $idx + 2;
            $sheet->setCellValue('A' . $r, $row->MK);
            $sheet->setCellValue('B' . $r, $row->NAMA_MK);
            $sheet->setCellValue('C' . $r, $row->CPL ?? '');
            $sheet->setCellValue('D' . $r, $row->CPMK);
            $sheet->setCellValue('E' . $r, 0);
            $sheet->setCellValue('F' . $r, 0);
            $sheet->setCellValue('G' . $r, $row->Tugas);
            $sheet->setCellValue('H' . $r, $row->UTS);
            $sheet->setCellValue('I' . $r, $row->UAS);
            $sheet->setCellValue('J' . $r, $row->Aktifitas_Partisipatif);
            $sheet->setCellValue('K' . $r, $row->Hasil_Proyek);
            $sheet->setCellValue('L' . $r, $row->Total);
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'bobot_cpmk_' . ($mkId ? "mk{$mkId}_" : 'all_') . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
