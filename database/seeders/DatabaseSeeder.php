<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ProfilLulusanSeeder::class,
            CplSeeder::class,
            CplSndiktiSeeder::class,
            BahanKajianSeeder::class,
            MataKuliahSeeder::class,
            PivotSeeder::class,
            CpmkSeeder::class,
            SubCpmkSeeder::class,
            MkSubCpmkSeeder::class,
            MbkmBkpSeeder::class,
            BobotPenilaianSeeder::class,
            TeknikPenilaianSeeder::class,
            RumusanNilaiMkSeeder::class,
        ]);
    }
}
