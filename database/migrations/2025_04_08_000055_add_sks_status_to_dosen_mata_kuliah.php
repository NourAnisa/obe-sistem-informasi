<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosen_mata_kuliah', function (Blueprint $table) {
            if (!Schema::hasColumn('dosen_mata_kuliah', 'jumlah_sks')) {
                $table->unsignedTinyInteger('jumlah_sks')->nullable()->after('peran');
            }
            if (!Schema::hasColumn('dosen_mata_kuliah', 'status')) {
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('jumlah_sks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dosen_mata_kuliah', function (Blueprint $table) {
            if (Schema::hasColumn('dosen_mata_kuliah', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('dosen_mata_kuliah', 'jumlah_sks')) {
                $table->dropColumn('jumlah_sks');
            }
        });
    }
};
