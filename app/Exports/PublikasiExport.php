<?php

namespace App\Exports;

use App\Models\PublikasiDosen;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PublikasiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = PublikasiDosen::with('dosen')
            ->orderBy('tahun', 'desc')
            ->orderBy('judul');

        if (!empty($this->filters['tahun'])) {
            $query->where('tahun', $this->filters['tahun']);
        }
        if (!empty($this->filters['dosen_id'])) {
            $query->where('dosen_id', $this->filters['dosen_id']);
        }
        if (!empty($this->filters['tipe'])) {
            $query->where('tipe', $this->filters['tipe']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Dosen', 'Tahun', 'Judul', 'Authors', 'Tipe', 'Jenis', 'Sumber', 'Link'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->dosen?->name ?? '-',
            $row->tahun,
            $row->judul,
            $row->authors,
            ucfirst($row->tipe),
            $row->jenis ? ucfirst($row->jenis) : '-',
            $row->sumber,
            $row->link,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']]],
        ];
    }
}
