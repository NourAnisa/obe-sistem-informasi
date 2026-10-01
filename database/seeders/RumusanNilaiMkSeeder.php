<?php

namespace Database\Seeders;

use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\MataKuliah;
use App\Models\RumusanNilaiMk;
use Illuminate\Database\Seeder;

class RumusanNilaiMkSeeder extends Seeder
{
    public function run(): void
    {
        // CPL-level total skor_maks (sum of all CPMK skor_maks across all MK for each CPL)
        $cplTotals = [
            'CPL01' => 430,
            'CPL02' => 540,
            'CPL03' => 680,
            'CPL04' => 580,
            'CPL05' => 320,
            'CPL06' => 910,
            'CPL07' => 275,
            'CPL08' => 660,
            'CPL09' => 450,
            'CPL10' => 455,
        ];
        foreach ($cplTotals as $kode => $total) {
            Cpl::where('kode', $kode)->update(['total_skor_maks' => $total]);
        }

        // Format: [cpl_kode, mk_kode, cpmk_kode, skor_maks, skor_min, total_maks, total_min, keterangan]
        $data = [
            ['CPL01', 'FSaint.001',     'CPMK015', 45, 27, 100, 60, 'batas minimal kelulusan setiap CPMK dituangkan di buku kurikulum'],
            ['CPL03', 'FSaint.001',     'CPMK034', 55, 33, null, null, null],
            ['CPL03', 'UNISM.001',      'CPMK033', 20, 12, 100, 60, 'batas minimal kelulusan mata kuliah 60%; nilai mata kuliah minimum lulus adalah 60 (C+)'],
            ['CPL05', 'UNISM.001',      'CPMK052', 20, 12, null, null, null],
            ['CPL06', 'UNISM.001',      'CPMK064', 15,  9, null, null, null],
            ['CPL07', 'UNISM.001',      'CPMK073', 15,  9, null, null, null],
            ['CPL09', 'UNISM.001',      'CPMK091', 15,  9, null, null, null],
            ['CPL09', 'UNISM.001',      'CPMK092', 15,  9, null, null, null],
            ['CPL08', 'UNISM.MKPU.001', 'CPMK081', 25, 15, 100, 60, null],
            ['CPL08', 'UNISM.MKPU.001', 'CPMK082', 25, 15, null, null, null],
            ['CPL10', 'UNISM.MKPU.001', 'CPMK101', 25, 15, null, null, null],
            ['CPL10', 'UNISM.MKPU.001', 'CPMK102', 25, 15, null, null, null],
            ['CPL08', 'UNISM.MKPU.002', 'CPMK081', 25, 15, 100, 60, null],
            ['CPL08', 'UNISM.MKPU.002', 'CPMK082', 25, 15, null, null, null],
            ['CPL08', 'UNISM.MKPU.002', 'CPMK101', 25, 15, null, null, null],
            ['CPL08', 'UNISM.MKPU.002', 'CPMK102', 25, 15, null, null, null],
            ['CPL08', 'UNISM.MKPU.003', 'CPMK081', 25, 15, 100, 60, null],
            ['CPL08', 'UNISM.MKPU.003', 'CPMK082', 25, 15, null, null, null],
            ['CPL09', 'UNISM.MKPU.003', 'CPMK092', 25, 15, null, null, null],
            ['CPL10', 'UNISM.MKPU.003', 'CPMK101', 15,  9, null, null, null],
            ['CPL10', 'UNISM.MKPU.003', 'CPMK102', 10,  6, null, null, null],
            ['CPL09', 'UNISM.MKU.001',  'CPMK092', 100, 60, 100, 60, null],
            ['CPL10', 'UNISM.MKU.002',  'CPMK102', 100, 60, 100, 60, null],
            ['CPL08', 'UNISM.MKU.003',  'CPMK081', 50, 30, 100, 60, null],
            ['CPL08', 'UNISM.MKU.003',  'CPMK082', 50, 30, null, null, null],
            ['CPL08', 'UNISM.MKU.004',  'CPMK081', 50, 30, 100, 60, null],
            ['CPL08', 'UNISM.MKU.004',  'CPMK082', 50, 30, null, null, null],
            ['CPL08', 'UNISM.MKU.005',  'CPMK081', 50, 30, 100, 60, null],
            ['CPL08', 'UNISM.MKU.005',  'CPMK082', 50, 30, null, null, null],
            ['CPL08', 'UNISM.MKU.006',  'CPMK081', 50, 30, 100, 60, null],
            ['CPL08', 'UNISM.MKU.006',  'CPMK082', 50, 30, null, null, null],
            ['CPL01', 'UNISM.SI001',    'CPMK012', 45, 27, 100, 60, null],
            ['CPL03', 'UNISM.SI001',    'CPMK031', 55, 33, null, null, null],
            ['CPL03', 'UNISM.SI002',    'CPMK031', 20, 12, 100, 60, null],
            ['CPL03', 'UNISM.SI002',    'CPMK033', 35, 21, null, null, null],
            ['CPL06', 'UNISM.SI002',    'CPMK061', 20, 12, null, null, null],
            ['CPL07', 'UNISM.SI002',    'CPMK071', 25, 15, null, null, null],
            ['CPL06', 'UNISM.SI003',    'CPMK062', 100, 60, 100, 60, null],
            ['CPL04', 'UNISM.SI004',    'CPMK042', 35, 21, 100, 60, null],
            ['CPL05', 'UNISM.SI004',    'CPMK051', 25, 15, null, null, null],
            ['CPL07', 'UNISM.SI004',    'CPMK072', 40, 24, null, null, null],
            ['CPL10', 'UNISM.SI005',    'CPMK101', 100, 60, 100, 60, null],
            ['CPL10', 'UNISM.SI006',    'CPMK101', 100, 60, 100, 60, null],
            ['CPL02', 'UNISM.SI007',    'CPMK021', 35, 21, 100, 60, null],
            ['CPL02', 'UNISM.SI007',    'CPMK022', 45, 27, null, null, null],
            ['CPL02', 'UNISM.SI007',    'CPMK023', 20, 12, null, null, null],
            ['CPL02', 'UNISM.SI008',    'CPMK023', 30, 18, 100, 60, null],
            ['CPL03', 'UNISM.SI008',    'CPMK024', 30, 18, null, null, null],
            ['CPL09', 'UNISM.SI008',    'CPMK091', 40, 24, null, null, null],
            ['CPL02', 'UNISM.SI009',    'CPMK023', 20, 12, 100, 60, null],
            ['CPL02', 'UNISM.SI009',    'CPMK024', 35, 21, null, null, null],
            ['CPL03', 'UNISM.SI009',    'CPMK091', 20, 12, null, null, null],
            ['CPL09', 'UNISM.SI009',    'CPMK092', 25, 15, null, null, null],
            ['CPL06', 'UNISM.SI010',    'CPMK064', 100, 60, 100, 60, null],
            ['CPL04', 'UNISM.SI011',    'CPMK041', 100, 60, 100, 60, null],
            ['CPL06', 'UNISM.SI012',    'CPMK063', 100, 60, 100, 60, null],
            ['CPL02', 'UNISM.SI013',    'CPMK023', 20, 12, 100, 60, null],
            ['CPL02', 'UNISM.SI013',    'CPMK024', 35, 21, null, null, null],
            ['CPL09', 'UNISM.SI013',    'CPMK091', 20, 12, null, null, null],
            ['CPL09', 'UNISM.SI013',    'CPMK092', 25, 15, null, null, null],
            ['CPL02', 'UNISM.SI014',    'CPMK021', 20, 12, 100, 60, null],
            ['CPL02', 'UNISM.SI014',    'CPMK023', 35, 21, null, null, null],
            ['CPL02', 'UNISM.SI014',    'CPMK024', 20, 12, null, null, null],
            ['CPL09', 'UNISM.SI014',    'CPMK091', 25, 15, null, null, null],
            ['CPL06', 'UNISM.SI015',    'CPMK064', 100, 60, 100, 60, null],
            ['CPL07', 'UNISM.SI016',    'CPMK071', 30, 18, 100, 60, null],
            ['CPL07', 'UNISM.SI016',    'CPMK072', 30, 18, null, null, null],
            ['CPL07', 'UNISM.SI016',    'CPMK073', 40, 24, null, null, null],
            ['CPL06', 'UNISM.SI017',    'CPMK061', 100, 60, 100, 60, null],
            ['CPL01', 'UNISM.SI018',    'CPMK013', 20, 12, 100, 60, null],
            ['CPL01', 'UNISM.SI018',    'CPMK014', 20, 12, null, null, null],
            ['CPL03', 'UNISM.SI018',    'CPMK033', 20, 12, null, null, null],
            ['CPL06', 'UNISM.SI018',    'CPMK062', 20, 12, null, null, null],
            ['CPL07', 'UNISM.SI018',    'CPMK072', 20, 12, null, null, null],
            ['CPL05', 'UNISM.SI019',    'CPMK051', 100, 60, 100, 60, null],
            ['CPL04', 'UNISM.SI020',    'CPMK042', 35, 21, 100, 60, null],
            ['CPL05', 'UNISM.SI020',    'CPMK051', 20, 12, null, null, null],
            ['CPL05', 'UNISM.SI020',    'CPMK052', 45, 27, null, null, null],
            ['CPL04', 'UNISM.SI021',    'CPMK041', 100, 60, 100, 60, null],
            ['CPL03', 'UNISM.SI022',    'CPMK031', 100, 60, 100, 60, null],
            ['CPL04', 'UNISM.SI023',    'CPMK041', 100, 60, 100, 60, null],
            ['CPL01', 'UNISM.SI024',    'CPMK011', 100, 60, 100, 60, null],
            ['CPL05', 'UNISM.SI025',    'CPMK052', 30, 18, 100, 60, null],
            ['CPL07', 'UNISM.SI025',    'CPMK073', 30, 18, null, null, null],
            ['CPL09', 'UNISM.SI025',    'CPMK092', 40, 24, null, null, null],
            ['CPL08', 'UNISM.SI026',    'CPMK082', 40, 24, 100, 60, null],
            ['CPL10', 'UNISM.SI026',    'CPMK101', 25, 15, null, null, null],
            ['CPL10', 'UNISM.SI026',    'CPMK102', 35, 21, null, null, null],
            ['CPL09', 'UNISM.SI027',    'CPMK091', 100, 60, 100, 60, null],
            ['CPL02', 'UNISM.SI028',    'CPMK023', 50, 30, 100, 60, null],
            ['CPL02', 'UNISM.SI028',    'CPMK024', 50, 30, null, null, null],
            ['CPL03', 'UNISM.SI029',    'CPMK032', 40, 24, 100, 60, null],
            ['CPL06', 'UNISM.SI029',    'CPMK061', 30, 18, null, null, null],
            ['CPL06', 'UNISM.SI029',    'CPMK062', 30, 18, null, null, null],
            ['CPL03', 'UNISM.SI030',    'CPMK032', 40, 24, 100, 60, null],
            ['CPL06', 'UNISM.SI030',    'CPMK061', 30, 18, null, null, null],
            ['CPL06', 'UNISM.SI030',    'CPMK062', 30, 18, null, null, null],
            ['CPL02', 'UNISM.SI031',    'CPMK022', 25, 15, 100, 60, null],
            ['CPL03', 'UNISM.SI031',    'CPMK032', 35, 21, null, null, null],
            ['CPL06', 'UNISM.SI031',    'CPMK061', 20, 12, null, null, null],
            ['CPL06', 'UNISM.SI031',    'CPMK062', 20, 12, null, null, null],
            ['CPL04', 'UNISM.SI032',    'CPMK041', 100, 60, 100, 60, null],
            ['CPL01', 'UNISM.SI033',    'CPMK011', 100, 60, 100, 60, null],
            ['CPL05', 'UNISM.SI034',    'CPMK052', 20, 12, 100, 60, null],
            ['CPL06', 'UNISM.SI034',    'CPMK064', 20, 12, null, null, null],
            ['CPL08', 'UNISM.SI034',    'CPMK082', 20, 12, null, null, null],
            ['CPL09', 'UNISM.SI034',    'CPMK092', 20, 12, null, null, null],
            ['CPL10', 'UNISM.SI034',    'CPMK101', 10,  6, null, null, null],
            ['CPL10', 'UNISM.SI034',    'CPMK102', 10,  6, null, null, null],
            ['CPL03', 'UNISM.SI035',    'CPMK031', 20, 12, 100, 60, null],
            ['CPL06', 'UNISM.SI035',    'CPMK061', 35, 21, null, null, null],
            ['CPL07', 'UNISM.SI035',    'CPMK071', 20, 12, null, null, null],
            ['CPL07', 'UNISM.SI035',    'CPMK072', 25, 15, null, null, null],
            ['CPL01', 'UNISM.SI036',    'CPMK012', 30, 18, 100, 60, null],
            ['CPL01', 'UNISM.SI036',    'CPMK013', 30, 18, null, null, null],
            ['CPL01', 'UNISM.SI036',    'CPMK014', 40, 24, null, null, null],
            ['CPL03', 'UNISM.SI037',    'CPMK031', 50, 30, 100, 60, null],
            ['CPL03', 'UNISM.SI037',    'CPMK032', 50, 30, null, null, null],
            ['CPL03', 'UNISM.SI038',    'CPMK032', 30, 18, 100, 60, null],
            ['CPL04', 'UNISM.SI038',    'CPMK042', 30, 18, null, null, null],
            ['CPL06', 'UNISM.SI038',    'CPMK063', 40, 24, null, null, null],
            ['CPL06', 'UNISM.SI039',    'CPMK063', 100, 60, 100, 60, null],
            ['CPL02', 'UNISM.SI040',    'CPMK023', 50, 30, 100, 60, null],
            ['CPL02', 'UNISM.SI040',    'CPMK024', 50, 30, null, null, null],
            ['CPL04', 'UNISM.SI041',    'CPMK012', 40, 24, 100, 60, null],
            ['CPL04', 'UNISM.SI041',    'CPMK013', 40, 24, null, null, null],
            ['CPL05', 'UNISM.SI041',    'CPMK051', 20, 12, null, null, null],
            ['CPL03', 'UNISM.SI042',    'CPMK033', 30, 18, 100, 60, null],
            ['CPL03', 'UNISM.SI042',    'CPMK034', 30, 18, null, null, null],
            ['CPL05', 'UNISM.SI042',    'CPMK051', 40, 24, null, null, null],
        ];

        $mkCache   = MataKuliah::all()->keyBy('kode');
        $cpmkCache = Cpmk::all()->keyBy('kode');
        $cplCache  = Cpl::all()->keyBy('kode');

        foreach ($data as [$cplKode, $mkKode, $cpmkKode, $skorMaks, $skorMin, $totalMaks, $totalMin, $ket]) {
            $mk   = $mkCache->get($mkKode);
            $cpmk = $cpmkCache->get($cpmkKode);
            if (!$mk || !$cpmk) continue;

            RumusanNilaiMk::updateOrCreate(
                ['mata_kuliah_id' => $mk->id, 'cpmk_id' => $cpmk->id],
                [
                    'cpl_id'      => $cplCache->get($cplKode)?->id,
                    'skor_maks'   => $skorMaks,
                    'skor_min'    => $skorMin,
                    'total_maks'  => $totalMaks,
                    'total_min'   => $totalMin,
                    'keterangan'  => $ket,
                ]
            );
        }
    }
}
