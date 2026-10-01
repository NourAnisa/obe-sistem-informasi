<?php

namespace Database\Seeders;

use App\Models\Cpl;
use Illuminate\Database\Seeder;

class CplSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'CPL01', 'deskripsi' => 'Mampu memahami, menganalisis, dan menilai konsep dasar dan peran sistem informasi dalam mengelola data dan memberikan rekomendasi pengambilan keputusan pada proses dan sistem organisasi.', 'kategori' => 'KK'],
            ['kode' => 'CPL02', 'deskripsi' => 'Mampu merancang dan menggunakan database, serta mengolah dan menganalisa data dengan alat dan teknik pengolahan data.', 'kategori' => 'KK'],
            ['kode' => 'CPL03', 'deskripsi' => 'Mampu memahami dan menggunakan berbagai metodologi pengembangan sistem beserta alat pemodelan sistem dan menganalisa kebutuhan pengguna dalam membangun sistem informasi untuk mencapai tujuan organisasi.', 'kategori' => 'KK'],
            ['kode' => 'CPL04', 'deskripsi' => 'Mampu membuat perencanaan infrastruktur TI, arsitektur jaringan, layanan fisik dan cloud, menganalisa konsep identifikasi, otentikasi, otorisasi akses dalam konteks melindungi orang dan perangkat.', 'kategori' => 'KK'],
            ['kode' => 'CPL05', 'deskripsi' => 'Mampu memahami dan menerapkan kode etik dalam penggunaan informasi dan data pada perancangan, implementasi, dan penggunaan suatu sistem.', 'kategori' => 'KK'],
            ['kode' => 'CPL06', 'deskripsi' => 'Memiliki kemampuan merencanakan, menerapkan, memelihara dan meningkatkan sistem informasi organisasi untuk mencapai tujuan dan sasaran organisasi yang strategis baik jangka pendek maupun jangka panjang.', 'kategori' => 'KK'],
            ['kode' => 'CPL07', 'deskripsi' => 'Mampu memahami, mengidentifikasi dan menerapkan konsep, teknik dan metodologi manajemen proyek sistem informasi.', 'kategori' => 'KK'],
            ['kode' => 'CPL08', 'deskripsi' => 'Mampu menjunjung tinggi nilai kemanusiaan dalam menjalankan tugas berdasarkan agama, moral, dan etika akademik serta rasa tanggungjawab pada negara dan bangsa.', 'kategori' => 'Sikap'],
            ['kode' => 'CPL09', 'deskripsi' => 'Mampu mengambil keputusan secara tepat dalam konteks penyelesaian masalah berdasarkan hasil analisis informasi dan data serta menyusun deskripsi saintifik berupa karya ilmiah.', 'kategori' => 'KU'],
            ['kode' => 'CPL10', 'deskripsi' => 'Mampu berkomunikasi, bekerja sama, dan bertanggung jawab di bidang keahliannya secara mandiri maupun kelompok dalam berbagai konteks profesional.', 'kategori' => 'Sikap_KU'],
        ];

        foreach ($data as $item) {
            Cpl::create($item);
        }
    }
}
