<?php

namespace Database\Seeders;

use App\Models\BahanKajian;
use Illuminate\Database\Seeder;

class BahanKajianSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['kode' => 'BK01', 'nama' => 'Foundation of Information Systems',    'referensi' => 'IS2020'],
            ['kode' => 'BK02', 'nama' => 'Data / Information Management',         'referensi' => 'IS2020'],
            ['kode' => 'BK03', 'nama' => 'IT Infrastructure',                     'referensi' => 'IS2020'],
            ['kode' => 'BK04', 'nama' => 'IS Project Management',                 'referensi' => 'IS2020'],
            ['kode' => 'BK05', 'nama' => 'Systems Analysis & Design',             'referensi' => 'IS2020'],
            ['kode' => 'BK06', 'nama' => 'IS Management and Strategy',            'referensi' => 'IS2020'],
            ['kode' => 'BK07', 'nama' => 'Application Development / Programming', 'referensi' => 'IS2020'],
            ['kode' => 'BK08', 'nama' => 'Secure Computing',                      'referensi' => 'IS2020'],
            ['kode' => 'BK09', 'nama' => 'Ethics, use and implications for society', 'referensi' => 'IS2020'],
            ['kode' => 'BK10', 'nama' => 'IS Practicum / Research Methods',       'referensi' => 'IS2020'],
            ['kode' => 'BK11', 'nama' => 'Mathematics and statistics',            'referensi' => 'CC2020'],
            ['kode' => 'BK12', 'nama' => 'Data / Business Analytics',             'referensi' => 'CC2020'],
            ['kode' => 'BK13', 'nama' => 'Personality Development',               'referensi' => 'CC2020'],
        ];

        foreach ($data as $item) {
            BahanKajian::create($item);
        }
    }
}
