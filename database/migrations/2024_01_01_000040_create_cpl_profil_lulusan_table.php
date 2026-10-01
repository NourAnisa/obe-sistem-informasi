<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cpl_profil_lulusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpl_id')->constrained('cpl')->cascadeOnDelete();
            $table->foreignId('profil_lulusan_id')->constrained('profil_lulusan')->cascadeOnDelete();
            $table->unique(['cpl_id', 'profil_lulusan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cpl_profil_lulusan');
    }
};
