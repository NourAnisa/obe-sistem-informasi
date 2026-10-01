<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_kuliah_sub_cpmk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliah')->cascadeOnDelete();
            $table->foreignId('sub_cpmk_id')->constrained('sub_cpmk')->cascadeOnDelete();
            $table->unique(['mata_kuliah_id', 'sub_cpmk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah_sub_cpmk');
    }
};
