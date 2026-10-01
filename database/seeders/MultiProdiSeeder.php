<?php

namespace Database\Seeders;

use App\Models\Cpl;
use App\Models\MataKuliah;
use Illuminate\Database\Seeder;

class MultiProdiSeeder extends Seeder
{
    public function run(): void
    {
        // ── PRODI TEKNOLOGI INFORMASI (ID: 13) ──
        $cplsTi = [
            ['kode' => 'TI-CPL01', 'deskripsi' => 'Mampu merancang, mengimplementasikan, dan mengelola infrastruktur jaringan komputer dan keamanan siber.', 'kategori' => 'KK', 'program_id' => 13],
            ['kode' => 'TI-CPL02', 'deskripsi' => 'Mampu mengembangkan aplikasi berbasis cloud, mobile, dan Internet of Things (IoT) secara efektif.', 'kategori' => 'KK', 'program_id' => 13],
            ['kode' => 'TI-CPL03', 'deskripsi' => 'Menerapkan prinsip-prinsip kecerdasan buatan dan pemrosesan data untuk penyelesaian masalah teknis.', 'kategori' => 'KK', 'program_id' => 13],
            ['kode' => 'TI-CPL04', 'deskripsi' => 'Menunjukkan sikap profesional, beretika, dan mampu bekerja secara mandiri maupun tim.', 'kategori' => 'Sikap', 'program_id' => 13],
        ];

        foreach ($cplsTi as $c) {
            Cpl::updateOrCreate(['kode' => $c['kode'], 'program_id' => 13], $c);
        }

        $mksTi = [
            ['kode' => 'UNISM.TI001', 'nama' => 'Pengantar Teknologi Informasi', 'sks' => 3, 'sks_teori' => 2, 'sks_praktikum' => 1, 'semester' => 1, 'kategori' => 'MKKP', 'pjmk' => 'Ahmad Hidayat, M.Kes.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI002', 'nama' => 'Algoritma dan Pemrograman Terapan', 'sks' => 3, 'sks_teori' => 1, 'sks_praktikum' => 2, 'semester' => 1, 'kategori' => 'MKKP', 'pjmk' => 'M. Riko Anshori, M.Kom.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI003', 'nama' => 'Sistem Operasi & Arsitektur Komputer', 'sks' => 3, 'sks_teori' => 2, 'sks_praktikum' => 1, 'semester' => 1, 'kategori' => 'MKKP', 'pjmk' => 'Nurhaeni, M.Cs.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI004', 'nama' => 'Dasar Jaringan Komputer', 'sks' => 3, 'sks_teori' => 2, 'sks_praktikum' => 1, 'semester' => 2, 'kategori' => 'MKKP', 'pjmk' => 'Nor Anisa, M.Kom.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI005', 'nama' => 'Pemrograman Web & API', 'sks' => 3, 'sks_teori' => 1, 'sks_praktikum' => 2, 'semester' => 2, 'kategori' => 'MKKP', 'pjmk' => 'M. Riko Anshori, M.Kom.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI006', 'nama' => 'Keamanan Siber & Kriptografi', 'sks' => 3, 'sks_teori' => 2, 'sks_praktikum' => 1, 'semester' => 3, 'kategori' => 'MKKP', 'pjmk' => 'Nor Anisa, M.Kom.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI007', 'nama' => 'Cloud Computing & Virtualisasi', 'sks' => 3, 'sks_teori' => 2, 'sks_praktikum' => 1, 'semester' => 3, 'kategori' => 'MKPP', 'pjmk' => 'M. Riko Anshori, M.Kom.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI008', 'nama' => 'System & Network Administration', 'sks' => 3, 'sks_teori' => 1, 'sks_praktikum' => 2, 'semester' => 4, 'kategori' => 'MKPP', 'pjmk' => 'Ahmad Hidayat, M.Kes.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI009', 'nama' => 'Internet of Things (IoT) Terapan', 'sks' => 3, 'sks_teori' => 1, 'sks_praktikum' => 2, 'semester' => 5, 'kategori' => 'MKPP', 'pjmk' => 'Nor Anisa, M.Kom.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI010', 'nama' => 'Kecerdasan Buatan (AI)', 'sks' => 3, 'sks_teori' => 2, 'sks_praktikum' => 1, 'semester' => 6, 'kategori' => 'MKPP', 'pjmk' => 'Nurhaeni, M.Cs.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.TI011', 'nama' => 'Tugas Akhir Teknologi Informasi', 'sks' => 6, 'sks_teori' => 0, 'sks_praktikum' => 6, 'semester' => 8, 'kategori' => 'MKKP', 'pjmk' => 'Nurhaeni, M.Cs.', 'is_wajib' => true, 'program_id' => 13],
        ];

        foreach ($mksTi as $m) {
            MataKuliah::updateOrCreate(['kode' => $m['kode'], 'program_id' => 13], $m);
        }

        $mkuSharedTi = [
            ['kode' => 'UNISM.MKU.TI.001', 'nama' => 'Bahasa Indonesia', 'sks' => 2, 'sks_teori' => 2, 'sks_praktikum' => 0, 'semester' => 3, 'kategori' => 'MKWK', 'pjmk' => 'Cynthia Eka Fayuning Tjomiadi, M.SN.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.MKU.TI.002', 'nama' => 'Kewirausahaan', 'sks' => 2, 'sks_teori' => 2, 'sks_praktikum' => 0, 'semester' => 5, 'kategori' => 'MKPU', 'pjmk' => 'Iwan Yuwindry, M.Farm., S.Farm., Apt.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.MKU.TI.003', 'nama' => 'Pendidikan Agama', 'sks' => 2, 'sks_teori' => 2, 'sks_praktikum' => 0, 'semester' => 1, 'kategori' => 'MKWK', 'pjmk' => 'Yusri, S.E., M.M.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.MKU.TI.004', 'nama' => 'Pendidikan Anti Korupsi', 'sks' => 2, 'sks_teori' => 2, 'sks_praktikum' => 0, 'semester' => 3, 'kategori' => 'MKWK', 'pjmk' => 'Muhammad Mahendra Abdi, M.H', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.MKU.TI.005', 'nama' => 'Pendidikan Kewarganegaraan', 'sks' => 2, 'sks_teori' => 2, 'sks_praktikum' => 0, 'semester' => 4, 'kategori' => 'MKWK', 'pjmk' => 'Yulian, M.H.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.MKU.TI.006', 'nama' => 'Pendidikan Pancasila', 'sks' => 2, 'sks_teori' => 2, 'sks_praktikum' => 0, 'semester' => 1, 'kategori' => 'MKWK', 'pjmk' => 'Septyan Eka Prastya, M.Kom.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.MKPU.TI.001', 'nama' => 'ICC I', 'sks' => 2, 'sks_teori' => 2, 'sks_praktikum' => 0, 'semester' => 4, 'kategori' => 'MKPU', 'pjmk' => 'Angga Irawan, S.Kep., M.Kep., Ners.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.MKPU.TI.002', 'nama' => 'ICC II', 'sks' => 2, 'sks_teori' => 0, 'sks_praktikum' => 2, 'semester' => 5, 'kategori' => 'MKPU', 'pjmk' => 'Angga Irawan, S.Kep., M.Kep., Ners.', 'is_wajib' => true, 'program_id' => 13],
            ['kode' => 'UNISM.MKPU.TI.003', 'nama' => 'Kuliah Kerja Nyata', 'sks' => 4, 'sks_teori' => 0, 'sks_praktikum' => 4, 'semester' => 7, 'kategori' => 'MKPU', 'pjmk' => 'Angga Irawan, S.Kep., M.Kep., Ners.', 'is_wajib' => true, 'program_id' => 13],
        ];

        foreach ($mkuSharedTi as $mku) {
            MataKuliah::updateOrCreate(['kode' => $mku['kode'], 'program_id' => 13], $mku);
        }
    }
}
