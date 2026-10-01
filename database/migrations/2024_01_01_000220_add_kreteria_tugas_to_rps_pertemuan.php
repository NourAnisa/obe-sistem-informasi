<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rps_pertemuan', function (Blueprint $table) {
            $table->text('kreteria')->nullable()->after('teknik_penilaian');
            $table->text('tugas')->nullable()->after('metode_asinkron');
        });
    }

    public function down(): void
    {
        Schema::table('rps_pertemuan', function (Blueprint $table) {
            $table->dropColumn(['kreteria', 'tugas']);
        });
    }
};
