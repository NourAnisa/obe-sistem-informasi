<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MahasiswaCplExport implements WithMultipleSheets
{
    public function __construct(
        protected $mahasiswa,
        protected $cplAchievements,
        protected $cpmkAchievements
    ) {}

    public function sheets(): array
    {
        return [
            new MahasiswaCplSheetCpl($this->mahasiswa, $this->cplAchievements),
            new MahasiswaCplSheetCpmk($this->mahasiswa, $this->cpmkAchievements),
        ];
    }
}

class MahasiswaCplSheetCpl implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(protected $mahasiswa, protected $cplAchievements) {}

    public function title(): string
    {
        return 'Capaian CPL';
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Kode CPL',
            'Deskripsi CPL',
            'Nilai CPL',
            'Threshold',
            'Jumlah CPMK',
            'CPMK Tercapai',
            'Status',
        ];
    }

    public function collection()
    {
        return $this->cplAchievements->map(fn($row) => [
            $this->mahasiswa->nim,
            $this->mahasiswa->nama,
            $row->kode,
            $row->deskripsi,
            round($row->nilai_cpl ?? 0, 2),
            round($row->threshold ?? 60, 1),
            $row->jumlah_cpmk ?? 0,
            $row->jumlah_achieved ?? 0,
            ($row->achieved ? 'Tercapai' : 'Belum Tercapai'),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']]],
        ];
    }
}

class MahasiswaCplSheetCpmk implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(protected $mahasiswa, protected $cpmkAchievements) {}

    public function title(): string
    {
        return 'Capaian CPMK';
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama Mahasiswa',
            'Kode MK',
            'Nama MK',
            'Kode CPMK',
            'Deskripsi CPMK',
            'Nilai Tugas',
            'Nilai UTS',
            'Nilai UAS',
            'Nilai Partisipatif',
            'Nilai Proyek',
            'Nilai CPMK',
            'Threshold',
            'Status',
        ];
    }

    public function collection()
    {
        return $this->cpmkAchievements->map(fn($row) => [
            $this->mahasiswa->nim,
            $this->mahasiswa->nama,
            $row->mk_kode,
            $row->mk_nama,
            $row->cpmk_kode,
            $row->cpmk_deskripsi,
            round($row->nilai_tugas ?? 0, 2),
            round($row->nilai_uts ?? 0, 2),
            round($row->nilai_uas ?? 0, 2),
            round($row->nilai_partisipatif ?? 0, 2),
            round($row->nilai_proyek ?? 0, 2),
            round($row->nilai_cpmk ?? 0, 2),
            round($row->threshold ?? 60, 1),
            ($row->achieved ? 'Tercapai' : 'Belum Tercapai'),
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D1FAE5']]],
        ];
    }
}
