<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpl_sndikti_cpl', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpl_sndikti_id')->constrained('cpl_sndikti')->cascadeOnDelete();
            $table->foreignId('cpl_id')->constrained('cpl')->cascadeOnDelete();
            $table->unique(['cpl_sndikti_id', 'cpl_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpl_sndikti_cpl');
    }
};
