<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->string('kelas', 10)->nullable();
            $table->unsignedTinyInteger('semester');
            $table->string('tahun_akademik', 20);
            $table->enum('peran', ['pengampu', 'pengembang_rps'])->default('pengampu');
            $table->timestamps();

            $table->unique(
                ['dosen_id', 'mata_kuliah_id', 'kelas', 'tahun_akademik', 'peran'],
                'dosen_mk_unique'
            );
            $table->index(['tahun_akademik', 'semester']);
            $table->index('dosen_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_mata_kuliah');
    }
};
