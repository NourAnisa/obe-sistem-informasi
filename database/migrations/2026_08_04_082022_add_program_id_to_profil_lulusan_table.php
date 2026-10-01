<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_lulusan', function (Blueprint $table) {
            $table->foreignId('program_id')->nullable()->after('id')->constrained('programs')->nullOnDelete();
        });

        // Set semua data existing ke program Sistem Informasi
        $siId = DB::table('programs')->where('nama', 'like', '%Sistem Informasi%')->value('id');
        if ($siId) {
            DB::table('profil_lulusan')->update(['program_id' => $siId]);
        }
    }

    public function down(): void
    {
        Schema::table('profil_lulusan', function (Blueprint $table) {
            $table->dropForeign(['program_id']);
            $table->dropColumn('program_id');
        });
    }
};
