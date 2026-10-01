<?php

namespace Database\Seeders;

use App\Models\Cpmk;
use App\Models\SubCpmk;
use Illuminate\Database\Seeder;

class SubCpmkSeeder extends Seeder
{
    public function run(): void
    {
        // [kode, cpmk_kode, deskripsi]
        $data = [
            // CPL01 - CPMK011
            ['Sub-CPMK0111', 'CPMK011', 'Mampu memahami konsep informasi dan sistem informasi'],
            ['Sub-CPMK0112', 'CPMK011', 'Mampu memahami komponen sistem informasi'],
            ['Sub-CPMK0113', 'CPMK011', 'Mampu memahami sistem informasi dan nilai dari informasi'],
            ['Sub-CPMK0114', 'CPMK011', 'Mampu memahami jenis sistem informasi yang umum'],
            ['Sub-CPMK0115', 'CPMK011', 'Mampu memahami konsep dasar sistem informasi pada proses dan sistem organisasi'],
            ['Sub-CPMK0116', 'CPMK011', 'Mampu memahami peran sistem informasi pada proses dan sistem organisasi'],
            // CPMK012
            ['Sub-CPMK0121', 'CPMK012', 'Mampu menganalisis proses organisasi'],
            ['Sub-CPMK0122', 'CPMK012', 'Mampu menganalisis sistem organisasi'],
            // CPMK013
            ['Sub-CPMK0131', 'CPMK013', 'Mampu menilai proses yang ada pada organisasi'],
            ['Sub-CPMK0132', 'CPMK013', 'Mampu menilai sistem pengelolaan data pada organisasi'],
            ['Sub-CPMK0133', 'CPMK013', 'Mampu mengidentifikasi proses bisnis dalam organisasi'],
            // CPMK014
            ['Sub-CPMK0141', 'CPMK014', 'Mampu menilai peran sistem informasi pada organisasi'],
            ['Sub-CPMK0142', 'CPMK014', 'Mampu memberikan rekomendasi pengambilan keputusan di organisasi'],
            ['Sub-CPMK0143', 'CPMK014', 'Mampu mengevaluasi kesesuaian sistem dengan kebutuhan organisasi'],
            // CPMK015
            ['Sub-CPMK0151', 'CPMK015', 'Mampu menjelaskan konsep dasar keahlian khusus dalam bidang teknologi informasi.'],
            ['Sub-CPMK0152', 'CPMK015', 'Mampu menerapkan konsep dasar keahlian khusus dalam bidang teknologi informasi.'],

            // CPL02 - CPMK021
            ['Sub-CPMK0211', 'CPMK021', 'Mampu menjelaskan komponen database'],
            ['Sub-CPMK0212', 'CPMK021', 'Mampu memahami model data'],
            ['Sub-CPMK0213', 'CPMK021', 'Mampu merancang database'],
            ['Sub-CPMK0214', 'CPMK021', 'Mampu menganalisis kebutuhan data dan merancang model multidimensional'],
            ['Sub-CPMK0215', 'CPMK021', 'Mampu membuat rancangan logis dan fisik data warehouse'],
            // CPMK022
            ['Sub-CPMK0221', 'CPMK022', 'Mampu memahami bahasa database'],
            ['Sub-CPMK0222', 'CPMK022', 'Mampu menggunakan database'],
            // CPMK023
            ['Sub-CPMK0231', 'CPMK023', 'Mampu menjelaskan konsep dasar pengolahan data'],
            ['Sub-CPMK0232', 'CPMK023', 'Mampu mengolah data dengan alat pengolahan data'],
            ['Sub-CPMK0233', 'CPMK023', 'Mampu mengolah data dengan teknik pengolahan data'],
            // CPMK024
            ['Sub-CPMK0241', 'CPMK024', 'Mampu menganalisis data dengan alat pengolahan data'],
            ['Sub-CPMK0242', 'CPMK024', 'Mampu menganalisis data dengan teknik pengolahan data'],

            // CPL03 - CPMK031
            ['Sub-CPMK0311', 'CPMK031', 'Mampu menjelaskan berbagai metodologi pengembangan sistem'],
            ['Sub-CPMK0312', 'CPMK031', 'Mampu membandingkan berbagai metodologi pengembangan sistem'],
            // CPMK032
            ['Sub-CPMK0321', 'CPMK032', 'Mampu menerapkan berbagai metodologi pengembangan sistem'],
            ['Sub-CPMK0322', 'CPMK032', 'Mampu menggambarkan berbagai metodologi pengembangan sistem'],
            // CPMK033
            ['Sub-CPMK0331', 'CPMK033', 'Mampu mengidentifikasi kebutuhan pengguna dalam membangun sistem informasi untuk mencapai tujuan organisasi'],
            ['Sub-CPMK0332', 'CPMK033', 'Mampu menentukan metodologi pengembangan sistem yang sesuai dengan tujuan organisasi'],
            // CPMK034
            ['Sub-CPMK0341', 'CPMK034', 'Mampu mengidentifikasi jenis-jenis permasalahan yang tepat untuk diselesaikan menggunakan solusi teknologi informasi berdasarkan analisis kebutuhan dan kondisi lingkungan'],

            // CPL04 - CPMK041
            ['Sub-CPMK0411', 'CPMK041', 'Mampu memahami infrastruktur TI, arsitektur jaringan, layanan fisik dan cloud'],
            ['Sub-CPMK0412', 'CPMK041', 'Mampu membuat perencanaan infrastruktur TI'],
            ['Sub-CPMK0413', 'CPMK041', 'Mampu membuat perencanaan infrastruktur arsitektur jaringan'],
            ['Sub-CPMK0414', 'CPMK041', 'Mampu membuat perencanaan infrastruktur layanan fisik/cloud'],
            // CPMK042
            ['Sub-CPMK0421', 'CPMK042', 'Mampu menganalisis konsep identifikasi akses dalam konteks keamanan sistem'],
            ['Sub-CPMK0422', 'CPMK042', 'Mampu menganalisis konsep otentikasi dan otorisasi dalam konteks keamanan sistem'],

            // CPL05 - CPMK051
            ['Sub-CPMK0511', 'CPMK051', 'Mampu memahami kode etik dalam penggunaan informasi data pada perancangan suatu sistem'],
            ['Sub-CPMK0512', 'CPMK051', 'Mampu memahami kode etik dalam penggunaan informasi data pada implementasi dan penggunaan suatu sistem'],
            // CPMK052
            ['Sub-CPMK0521', 'CPMK052', 'Mampu menerapkan kode etik dalam penggunaan informasi data pada perancangan suatu sistem'],
            ['Sub-CPMK0522', 'CPMK052', 'Mampu menerapkan kode etik dalam penggunaan informasi data pada implementasi dan penggunaan suatu sistem'],

            // CPL06 - CPMK061
            ['Sub-CPMK0611', 'CPMK061', 'Mampu menganalisis sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis baik jangka pendek maupun jangka panjang'],
            ['Sub-CPMK0612', 'CPMK061', 'Mampu merencanakan sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis baik jangka pendek maupun jangka panjang'],
            // CPMK062
            ['Sub-CPMK0621', 'CPMK062', 'Mampu merancang sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis jangka pendek maupun jangka panjang'],
            ['Sub-CPMK0622', 'CPMK062', 'Mampu menerapkan sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis jangka pendek maupun jangka panjang'],
            // CPMK063
            ['Sub-CPMK0631', 'CPMK063', 'Mampu memelihara sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis jangka pendek maupun jangka panjang'],
            ['Sub-CPMK0632', 'CPMK063', 'Mampu menilai sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis jangka pendek maupun jangka panjang'],
            // CPMK064
            ['Sub-CPMK0641', 'CPMK064', 'Mampu mengevaluasi sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis jangka pendek maupun jangka panjang'],
            ['Sub-CPMK0642', 'CPMK064', 'Mampu meningkatkan sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis jangka pendek maupun jangka panjang'],

            // CPL07 - CPMK071
            ['Sub-CPMK0711', 'CPMK071', 'Mampu memahami konsep manajemen proyek sistem informasi'],
            ['Sub-CPMK0712', 'CPMK071', 'Mampu memahami teknik dan metodologi manajemen proyek sistem informasi'],
            // CPMK072
            ['Sub-CPMK0721', 'CPMK072', 'Mampu mengidentifikasi konsep manajemen proyek sistem informasi'],
            ['Sub-CPMK0722', 'CPMK072', 'Mampu mengidentifikasi teknik dan metodologi manajemen proyek sistem informasi'],
            // CPMK073
            ['Sub-CPMK0731', 'CPMK073', 'Mampu menerapkan konsep manajemen proyek sistem informasi'],
            ['Sub-CPMK0732', 'CPMK073', 'Mampu menerapkan teknik dan metodologi manajemen proyek sistem informasi'],
            ['Sub-CPMK0733', 'CPMK073', 'Mampu membangun perangkat lunak dalam sebuah proyek sistem informasi'],

            // CPL08 - CPMK081
            ['Sub-CPMK0811', 'CPMK081', 'Mampu menjelaskan nilai kemanusiaan dalam menjalankan tugas berdasarkan agama dan moral'],
            ['Sub-CPMK0812', 'CPMK081', 'Mampu menunjukkan nilai kemanusiaan dalam menjalankan tugas berdasarkan agama dan moral'],
            // CPMK082
            ['Sub-CPMK0821', 'CPMK082', 'Mampu menjelaskan nilai kemanusiaan dalam menjalankan tugas berdasarkan etika akademik dan rasa tanggungjawab pada negara dan bangsa'],
            ['Sub-CPMK0822', 'CPMK082', 'Mampu menunjukkan nilai kemanusiaan dalam menjalankan tugas berdasarkan etika akademik dan rasa tanggungjawab pada negara dan bangsa'],
            // CPMK083
            ['Sub-CPMK0831', 'CPMK083', 'Mampu menunjukkan nilai kemanusiaan dalam menjalankan tugas berdasarkan etika akademik'],
            ['Sub-CPMK0832', 'CPMK083', 'Mampu menjelaskan nilai kemanusiaan dalam menjalankan tugas berdasarkan etika akademik'],
            // CPMK084
            ['Sub-CPMK0841', 'CPMK084', 'Mampu menunjukkan nilai kemanusiaan dalam menjalankan tugas berdasarkan rasa tanggungjawab pada negara dan bangsa'],

            // CPL09 - CPMK091
            ['Sub-CPMK0911', 'CPMK091', 'Mampu mengumpulkan informasi dan data secara tepat dalam konteks penyelesaian masalah di bidang keahliannya'],
            ['Sub-CPMK0912', 'CPMK091', 'Mampu mengambil keputusan secara tepat dalam konteks penyelesaian masalah di bidang keahliannya, berdasarkan hasil analisis informasi dan data'],
            // CPMK092
            ['Sub-CPMK0921', 'CPMK092', 'Mampu menjelaskan konsep dan prinsip dasar berpikir kritis, termasuk analisis, evaluasi, dan sintesis informasi yang diperlukan menggunakan bahasa dan kaidah penulisan akademik yang berlaku dalam penyusunan karya ilmiah.'],
            ['Sub-CPMK0922', 'CPMK092', 'Mampu menyusun deskripsi saintifik dalam bentuk karya ilmiah berdasarkan hasil analisis informasi dan data'],

            // CPL10 - CPMK101
            ['Sub-CPMK1011', 'CPMK101', 'Mampu menyampaikan gagasan secara efektif dalam berbagai konteks profesional'],
            ['Sub-CPMK1012', 'CPMK101', 'Mampu berkomunikasi secara efektif dalam bentuk tulisan dan lisan yang sesuai dalam berbagai konteks profesional.'],
            ['Sub-CPMK1013', 'CPMK101', 'Mampu menulis dokumen formal dengan tata bahasa yang baku.'],
            // CPMK102
            ['Sub-CPMK1021', 'CPMK102', 'Mampu bekerjasama secara efektif dalam berbagai konteks profesional'],
            ['Sub-CPMK1022', 'CPMK102', 'Mampu mengelola kelompok kerja secara mandiri dalam berbagai konteks profesional'],
        ];

        $cpmkCache = Cpmk::all()->keyBy('kode');

        foreach ($data as [$kode, $cpmkKode, $deskripsi]) {
            $cpmk = $cpmkCache->get($cpmkKode);
            if (!$cpmk) continue;

            SubCpmk::updateOrCreate(
                ['kode' => $kode],
                ['deskripsi' => $deskripsi, 'cpmk_id' => $cpmk->id]
            );
        }
    }
}
