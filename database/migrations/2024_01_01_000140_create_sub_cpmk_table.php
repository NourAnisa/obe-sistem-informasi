<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_cpmk', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 25)->unique();
            $table->text('deskripsi');
            $table->foreignId('cpmk_id')->constrained('cpmk')->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_cpmk');
    }
};
