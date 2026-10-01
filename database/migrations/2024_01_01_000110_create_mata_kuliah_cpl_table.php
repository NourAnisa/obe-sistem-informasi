<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah_cpl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->foreignId('cpl_id')->constrained('cpl')->cascadeOnDelete();
            $table->unique(['mata_kuliah_id', 'cpl_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah_cpl');
    }
};
