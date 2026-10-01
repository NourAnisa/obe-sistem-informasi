<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bap_evaluasi_mahasiswa', function (Blueprint $table) {
            if (!Schema::hasColumn('bap_evaluasi_mahasiswa', 'kritik')) {
                $table->text('kritik')->nullable()->after('hadir');
            }
            if (!Schema::hasColumn('bap_evaluasi_mahasiswa', 'saran')) {
                $table->text('saran')->nullable()->after('kritik');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bap_evaluasi_mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['kritik', 'saran']);
        });
    }
};
