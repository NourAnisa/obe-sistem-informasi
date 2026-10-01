<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('course_schedules') && !Schema::hasColumn('course_schedules', 'student_count')) {
            Schema::table('course_schedules', function (Blueprint $table) {
                $table->unsignedSmallInteger('student_count')->nullable()->after('academic_year')
                    ->comment('Cached from COUNT(mahasiswa_mk) — refresh via ScheduleService::syncStudentCount');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('course_schedules') && Schema::hasColumn('course_schedules', 'student_count')) {
            Schema::table('course_schedules', function (Blueprint $table) {
                $table->dropColumn('student_count');
            });
        }
    }
};
