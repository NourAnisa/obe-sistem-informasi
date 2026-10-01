<?php

namespace App\Http\Controllers;

use App\Models\CplSndikti;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CplSndiktiController extends Controller
{
    public function index()
    {
        $all = CplSndikti::with('cpls')->orderBy('kode')->get();

        $grouped = $all->groupBy('kategori');

        $orderedKategori = ['Sikap', 'KU', 'KK', 'PP'];

        $data = collect($orderedKategori)->mapWithKeys(function ($kategori) use ($grouped) {
            return [$kategori => $grouped->get($kategori, collect())];
        });

        return view('cpl-sndikti.index', compact('data'));
    }

    public function export()
    {
        $all = CplSndikti::with('cpls')->orderBy('kode')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('CPL SN-DIKTI');

        // ── Header row ────────────────────────────────────────────
        $headers = ['No', 'Kategori', 'Kode SN-DIKTI', 'CPL Prodi', 'Deskripsi'];
        foreach ($headers as $colIdx => $header) {
            $col = chr(65 + $colIdx); // A, B, C, D, E
            $sheet->setCellValue("{$col}1", $header);
        }

        // Header style
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '93C5FD']]],
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── Data rows ─────────────────────────────────────────────
        $row = 2;
        foreach ($all as $idx => $item) {
            $cplKodes = $item->cpls->pluck('kode')->join(', ');

            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $item->kategori);
            $sheet->setCellValue("C{$row}", $item->kode);
            $sheet->setCellValue("D{$row}", $cplKodes);
            $sheet->setCellValue("E{$row}", $item->deskripsi);

            // Alternate row shading
            $bgColor = ($idx % 2 === 0) ? 'F0F7FF' : 'FFFFFF';
            $rowStyle = [
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DBEAFE']]],
            ];
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($rowStyle);
            $sheet->getStyle("A{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $row++;
        }

        // ── Column widths ─────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(80);

        // ── Stream to browser ─────────────────────────────────────
        $writer   = new Xlsx($spreadsheet);
        $filename = 'CPL_SN-DIKTI_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}
