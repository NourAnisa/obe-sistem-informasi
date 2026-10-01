<?php

namespace App\Services;

use App\Models\{Room, CourseSchedule, RoomBlockRule, MataKuliah};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RoomAssignmentService
{
    const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    /**
     * Cari semua ruangan yang tersedia untuk slot tertentu.
     *
     * Kriteria:
     *   1. is_active = true
     *   2. capacity >= $minCapacity
     *   3. Tidak ada block_rule yang overlap
     *   4. Tidak ada course_schedule lain yang overlap di hari + waktu yang sama
     *
     * Hasilnya diurutkan capacity ASC (terkecil dulu — efisien).
     */
    public function findAvailableRooms(
        int    $minCapacity,
        string $day,
        string $startTime,    // "HH:MM"
        string $endTime,      // "HH:MM"
        string $academicYear  = '',
        ?int   $excludeScheduleId = null
    ): Collection {
        // Ruangan aktif dengan kapasitas cukup
        $candidates = Room::where('is_active', true)
            ->where('capacity', '>=', $minCapacity)
            ->orderBy('capacity')
            ->get();

        return $candidates->filter(function (Room $room) use ($day, $startTime, $endTime, $academicYear, $excludeScheduleId) {
            return !$this->isBlocked($room->id, $day, $startTime, $endTime)
                && !$this->hasScheduleConflict($room->id, $day, $startTime, $endTime, $academicYear, $excludeScheduleId);
        })->values();
    }

    /**
     * Cek apakah ruangan diblokir (room_block_rules) untuk slot waktu ini.
     */
    public function isBlocked(int $roomId, string $day, string $startTime, string $endTime): bool
    {
        return RoomBlockRule::where('room_id', $roomId)
            ->where('day_of_week', $day)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->exists();
    }

    /**
     * Cek apakah ada jadwal MK lain di ruangan ini yang konflik waktu.
     */
    public function hasScheduleConflict(
        int    $roomId,
        string $day,
        string $startTime,
        string $endTime,
        string $academicYear  = '',
        ?int   $excludeScheduleId = null
    ): bool {
        $query = CourseSchedule::where('room_id', $roomId)
            ->where('day_of_week', $day)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        if ($excludeScheduleId) {
            $query->where('id', '!=', $excludeScheduleId);
        }

        return $query->exists();
    }

    /**
     * Wrapper untuk backward-compat: cek konflik saja (true = ada konflik).
     */
    public function checkConflict(int $roomId, string $day, string $startTime, string $endTime): bool
    {
        return $this->isBlocked($roomId, $day, $startTime, $endTime)
            || $this->hasScheduleConflict($roomId, $day, $startTime, $endTime);
    }

    /**
     * Auto-assign ruangan terkecil yang tersedia untuk MK tertentu.
     *
     * Menghitung jumlah mahasiswa dari mahasiswa_mk (enrolled + disetujui)
     * jika $studentCount tidak diberikan.
     *
     * Returns Room atau null jika tidak ada yang tersedia.
     */
    public function autoAssign(
        int    $mataKuliahId,
        int    $studentCount,
        string $day,
        string $startTime,
        string $endTime,
        string $academicYear
    ): ?Room {
        // Jika student count tidak diberikan, hitung dari enrollment
        if ($studentCount <= 0) {
            $studentCount = DB::table('mahasiswa_mk')
                ->where('mata_kuliah_id', $mataKuliahId)
                ->where('status', 'disetujui')
                ->count();
        }

        // +10% buffer kapasitas
        $minCapacity = (int) ceil($studentCount * 1.0);

        $available = $this->findAvailableRooms($minCapacity, $day, $startTime, $endTime, $academicYear);

        return $available->first(); // sudah urut capacity ASC → terkecil dulu
    }

    /**
     * Buat atau update CourseSchedule dengan auto-assign ruangan.
     * Returns ['schedule' => CourseSchedule, 'room' => Room|null, 'conflict' => bool]
     */
    public function createSchedule(array $data): array
    {
        $room = null;
        $conflict = false;

        // Jika room_id diberikan manual, cek konflik
        if (!empty($data['room_id'])) {
            $conflict = $this->checkConflict(
                $data['room_id'],
                $data['day_of_week'],
                $data['start_time'],
                $data['end_time']
            );
            $room = Room::find($data['room_id']);
        }

        $schedule = CourseSchedule::updateOrCreate(
            [
                'mata_kuliah_id' => $data['mata_kuliah_id'],
                'class_name'     => $data['class_name'] ?? null,
                'day_of_week'    => $data['day_of_week'],
                'academic_year'  => $data['academic_year'],
            ],
            [
                'room_id'     => $data['room_id'] ?? null,
                'start_time'  => $data['start_time'],
                'end_time'    => $data['end_time'],
                'semester'    => $data['semester'],
            ]
        );

        return compact('schedule', 'room', 'conflict');
    }

    /**
     * Hitung utilization rate ruangan: jam terpakai / jam operasional × 100.
     *
     * @param  int    $roomId
     * @param  string $academicYear
     * @param  int    $operationalHoursPerDay  Jam operasional per hari (default 10 jam)
     * @param  int    $daysPerWeek             Hari kerja per minggu (default 5)
     * @param  int    $weeksPerSemester        Minggu per semester (default 16)
     */
    public function getRoomUtilization(
        int    $roomId,
        string $academicYear,
        int    $operationalHoursPerDay = 10,
        int    $daysPerWeek            = 5,
        int    $weeksPerSemester       = 16
    ): array {
        $schedules = CourseSchedule::where('room_id', $roomId)
            ->where('academic_year', $academicYear)
            ->get();

        $totalScheduledMinutes = 0;
        foreach ($schedules as $s) {
            $totalScheduledMinutes += $s->duration_minutes * $weeksPerSemester;
        }

        $totalOperationalMinutes = $operationalHoursPerDay * 60 * $daysPerWeek * $weeksPerSemester;
        $utilizationPct = $totalOperationalMinutes > 0
            ? round($totalScheduledMinutes / $totalOperationalMinutes * 100, 2)
            : 0;

        return [
            'room_id'                  => $roomId,
            'academic_year'            => $academicYear,
            'scheduled_hours'          => round($totalScheduledMinutes / 60, 1),
            'operational_hours'        => round($totalOperationalMinutes / 60, 1),
            'utilization_pct'          => $utilizationPct,
            'schedule_count'           => $schedules->count(),
        ];
    }

    /**
     * Summary utilization semua ruangan aktif untuk satu tahun akademik.
     */
    public function getAllRoomsUtilization(string $academicYear): Collection
    {
        return Room::where('is_active', true)
            ->orderBy('building')
            ->orderBy('code')
            ->get()
            ->map(fn(Room $r) => array_merge(
                ['room' => $r],
                $this->getRoomUtilization($r->id, $academicYear)
            ));
    }
}
