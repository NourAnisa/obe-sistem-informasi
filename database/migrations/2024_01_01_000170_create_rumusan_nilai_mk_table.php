<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rumusan_nilai_mk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->foreignId('cpl_id')->nullable()->constrained('cpl')->nullOnDelete();
            $table->foreignId('cpmk_id')->constrained('cpmk')->cascadeOnDelete();
            $table->unsignedSmallInteger('skor_maks')->default(100);
            $table->unsignedSmallInteger('skor_min')->default(0);
            $table->unsignedSmallInteger('total_maks')->nullable();
            $table->unsignedSmallInteger('total_min')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rumusan_nilai_mk');
    }
};
