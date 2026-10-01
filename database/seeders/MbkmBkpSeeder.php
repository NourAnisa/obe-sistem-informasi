<?php

namespace Database\Seeders;

use App\Models\MbkmBkp;
use Illuminate\Database\Seeder;

class MbkmBkpSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [1, 'Pertukaran Mahasiswa', 20, 20, 'Kegiatan belajar di perguruan tinggi lain dalam negeri selama 1–2 semester.', 'Konversi MK semester berjalan sesuai kesepakatan PT asal dan PT tujuan, maks 20 SKS.'],
            [2, 'Magang/Praktik Kerja', 20, 20, 'Aktivitas magang di perusahaan/industri/organisasi selama 1–2 semester.', 'PKL (UNISM.SI034) 4 SKS + konversi MK lain hingga total 20 SKS.'],
            [3, 'Asistensi Mengajar di Satuan Pendidikan', 20, 20, 'Membantu proses pembelajaran di sekolah/madrasah/pendidikan nonformal.', 'Konversi MK bidang pendidikan dan teknologi, maks 20 SKS.'],
            [4, 'Penelitian/Riset', 20, 20, 'Kegiatan penelitian di laboratorium atau lembaga riset di bawah bimbingan dosen/peneliti.', 'Metode Penelitian (UNISM.SI027) 3 SKS + konversi MK terkait, maks 20 SKS.'],
            [5, 'Proyek Kemanusiaan', 20, 20, 'Kegiatan sosial kemasyarakatan untuk mengatasi bencana, kemanusiaan, dll.', 'Konversi MK soft skills dan leadership, maks 20 SKS.'],
            [6, 'Kegiatan Wirausaha', 20, 20, 'Membangun startup atau usaha berbasis teknologi/bisnis digital.', 'Kewirausahaan (UNISM.MKU.002) 2 SKS + konversi MK terkait, maks 20 SKS.'],
            [7, 'Studi/Proyek Independen', 20, 20, 'Mengembangkan proyek inovatif secara mandiri atau dalam tim lintas prodi.', 'Konversi MK sesuai tema proyek, maks 20 SKS.'],
            [8, 'Membangun Desa/KKNT', 4, 20, 'Kuliah Kerja Nyata Tematik yang berfokus pada pemberdayaan masyarakat desa.', 'KKN (UNISM.MKPU.003) 4 SKS + potensi konversi MK sosial/manajerial.'],
        ];

        foreach ($data as $row) {
            MbkmBkp::create([
                'no'               => $row[0],
                'bentuk_kegiatan'  => $row[1],
                'sks_reguler'      => $row[2],
                'sks_mbkm_maks'    => $row[3],
                'deskripsi'        => $row[4],
                'konversi_mk'      => $row[5],
            ]);
        }
    }
}
