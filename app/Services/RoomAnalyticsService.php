<?php

namespace App\Services;

use App\Models\{Room, CourseSchedule};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * RoomAnalyticsService — statistik utilisasi ruangan dan korelasi OBE.
 *
 * Asumsi operasional:
 *   - 5 hari kerja (Senin–Jumat)
 *   - 11 jam per hari (07:00–18:00)
 *   - 16 minggu per semester
 */
class RoomAnalyticsService
{
    // Jam operasional per minggu per ruangan (menit): 5 hari × 11 jam × 60 menit = 3300
    const OPERATIONAL_MINUTES_PER_WEEK = 3300;
    const WEEKS_PER_SEMESTER           = 16;
    const OPERATIONAL_MINUTES_SEMESTER = self::OPERATIONAL_MINUTES_PER_WEEK * self::WEEKS_PER_SEMESTER; // 52800

    /**
     * Utilisasi setiap ruangan (%) untuk TA tertentu.
     *
     * @param  string $ta  e.g. "2025/2026"
     * @return Collection  [{room_id, room_code, room_name, scheduled_minutes, utilization_pct, schedule_count}, ...]
     */
    public function getUtilizationPerRoom(string $ta): Collection
    {
        $rooms = Room::where('is_active', true)->get();

        return $rooms->map(function (Room $room) use ($ta) {
            $schedules = CourseSchedule::where('room_id', $room->id)
                ->where('academic_year', $ta)
                ->get(['start_time', 'end_time']);

            $totalMinutes = $schedules->sum(fn($s) => $this->diffMinutes($s->start_time, $s->end_time));
            $totalScheduled = $totalMinutes * self::WEEKS_PER_SEMESTER;
            $utilizationPct = round($totalScheduled / self::OPERATIONAL_MINUTES_SEMESTER * 100, 2);

            return [
                'room_id'           => $room->id,
                'room_code'         => $room->code,
                'room_name'         => $room->name,
                'building'          => $room->building,
                'capacity'          => $room->capacity,
                'type'              => $room->type,
                'schedule_count'    => $schedules->count(),
                'scheduled_minutes_per_week' => $totalMinutes,
                'utilization_pct'   => $utilizationPct,
            ];
        });
    }

    /**
     * Ruangan paling banyak digunakan.
     */
    public function getMostUsedRooms(string $ta, int $limit = 5): Collection
    {
        return $this->getUtilizationPerRoom($ta)
            ->sortByDesc('utilization_pct')
            ->take($limit)
            ->values();
    }

    /**
     * Ruangan paling sedikit digunakan (aktif).
     */
    public function getLeastUsedRooms(string $ta, int $limit = 5): Collection
    {
        return $this->getUtilizationPerRoom($ta)
            ->sortBy('utilization_pct')
            ->take($limit)
            ->values();
    }

    /**
     * Statistik konflik jadwal (double-booking).
     * Mendeteksi 2 jadwal di ruangan yang sama, hari yang sama, dengan waktu overlap.
     *
     * @return array  ['conflicts' => int, 'details' => Collection]
     */
    public function getConflictStats(string $ta): array
    {
        $schedules = CourseSchedule::where('academic_year', $ta)
            ->whereNotNull('room_id')
            ->orderBy('room_id')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get(['id', 'room_id', 'day_of_week', 'start_time', 'end_time', 'mata_kuliah_id', 'class_name']);

        $conflicts = [];

        // Group by room_id + day_of_week, lalu cek overlap
        $grouped = $schedules->groupBy(fn($s) => $s->room_id . '_' . $s->day_of_week);

        foreach ($grouped as $group) {
            $items = $group->values();
            for ($i = 0; $i < $items->count(); $i++) {
                for ($j = $i + 1; $j < $items->count(); $j++) {
                    $a = $items[$i];
                    $b = $items[$j];
                    if ($this->isOverlapping($a->start_time, $a->end_time, $b->start_time, $b->end_time)) {
                        $conflicts[] = [
                            'room_id'   => $a->room_id,
                            'day'       => $a->day_of_week,
                            'schedule_a' => ['id' => $a->id, 'mk_id' => $a->mata_kuliah_id, 'class' => $a->class_name, 'time' => "{$a->start_time}–{$a->end_time}"],
                            'schedule_b' => ['id' => $b->id, 'mk_id' => $b->mata_kuliah_id, 'class' => $b->class_name, 'time' => "{$b->start_time}–{$b->end_time}"],
                        ];
                    }
                }
            }
        }

        return [
            'conflict_count' => count($conflicts),
            'details'        => collect($conflicts),
        ];
    }

    /**
     * Summary dashboard: total rooms, total schedules, avg utilization, conflicts.
     */
    public function getDashboardSummary(string $ta): array
    {
        $utilization   = $this->getUtilizationPerRoom($ta);
        $conflictStats = $this->getConflictStats($ta);
        $totalSchedules = CourseSchedule::where('academic_year', $ta)->count();

        return [
            'ta'               => $ta,
            'total_rooms'      => Room::where('is_active', true)->count(),
            'total_schedules'  => $totalSchedules,
            'avg_utilization'  => round($utilization->avg('utilization_pct'), 2),
            'max_utilization'  => round($utilization->max('utilization_pct'), 2),
            'conflict_count'   => $conflictStats['conflict_count'],
            'by_type'          => $this->getScheduleCountByRoomType($ta),
            'by_day'           => $this->getScheduleCountByDay($ta),
        ];
    }

    /**
     * OBE Correlation Hook:
     * rooms → course_schedules → mata_kuliah → cpmk_achievement → cpl_achievement
     *
     * Returns per-room: avg CPL achievement of courses held in that room.
     * Useful to correlate room conditions with learning outcomes.
     */
    public function getRoomObeCorrelation(string $ta): Collection
    {
        if (!DB::getSchemaBuilder()->hasTable('cpl_achievement')) {
            return collect();
        }

        $rows = DB::table('rooms as r')
            ->join('course_schedules as cs', 'cs.room_id', '=', 'r.id')
            ->join('mata_kuliah as mk', 'mk.id', '=', 'cs.mata_kuliah_id')
            ->join('cpmk', 'cpmk.mata_kuliah_id', '=', 'mk.id')
            ->join('cpmk_achievement as ca', 'ca.cpmk_id', '=', 'cpmk.id')
            ->leftJoin('cpl_achievement as cpa', function ($j) {
                $j->on('cpa.mahasiswa_id', '=', 'ca.mahasiswa_id')
                    ->on('cpa.cpl_id', '=', 'cpmk.cpl_id');
            })
            ->where('cs.academic_year', $ta)
            ->where('r.is_active', true)
            ->select([
                'r.id as room_id',
                'r.code as room_code',
                'r.name as room_name',
                'r.capacity',
                DB::raw('COUNT(DISTINCT mk.id) as mk_count'),
                DB::raw('COUNT(DISTINCT ca.mahasiswa_id) as student_count'),
                DB::raw('AVG(ca.score) as avg_cpmk_score'),
                DB::raw('AVG(cpa.score) as avg_cpl_score'),
                DB::raw('AVG(CASE WHEN ca.achieved = 1 THEN 100 ELSE 0 END) as cpmk_achievement_pct'),
            ])
            ->groupBy('r.id', 'r.code', 'r.name', 'r.capacity')
            ->orderByDesc('avg_cpl_score')
            ->get();

        return $rows;
    }

    /**
     * Jumlah jadwal per tipe ruangan.
     */
    public function getScheduleCountByRoomType(string $ta): Collection
    {
        return DB::table('course_schedules as cs')
            ->join('rooms as r', 'r.id', '=', 'cs.room_id')
            ->where('cs.academic_year', $ta)
            ->select('r.type', DB::raw('COUNT(*) as count'))
            ->groupBy('r.type')
            ->get();
    }

    /**
     * Jumlah jadwal per hari.
     */
    public function getScheduleCountByDay(string $ta): Collection
    {
        return DB::table('course_schedules')
            ->where('academic_year', $ta)
            ->select('day_of_week', DB::raw('COUNT(*) as count'))
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get();
    }

    // ─────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────

    private function diffMinutes(string $start, string $end): int
    {
        [$sh, $sm] = array_map('intval', explode(':', $start));
        [$eh, $em] = array_map('intval', explode(':', $end));
        return ($eh * 60 + $em) - ($sh * 60 + $sm);
    }

    private function isOverlapping(string $s1, string $e1, string $s2, string $e2): bool
    {
        // Overlap jika start1 < end2 AND end1 > start2
        return $s1 < $e2 && $e1 > $s2;
    }
}
