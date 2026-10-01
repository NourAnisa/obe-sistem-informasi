<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rps_pertemuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->onDelete('cascade');
            $table->string('minggu', 10);
            $table->json('sub_cpmk_ids')->nullable();
            $table->string('cpmk_label')->nullable();
            $table->text('indikator')->nullable();
            $table->text('teknik_penilaian')->nullable();
            $table->text('metode_sinkron')->nullable();
            $table->text('metode_asinkron')->nullable();
            $table->text('materi')->nullable();
            $table->decimal('bobot', 5, 2)->default(0);
            $table->string('dosen')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps_pertemuan');
    }
};
