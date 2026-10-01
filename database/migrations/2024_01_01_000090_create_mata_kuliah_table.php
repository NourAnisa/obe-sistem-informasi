<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama');
            $table->unsignedTinyInteger('sks');
            $table->unsignedTinyInteger('sks_teori')->nullable();
            $table->unsignedTinyInteger('sks_praktikum')->nullable();
            $table->unsignedTinyInteger('semester');
            $table->enum('kategori', ['MKF', 'MKPU', 'MKWK', 'MKPP', 'MKP', 'MKKP']);
            $table->string('pjmk');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_mbkm')->default(false);
            $table->boolean('is_wajib')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
