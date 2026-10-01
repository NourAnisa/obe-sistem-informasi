<?php

namespace App\Services;

use App\Models\CourseSchedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ScheduleService — KRS integration & student count sync.
 */
class ScheduleService
{
    /**
     * Sync student_count for one schedule from live mahasiswa_mk count.
     * Uses disetujui status only (confirmed enrollments).
     */
    public function syncStudentCount(int $scheduleId): int
    {
        $schedule = CourseSchedule::findOrFail($scheduleId);

        $count = 0;
        if (Schema::hasTable('mahasiswa_mk')) {
            $count = DB::table('mahasiswa_mk')
                ->where('mata_kuliah_id', $schedule->mata_kuliah_id)
                ->where('status', 'disetujui')
                ->count();
        }

        $schedule->update(['student_count' => $count]);
        return $count;
    }

    /**
     * Sync student_count for ALL schedules in a given academic year.
     * Returns number of records updated.
     */
    public function syncAllStudentCounts(string $academicYear): int
    {
        if (!Schema::hasTable('mahasiswa_mk')) return 0;

        $schedules = CourseSchedule::where('academic_year', $academicYear)->get();
        $updated   = 0;

        foreach ($schedules as $schedule) {
            $count = DB::table('mahasiswa_mk')
                ->where('mata_kuliah_id', $schedule->mata_kuliah_id)
                ->where('status', 'disetujui')
                ->count();

            $schedule->update(['student_count' => $count]);
            $updated++;
        }

        return $updated;
    }

    /**
     * Get student count for a MK (from mahasiswa_mk if exists, else 0).
     */
    public function getStudentCount(int $mataKuliahId, string $status = 'disetujui'): int
    {
        if (!Schema::hasTable('mahasiswa_mk')) return 0;

        return DB::table('mahasiswa_mk')
            ->where('mata_kuliah_id', $mataKuliahId)
            ->where('status', $status)
            ->count();
    }

    /**
     * Summary: scheduled MKs vs unscheduled MKs for a semester/TA.
     */
    public function getSchedulingSummary(int $semester, string $academicYear): array
    {
        $allMks = DB::table('mata_kuliah')
            ->where('semester', $semester)
            ->pluck('id');

        $scheduledMkIds = CourseSchedule::where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->distinct()->pluck('mata_kuliah_id');

        $unscheduled = $allMks->diff($scheduledMkIds);

        return [
            'total_mk'       => $allMks->count(),
            'scheduled'      => $scheduledMkIds->count(),
            'unscheduled'    => $unscheduled->count(),
            'unscheduled_ids' => $unscheduled->values(),
        ];
    }
}
