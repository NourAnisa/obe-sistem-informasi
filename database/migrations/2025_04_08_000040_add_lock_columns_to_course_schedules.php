<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('course_schedules')) return;

        Schema::table('course_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('course_schedules', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('student_count')
                    ->comment('Locked schedules cannot be modified or overwritten by auto-scheduler');
            }
            if (!Schema::hasColumn('course_schedules', 'locked_by')) {
                $table->unsignedBigInteger('locked_by')->nullable()->after('is_locked');
                $table->foreign('locked_by')->references('id')->on('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('course_schedules', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('locked_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('course_schedules')) return;

        Schema::table('course_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('course_schedules', 'locked_by')) {
                $table->dropForeign(['locked_by']);
                $table->dropColumn('locked_by');
            }
            foreach (['is_locked', 'locked_at'] as $col) {
                if (Schema::hasColumn('course_schedules', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
