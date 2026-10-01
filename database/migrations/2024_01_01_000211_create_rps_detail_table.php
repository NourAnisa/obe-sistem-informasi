<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rps_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->unique()->constrained('mata_kuliah')->onDelete('cascade');
            $table->text('tautan_kelas_daring')->nullable();
            $table->json('ketentuan_tambahan')->nullable();
            $table->json('jadwal_kuliah')->nullable();
            $table->json('dosen_pengampu')->nullable();
            $table->text('catatan_blueprint')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps_detail');
    }
};
