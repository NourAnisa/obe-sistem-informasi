<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index on mahasiswas.dosen_pa_id — used heavily in view composer PA count query
        if (Schema::hasTable('mahasiswas') && Schema::hasColumn('mahasiswas', 'dosen_pa_id')) {
            Schema::table('mahasiswas', function (Blueprint $table) {
                $table->index('dosen_pa_id', 'mahasiswas_dosen_pa_id_idx');
            });
        }

        // Indexes on course_schedules — used in room conflict detection and schedule queries
        if (Schema::hasTable('course_schedules')) {
            Schema::table('course_schedules', function (Blueprint $table) {
                if (Schema::hasColumn('course_schedules', 'room_id')) {
                    $table->index('room_id', 'course_schedules_room_id_idx');
                }
                if (Schema::hasColumn('course_schedules', 'academic_year')) {
                    $table->index('academic_year', 'course_schedules_academic_year_idx');
                }
                if (Schema::hasColumn('course_schedules', 'locked_by')) {
                    $table->index('locked_by', 'course_schedules_locked_by_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mahasiswas')) {
            Schema::table('mahasiswas', function (Blueprint $table) {
                $table->dropIndex('mahasiswas_dosen_pa_id_idx');
            });
        }

        if (Schema::hasTable('course_schedules')) {
            Schema::table('course_schedules', function (Blueprint $table) {
                $table->dropIndex('course_schedules_room_id_idx');
                $table->dropIndex('course_schedules_academic_year_idx');
                $table->dropIndex('course_schedules_locked_by_idx');
            });
        }
    }
};
