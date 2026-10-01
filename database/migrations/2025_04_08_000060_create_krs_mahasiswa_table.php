<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('krs_mahasiswa')) {
            Schema::create('krs_mahasiswa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
                $table->string('kelas', 10)->default('A');
                $table->string('tahun_akademik', 20);   // e.g. "2025/2026"
                $table->unsignedTinyInteger('semester'); // 1–8
                $table->enum('status', ['aktif', 'drop'])->default('aktif');
                $table->timestamps();

                $table->unique(['mahasiswa_id', 'mata_kuliah_id', 'tahun_akademik'], 'krs_unique');
                $table->index(['mata_kuliah_id', 'tahun_akademik', 'kelas', 'status'], 'krs_enrollment_idx');
                $table->index(['mahasiswa_id', 'tahun_akademik'], 'krs_mahasiswa_ta_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('krs_mahasiswa');
    }
};
