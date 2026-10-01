<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbkm_bkp', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('no');
            $table->string('bentuk_kegiatan');
            $table->unsignedTinyInteger('sks_reguler')->nullable();
            $table->unsignedTinyInteger('sks_mbkm_maks');
            $table->text('deskripsi');
            $table->text('konversi_mk')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbkm_bkp');
    }
};
