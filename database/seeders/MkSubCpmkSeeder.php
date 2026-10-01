<?php

namespace Database\Seeders;

use App\Models\MataKuliah;
use App\Models\SubCpmk;
use Illuminate\Database\Seeder;

class MkSubCpmkSeeder extends Seeder
{
    public function run(): void
    {
        // [mk_kode => [sub_cpmk_kodes...]]
        $mapping = [
            'FSaint.001'      => ['Sub-CPMK0151', 'Sub-CPMK0152', 'Sub-CPMK0341'],
            'UNISM.MKPU.001'  => ['Sub-CPMK0811', 'Sub-CPMK0821', 'Sub-CPMK1011', 'Sub-CPMK1012', 'Sub-CPMK1021'],
            'UNISM.MKPU.002'  => ['Sub-CPMK0812', 'Sub-CPMK0822', 'Sub-CPMK1011', 'Sub-CPMK1012', 'Sub-CPMK1022'],
            'UNISM.MKPU.003'  => ['Sub-CPMK0821', 'Sub-CPMK0831', 'Sub-CPMK0841', 'Sub-CPMK0922', 'Sub-CPMK1011', 'Sub-CPMK1012', 'Sub-CPMK1021', 'Sub-CPMK1022'],
            'UNISM.MKU.001'   => ['Sub-CPMK0921'],
            'UNISM.MKU.002'   => ['Sub-CPMK1021', 'Sub-CPMK1022'],
            'UNISM.MKU.003'   => ['Sub-CPMK0811', 'Sub-CPMK0812', 'Sub-CPMK0831', 'Sub-CPMK0832'],
            'UNISM.MKU.004'   => ['Sub-CPMK0811', 'Sub-CPMK0812', 'Sub-CPMK0821', 'Sub-CPMK0822'],
            'UNISM.MKU.005'   => ['Sub-CPMK0811', 'Sub-CPMK0812', 'Sub-CPMK0821', 'Sub-CPMK0822'],
            'UNISM.MKU.006'   => ['Sub-CPMK0811', 'Sub-CPMK0812', 'Sub-CPMK0821', 'Sub-CPMK0822'],
            'UNISM.SI001'     => ['Sub-CPMK0121', 'Sub-CPMK0122', 'Sub-CPMK0311'],
            'UNISM.SI002'     => ['Sub-CPMK0311', 'Sub-CPMK0312', 'Sub-CPMK0331', 'Sub-CPMK0332', 'Sub-CPMK0611', 'Sub-CPMK0612', 'Sub-CPMK0711'],
            'UNISM.SI003'     => ['Sub-CPMK0621', 'Sub-CPMK0622'],
            'UNISM.SI004'     => ['Sub-CPMK0721', 'Sub-CPMK0722'],
            'UNISM.SI005'     => ['Sub-CPMK1011', 'Sub-CPMK1012', 'Sub-CPMK1013'],
            'UNISM.SI006'     => ['Sub-CPMK1011', 'Sub-CPMK1012', 'Sub-CPMK1013'],
            'UNISM.SI007'     => ['Sub-CPMK0211', 'Sub-CPMK0212', 'Sub-CPMK0213', 'Sub-CPMK0221', 'Sub-CPMK0222', 'Sub-CPMK0231'],
            'UNISM.SI008'     => ['Sub-CPMK0232', 'Sub-CPMK0233', 'Sub-CPMK0241', 'Sub-CPMK0242', 'Sub-CPMK0911'],
            'UNISM.SI009'     => ['Sub-CPMK0321', 'Sub-CPMK0322', 'Sub-CPMK0331', 'Sub-CPMK0332', 'Sub-CPMK0911', 'Sub-CPMK0912', 'Sub-CPMK0922'],
            'UNISM.SI010'     => ['Sub-CPMK0641', 'Sub-CPMK0642'],
            'UNISM.SI011'     => ['Sub-CPMK0411', 'Sub-CPMK0412', 'Sub-CPMK0413', 'Sub-CPMK0414'],
            'UNISM.SI012'     => ['Sub-CPMK0631', 'Sub-CPMK0632'],
            'UNISM.SI013'     => ['Sub-CPMK0311', 'Sub-CPMK0312', 'Sub-CPMK0911', 'Sub-CPMK0912'],
            'UNISM.SI014'     => ['Sub-CPMK0214', 'Sub-CPMK0215', 'Sub-CPMK0232', 'Sub-CPMK0233', 'Sub-CPMK0241', 'Sub-CPMK0242', 'Sub-CPMK0911'],
            'UNISM.SI015'     => ['Sub-CPMK0641', 'Sub-CPMK0642'],
            'UNISM.SI016'     => ['Sub-CPMK0711', 'Sub-CPMK0712', 'Sub-CPMK0721', 'Sub-CPMK0722', 'Sub-CPMK0731', 'Sub-CPMK0732'],
            'UNISM.SI017'     => ['Sub-CPMK0611', 'Sub-CPMK0612'],
            'UNISM.SI018'     => ['Sub-CPMK0131', 'Sub-CPMK0132', 'Sub-CPMK0133', 'Sub-CPMK0141', 'Sub-CPMK0142', 'Sub-CPMK0143', 'Sub-CPMK0331', 'Sub-CPMK0332'],
            'UNISM.SI019'     => ['Sub-CPMK0511', 'Sub-CPMK0512'],
            'UNISM.SI020'     => ['Sub-CPMK0421', 'Sub-CPMK0422', 'Sub-CPMK0512', 'Sub-CPMK0521', 'Sub-CPMK0522'],
            'UNISM.SI021'     => ['Sub-CPMK0411', 'Sub-CPMK0412', 'Sub-CPMK0413'],
            'UNISM.SI022'     => ['Sub-CPMK0311', 'Sub-CPMK0312'],
            'UNISM.SI023'     => ['Sub-CPMK0411', 'Sub-CPMK0413'],
            'UNISM.SI024'     => ['Sub-CPMK0111', 'Sub-CPMK0112', 'Sub-CPMK0113', 'Sub-CPMK0114'],
            'UNISM.SI025'     => ['Sub-CPMK0332', 'Sub-CPMK0522', 'Sub-CPMK0611', 'Sub-CPMK0612', 'Sub-CPMK0621', 'Sub-CPMK0622', 'Sub-CPMK0731', 'Sub-CPMK0732'],
            'UNISM.SI026'     => ['Sub-CPMK0832', 'Sub-CPMK1011', 'Sub-CPMK1021', 'Sub-CPMK1022'],
            'UNISM.SI028'     => ['Sub-CPMK0231', 'Sub-CPMK0233', 'Sub-CPMK0242'],
        ];

        $mkCache  = MataKuliah::all()->keyBy('kode');
        $subCache = SubCpmk::all()->keyBy('kode');

        foreach ($mapping as $mkKode => $subKodes) {
            $mk = $mkCache->get($mkKode);
            if (!$mk) continue;

            $subIds = collect($subKodes)
                ->map(fn($k) => $subCache->get($k)?->id)
                ->filter()
                ->values()
                ->all();

            $mk->subCpmks()->sync($subIds);
        }
    }
}
