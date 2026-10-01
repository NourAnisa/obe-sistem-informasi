<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create faculties table
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->string('kode')->nullable();
            $table->timestamps();
        });

        // 2. Create programs table
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('nama')->unique();
            $table->string('jenjang');
            $table->string('kode_prodi')->nullable();
            $table->timestamps();
        });

        // Seed Faculties
        $now = now();
        $fkId = DB::table('faculties')->insertGetId([
            'nama' => 'Fakultas Kesehatan',
            'kode' => 'FK',
            'created_at' => $now,
            'updated_at' => $now
        ]);
        $fhId = DB::table('faculties')->insertGetId([
            'nama' => 'Fakultas Humaniora',
            'kode' => 'FH',
            'created_at' => $now,
            'updated_at' => $now
        ]);
        $fstId = DB::table('faculties')->insertGetId([
            'nama' => 'Fakultas Sains dan Teknologi',
            'kode' => 'FST',
            'created_at' => $now,
            'updated_at' => $now
        ]);

        // Seed Programs under Fakultas Kesehatan
        $kesehatanProdis = [
            ['nama' => 'Diploma Tiga Kebidanan', 'jenjang' => 'D3'],
            ['nama' => 'Sarjana Kebidanan', 'jenjang' => 'S1'],
            ['nama' => 'Pendidikan Profesi Bidan', 'jenjang' => 'Profesi'],
            ['nama' => 'Sarjana Keperawatan', 'jenjang' => 'S1'],
            ['nama' => 'Profesi Ners', 'jenjang' => 'Profesi'],
            ['nama' => 'Sarjana Farmasi', 'jenjang' => 'S1'],
            ['nama' => 'Diploma Empat Promosi Kesehatan', 'jenjang' => 'D4'],
        ];
        foreach ($kesehatanProdis as $p) {
            DB::table('programs')->insert([
                'faculty_id' => $fkId,
                'nama' => $p['nama'],
                'jenjang' => $p['jenjang'],
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        // Seed Programs under Fakultas Humaniora
        $humanioraProdis = [
            ['nama' => 'Sarjana Akuntansi', 'jenjang' => 'S1'],
            ['nama' => 'Sarjana Manajemen', 'jenjang' => 'S1'],
            ['nama' => 'Sarjana Hukum', 'jenjang' => 'S1'],
            ['nama' => 'Sarjana Pendidikan Bahasa Inggris', 'jenjang' => 'S1'],
        ];
        foreach ($humanioraProdis as $p) {
            DB::table('programs')->insert([
                'faculty_id' => $fhId,
                'nama' => $p['nama'],
                'jenjang' => $p['jenjang'],
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        // Seed Programs under Fakultas Sains dan Teknologi
        $sainsProdis = [
            ['nama' => 'Sarjana Sistem Informasi', 'jenjang' => 'S1'],
            ['nama' => 'Sarjana Teknologi Informasi', 'jenjang' => 'S1'],
            ['nama' => 'Sarjana Teknik Industri', 'jenjang' => 'S1'],
        ];
        foreach ($sainsProdis as $p) {
            DB::table('programs')->insert([
                'faculty_id' => $fstId,
                'nama' => $p['nama'],
                'jenjang' => $p['jenjang'],
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }

        // Get the ID of "Sarjana Sistem Informasi" to link existing data
        $siProgId = DB::table('programs')
            ->where('nama', 'Sarjana Sistem Informasi')
            ->value('id');

        // Add program_id columns to existing tables
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
        });

        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
        });

        Schema::table('mata_kuliah', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
        });

        Schema::table('cpl', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
        });

        // Set all existing data to "Sarjana Sistem Informasi" (since that's the only one currently used)
        if ($siProgId) {
            DB::table('users')->update(['program_id' => $siProgId]);
            DB::table('mahasiswas')->update(['program_id' => $siProgId]);
            DB::table('mata_kuliah')->update(['program_id' => $siProgId]);
            DB::table('cpl')->update(['program_id' => $siProgId]);
        }
    }

    public function down(): void
    {
        Schema::table('cpl', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });

        Schema::table('mata_kuliah', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });

        Schema::table('mahasiswas', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });

        Schema::dropIfExists('programs');
        Schema::dropIfExists('faculties');
    }
};
