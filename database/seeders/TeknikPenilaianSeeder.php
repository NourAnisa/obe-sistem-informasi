<?php

namespace Database\Seeders;

use App\Models\BobotPenilaian;
use App\Models\Cpmk;
use App\Models\MataKuliah;
use App\Models\TeknikPenilaian;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeknikPenilaianSeeder extends Seeder
{
    public function run(): void
    {
        // Format: [cpl(ignored), mk_kode, cpmk_kode, mbkm, quiz, tugas, uts, uas, partisipatif, proyek]
        // Note: UNISM.001 rows excluded — MK code not in DB (likely data entry error in source doc)
        // Note: UNISM.MKPU.002 rows CPMK101/CPMK102 listed under CPL08 — seeded as-is per document
        // Note: UNISM.SI041 CPMK012/CPMK013 listed under CPL04 — seeded as-is per document (CPMK are CPL01)
        $data = [
            ['CPL01', 'FSaint.001', 'CPMK015',  0, 0, 0, 1, 1, 1, 1, 0],
            ['CPL03', 'FSaint.001', 'CPMK034',  0, 0, 0, 1, 1, 1, 1, 0],
            ['CPL08', 'UNISM.MKPU.001', 'CPMK081', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL08', 'UNISM.MKPU.001', 'CPMK082', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL10', 'UNISM.MKPU.001', 'CPMK101', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL10', 'UNISM.MKPU.001', 'CPMK102', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL08', 'UNISM.MKPU.002', 'CPMK081', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL08', 'UNISM.MKPU.002', 'CPMK082', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL08', 'UNISM.MKPU.002', 'CPMK101', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL08', 'UNISM.MKPU.002', 'CPMK102', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL08', 'UNISM.MKPU.003', 'CPMK081', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL08', 'UNISM.MKPU.003', 'CPMK082', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL09', 'UNISM.MKPU.003', 'CPMK092', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL10', 'UNISM.MKPU.003', 'CPMK101', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL10', 'UNISM.MKPU.003', 'CPMK102', 0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL09', 'UNISM.MKU.001', 'CPMK092',  0, 0, 0, 1, 1, 1, 1, 1],
            ['CPL10', 'UNISM.MKU.002', 'CPMK102',  0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL08', 'UNISM.MKU.003', 'CPMK081',  0, 0, 0, 1, 1, 1, 1, 1],
            ['CPL08', 'UNISM.MKU.003', 'CPMK082',  0, 0, 0, 1, 1, 1, 1, 1],
            ['CPL08', 'UNISM.MKU.004', 'CPMK081',  0, 0, 0, 1, 1, 1, 1, 1],
            ['CPL08', 'UNISM.MKU.004', 'CPMK082',  0, 0, 0, 1, 1, 1, 1, 1],
            ['CPL08', 'UNISM.MKU.005', 'CPMK081',  0, 0, 0, 1, 1, 1, 1, 1],
            ['CPL08', 'UNISM.MKU.005', 'CPMK082',  0, 0, 0, 1, 1, 1, 1, 1],
            ['CPL08', 'UNISM.MKU.006', 'CPMK081',  0, 0, 0, 1, 1, 1, 1, 1],
            ['CPL08', 'UNISM.MKU.006', 'CPMK082',  0, 0, 0, 1, 1, 1, 1, 1],
            ['CPL01', 'UNISM.SI001', 'CPMK012',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL03', 'UNISM.SI001', 'CPMK031',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL03', 'UNISM.SI002', 'CPMK031',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL03', 'UNISM.SI002', 'CPMK033',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI002', 'CPMK061',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL07', 'UNISM.SI002', 'CPMK071',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI003', 'CPMK062',    1, 0, 0, 1, 1, 0, 1, 1],
            ['CPL04', 'UNISM.SI004', 'CPMK042',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL05', 'UNISM.SI004', 'CPMK051',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL07', 'UNISM.SI004', 'CPMK072',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL10', 'UNISM.SI005', 'CPMK101',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL10', 'UNISM.SI006', 'CPMK101',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL02', 'UNISM.SI007', 'CPMK021',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI007', 'CPMK022',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI007', 'CPMK023',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI008', 'CPMK023',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI008', 'CPMK024',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL09', 'UNISM.SI008', 'CPMK091',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI009', 'CPMK023',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI009', 'CPMK024',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL09', 'UNISM.SI009', 'CPMK091',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL09', 'UNISM.SI009', 'CPMK092',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI010', 'CPMK064',    1, 0, 0, 1, 0, 1, 1, 1],
            ['CPL04', 'UNISM.SI011', 'CPMK041',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI012', 'CPMK063',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI013', 'CPMK023',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI013', 'CPMK024',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL09', 'UNISM.SI013', 'CPMK091',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL09', 'UNISM.SI013', 'CPMK092',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI014', 'CPMK021',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI014', 'CPMK023',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI014', 'CPMK024',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL09', 'UNISM.SI014', 'CPMK091',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI015', 'CPMK064',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL07', 'UNISM.SI016', 'CPMK071',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL07', 'UNISM.SI016', 'CPMK072',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL07', 'UNISM.SI016', 'CPMK073',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI017', 'CPMK061',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL01', 'UNISM.SI018', 'CPMK013',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL01', 'UNISM.SI018', 'CPMK014',    1, 0, 0, 1, 0, 1, 1, 1],
            ['CPL03', 'UNISM.SI018', 'CPMK033',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI018', 'CPMK062',    1, 0, 0, 1, 0, 1, 1, 1],
            ['CPL07', 'UNISM.SI018', 'CPMK072',    1, 0, 0, 0, 1, 1, 0, 1],
            ['CPL05', 'UNISM.SI019', 'CPMK051',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL04', 'UNISM.SI020', 'CPMK042',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL05', 'UNISM.SI020', 'CPMK051',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL05', 'UNISM.SI020', 'CPMK052',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL04', 'UNISM.SI021', 'CPMK041',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL03', 'UNISM.SI022', 'CPMK031',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL04', 'UNISM.SI023', 'CPMK041',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL01', 'UNISM.SI024', 'CPMK011',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL05', 'UNISM.SI025', 'CPMK052',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL07', 'UNISM.SI025', 'CPMK073',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL09', 'UNISM.SI025', 'CPMK092',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL08', 'UNISM.SI026', 'CPMK082',    1, 0, 1, 1, 1, 1, 1, 1],
            ['CPL10', 'UNISM.SI026', 'CPMK101',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL10', 'UNISM.SI026', 'CPMK102',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL09', 'UNISM.SI027', 'CPMK091',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL02', 'UNISM.SI028', 'CPMK023',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL02', 'UNISM.SI028', 'CPMK024',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL03', 'UNISM.SI029', 'CPMK032',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI029', 'CPMK061',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI029', 'CPMK062',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL03', 'UNISM.SI030', 'CPMK032',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI030', 'CPMK061',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI030', 'CPMK062',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI031', 'CPMK022',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL03', 'UNISM.SI031', 'CPMK032',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI031', 'CPMK061',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI031', 'CPMK062',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL04', 'UNISM.SI032', 'CPMK041',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL01', 'UNISM.SI033', 'CPMK011',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL05', 'UNISM.SI034', 'CPMK052',    0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL06', 'UNISM.SI034', 'CPMK064',    0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL08', 'UNISM.SI034', 'CPMK082',    0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL09', 'UNISM.SI034', 'CPMK092',    0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL10', 'UNISM.SI034', 'CPMK101',    0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL10', 'UNISM.SI034', 'CPMK102',    0, 0, 0, 0, 0, 0, 1, 1],
            ['CPL03', 'UNISM.SI035', 'CPMK031',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI035', 'CPMK061',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL07', 'UNISM.SI035', 'CPMK071',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL07', 'UNISM.SI035', 'CPMK072',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL01', 'UNISM.SI036', 'CPMK012',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL01', 'UNISM.SI036', 'CPMK013',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL01', 'UNISM.SI036', 'CPMK014',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL03', 'UNISM.SI037', 'CPMK031',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL03', 'UNISM.SI037', 'CPMK032',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL03', 'UNISM.SI038', 'CPMK032',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL04', 'UNISM.SI038', 'CPMK042',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI038', 'CPMK063',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL06', 'UNISM.SI039', 'CPMK063',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL02', 'UNISM.SI040', 'CPMK023',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL02', 'UNISM.SI040', 'CPMK024',    1, 0, 0, 1, 1, 1, 1, 0],
            ['CPL04', 'UNISM.SI041', 'CPMK012',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL04', 'UNISM.SI041', 'CPMK013',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL05', 'UNISM.SI041', 'CPMK051',    1, 0, 0, 0, 1, 1, 1, 1],
            ['CPL03', 'UNISM.SI042', 'CPMK033',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL03', 'UNISM.SI042', 'CPMK034',    1, 0, 0, 1, 1, 1, 1, 1],
            ['CPL05', 'UNISM.SI042', 'CPMK051',    1, 0, 0, 0, 1, 1, 1, 1],
        ];

        $mkCache   = MataKuliah::all()->keyBy('kode');
        $cpmkCache = Cpmk::all()->keyBy('kode');

        foreach ($data as [, $mkKode, $cpmkKode, $mbkm, $quiz, $tugas, $uts, $uas, $partisipatif, $proyek]) {
            $mk   = $mkCache->get($mkKode);
            $cpmk = $cpmkCache->get($cpmkKode);
            if (!$mk || !$cpmk) continue;

            TeknikPenilaian::updateOrCreate(
                ['mata_kuliah_id' => $mk->id, 'cpmk_id' => $cpmk->id],
                [
                    'is_mbkm'          => (bool) $mbkm,
                    'has_quiz'         => (bool) $quiz,
                    'has_tugas'        => (bool) $tugas,
                    'has_uts'          => (bool) $uts,
                    'has_uas'          => (bool) $uas,
                    'has_partisipatif' => (bool) $partisipatif,
                    'has_proyek'       => (bool) $proyek,
                ]
            );
        }

        // Auto-sync has_* flags from bobot_penilaian (single source of truth).
        // Overrides any discrepancies in the $data array above.
        $bobots = DB::table('bobot_penilaian as b')
            ->join('teknik_penilaian as t', function ($j) {
                $j->on('t.mata_kuliah_id', '=', 'b.mata_kuliah_id')
                  ->on('t.cpmk_id', '=', 'b.cpmk_id');
            })
            ->select('t.id', 'b.bobot_tugas', 'b.bobot_uts', 'b.bobot_uas',
                     'b.bobot_partisipatif', 'b.bobot_proyek')
            ->get();

        foreach ($bobots as $b) {
            DB::table('teknik_penilaian')->where('id', $b->id)->update([
                'has_tugas'        => $b->bobot_tugas > 0,
                'has_uts'          => $b->bobot_uts > 0,
                'has_uas'          => $b->bobot_uas > 0,
                'has_partisipatif' => $b->bobot_partisipatif > 0,
                'has_proyek'       => $b->bobot_proyek > 0,
            ]);
        }
    }
}
