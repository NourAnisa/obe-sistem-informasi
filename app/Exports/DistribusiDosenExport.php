<?php

namespace App\Exports;

use App\Models\DosenMataKuliah;
use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;

class DistribusiDosenExport
{
    public function __construct(
        private string $ta,
        private ?int   $semester = null
    ) {}

    /**
     * Stream an XLSX download to the browser.
     */
    public function download(string $filename = 'distribusi-dosen.xlsx'): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $rows = $this->getData();

        return response()->streamDownload(function () use ($rows) {
            $writer = new Writer();
            $writer->openToFile('php://output');

            // ── Header row ──────────────────────────────────────
            $headerStyle = (new Style())
                ->setFontBold()
                ->setFontSize(11)
                ->setBackgroundColor('4472C4')
                ->setFontColor(Color::WHITE);

            $headers = [
                'No',
                'Dosen',
                'NIK',
                'Mata Kuliah',
                'Kode MK',
                'Kelas',
                'Semester',
                'Tahun Akademik',
                'Peran',
                'Jumlah SKS',
                'Status',
            ];

            $writer->addRow(Row::fromValues($headers, $headerStyle));

            // ── Data rows ────────────────────────────────────────
            foreach ($rows as $i => $d) {
                $writer->addRow(Row::fromValues([
                    $i + 1,
                    $d->dosen->name ?? '-',
                    $d->dosen->nik  ?? '-',
                    $d->mataKuliah->nama ?? '-',
                    $d->mataKuliah->kode ?? '-',
                    $d->kelas ?? '-',
                    (int) $d->semester,
                    $d->tahun_akademik,
                    ucfirst(str_replace('_', ' ', $d->peran)),
                    $d->jumlah_sks !== null ? (int) $d->jumlah_sks : '-',
                    ucfirst($d->status ?? 'aktif'),
                ]));
            }

            $writer->close();
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getData()
    {
        return DosenMataKuliah::with(['dosen', 'mataKuliah'])
            ->whereHas('mataKuliah')
            ->where('tahun_akademik', $this->ta)
            ->when($this->semester, fn($q) => $q->where('semester', $this->semester))
            ->orderBy('semester')
            ->orderBy('mata_kuliah_id')
            ->get();
    }
}
