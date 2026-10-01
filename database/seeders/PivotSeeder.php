<?php

namespace Database\Seeders;

use App\Models\BahanKajian;
use App\Models\Cpl;
use App\Models\MataKuliah;
use App\Models\ProfilLulusan;
use Illuminate\Database\Seeder;

class PivotSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCplMk();
        $this->seedCplProfilLulusan();
        $this->seedCplBahanKajian();
        $this->seedBkMk();
    }

    private function seedCplMk(): void
    {
        $mapping = [
            'FSaint.001'      => ['CPL01', 'CPL03'],
            'UNISM.MKPU.001'  => ['CPL08', 'CPL10'],
            'UNISM.MKPU.002'  => ['CPL08', 'CPL10'],
            'UNISM.MKPU.003'  => ['CPL08', 'CPL09', 'CPL10'],
            'UNISM.MKU.001'   => ['CPL09'],
            'UNISM.MKU.002'   => ['CPL10'],
            'UNISM.MKU.003'   => ['CPL08'],
            'UNISM.MKU.004'   => ['CPL08'],
            'UNISM.MKU.005'   => ['CPL08'],
            'UNISM.MKU.006'   => ['CPL08'],
            'UNISM.SI001'     => ['CPL01', 'CPL03'],
            'UNISM.SI002'     => ['CPL03', 'CPL06', 'CPL07'],
            'UNISM.SI003'     => ['CPL06'],
            'UNISM.SI004'     => ['CPL04', 'CPL05', 'CPL07'],
            'UNISM.SI005'     => ['CPL10'],
            'UNISM.SI006'     => ['CPL10'],
            'UNISM.SI007'     => ['CPL02'],
            'UNISM.SI008'     => ['CPL02', 'CPL03', 'CPL09'],
            'UNISM.SI009'     => ['CPL02', 'CPL03', 'CPL09'],
            'UNISM.SI010'     => ['CPL06'],
            'UNISM.SI011'     => ['CPL04'],
            'UNISM.SI012'     => ['CPL06'],
            'UNISM.SI013'     => ['CPL02', 'CPL09'],
            'UNISM.SI014'     => ['CPL02', 'CPL09'],
            'UNISM.SI015'     => ['CPL06'],
            'UNISM.SI016'     => ['CPL07'],
            'UNISM.SI017'     => ['CPL06'],
            'UNISM.SI018'     => ['CPL01', 'CPL06', 'CPL07'],
            'UNISM.SI019'     => ['CPL05'],
            'UNISM.SI020'     => ['CPL04', 'CPL05'],
            'UNISM.SI021'     => ['CPL04'],
            'UNISM.SI022'     => ['CPL03'],
            'UNISM.SI023'     => ['CPL04'],
            'UNISM.SI024'     => ['CPL01'],
            'UNISM.SI025'     => ['CPL05', 'CPL07', 'CPL09'],
            'UNISM.SI026'     => ['CPL08', 'CPL10'],
            'UNISM.SI027'     => ['CPL09'],
            'UNISM.SI028'     => ['CPL02'],
            'UNISM.SI029'     => ['CPL03', 'CPL06'],
            'UNISM.SI030'     => ['CPL03', 'CPL06'],
            'UNISM.SI031'     => ['CPL02', 'CPL03', 'CPL06'],
            'UNISM.SI032'     => ['CPL04'],
            'UNISM.SI033'     => ['CPL01'],
            'UNISM.SI034'     => ['CPL05', 'CPL06', 'CPL08', 'CPL09', 'CPL10'],
            'UNISM.SI035'     => ['CPL03', 'CPL06', 'CPL07'],
            'UNISM.SI036'     => ['CPL01'],
            'UNISM.SI037'     => ['CPL03'],
            'UNISM.SI038'     => ['CPL03', 'CPL04', 'CPL06'],
            'UNISM.SI039'     => ['CPL06'],
            'UNISM.SI040'     => ['CPL02'],
            'UNISM.SI041'     => ['CPL01', 'CPL05'],
            'UNISM.001'       => ['CPL03', 'CPL05', 'CPL06', 'CPL07', 'CPL09'],
            'UNISM.SI042'     => ['CPL03', 'CPL05'],
        ];

        $cplCache = Cpl::all()->keyBy('kode');

        foreach ($mapping as $kodeMk => $cplKodes) {
            $mk = MataKuliah::where('kode', $kodeMk)->first();
            if (!$mk) continue;

            $cplIds = collect($cplKodes)
                ->map(fn($k) => $cplCache->get($k)?->id)
                ->filter()
                ->all();

            $mk->cpls()->sync($cplIds);
        }
    }

    private function seedCplProfilLulusan(): void
    {
        // CPL → Profil Lulusan mapping
        $mapping = [
            'CPL01' => ['PL01', 'PL03'],
            'CPL02' => ['PL01'],
            'CPL03' => ['PL01'],
            'CPL04' => ['PL01'],
            'CPL05' => ['PL02'],
            'CPL06' => ['PL02', 'PL03'],
            'CPL07' => ['PL02'],
            'CPL08' => ['PL04'],
            'CPL09' => ['PL03'],
            'CPL10' => ['PL04'],
        ];

        $plCache  = ProfilLulusan::all()->keyBy('kode');
        $cplCache = Cpl::all()->keyBy('kode');

        foreach ($mapping as $cplKode => $plKodes) {
            $cpl = $cplCache->get($cplKode);
            if (!$cpl) continue;

            $plIds = collect($plKodes)
                ->map(fn($k) => $plCache->get($k)?->id)
                ->filter()
                ->all();

            $cpl->profilLulusans()->sync($plIds);
        }
    }

    private function seedCplBahanKajian(): void
    {
        // BK → CPL mapping (Pemetaan CPL-BK, dokumen OBE UNISM)
        $mapping = [
            'BK01' => ['CPL01'],
            'BK02' => ['CPL01', 'CPL02'],
            'BK03' => ['CPL04'],
            'BK04' => ['CPL07'],
            'BK05' => ['CPL03', 'CPL06'],
            'BK06' => ['CPL06'],
            'BK07' => ['CPL07'],
            'BK08' => ['CPL04'],
            'BK09' => ['CPL05', 'CPL09'],
            'BK10' => ['CPL03', 'CPL06', 'CPL07', 'CPL09'],
            'BK11' => ['CPL02', 'CPL09'],
            'BK12' => ['CPL03'],
            'BK13' => ['CPL08', 'CPL10'],
        ];

        $cplCache = Cpl::all()->keyBy('kode');
        $bkCache  = BahanKajian::all()->keyBy('kode');

        foreach ($mapping as $bkKode => $cplKodes) {
            $bk = $bkCache->get($bkKode);
            if (!$bk) continue;

            $cplIds = collect($cplKodes)
                ->map(fn($k) => $cplCache->get($k)?->id)
                ->filter()
                ->all();

            $bk->cpls()->sync($cplIds);
        }
    }

    private function seedBkMk(): void
    {
        // MK → BK mapping (Pemetaan BK-MK, dokumen OBE UNISM)
        $mapping = [
            'FSaint.001'     => ['BK06'],
            'UNISM.MKPU.001' => ['BK13'],
            'UNISM.MKPU.002' => ['BK13'],
            'UNISM.MKPU.003' => ['BK10', 'BK13'],
            'UNISM.MKU.001'  => ['BK13'],
            'UNISM.MKU.002'  => ['BK06'],
            'UNISM.MKU.003'  => ['BK09'],
            'UNISM.MKU.004'  => ['BK09'],
            'UNISM.MKU.005'  => ['BK09'],
            'UNISM.MKU.006'  => ['BK09'],
            'UNISM.SI001'    => ['BK02', 'BK11'],
            'UNISM.SI002'    => ['BK05', 'BK10'],
            'UNISM.SI003'    => ['BK04'],
            'UNISM.SI004'    => ['BK04'],
            'UNISM.SI005'    => ['BK13'],
            'UNISM.SI006'    => ['BK13'],
            'UNISM.SI007'    => ['BK02'],
            'UNISM.SI008'    => ['BK12'],
            'UNISM.SI009'    => ['BK12'],
            'UNISM.SI010'    => ['BK06'],
            'UNISM.SI011'    => ['BK03', 'BK08'],
            'UNISM.SI012'    => ['BK06'],
            'UNISM.SI013'    => ['BK12'],
            'UNISM.SI014'    => ['BK12'],
            'UNISM.SI015'    => ['BK06'],
            'UNISM.SI016'    => ['BK04'],
            'UNISM.SI017'    => ['BK06'],
            'UNISM.SI018'    => ['BK01', 'BK04'],
            'UNISM.SI019'    => ['BK09'],
            'UNISM.SI020'    => ['BK08'],
            'UNISM.SI021'    => ['BK02'],
            'UNISM.SI022'    => ['BK09'],
            'UNISM.SI023'    => ['BK03'],
            'UNISM.SI024'    => ['BK01'],
            'UNISM.SI025'    => ['BK04', 'BK10'],
            'UNISM.SI026'    => ['BK13'],
            'UNISM.SI027'    => ['BK05', 'BK06'],
            'UNISM.SI028'    => ['BK11'],
            'UNISM.SI029'    => ['BK05', 'BK07'],
            'UNISM.SI030'    => ['BK05', 'BK07'],
            'UNISM.SI031'    => ['BK05', 'BK07'],
            'UNISM.SI032'    => ['BK03'],
            'UNISM.SI033'    => ['BK01'],
            'UNISM.SI034'    => ['BK09', 'BK10', 'BK13'],
            'UNISM.SI035'    => ['BK05'],
            'UNISM.SI036'    => ['BK01'],
            'UNISM.SI037'    => ['BK12'],
            'UNISM.SI038'    => ['BK05', 'BK08'],
            'UNISM.SI039'    => ['BK06'],
            'UNISM.SI040'    => ['BK11'],
            'UNISM.SI041'    => ['BK03'],
            'UNISM.001'      => ['BK05', 'BK09', 'BK10', 'BK12'],
            'UNISM.SI042'    => ['BK05', 'BK07'],
        ];

        $bkCache = BahanKajian::all()->keyBy('kode');
        $mkCache = MataKuliah::all()->keyBy('kode');

        foreach ($mapping as $mkKode => $bkKodes) {
            $mk = $mkCache->get($mkKode);
            if (!$mk) continue;

            $bkIds = collect($bkKodes)
                ->map(fn($k) => $bkCache->get($k)?->id)
                ->filter()
                ->all();

            $mk->bahanKajians()->sync($bkIds);
        }
    }
}
