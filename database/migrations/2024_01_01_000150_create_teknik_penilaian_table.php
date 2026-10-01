<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teknik_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->foreignId('cpmk_id')->constrained('cpmk')->cascadeOnDelete();
            $table->boolean('is_mbkm')->default(false);
            $table->boolean('has_quiz')->default(false);
            $table->boolean('has_tugas')->default(false);
            $table->boolean('has_uts')->default(false);
            $table->boolean('has_uas')->default(false);
            $table->boolean('has_partisipatif')->default(false);
            $table->boolean('has_proyek')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teknik_penilaian');
    }
};
