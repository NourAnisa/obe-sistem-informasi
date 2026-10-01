<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add room_id FK to bap — keep existing 'ruangan' string for backward compat
        if (Schema::hasTable('bap') && !Schema::hasColumn('bap', 'room_id')) {
            Schema::table('bap', function (Blueprint $table) {
                $table->foreignId('room_id')
                    ->nullable()
                    ->after('ruangan')
                    ->constrained('rooms')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bap') && Schema::hasColumn('bap', 'room_id')) {
            Schema::table('bap', function (Blueprint $table) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            });
        }
    }
};
