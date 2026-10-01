<?php

namespace Database\Seeders;

use App\Models\MataKuliah;
use Illuminate\Database\Seeder;

class MataKuliahSeeder extends Seeder
{
    public function run(): void
    {
        // [kode, nama, sks, sks_teori, sks_praktikum, semester, kategori, pjmk, is_wajib]
        $data = [
            // SEMESTER 1
            ['UNISM.SI001', 'Algoritma dan Struktur Data', 3, 2, 1, 1, 'MKKP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            ['UNISM.SI024', 'Konsep Sistem Informasi', 2, 2, null, 1, 'MKKP', 'Nurhaeni, S.T., M.Cs.', true],
            ['UNISM.SI028', 'Matematika Diskrit', 2, 2, null, 1, 'MKKP', 'Ahmad Hidayat, S.Kom., M.Kes.', true],
            ['UNISM.SI040', 'Statistika', 3, 3, null, 1, 'MKKP', 'Ahmad Hidayat, S.Kom., M.Kes.', true],
            ['UNISM.MKU.006', 'Pendidikan Pancasila', 2, 2, null, 1, 'MKWK', 'Septyan Eka Prastya, M.Kom.', true],
            ['UNISM.MKU.003', 'Pendidikan Agama', 2, 2, null, 1, 'MKWK', 'Yusri, S.E., M.M.', true],
            ['UNISM.SI026', 'Leadership and Communication', 2, 2, null, 1, 'MKKP', 'Nurhaeni, S.T., M.Cs.', true],
            ['UNISM.SI005', 'Bahasa Inggris Basic', 2, 2, null, 1, 'MKP', 'Nurhaeni, S.T., M.Cs.', true],
            // SEMESTER 2
            ['UNISM.SI031', 'Pemrograman Web', 3, 1, 2, 2, 'MKKP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            ['UNISM.SI007', 'Basis Data', 3, 2, 1, 2, 'MKKP', 'Nurhaeni, S.T., M.Cs.', true],
            ['UNISM.SI033', 'Pengantar Organisasi dan Manajemen', 3, 3, null, 2, 'MKKP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            ['UNISM.SI023', 'Jaringan Komputer', 2, 2, null, 2, 'MKKP', 'Nor Anisa, M.Kom.', true],
            ['UNISM.SI037', 'Sistem Pendukung Keputusan', 3, 2, 1, 2, 'MKKP', 'Nor Anisa, M.Kom.', true],
            ['UNISM.SI032', 'Pengantar IoT', 3, 2, 1, 2, 'MKKP', 'Nor Anisa, M.Kom.', true],
            ['UNISM.SI036', 'Sistem Informasi Manajemen', 3, 3, null, 2, 'MKKP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            // SEMESTER 3
            ['UNISM.SI035', 'Rekayasa Perangkat Lunak', 3, 2, 1, 3, 'MKKP', 'Nurhaeni, S.T., M.Cs.', true],
            ['UNISM.SI030', 'Pemrograman Berorientasi Objek', 3, 1, 2, 3, 'MKKP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            ['UNISM.SI002', 'Analisis dan Perancangan Sistem Informasi', 3, 2, 1, 3, 'MKKP', 'Nurhaeni, S.T., M.Cs.', true],
            ['UNISM.SI017', 'E-Commerce', 2, 2, null, 3, 'MKKP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            ['UNISM.SI008', 'Big Data', 3, 2, 1, 3, 'MKPP', 'Nor Anisa, M.Kom.', true],
            ['UNISM.MKU.001', 'Bahasa Indonesia', 2, 2, null, 3, 'MKWK', 'Cynthia Eka Fayuning Tjomiadi, M.SN.', true],
            ['UNISM.MKU.004', 'Pendidikan Anti Korupsi', 2, 2, null, 3, 'MKWK', 'Muhammad Mahendra Abdi, M.H', true],
            ['UNISM.SI006', 'Bahasa Inggris Intermediate', 2, 2, null, 3, 'MKP', 'Nurhaeni, S.T., M.Cs.', true],
            // SEMESTER 4
            ['UNISM.SI029', 'Mobile Programming', 3, 1, 2, 4, 'MKKP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            ['UNISM.SI041', 'Tata Kelola Teknologi Informasi', 2, 2, null, 4, 'MKKP', 'Nor Anisa, M.Kom.', true],
            ['UNISM.SI010', 'Business Process Management', 3, 3, null, 4, 'MKPP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            ['UNISM.SI020', 'Information Security', 3, 2, 1, 4, 'MKKP', 'Nor Anisa, M.Kom.', true],
            ['UNISM.MKU.005', 'Pendidikan Kewarganegaraan', 2, 2, null, 4, 'MKWK', 'Yulian, M.H.', true],
            ['UNISM.MKPU.001', 'ICC I', 2, 2, null, 4, 'MKPU', 'Angga Irawan, S.Kep., M.Kep., Ners.', true],
            ['FSaint.001', 'Transformasi Digital', 2, 2, null, 4, 'MKF', 'Mambang, M.Kom.', true],
            ['UNISM.SI014', 'Data Warehouse', 3, 2, 1, 4, 'MKPP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            // SEMESTER 5
            ['UNISM.SI038', 'Software Testing', 3, 2, 1, 5, 'MKKP', 'Nurhaeni, S.T., M.Cs.', true],
            ['UNISM.SI018', 'Enterprise Resource Planning', 3, 2, 1, 5, 'MKPP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            ['UNISM.SI012', 'Customer Relationship Management', 3, 2, 1, 5, 'MKPP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            ['UNISM.SI042', 'UI/UX Design', 3, 1, 2, 5, 'MKKP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            ['UNISM.SI013', 'Data Mining', 3, 2, 1, 5, 'MKPP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            ['UNISM.MKPU.002', 'ICC II', 2, null, 2, 5, 'MKPU', 'Angga Irawan, S.Kep., M.Kep., Ners.', true],
            ['UNISM.MKU.002', 'Kewirausahaan', 2, 2, null, 5, 'MKPU', 'Iwan Yuwindry, M.Farm., S.Farm., Apt.', true],
            // SEMESTER 6
            ['UNISM.SI027', 'Metode Penelitian', 3, 3, null, 6, 'MKKP', 'Ahmad Hidayat, S.Kom., M.Kes.', true],
            ['UNISM.SI003', 'Arsitektur Enterprise', 3, 3, null, 6, 'MKKP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            ['UNISM.SI009', 'Business Intelligence', 3, 2, 1, 6, 'MKPP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            ['UNISM.SI034', 'Praktik Kerja Lapangan', 4, null, 4, 6, 'MKKP', 'Nurhaeni, S.T., M.Cs.', true],
            ['UNISM.SI039', 'Supply Chain Management', 3, 3, null, 6, 'MKKP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            ['UNISM.SI022', 'Interaksi Manusia dan Komputer', 3, 2, 1, 6, 'MKKP', 'Nor Anisa, M.Kom.', true],
            // SEMESTER 7
            ['UNISM.SI016', 'E-Business', 3, 3, null, 7, 'MKKP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            ['UNISM.SI025', 'Manajemen Proyek Sistem Informasi', 3, 2, 1, 7, 'MKKP', 'Nurhaeni, S.T., M.Cs.', true],
            ['UNISM.SI021', 'Integrasi Sistem', 3, 2, 1, 7, 'MKKP', 'Nor Anisa, M.Kom.', true],
            ['UNISM.MKPU.003', 'Kuliah Kerja Nyata', 4, null, 4, 7, 'MKPU', 'Angga Irawan, S.Kep., M.Kep., Ners.', true],
            ['UNISM.SI015', 'Digital Innovation', 3, 3, null, 7, 'MKKP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            ['UNISM.SI011', 'Cloud Computing', 3, 2, 1, 7, 'MKKP', 'M.Riko Anshori Prasetya, M.Kom.', true],
            // SEMESTER 8
            ['UNISM.001', 'Tugas Akhir', 6, null, 6, 8, 'MKKP', 'Putri Vidiasari Darsono, S.Si., M.Pd.', true],
            ['UNISM.SI004', 'Audit Sistem Informasi', 3, 3, null, 8, 'MKKP', 'Bayu Nugraha, S.Kom., M.MSI.', true],
            ['UNISM.SI019', 'Etika Profesi', 2, 2, null, 8, 'MKKP', 'Nor Anisa, M.Kom.', true],
        ];

        foreach ($data as $row) {
            MataKuliah::create([
                'kode'           => $row[0],
                'nama'           => $row[1],
                'sks'            => $row[2],
                'sks_teori'      => $row[3],
                'sks_praktikum'  => $row[4],
                'semester'       => $row[5],
                'kategori'       => $row[6],
                'pjmk'           => $row[7],
                'is_wajib'       => $row[8],
            ]);
        }
    }
}
