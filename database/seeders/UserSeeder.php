<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email'   => 'admin@unism.ac.id',
                'name'    => 'Administrator',
                'role'    => 'admin',
                'jabatan' => 'Admin Sistem',
            ],
            [
                'email'   => 'noranisa@unism.ac.id',
                'name'    => 'Nor Anisa, S.Kom., M.Kom.',
                'role'    => 'kaprodi',
                'jabatan' => 'Ketua Program Studi Sistem Informasi',
                'nik'     => '',
                'nuptk'   => '',
            ],
            [
                'email'   => 'kaprodi@unism.ac.id',
                'name'    => 'M. Riko Anshori Prasetya, M.Kom.',
                'role'    => 'kaprodi',
                'jabatan' => 'Ketua Program Studi',
            ],
            [
                'email'   => 'dosen@unism.ac.id',
                'name'    => 'Ahmad Hidayat, S.Kom., M.Kes.',
                'role'    => 'dosen',
                'jabatan' => 'Dosen Pengampu',
            ],
            [
                'email'   => 'akademik@unism.ac.id',
                'name'    => 'Nurhaeni, S.T., M.Cs.',
                'role'    => 'akademik',
                'jabatan' => 'Bagian Akademik',
            ],
            [
                'email'   => 'kemahasiswaan@unism.ac.id',
                'name'    => 'Kemahasiswaan UNISM',
                'role'    => 'kemahasiswaan',
                'jabatan' => 'Bagian Kemahasiswaan',
            ],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                array_merge($u, ['password' => Hash::make('password')])
            );
        }
    }
}
