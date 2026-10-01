<?php

namespace App\Http\Controllers;

use App\Models\CourseSchedule;
use App\Services\{AutoSchedulerService, ConflictDetectionService, ScheduleExportService, ScheduleService};
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    public function __construct(
        private AutoSchedulerService    $autoScheduler,
        private ScheduleService         $scheduleService,
        private ConflictDetectionService $conflictService,
        private ScheduleExportService   $exportService,
    ) {}

    /**
     * POST /schedule/auto-generate
     * Generate jadwal otomatis untuk semester + TA tertentu.
     */
    public function autoGenerate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'semester'        => 'required|integer|min:1|max:8',
            'academic_year'   => 'required|string',
            'overwrite'       => 'boolean',
        ]);

        try {
            $result = $this->autoScheduler->generateSchedule(
                (int) $validated['semester'],
                $validated['academic_year'],
                (bool) ($validated['overwrite'] ?? false)
            );

            return response()->json([
                'success' => true,
                'message' => "Jadwal semester {$result['semester']} TA {$result['ta']} berhasil di-generate. "
                    . "{$result['assigned']} MK dijadwalkan, {$result['skipped']} dilewati.",
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('AutoScheduler error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Gagal generate jadwal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * POST /schedule/auto-generate/preview
     * Preview jadwal tanpa menyimpan ke DB.
     */
    public function previewSchedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'semester'      => 'required|integer|min:1|max:8',
            'academic_year' => 'required|string',
        ]);

        try {
            $preview = $this->autoScheduler->previewSchedule(
                (int) $validated['semester'],
                $validated['academic_year']
            );

            return response()->json([
                'success' => true,
                'count'   => count($preview),
                'data'    => $preview,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /schedule/sync-student-count
     * Sync student_count dari mahasiswa_mk ke course_schedules.
     */
    public function syncStudentCount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year' => 'required|string',
        ]);

        try {
            $updated = $this->scheduleService->syncAllStudentCounts($validated['academic_year']);

            return response()->json([
                'success' => true,
                'message' => "{$updated} jadwal berhasil disinkronisasi jumlah mahasiswanya.",
                'updated' => $updated,
            ]);
        } catch (\Throwable $e) {
            Log::error('syncStudentCount error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /schedule/sync-student-count/{scheduleId}
     * Sync satu jadwal saja.
     */
    public function syncOne(int $scheduleId): JsonResponse
    {
        try {
            $count = $this->scheduleService->syncStudentCount($scheduleId);
            return response()->json(['success' => true, 'student_count' => $count]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /schedule/summary
     * Summary penjadwalan per semester/TA.
     */
    public function summary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'semester'      => 'required|integer|min:1|max:8',
            'academic_year' => 'required|string',
        ]);

        $summary = $this->scheduleService->getSchedulingSummary(
            (int) $validated['semester'],
            $validated['academic_year']
        );

        return response()->json(['success' => true, 'data' => $summary]);
    }

    // ──────────────────────────────────────────────────────────────
    // Calendar API
    // ──────────────────────────────────────────────────────────────

    /**
     * GET /api/schedules/calendar
     * Return FullCalendar-compatible event JSON for a given TA + optional semester.
     *
     * Query params: ta (string), semester (int, optional)
     *
     * Example response:
     * [
     *   {
     *     "id": 1,
     *     "title": "Basis Data (SI101)",
     *     "start": "2025-09-01T07:30:00",
     *     "end": "2025-09-01T09:10:00",
     *     "extendedProps": { "room": "R101", "class_name": "SI-A", "is_locked": false }
     *   }, ...
     * ]
     *
     * Days are mapped to the first Monday of the academic year for visualization.
     */
    public function calendar(Request $request): JsonResponse
    {
        $ta       = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $semester = $request->integer('semester', 0); // 0 = all

        try {
            $query = CourseSchedule::with(['mataKuliah', 'room'])
                ->where('academic_year', $ta);

            if ($semester > 0) {
                $query->where('semester', $semester);
            }

            $schedules = $query->get();

            // Map day names → ISO weekday offset (Mon=0 … Sun=6)
            $dayOffset = [
                'Senin'   => 0,
                'Selasa'  => 1,
                'Rabu'    => 2,
                'Kamis'   => 3,
                'Jumat'   => 4,
                'Sabtu'   => 5,
                'Minggu'  => 6,
            ];

            // Use first week of the TA's first semester as anchor
            // e.g. "2025/2026" → 2025-09-01 (week starting Monday)
            $year       = (int) substr($ta, 0, 4);
            $anchorDate = \Carbon\Carbon::create($year, 9, 1)->startOfWeek(); // First Monday of Sep

            $events = $schedules->map(function (CourseSchedule $s) use ($dayOffset, $anchorDate) {
                $offset = $dayOffset[$s->day_of_week] ?? 0;
                $date   = $anchorDate->copy()->addDays($offset)->format('Y-m-d');

                return [
                    'id'    => $s->id,
                    'title' => ($s->mataKuliah?->nama ?? 'MK') . ' (' . ($s->mataKuliah?->kode ?? '-') . ')',
                    'start' => $date . 'T' . $s->start_time . ':00',
                    'end'   => $date . 'T' . $s->end_time . ':00',
                    'color' => $s->is_locked ? '#c53030' : ($s->room_id ? '#2b6cb0' : '#718096'),
                    'extendedProps' => [
                        'room'          => $s->room?->name ?? '-',
                        'room_code'     => $s->room?->code ?? '-',
                        'class_name'    => $s->class_name ?? '-',
                        'semester'      => $s->semester,
                        'sks'           => $s->mataKuliah?->sks ?? 0,
                        'student_count' => $s->student_count,
                        'is_locked'     => $s->is_locked,
                        'day_of_week'   => $s->day_of_week,
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'ta'      => $ta,
                'count'   => $events->count(),
                'events'  => $events->values(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Calendar API error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Conflict Detection
    // ──────────────────────────────────────────────────────────────

    /**
     * GET /schedule/conflicts
     * Detect all scheduling conflicts for a TA.
     */
    public function detectConflicts(Request $request): JsonResponse
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        try {
            $result = $this->conflictService->detectConflicts($ta);

            return response()->json([
                'success' => true,
                'data'    => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Conflict detection error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // PDF Export
    // ──────────────────────────────────────────────────────────────

    /**
     * GET /schedule/export/semester?semester=X&ta=Y
     */
    public function exportSemester(Request $request): \Illuminate\Http\Response
    {
        $validated = $request->validate([
            'semester'      => 'required|integer|min:1|max:8',
            'academic_year' => 'required|string',
        ]);

        return $this->exportService->exportSemester(
            (int) $validated['semester'],
            $validated['academic_year']
        );
    }

    /**
     * GET /schedule/export/room/{id}?ta=Y
     */
    public function exportRoom(Request $request, int $roomId): \Illuminate\Http\Response
    {
        $ta = $request->input('academic_year', config('obe.tahun_akademik', '2025/2026'));
        return $this->exportService->exportRoom($roomId, $ta);
    }

    /**
     * GET /schedule/export/lecturer/{id}?ta=Y
     */
    public function exportLecturer(Request $request, int $dosenId): \Illuminate\Http\Response
    {
        $ta = $request->input('academic_year', config('obe.tahun_akademik', '2025/2026'));
        return $this->exportService->exportLecturer($dosenId, $ta);
    }
}
