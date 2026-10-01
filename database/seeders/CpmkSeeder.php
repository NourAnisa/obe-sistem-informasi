<?php

namespace Database\Seeders;

use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\MataKuliah;
use Illuminate\Database\Seeder;

class CpmkSeeder extends Seeder
{
    public function run(): void
    {
        // [kode, cpl_kode, deskripsi, [mk_kodes...]]
        $data = [
            // CPL01
            [
                'CPMK011',
                'CPL01',
                'Mampu memahami konsep dasar sistem informasi',
                ['UNISM.SI024', 'UNISM.SI033']
            ],
            [
                'CPMK012',
                'CPL01',
                'Mampu menganalisis konsep proses sistem dan sistem organisasi',
                ['UNISM.SI001', 'UNISM.SI036', 'UNISM.SI041']
            ],
            [
                'CPMK013',
                'CPL01',
                'Mampu menilai proses dan sistem pengelolaan pada organisasi',
                ['UNISM.SI018', 'UNISM.SI036', 'UNISM.SI041']
            ],
            [
                'CPMK014',
                'CPL01',
                'Mampu menilai peran sistem informasi dalam memberikan rekomendasi pengambilan keputusan di organisasi',
                ['UNISM.SI018', 'UNISM.SI036']
            ],
            [
                'CPMK015',
                'CPL01',
                'Mampu menguasai konsep dasar keahlian khusus dalam bidang teknologi informasi.',
                ['FSaint.001']
            ],

            // CPL02
            [
                'CPMK021',
                'CPL02',
                'Mampu memahami dan merancang database',
                ['UNISM.SI007']
            ],
            [
                'CPMK022',
                'CPL02',
                'Mampu menggunakan database',
                ['UNISM.SI007', 'UNISM.SI031']
            ],
            [
                'CPMK023',
                'CPL02',
                'Mampu mengolah data dengan alat dan teknik pengolahan data',
                ['UNISM.SI009', 'UNISM.SI013', 'UNISM.SI014', 'UNISM.SI028', 'UNISM.SI040']
            ],
            [
                'CPMK024',
                'CPL02',
                'Mampu menganalisa data dengan alat dan teknik pengolahan data',
                ['UNISM.SI009', 'UNISM.SI013', 'UNISM.SI014', 'UNISM.SI040']
            ],

            // CPL03
            [
                'CPMK031',
                'CPL03',
                'Mampu memahami berbagai metodologi pengembangan sistem',
                ['UNISM.SI001', 'UNISM.SI002', 'UNISM.SI013', 'UNISM.SI022', 'UNISM.SI035', 'UNISM.SI037']
            ],
            [
                'CPMK032',
                'CPL03',
                'Mampu menggunakan berbagai metodologi pengembangan sistem',
                ['UNISM.SI009', 'UNISM.SI014', 'UNISM.SI029', 'UNISM.SI030', 'UNISM.SI031', 'UNISM.SI037', 'UNISM.SI038']
            ],
            [
                'CPMK033',
                'CPL03',
                'Mampu menganalisa kebutuhan pengguna dalam membangun sistem informasi untuk mencapai tujuan organisasi',
                ['UNISM.SI002', 'UNISM.SI008', 'UNISM.SI009', 'UNISM.SI042', 'UNISM.001']
            ],
            [
                'CPMK034',
                'CPL03',
                'Mampu memberikan pendekatan teknologi informasi sebagai solusi penyelesaian permasalahan.',
                ['FSaint.001', 'UNISM.SI042']
            ],

            // CPL04
            [
                'CPMK041',
                'CPL04',
                'Mampu membuat perencanaan infrastruktur TI, arsitektur jaringan, serta layanan fisik/cloud',
                ['UNISM.SI011', 'UNISM.SI021', 'UNISM.SI023', 'UNISM.SI032']
            ],
            [
                'CPMK042',
                'CPL04',
                'Mampu menganalisis konsep identifikasi, otentikasi, otorisasi akses dalam konteks keamanan sistem',
                ['UNISM.SI004', 'UNISM.SI020', 'UNISM.SI038']
            ],

            // CPL05
            [
                'CPMK051',
                'CPL05',
                'Mampu memahami kode etik dalam penggunaan informasi data pada perancangan, implementasi dan penggunaan suatu sistem',
                ['UNISM.MKU.004', 'UNISM.MKU.005', 'UNISM.MKU.006', 'UNISM.SI004', 'UNISM.SI019', 'UNISM.SI020', 'UNISM.SI041', 'UNISM.SI042']
            ],
            [
                'CPMK052',
                'CPL05',
                'Mampu menerapkan kode etik dalam penggunaan informasi data pada perancangan, implementasi dan penggunaan suatu sistem',
                ['UNISM.MKPU.003', 'UNISM.SI020', 'UNISM.SI025', 'UNISM.SI034', 'UNISM.001']
            ],

            // CPL06
            [
                'CPMK061',
                'CPL06',
                'Mampu merencanakan sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis baik jangka pendek maupun jangka panjang',
                ['UNISM.SI002', 'UNISM.SI017', 'UNISM.SI018', 'UNISM.SI025', 'UNISM.SI029', 'UNISM.SI030', 'UNISM.SI031', 'UNISM.SI035']
            ],
            [
                'CPMK062',
                'CPL06',
                'Mampu menerapkan sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis jangka pendek maupun jangka panjang',
                ['FSaint.001', 'UNISM.SI003', 'UNISM.SI018', 'UNISM.SI026', 'UNISM.SI029', 'UNISM.SI030', 'UNISM.SI031']
            ],
            [
                'CPMK063',
                'CPL06',
                'Mampu memelihara sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis jangka pendek maupun jangka panjang',
                ['UNISM.SI012', 'UNISM.SI038', 'UNISM.SI039']
            ],
            [
                'CPMK064',
                'CPL06',
                'Mampu meningkatkan sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis jangka pendek maupun jangka panjang',
                ['UNISM.SI010', 'UNISM.SI015', 'UNISM.SI022', 'UNISM.SI034', 'UNISM.001']
            ],

            // CPL07
            [
                'CPMK071',
                'CPL07',
                'Mampu memahami konsep, teknik dan metodologi manajemen proyek sistem informasi',
                ['UNISM.SI002', 'UNISM.SI016', 'UNISM.SI035']
            ],
            [
                'CPMK072',
                'CPL07',
                'Mampu mengidentifikasi konsep, teknik dan metodologi manajemen proyek sistem informasi',
                ['UNISM.SI004', 'UNISM.SI016', 'UNISM.SI018', 'UNISM.SI035']
            ],
            [
                'CPMK073',
                'CPL07',
                'Mampu menerapkan konsep, teknik dan metodologi manajemen proyek sistem informasi',
                ['UNISM.SI016', 'UNISM.SI025', 'UNISM.SI029', 'UNISM.SI030', 'UNISM.SI031', 'UNISM.001']
            ],

            // CPL08
            [
                'CPMK081',
                'CPL08',
                'Mampu menjunjung tinggi nilai kemanusiaan dalam menjalankan tugas berdasarkan agama dan moral',
                ['UNISM.MKPU.001', 'UNISM.MKPU.002', 'UNISM.MKPU.003', 'UNISM.MKU.003', 'UNISM.MKU.004', 'UNISM.MKU.005', 'UNISM.MKU.006', 'UNISM.SI034']
            ],
            [
                'CPMK082',
                'CPL08',
                'Mampu menjunjung tinggi nilai kemanusiaan dalam menjalankan tugas berdasarkan etika akademik dan rasa tanggung jawab',
                ['UNISM.MKPU.001', 'UNISM.MKPU.002', 'UNISM.MKPU.003', 'UNISM.MKU.003', 'UNISM.MKU.004', 'UNISM.MKU.005', 'UNISM.MKU.006', 'UNISM.SI026', 'UNISM.SI034']
            ],
            [
                'CPMK083',
                'CPL08',
                'Mampu menjunjung tinggi nilai kemanusiaan dalam menjalankan tugas berdasarkan etika akademik',
                ['UNISM.MKPU.003', 'UNISM.SI026', 'UNISM.SI034']
            ],
            [
                'CPMK084',
                'CPL08',
                'Mampu menjunjung tinggi nilai kemanusiaan dalam menjalankan tugas berdasarkan rasa tanggungjawab pada negara dan bangsa',
                ['UNISM.SI034']
            ],

            // CPL09
            [
                'CPMK091',
                'CPL09',
                'Mampu mengambil keputusan secara tepat dalam konteks penyelesaian masalah berdasarkan hasil analisis informasi dan data',
                ['UNISM.MKPU.003', 'UNISM.SI008', 'UNISM.SI009', 'UNISM.SI013', 'UNISM.SI014', 'UNISM.SI027', 'UNISM.SI042']
            ],
            [
                'CPMK092',
                'CPL09',
                'Mampu menyusun deskripsi saintifik dalam bentuk karya ilmiah berdasarkan hasil analisis informasi dan data',
                ['UNISM.MKU.001', 'UNISM.MKPU.003', 'UNISM.SI009', 'UNISM.SI025', 'UNISM.SI034', 'UNISM.001']
            ],

            // CPL10
            [
                'CPMK101',
                'CPL10',
                'Mampu berkomunikasi secara efektif dalam berbagai konteks profesional',
                ['UNISM.MKPU.001', 'UNISM.MKPU.002', 'UNISM.MKPU.003', 'UNISM.SI005', 'UNISM.SI006', 'UNISM.SI026', 'UNISM.SI034']
            ],
            [
                'CPMK102',
                'CPL10',
                'Mampu bekerjasama secara efektif dalam berbagai konteks profesional',
                ['UNISM.MKPU.001', 'UNISM.MKPU.002', 'UNISM.MKPU.003', 'UNISM.MKU.002', 'UNISM.SI026', 'UNISM.SI034']
            ],
        ];

        $cplCache = Cpl::all()->keyBy('kode');
        $mkCache  = MataKuliah::all()->keyBy('kode');

        foreach ($data as [$kode, $cplKode, $deskripsi, $mkKodes]) {
            $cpl = $cplCache->get($cplKode);
            if (!$cpl) {
                echo "CPL not found: $cplKode\n";
                continue;
            }

            $cpmk = Cpmk::updateOrCreate(
                ['kode' => $kode],
                ['deskripsi' => $deskripsi, 'cpl_id' => $cpl->id]
            );

            $mkIds = collect($mkKodes)
                ->map(fn($k) => $mkCache->get($k)?->id)
                ->filter()
                ->values()
                ->all();

            $cpmk->mataKuliahs()->sync($mkIds);
        }
    }
}
