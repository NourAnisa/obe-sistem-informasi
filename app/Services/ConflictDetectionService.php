<?php

namespace App\Services;

use App\Models\{CourseSchedule, Room};
use Illuminate\Support\{Collection, Facades\DB};

/**
 * ConflictDetectionService — detect scheduling conflicts.
 *
 * Types detected:
 *  1. Room double-booking   — same room, same day, overlapping time
 *  2. Lecturer double-booking — same dosen, same day, overlapping time (if dosen_id on mata_kuliah)
 *  3. Capacity mismatch     — student_count > room.capacity
 */
class ConflictDetectionService
{
    /**
     * Run all detectors and return merged conflict report.
     */
    public function detectConflicts(string $ta): array
    {
        $room     = $this->detectRoomConflicts($ta);
        $lecturer = $this->detectLecturerConflicts($ta);
        $capacity = $this->detectCapacityMismatches($ta);

        $total = $room->count() + $lecturer->count() + $capacity->count();

        return [
            'ta'               => $ta,
            'total_conflicts'  => $total,
            'room_conflicts'   => $room,
            'lecturer_conflicts' => $lecturer,
            'capacity_mismatches' => $capacity,
            'has_conflicts'    => $total > 0,
        ];
    }

    /**
     * Detect room double-bookings:
     * Two different course_schedules assigned to the same room,
     * on the same day, with overlapping time windows.
     */
    public function detectRoomConflicts(string $ta): Collection
    {
        $schedules = CourseSchedule::with(['room', 'mataKuliah'])
            ->where('academic_year', $ta)
            ->whereNotNull('room_id')
            ->orderBy('room_id')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $conflicts = collect();

        $grouped = $schedules->groupBy(fn($s) => $s->room_id . '___' . $s->day_of_week);

        foreach ($grouped as $group) {
            $items = $group->values();
            for ($i = 0; $i < $items->count(); $i++) {
                for ($j = $i + 1; $j < $items->count(); $j++) {
                    $a = $items[$i];
                    $b = $items[$j];
                    if ($this->isOverlapping($a->start_time, $a->end_time, $b->start_time, $b->end_time)) {
                        $conflicts->push([
                            'type'        => 'room_conflict',
                            'severity'    => 'critical',
                            'room_id'     => $a->room_id,
                            'room_name'   => $a->room?->name ?? "Room #{$a->room_id}",
                            'day'         => $a->day_of_week,
                            'schedule_a'  => $this->formatSchedule($a),
                            'schedule_b'  => $this->formatSchedule($b),
                            'description' => "Ruangan {$a->room?->name} dipakai 2 kelas sekaligus pada hari {$a->day_of_week} {$a->start_time}–{$a->end_time} / {$b->start_time}–{$b->end_time}",
                        ]);
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * Detect lecturer double-bookings:
     * Same dosen teaching two different MKs at overlapping times.
     * Requires `mata_kuliah.dosen_id` column.
     */
    public function detectLecturerConflicts(string $ta): Collection
    {
        // Check if mata_kuliah has dosen_id column
        if (!DB::getSchemaBuilder()->hasColumn('mata_kuliah', 'dosen_id')) {
            return collect(); // Column doesn't exist, skip
        }

        $schedules = CourseSchedule::with(['mataKuliah.dosen'])
            ->where('academic_year', $ta)
            ->whereHas('mataKuliah', fn($q) => $q->whereNotNull('dosen_id'))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        $conflicts = collect();

        // Group by dosen_id + day_of_week
        $grouped = $schedules->groupBy(fn($s) => ($s->mataKuliah?->dosen_id ?? 0) . '___' . $s->day_of_week);

        foreach ($grouped as $key => $group) {
            if (str_starts_with($key, '0___')) continue; // No dosen assigned

            $items = $group->values();
            for ($i = 0; $i < $items->count(); $i++) {
                for ($j = $i + 1; $j < $items->count(); $j++) {
                    $a = $items[$i];
                    $b = $items[$j];
                    if ($this->isOverlapping($a->start_time, $a->end_time, $b->start_time, $b->end_time)) {
                        $dosenName = $a->mataKuliah?->dosen?->name ?? "Dosen #{$a->mataKuliah?->dosen_id}";
                        $conflicts->push([
                            'type'        => 'lecturer_conflict',
                            'severity'    => 'critical',
                            'dosen_id'    => $a->mataKuliah?->dosen_id,
                            'dosen_name'  => $dosenName,
                            'day'         => $a->day_of_week,
                            'schedule_a'  => $this->formatSchedule($a),
                            'schedule_b'  => $this->formatSchedule($b),
                            'description' => "{$dosenName} mengajar 2 MK bersamaan: {$a->mataKuliah?->kode} dan {$b->mataKuliah?->kode} hari {$a->day_of_week}",
                        ]);
                    }
                }
            }
        }

        return $conflicts;
    }

    /**
     * Detect capacity mismatches:
     * student_count > room.capacity
     */
    public function detectCapacityMismatches(string $ta): Collection
    {
        $mismatches = CourseSchedule::with(['room', 'mataKuliah'])
            ->where('academic_year', $ta)
            ->whereNotNull('room_id')
            ->whereNotNull('student_count')
            ->where('student_count', '>', 0)
            ->whereHas('room', fn($q) => $q->whereColumn('course_schedules.student_count', '>', 'rooms.capacity'))
            ->get();

        return $mismatches->map(fn($s) => [
            'type'          => 'capacity_mismatch',
            'severity'      => 'warning',
            'schedule_id'   => $s->id,
            'mk_kode'       => $s->mataKuliah?->kode,
            'mk_nama'       => $s->mataKuliah?->nama,
            'room_name'     => $s->room?->name,
            'room_capacity' => $s->room?->capacity,
            'student_count' => $s->student_count,
            'overflow'      => $s->student_count - ($s->room?->capacity ?? 0),
            'day'           => $s->day_of_week,
            'time'          => "{$s->start_time}–{$s->end_time}",
            'description'   => "MK {$s->mataKuliah?->kode}: {$s->student_count} mahasiswa tapi kapasitas ruangan {$s->room?->name} hanya {$s->room?->capacity}",
        ]);
    }

    /**
     * Quick summary: total conflicts by type for a TA.
     */
    public function getConflictSummary(string $ta): array
    {
        $room     = $this->detectRoomConflicts($ta)->count();
        $lecturer = $this->detectLecturerConflicts($ta)->count();
        $capacity = $this->detectCapacityMismatches($ta)->count();

        return [
            'room_conflicts'      => $room,
            'lecturer_conflicts'  => $lecturer,
            'capacity_mismatches' => $capacity,
            'total'               => $room + $lecturer + $capacity,
            'has_critical'        => ($room + $lecturer) > 0,
        ];
    }

    // ──────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────

    private function isOverlapping(string $s1, string $e1, string $s2, string $e2): bool
    {
        return $s1 < $e2 && $e1 > $s2;
    }

    private function formatSchedule(CourseSchedule $s): array
    {
        return [
            'id'         => $s->id,
            'mk_kode'    => $s->mataKuliah?->kode ?? '-',
            'mk_nama'    => $s->mataKuliah?->nama ?? '-',
            'class_name' => $s->class_name,
            'day'        => $s->day_of_week,
            'time'       => "{$s->start_time}–{$s->end_time}",
            'is_locked'  => $s->is_locked,
        ];
    }
}
