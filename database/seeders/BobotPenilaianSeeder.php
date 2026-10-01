<?php

namespace Database\Seeders;

use App\Models\BobotPenilaian;
use App\Models\Cpmk;
use App\Models\MataKuliah;
use Illuminate\Database\Seeder;

class BobotPenilaianSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [mk_kode, cpmk_kode, tugas, uts, uas, partisipatif, proyek]
        // bobot (total) is calculated as the sum of the five technique values.
        // Source: OBE bobot penilaian per-technique breakdown document.
        // Notes:
        //   UNISM.SI008 CPMK024 listed under CPL03 in source doc (should be CPL02) — seeded by CPMK key
        //   UNISM.SI026 uses CPMK082 per per-technique doc (previous aggregate doc showed CPMK083)
        $data = [
            // [mk_kode, cpmk_kode, tugas, uts, uas, partisipatif, proyek]
            ['FSaint.001',     'CPMK015',  5,  5, 10, 10, 15],
            ['FSaint.001',     'CPMK034', 10, 10, 10, 10, 15],
            ['UNISM.001',      'CPMK033',  0,  0,  0, 10, 10],
            ['UNISM.001',      'CPMK052',  0,  0,  0, 10, 10],
            ['UNISM.001',      'CPMK064',  0,  0,  0,  5, 10],
            ['UNISM.001',      'CPMK073',  0,  0,  0,  5, 10],
            ['UNISM.001',      'CPMK091',  0,  0,  0,  5, 10],
            ['UNISM.001',      'CPMK092',  0,  0,  0,  5, 10],
            ['UNISM.MKPU.001', 'CPMK081',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.001', 'CPMK082',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.001', 'CPMK101',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.001', 'CPMK102',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.002', 'CPMK081',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.002', 'CPMK082',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.002', 'CPMK101',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.002', 'CPMK102',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.003', 'CPMK081',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.003', 'CPMK082',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.003', 'CPMK092',  0,  0,  0, 10, 15],
            ['UNISM.MKPU.003', 'CPMK101',  0,  0,  0,  5, 10],
            ['UNISM.MKPU.003', 'CPMK102',  0,  0,  0,  5,  5],
            ['UNISM.MKU.001',  'CPMK092', 20, 10, 20, 20, 30],
            ['UNISM.MKU.002',  'CPMK102',  0,  0,  0, 50, 50],
            ['UNISM.MKU.003',  'CPMK081', 10,  5, 10, 10, 15],
            ['UNISM.MKU.003',  'CPMK082', 10,  5, 10, 10, 15],
            ['UNISM.MKU.004',  'CPMK081', 10,  5, 10, 10, 15],
            ['UNISM.MKU.004',  'CPMK082', 10,  5, 10, 10, 15],
            ['UNISM.MKU.005',  'CPMK081', 10,  5, 10, 10, 15],
            ['UNISM.MKU.005',  'CPMK082', 10,  5, 10, 10, 15],
            ['UNISM.MKU.006',  'CPMK081', 10,  5, 10, 10, 15],
            ['UNISM.MKU.006',  'CPMK082', 10,  5, 10, 10, 15],
            ['UNISM.SI001',    'CPMK012',  0,  5, 15, 10, 15],
            ['UNISM.SI001',    'CPMK031', 10, 10, 10, 10, 15],
            ['UNISM.SI002',    'CPMK031',  0,  5,  5,  5,  5],
            ['UNISM.SI002',    'CPMK033',  5,  5,  5, 10, 10],
            ['UNISM.SI002',    'CPMK061',  0,  5,  5,  5,  5],
            ['UNISM.SI002',    'CPMK071',  5,  5,  5,  5,  5],
            ['UNISM.SI003',    'CPMK062', 10, 15, 25, 10, 40],
            ['UNISM.SI004',    'CPMK042',  5,  5, 10,  5, 10],
            ['UNISM.SI004',    'CPMK051',  0,  5,  5,  5, 10],
            ['UNISM.SI004',    'CPMK072',  5,  5, 10, 10, 10],
            ['UNISM.SI005',    'CPMK101', 10, 15, 25, 50,  0],
            ['UNISM.SI006',    'CPMK101', 10, 15, 25, 50,  0],
            ['UNISM.SI007',    'CPMK021',  5,  5,  5,  5, 15],
            ['UNISM.SI007',    'CPMK022',  5,  5, 10, 10, 15],
            ['UNISM.SI007',    'CPMK023',  5,  5,  5,  5,  0],
            ['UNISM.SI008',    'CPMK023',  5,  5,  5, 10,  5],
            ['UNISM.SI008',    'CPMK024',  5,  5,  5, 10,  5],
            ['UNISM.SI008',    'CPMK091',  0, 10, 10, 10, 10],
            ['UNISM.SI009',    'CPMK023',  0,  5,  5,  5,  5],
            ['UNISM.SI009',    'CPMK024',  5,  5,  5, 10, 10],
            ['UNISM.SI009',    'CPMK091',  0,  5,  5,  5,  5],
            ['UNISM.SI009',    'CPMK092',  5,  5,  5,  5,  5],
            ['UNISM.SI010',    'CPMK064', 10, 15, 25, 10, 40],
            ['UNISM.SI011',    'CPMK041', 10, 15, 25, 10, 40],
            ['UNISM.SI012',    'CPMK063', 10, 15, 25, 20, 30],
            ['UNISM.SI013',    'CPMK023',  0,  5,  5,  5,  5],
            ['UNISM.SI013',    'CPMK024',  5,  5,  5, 10, 10],
            ['UNISM.SI013',    'CPMK091',  0,  5,  5,  5,  5],
            ['UNISM.SI013',    'CPMK092',  5,  5,  5,  5,  5],
            ['UNISM.SI014',    'CPMK021',  0,  5,  5,  5,  5],
            ['UNISM.SI014',    'CPMK023',  5,  5,  5, 10, 10],
            ['UNISM.SI014',    'CPMK024',  0,  5,  5,  5,  5],
            ['UNISM.SI014',    'CPMK091',  5,  5,  5,  5,  5],
            ['UNISM.SI015',    'CPMK064', 10, 15, 25, 10, 40],
            ['UNISM.SI016',    'CPMK071',  5,  5,  5, 10,  5],
            ['UNISM.SI016',    'CPMK072',  5,  5,  5, 10,  5],
            ['UNISM.SI016',    'CPMK073',  0, 10, 10, 10, 10],
            ['UNISM.SI017',    'CPMK061', 10, 15, 25, 50,  0],
            ['UNISM.SI018',    'CPMK013',  0,  5,  5,  5,  5],
            ['UNISM.SI018',    'CPMK014',  5,  0,  5,  5,  5],
            ['UNISM.SI018',    'CPMK033',  0,  5,  5,  5,  5],
            ['UNISM.SI018',    'CPMK062',  5,  0,  5,  5,  5],
            ['UNISM.SI018',    'CPMK072',  0,  5,  5,  0, 10],
            ['UNISM.SI019',    'CPMK051', 10, 15, 25, 50,  0],
            ['UNISM.SI020',    'CPMK042',  5,  5,  5,  5, 15],
            ['UNISM.SI020',    'CPMK051',  5,  5,  5,  5,  0],
            ['UNISM.SI020',    'CPMK052',  0, 10, 10, 10, 15],
            ['UNISM.SI021',    'CPMK041',  5, 20, 25, 10, 40],
            ['UNISM.SI022',    'CPMK031', 10, 15, 25, 50,  0],
            ['UNISM.SI023',    'CPMK041', 10, 15, 25, 20, 30],
            ['UNISM.SI024',    'CPMK011', 10, 15, 25, 30, 20],
            ['UNISM.SI025',    'CPMK052',  5,  5,  5, 10,  5],
            ['UNISM.SI025',    'CPMK073',  5,  5,  5, 10,  5],
            ['UNISM.SI025',    'CPMK092',  0, 10, 10, 10, 10],
            ['UNISM.SI026',    'CPMK082',  5,  5, 10, 10, 10],
            ['UNISM.SI026',    'CPMK101',  0,  5,  5, 10,  5],
            ['UNISM.SI026',    'CPMK102',  5, 10,  5, 10,  5],
            ['UNISM.SI027',    'CPMK091', 10, 15, 25, 20, 30],
            ['UNISM.SI028',    'CPMK023',  5, 10, 10, 25,  0],
            ['UNISM.SI028',    'CPMK024',  5, 10, 10, 25,  0],
            ['UNISM.SI029',    'CPMK032',  5, 10,  5, 10, 10],
            ['UNISM.SI029',    'CPMK061',  5,  5,  5,  5, 10],
            ['UNISM.SI029',    'CPMK062',  0,  5, 10,  5, 10],
            ['UNISM.SI030',    'CPMK032',  5, 10,  5, 10, 10],
            ['UNISM.SI030',    'CPMK061',  5,  5,  5,  5, 10],
            ['UNISM.SI030',    'CPMK062',  0,  5, 10,  5, 10],
            ['UNISM.SI031',    'CPMK022',  5,  5,  5,  5,  5],
            ['UNISM.SI031',    'CPMK032',  5,  5,  5, 10, 10],
            ['UNISM.SI031',    'CPMK061',  0,  5,  5,  5,  5],
            ['UNISM.SI031',    'CPMK062',  0,  5,  5,  5,  5],
            ['UNISM.SI032',    'CPMK041', 10, 15, 25, 20, 30],
            ['UNISM.SI033',    'CPMK011', 10, 15, 25, 50,  0],
            ['UNISM.SI034',    'CPMK052',  0,  0,  0, 10, 10],
            ['UNISM.SI034',    'CPMK064',  0,  0,  0, 10, 10],
            ['UNISM.SI034',    'CPMK082',  0,  0,  0, 10, 10],
            ['UNISM.SI034',    'CPMK092',  0,  0,  0, 10, 10],
            ['UNISM.SI034',    'CPMK101',  0,  0,  0,  5,  5],
            ['UNISM.SI034',    'CPMK102',  0,  0,  0,  5,  5],
            ['UNISM.SI035',    'CPMK031',  0,  5,  5,  5,  5],
            ['UNISM.SI035',    'CPMK061',  5,  5,  5, 10, 10],
            ['UNISM.SI035',    'CPMK071',  0,  5,  5,  5,  5],
            ['UNISM.SI035',    'CPMK072',  5,  5,  5,  5,  5],
            ['UNISM.SI036',    'CPMK012',  5,  5,  5, 10,  5],
            ['UNISM.SI036',    'CPMK013',  5,  5,  5, 10,  5],
            ['UNISM.SI036',    'CPMK014',  0, 10, 10, 10, 10],
            ['UNISM.SI037',    'CPMK031',  5, 10, 10, 10, 15],
            ['UNISM.SI037',    'CPMK032',  5, 10, 10, 10, 15],
            ['UNISM.SI038',    'CPMK032',  5,  5,  5, 10,  5],
            ['UNISM.SI038',    'CPMK042',  5,  5,  5, 10,  5],
            ['UNISM.SI038',    'CPMK063',  0, 10, 10, 10, 10],
            ['UNISM.SI039',    'CPMK063', 10, 15, 25, 20, 30],
            ['UNISM.SI040',    'CPMK023',  5, 10, 10, 25,  0],
            ['UNISM.SI040',    'CPMK024',  5, 10, 10, 25,  0],
            ['UNISM.SI041',    'CPMK012',  5, 10,  5, 10, 10],
            ['UNISM.SI041',    'CPMK013',  5, 10,  5, 10, 10],
            ['UNISM.SI041',    'CPMK051',  0,  0, 10,  5,  5],
            ['UNISM.SI042',    'CPMK033',  5,  5,  5, 10,  5],
            ['UNISM.SI042',    'CPMK034',  5,  5,  5, 10,  5],
            ['UNISM.SI042',    'CPMK051',  0, 10, 10, 10, 10],
        ];

        $mkCache   = MataKuliah::all()->keyBy('kode');
        $cpmkCache = Cpmk::all()->keyBy('kode');

        foreach ($data as [$mkKode, $cpmkKode, $tugas, $uts, $uas, $partisipatif, $proyek]) {
            $mk   = $mkCache->get($mkKode);
            $cpmk = $cpmkCache->get($cpmkKode);
            if (!$mk || !$cpmk) continue;

            BobotPenilaian::updateOrCreate(
                ['mata_kuliah_id' => $mk->id, 'cpmk_id' => $cpmk->id],
                [
                    'bobot'              => $tugas + $uts + $uas + $partisipatif + $proyek,
                    'bobot_tugas'        => $tugas,
                    'bobot_uts'          => $uts,
                    'bobot_uas'          => $uas,
                    'bobot_partisipatif' => $partisipatif,
                    'bobot_proyek'       => $proyek,
                    'skor_maks'          => $tugas + $uts + $uas + $partisipatif + $proyek,
                    'skor_min'           => (int) round(($tugas + $uts + $uas + $partisipatif + $proyek) * 0.6),
                ]
            );
        }
    }
}
