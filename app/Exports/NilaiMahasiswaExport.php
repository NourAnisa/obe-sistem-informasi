<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

/**
 * Export nilai mahasiswa (SubCPMK + Summary) untuk satu MK.
 * Sheet 1: Nilai Sub-CPMK per mahasiswa per SubCPMK
 * Sheet 2: Nilai Akhir / Summary per mahasiswa
 */
class NilaiMahasiswaExport implements WithMultipleSheets
{
    public function __construct(
        protected object     $mk,
        protected string     $ta,
        protected Collection $enrollments,
        protected Collection $subCpmks,
        protected Collection $nilaiSubMap,
        protected Collection $nilaiMap,
        protected bool       $hasSubCpmk,
    ) {}

    public function sheets(): array
    {
        $sheets = [new NilaiMahasiswaSummarySheet(
            $this->mk,
            $this->ta,
            $this->enrollments,
            $this->nilaiMap
        )];
        if ($this->hasSubCpmk) {
            array_unshift($sheets, new NilaiSubCpmkSheet(
                $this->mk,
                $this->ta,
                $this->enrollments,
                $this->subCpmks,
                $this->nilaiSubMap
            ));
        }
        return $sheets;
    }
}

class NilaiSubCpmkSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        protected object     $mk,
        protected string     $ta,
        protected Collection $enrollments,
        protected Collection $subCpmks,
        protected Collection $nilaiSubMap,
    ) {}

    public function title(): string
    {
        return 'Nilai SubCPMK';
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama',
            'Sub-CPMK Kode',
            'Sub-CPMK Deskripsi',
            'Tugas',
            'UTS',
            'UAS',
            'Partisipatif',
            'Proyek',
            'Nilai SubCPMK'
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();
        foreach ($this->enrollments as $mhs) {
            $nilaiSubs = $this->nilaiSubMap->get($mhs->mahasiswa_id, collect());
            foreach ($this->subCpmks as $sub) {
                $n = $nilaiSubs->get($sub->id);
                $rows->push([
                    $mhs->nim,
                    $mhs->nama,
                    $sub->kode,
                    $sub->deskripsi ?? '',
                    $n ? $n->tugas        : 0,
                    $n ? $n->uts          : 0,
                    $n ? $n->uas          : 0,
                    $n ? $n->partisipatif : 0,
                    $n ? $n->proyek       : 0,
                    $n ? round($n->nilai_subcpmk, 2) : 0,
                ]);
            }
        }
        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']]]];
    }
}

class NilaiMahasiswaSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        protected object     $mk,
        protected string     $ta,
        protected Collection $enrollments,
        protected Collection $nilaiMap,
    ) {}

    public function title(): string
    {
        return 'Nilai Akhir';
    }

    public function headings(): array
    {
        return [
            'NIM',
            'Nama',
            'Nilai Tugas',
            'Nilai UTS',
            'Nilai UAS',
            'Nilai Partisipatif',
            'Nilai Proyek',
            'Nilai Akhir',
            'Grade',
            'Lulus'
        ];
    }

    public function collection(): Collection
    {
        return $this->enrollments->map(function ($mhs) {
            $n = $this->nilaiMap->get($mhs->mahasiswa_id);
            return [
                $mhs->nim,
                $mhs->nama,
                $n ? round($n->nilai_tugas        ?? 0, 2) : '',
                $n ? round($n->nilai_uts           ?? 0, 2) : '',
                $n ? round($n->nilai_uas           ?? 0, 2) : '',
                $n ? round($n->nilai_partisipatif  ?? 0, 2) : '',
                $n ? round($n->nilai_proyek        ?? 0, 2) : '',
                $n ? round($n->nilai_akhir         ?? 0, 2) : '',
                $n ? ($n->grade ?? '-') : '-',
                $n ? ($n->lulus ? 'Lulus' : 'Tidak Lulus') : '-',
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D1FAE5']]]];
    }
}
