<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_sub_cpmk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->foreignId('sub_cpmk_id')->constrained('sub_cpmk')->cascadeOnDelete();

            $table->decimal('tugas', 5, 2)->default(0);
            $table->decimal('uts', 5, 2)->default(0);
            $table->decimal('uas', 5, 2)->default(0);
            $table->decimal('partisipatif', 5, 2)->default(0);
            $table->decimal('proyek', 5, 2)->default(0);

            $table->decimal('nilai_subcpmk', 5, 2)->default(0);

            $table->timestamps();

            $table->unique(['mahasiswa_id', 'sub_cpmk_id']);
            $table->index('sub_cpmk_id'); // speeds up whereIn lookups by sub_cpmk_id
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_sub_cpmk');
    }
};
