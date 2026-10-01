<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bobot_penilaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->foreignId('cpmk_id')->constrained('cpmk')->cascadeOnDelete();
            $table->unsignedTinyInteger('bobot')->default(0);
            $table->unsignedTinyInteger('bobot_tugas')->default(0);
            $table->unsignedTinyInteger('bobot_uts')->default(0);
            $table->unsignedTinyInteger('bobot_uas')->default(0);
            $table->unsignedTinyInteger('bobot_partisipatif')->default(0);
            $table->unsignedTinyInteger('bobot_proyek')->default(0);
            $table->unsignedTinyInteger('skor_maks')->default(0);
            $table->unsignedTinyInteger('skor_min')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bobot_penilaian');
    }
};
