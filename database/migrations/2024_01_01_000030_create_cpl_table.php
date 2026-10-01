<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpl', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->text('deskripsi');
            $table->enum('kategori', ['Sikap', 'KU', 'KK', 'Sikap_KU']);
            $table->unsignedSmallInteger('total_skor_maks')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpl');
    }
};
