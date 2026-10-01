<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rps_pertemuan', function (Blueprint $table) {
            if (!Schema::hasColumn('rps_pertemuan', 'indikator_en')) {
                $table->text('indikator_en')->nullable()->after('indikator');
            }
            if (!Schema::hasColumn('rps_pertemuan', 'teknik_penilaian_en')) {
                $table->text('teknik_penilaian_en')->nullable()->after('teknik_penilaian');
            }
            if (!Schema::hasColumn('rps_pertemuan', 'kreteria_en')) {
                $table->text('kreteria_en')->nullable()->after('kreteria');
            }
            if (!Schema::hasColumn('rps_pertemuan', 'metode_sinkron_en')) {
                $table->text('metode_sinkron_en')->nullable()->after('metode_sinkron');
            }
            if (!Schema::hasColumn('rps_pertemuan', 'metode_asinkron_en')) {
                $table->text('metode_asinkron_en')->nullable()->after('metode_asinkron');
            }
            if (!Schema::hasColumn('rps_pertemuan', 'materi_en')) {
                $table->text('materi_en')->nullable()->after('materi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rps_pertemuan', function (Blueprint $table) {
            $table->dropColumn(['indikator_en', 'teknik_penilaian_en', 'kreteria_en', 'metode_sinkron_en', 'metode_asinkron_en', 'materi_en']);
        });
    }
};
