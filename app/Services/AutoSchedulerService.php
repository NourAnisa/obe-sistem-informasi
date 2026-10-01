<?php

namespace App\Services;

use App\Models\{CourseSchedule, MataKuliah};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AutoSchedulerService — generate jadwal kuliah otomatis per semester.
 *
 * Algoritma:
 *  1. Iterasi semua MK di semester target
 *  2. Hitung student_count dari mahasiswa_mk
 *  3. Tentukan durasi slot dari SKS MK (1 SKS = 50 menit + 10 menit buffer)
 *  4. Cari slot hari/jam kosong dalam TIME_SLOTS yang tersedia
 *  5. Panggil RoomAssignmentService::autoAssign() untuk ruangan terkecil yang pas
 *  6. Simpan ke course_schedules
 */
class AutoSchedulerService
{
    /**
     * Slot waktu standar (start => end per durasi).
     * Key = durasi dalam SKS, Value = array pasangan [start, end]
     */
    const TIME_SLOTS = [
        '07:30' => '09:10',  // 100 menit = 2 SKS
        '09:10' => '10:50',
        '10:50' => '12:30',
        '13:00' => '14:40',
        '14:40' => '16:20',
        '16:20' => '18:00',
    ];

    const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    // Operasional: 07:00–18:00 = 11 jam per hari
    const OPERATIONAL_MINUTES_PER_DAY = 660;

    public function __construct(private RoomAssignmentService $roomSvc) {}

    /**
     * Generate jadwal untuk satu semester pada TA tertentu.
     *
     * @param  int    $semester   Semester MK (1–8)
     * @param  string $ta         Tahun akademik, e.g. "2025/2026"
     * @param  bool   $overwrite  Jika true, hapus jadwal yang ada sebelum generate ulang
     * @return array  ['assigned'=>int, 'skipped'=>int, 'errors'=>array]
     */
    public function generateSchedule(int $semester, string $ta, bool $overwrite = false): array
    {
        if ($overwrite) {
            // Only delete UNLOCKED schedules — preserve locked ones
            CourseSchedule::where('semester', $semester)
                ->where('academic_year', $ta)
                ->where('is_locked', false)
                ->delete();
        }

        $mataKuliahs = MataKuliah::where('semester', $semester)
            ->orderBy('sks', 'desc') // MK SKS besar diprioritaskan dulu
            ->orderBy('kode')
            ->get();

        $assigned = 0;
        $skipped  = 0;
        $errors   = [];

        // Track slot yang sudah dipakai di hari ini (room-agnostic) untuk distribusi merata
        $usedSlots = []; // ['Senin_07:30' => true, ...]

        foreach ($mataKuliahs as $mk) {
            // Skip jika sudah ada jadwal yang LOCKED (cannot overwrite)
            $existingLocked = CourseSchedule::where('mata_kuliah_id', $mk->id)
                ->where('academic_year', $ta)
                ->where('is_locked', true)
                ->exists();
            if ($existingLocked) {
                $skipped++;
                $errors[] = "MK {$mk->kode}: jadwal terkunci, dilewati";
                continue;
            }

            // Skip jika sudah ada jadwal (unlocked) dan tidak overwrite
            if (!$overwrite && CourseSchedule::where('mata_kuliah_id', $mk->id)
                ->where('academic_year', $ta)->exists()
            ) {
                $skipped++;
                continue;
            }

            $studentCount = DB::table('mahasiswa_mk')
                ->where('mata_kuliah_id', $mk->id)
                ->where('status', 'disetujui')
                ->count();

            // Cari slot hari+waktu yang tersedia
            $slot = $this->findAvailableSlot($mk->id, $mk->sks, $ta, $usedSlots);

            if (!$slot) {
                $errors[] = "MK {$mk->kode} ({$mk->nama}): tidak ada slot waktu tersedia";
                $skipped++;
                continue;
            }

            [$day, $startTime, $endTime] = $slot;

            // Cari ruangan
            $room = $this->roomSvc->autoAssign(
                $mk->id,
                max($studentCount, 1),
                $day,
                $startTime,
                $endTime,
                $ta
            );

            CourseSchedule::updateOrCreate(
                [
                    'mata_kuliah_id' => $mk->id,
                    'academic_year'  => $ta,
                    'class_name'     => null,
                ],
                [
                    'room_id'       => $room?->id,
                    'day_of_week'   => $day,
                    'start_time'    => $startTime,
                    'end_time'      => $endTime,
                    'semester'      => $semester,
                    'student_count' => $studentCount,
                ]
            );

            // Mark slot as used (untuk distribusi antar hari)
            $usedSlots["{$day}_{$startTime}"] = ($usedSlots["{$day}_{$startTime}"] ?? 0) + 1;

            if (!$room) {
                $errors[] = "MK {$mk->kode}: jadwal dibuat tanpa ruangan (tidak ada ruangan tersedia)";
            }

            $assigned++;
        }

        return [
            'semester'  => $semester,
            'ta'        => $ta,
            'assigned'  => $assigned,
            'skipped'   => $skipped,
            'errors'    => $errors,
        ];
    }

    /**
     * Cari slot (day, startTime, endTime) yang paling sedikit dipakai
     * untuk MK ini, sesuai SKS.
     *
     * Strategi: distribusi round-robin per hari, prioritaskan hari dengan
     * slot paling sedikit dipakai.
     */
    private function findAvailableSlot(int $mkId, int $sks, string $ta, array &$usedSlots): ?array
    {
        $slotPairs = array_keys(self::TIME_SLOTS);
        // Ambil slot yang sesuai SKS (1 SKS = 50 mnt; setiap slot = 100 mnt = 2 SKS)
        // Untuk MK ≥ 3 SKS, gunakan 2 slot berurutan (tapi disederhanakan: 1 slot per MK)

        // Urutkan hari berdasar yang paling sedikit jadwal (distribusi merata)
        $dayLoad = [];
        foreach (self::DAYS as $d) {
            $dayLoad[$d] = CourseSchedule::where('academic_year', $ta)
                ->where('day_of_week', $d)
                ->count();
        }
        asort($dayLoad);

        foreach (array_keys($dayLoad) as $day) {
            foreach ($slotPairs as $startTime) {
                $endTime = self::TIME_SLOTS[$startTime];

                // Cek apakah MK ini sudah punya jadwal di slot ini
                $alreadyScheduled = CourseSchedule::where('mata_kuliah_id', $mkId)
                    ->where('academic_year', $ta)
                    ->where('day_of_week', $day)
                    ->where('start_time', $startTime)
                    ->exists();

                if ($alreadyScheduled) continue;

                // Slot valid
                return [$day, $startTime, $endTime];
            }
        }

        return null;
    }

    /**
     * Preview: generate tanpa menyimpan ke DB.
     * Returns array of proposed schedules.
     */
    public function previewSchedule(int $semester, string $ta): array
    {
        $mataKuliahs = MataKuliah::where('semester', $semester)
            ->orderBy('sks', 'desc')->orderBy('kode')->get();

        $preview  = [];
        $dayLoad  = array_fill_keys(self::DAYS, 0);

        foreach ($mataKuliahs as $mk) {
            $studentCount = DB::table('mahasiswa_mk')
                ->where('mata_kuliah_id', $mk->id)
                ->where('status', 'disetujui')
                ->count();

            // Pilih hari dengan beban terendah
            asort($dayLoad);
            $day = array_key_first($dayLoad);

            // Pilih slot pertama yang tersedia di hari itu
            $usedInDay = collect($preview)
                ->where('day_of_week', $day)
                ->pluck('start_time')->toArray();

            $startTime = null;
            $endTime   = null;
            foreach (self::TIME_SLOTS as $s => $e) {
                if (!in_array($s, $usedInDay)) {
                    $startTime = $s;
                    $endTime   = $e;
                    break;
                }
            }

            if (!$startTime) {
                $preview[] = ['mk' => $mk->kode, 'status' => 'no_slot'];
                continue;
            }

            $room = $this->roomSvc->autoAssign($mk->id, max($studentCount, 1), $day, $startTime, $endTime, $ta);

            $preview[] = [
                'mata_kuliah_id' => $mk->id,
                'mk_kode'        => $mk->kode,
                'mk_nama'        => $mk->nama,
                'sks'            => $mk->sks,
                'student_count'  => $studentCount,
                'day_of_week'    => $day,
                'start_time'     => $startTime,
                'end_time'       => $endTime,
                'room_id'        => $room?->id,
                'room_code'      => $room?->code,
                'room_name'      => $room?->name,
                'room_capacity'  => $room?->capacity,
                'status'         => $room ? 'ok' : 'no_room',
            ];

            $dayLoad[$day]++;
        }

        return $preview;
    }
}
