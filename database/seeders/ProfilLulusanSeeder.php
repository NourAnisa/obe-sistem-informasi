<?php

namespace Database\Seeders;

use App\Models\ProfilLulusan;
use Illuminate\Database\Seeder;

class ProfilLulusanSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode'        => 'PL01',
                'deskripsi'   => 'Lulusan memiliki kemampuan menganalisis, merancang, membuat, dan melakukan evaluasi sistem informasi yang selaras dengan tujuan organisasi.',
                'hard_skills' => 'data analyst, system analyst',
                'soft_skills' => 'project manager, startup founder, kolaborasi dengan berbagai bidang',
            ],
            [
                'kode'        => 'PL02',
                'deskripsi'   => 'Lulusan memiliki kemampuan memahami, menerapkan dan mengintegrasikan model sistem, menggunakan metode dan berbagai teknik peningkatan bisnis proses yang mendatangkan suatu nilai untuk organisasi. (IS2020)',
                'hard_skills' => 'IS technopreneur, digital business developer',
                'soft_skills' => null,
            ],
            [
                'kode'        => 'PL03',
                'deskripsi'   => 'Lulusan memiliki kemampuan berpikir kritis dan inovatif dalam konteks pengembangan atau implementasi ilmu pengetahuan dan teknologi berdasarkan hasil analisis informasi dan data.',
                'hard_skills' => null,
                'soft_skills' => 'creativity, critical thinking, complex problem solving, judgement and decision making',
            ],
            [
                'kode'        => 'PL04',
                'deskripsi'   => 'Lulusan memiliki kemampuan berkomunikasi dan bekerjasama secara efektif dalam berbagai konteks profesional sesuai dengan bidang keahliannya berdasarkan agama, moral, dan etika akademik.',
                'hard_skills' => null,
                'soft_skills' => 'communication, negotiation, emotional intelligence, coordinating with others, people management',
            ],
        ];

        foreach ($data as $item) {
            ProfilLulusan::create($item);
        }
    }
}
