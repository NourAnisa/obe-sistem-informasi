<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('nilai_mahasiswa')) {
            Schema::create('nilai_mahasiswa', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
                $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
                $table->string('semester_aktif', 20);
                $table->decimal('nilai_tugas', 5, 2)->default(0);
                $table->decimal('nilai_uts', 5, 2)->default(0);
                $table->decimal('nilai_uas', 5, 2)->default(0);
                $table->decimal('nilai_partisipatif', 5, 2)->default(0);
                $table->decimal('nilai_proyek', 5, 2)->default(0);
                $table->decimal('nilai_akhir', 5, 2)->default(0);
                $table->char('grade', 1)->nullable(); // A B C D E
                $table->boolean('lulus')->default(false);
                $table->timestamps();

                $table->unique(['mahasiswa_id', 'mata_kuliah_id', 'semester_aktif']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_mahasiswa');
    }
};
