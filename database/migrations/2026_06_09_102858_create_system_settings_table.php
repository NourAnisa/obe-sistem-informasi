<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, image, number
            $table->string('group')->default('general'); // general, identity, academic
            $table->string('label');
            $table->timestamps();
        });

        // Seed defaults from config/obe.php
        $now = now();
        DB::table('system_settings')->insert([
            // Identity group
            ['key' => 'nama_sistem',      'value' => 'OBE SI UNISM',                  'type' => 'text',     'group' => 'identity', 'label' => 'Nama Sistem',          'created_at' => $now, 'updated_at' => $now],
            ['key' => 'universitas',      'value' => 'Universitas Sari Mulia',         'type' => 'text',     'group' => 'identity', 'label' => 'Nama Universitas',     'created_at' => $now, 'updated_at' => $now],
            ['key' => 'fakultas',         'value' => 'Sains dan Teknologi',            'type' => 'text',     'group' => 'identity', 'label' => 'Fakultas',             'created_at' => $now, 'updated_at' => $now],
            ['key' => 'prodi',            'value' => 'Sistem Informasi',               'type' => 'text',     'group' => 'identity', 'label' => 'Program Studi',        'created_at' => $now, 'updated_at' => $now],
            ['key' => 'jenjang',          'value' => 'S1',                             'type' => 'text',     'group' => 'identity', 'label' => 'Jenjang',              'created_at' => $now, 'updated_at' => $now],
            ['key' => 'logo_path',        'value' => null,                             'type' => 'image',    'group' => 'identity', 'label' => 'Logo Kampus',          'created_at' => $now, 'updated_at' => $now],
            ['key' => 'logo_prodi_path',  'value' => null,                             'type' => 'image',    'group' => 'identity', 'label' => 'Logo Program Studi',   'created_at' => $now, 'updated_at' => $now],
            // Academic group
            ['key' => 'tahun_akademik',   'value' => '2025/2026',                      'type' => 'text',     'group' => 'academic', 'label' => 'Tahun Akademik',       'created_at' => $now, 'updated_at' => $now],
            ['key' => 'kaprodi',          'value' => 'M. Riko Anshori Prasetya, M.Kom','type' => 'text',     'group' => 'academic', 'label' => 'Nama Kaprodi',         'created_at' => $now, 'updated_at' => $now],
            ['key' => 'nik_kaprodi',      'value' => '1166032022221',                  'type' => 'text',     'group' => 'academic', 'label' => 'NIK/NIDN Kaprodi',     'created_at' => $now, 'updated_at' => $now],
            ['key' => 'akreditasi',       'value' => 'Baik Sekali',                    'type' => 'text',     'group' => 'academic', 'label' => 'Akreditasi',           'created_at' => $now, 'updated_at' => $now],
            ['key' => 'sks_total',        'value' => '146',                            'type' => 'number',   'group' => 'academic', 'label' => 'Total SKS',            'created_at' => $now, 'updated_at' => $now],
            ['key' => 'total_semester',   'value' => '8',                              'type' => 'number',   'group' => 'academic', 'label' => 'Total Semester',       'created_at' => $now, 'updated_at' => $now],
            ['key' => 'visi',             'value' => 'Menjadi program studi yang unggul dalam bidang sistem informasi berbasis nilai-nilai islami dan berstandar internasional pada tahun 2030.', 'type' => 'textarea', 'group' => 'academic', 'label' => 'Visi Program Studi', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
