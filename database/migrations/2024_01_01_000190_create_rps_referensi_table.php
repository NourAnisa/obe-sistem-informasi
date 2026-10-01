<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rps_referensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->string('judul');
            $table->string('penulis')->nullable();
            $table->string('tahun', 4)->nullable();
            $table->string('penerbit')->nullable();
            $table->string('kota')->nullable();
            $table->string('url')->nullable();
            $table->enum('jenis', ['utama', 'pendukung'])->default('utama');
            $table->unsignedTinyInteger('urutan')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rps_referensi');
    }
};
