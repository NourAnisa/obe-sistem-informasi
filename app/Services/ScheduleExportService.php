<?php

namespace App\Services;

use App\Models\{CourseSchedule, Room, MataKuliah};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * ScheduleExportService — generate PDF exports of course schedules.
 */
class ScheduleExportService
{
    /**
     * Export all schedules for a semester × academic_year as PDF.
     * Returns a DomPDF Response instance.
     */
    public function exportSemester(int $semester, string $ta): \Illuminate\Http\Response
    {
        $schedules = CourseSchedule::with(['mataKuliah', 'room'])
            ->where('semester', $semester)
            ->where('academic_year', $ta)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $grouped = $schedules->groupBy('day_of_week');
        $dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $grouped  = collect($dayOrder)->mapWithKeys(fn($d) => [$d => $grouped->get($d, collect())])
            ->filter(fn($g) => $g->isNotEmpty());

        $pdf = Pdf::loadView('schedule.exports.semester', [
            'semester'  => $semester,
            'ta'        => $ta,
            'grouped'   => $grouped,
            'total'     => $schedules->count(),
            'generated' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("jadwal-semester-{$semester}-{$ta}.pdf");
    }

    /**
     * Export all schedules for a specific room as PDF.
     */
    public function exportRoom(int $roomId, string $ta): \Illuminate\Http\Response
    {
        $room = Room::findOrFail($roomId);

        $schedules = CourseSchedule::with(['mataKuliah'])
            ->where('room_id', $roomId)
            ->where('academic_year', $ta)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $grouped  = $schedules->groupBy('day_of_week');
        $grid     = collect($dayOrder)->mapWithKeys(fn($d) => [$d => $grouped->get($d, collect())]);

        // Build timetable grid (slot → day → schedule)
        $slots = ['07:30', '09:10', '10:50', '13:00', '14:40', '16:20'];

        $pdf = Pdf::loadView('schedule.exports.room', [
            'room'      => $room,
            'ta'        => $ta,
            'grid'      => $grid,
            'slots'     => $slots,
            'schedules' => $schedules,
            'generated' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = 'jadwal-ruangan-' . str_replace(' ', '-', $room->code) . "-{$ta}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Export all schedules for a specific lecturer (by dosen_id on mata_kuliah).
     * Falls back gracefully if dosen_id column doesn't exist.
     */
    public function exportLecturer(int $dosenId, string $ta): \Illuminate\Http\Response
    {
        $dosenName = 'Dosen #' . $dosenId;

        // Resolve dosen name from users table if possible
        $user = DB::table('users')->find($dosenId);
        if ($user) $dosenName = $user->name;

        $schedules = collect();
        if (DB::getSchemaBuilder()->hasColumn('mata_kuliah', 'dosen_id')) {
            $schedules = CourseSchedule::with(['mataKuliah', 'room'])
                ->whereHas('mataKuliah', fn($q) => $q->where('dosen_id', $dosenId))
                ->where('academic_year', $ta)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
        }

        $pdf = Pdf::loadView('schedule.exports.lecturer', [
            'dosen_id'   => $dosenId,
            'dosen_name' => $dosenName,
            'ta'         => $ta,
            'schedules'  => $schedules,
            'generated'  => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download("jadwal-dosen-{$dosenId}-{$ta}.pdf");
    }

    /**
     * Get raw schedule data for a semester (for calendar / JSON use).
     */
    public function getSemesterData(int $semester, string $ta): Collection
    {
        return CourseSchedule::with(['mataKuliah', 'room'])
            ->where('semester', $semester)
            ->where('academic_year', $ta)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }
}
