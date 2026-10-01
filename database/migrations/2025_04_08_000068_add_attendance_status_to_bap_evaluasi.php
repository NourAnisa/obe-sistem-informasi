<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bap_evaluasi_mahasiswa', function (Blueprint $table) {
            if (!Schema::hasColumn('bap_evaluasi_mahasiswa', 'status_kehadiran')) {
                $table->enum('status_kehadiran', ['hadir', 'sakit', 'izin', 'tk'])->nullable()->after('saran');
            }
            if (!Schema::hasColumn('bap_evaluasi_mahasiswa', 'bukti_file')) {
                $table->string('bukti_file', 255)->nullable()->after('status_kehadiran');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bap_evaluasi_mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['status_kehadiran', 'bukti_file']);
        });
    }
};
