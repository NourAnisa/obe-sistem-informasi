<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rps_detail', function (Blueprint $table) {
            $table->json('blueprint_overrides')->nullable()->after('catatan_blueprint');
        });
    }

    public function down(): void
    {
        Schema::table('rps_detail', function (Blueprint $table) {
            $table->dropColumn('blueprint_overrides');
        });
    }
};
