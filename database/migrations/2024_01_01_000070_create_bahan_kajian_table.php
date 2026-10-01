<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahan_kajian', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama');
            $table->string('referensi', 20)->default('IS2020');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_kajian');
    }
};
