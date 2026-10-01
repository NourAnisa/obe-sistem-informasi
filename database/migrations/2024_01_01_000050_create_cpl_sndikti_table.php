<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpl_sndikti', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();
            $table->enum('kategori', ['Sikap', 'KU', 'KK', 'PP']);
            $table->text('deskripsi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpl_sndikti');
    }
};
