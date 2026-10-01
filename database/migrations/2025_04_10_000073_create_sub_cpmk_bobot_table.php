<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_cpmk_bobot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_cpmk_id')->constrained('sub_cpmk')->cascadeOnDelete();
            $table->tinyInteger('bobot_tugas')->default(0);
            $table->tinyInteger('bobot_uts')->default(0);
            $table->tinyInteger('bobot_uas')->default(0);
            $table->tinyInteger('bobot_partisipatif')->default(0);
            $table->tinyInteger('bobot_proyek')->default(0);
            $table->timestamps();
            $table->unique('sub_cpmk_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_cpmk_bobot');
    }
};
