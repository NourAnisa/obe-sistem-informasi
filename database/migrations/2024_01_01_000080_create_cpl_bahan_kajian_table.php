<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpl_bahan_kajian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpl_id')->constrained('cpl')->cascadeOnDelete();
            $table->foreignId('bahan_kajian_id')->constrained('bahan_kajian')->cascadeOnDelete();
            $table->unique(['cpl_id', 'bahan_kajian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpl_bahan_kajian');
    }
};
