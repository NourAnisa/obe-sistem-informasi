<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cpl', function (Blueprint $table) {
            $table->text('deskripsi_en')->nullable()->after('deskripsi');
        });
        Schema::table('cpmk', function (Blueprint $table) {
            $table->text('deskripsi_en')->nullable()->after('deskripsi');
        });
        Schema::table('sub_cpmk', function (Blueprint $table) {
            $table->text('deskripsi_en')->nullable()->after('deskripsi');
        });
    }

    public function down(): void
    {
        Schema::table('cpl', function (Blueprint $table) {
            $table->dropColumn('deskripsi_en');
        });
        Schema::table('cpmk', function (Blueprint $table) {
            $table->dropColumn('deskripsi_en');
        });
        Schema::table('sub_cpmk', function (Blueprint $table) {
            $table->dropColumn('deskripsi_en');
        });
    }
};
