<?php

namespace App\Http\Controllers;

use App\Models\CourseSchedule;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, Log};

/**
 * ScheduleLockController — lock/unlock course schedules.
 *
 * Rules enforced here:
 *  - Only kaprodi/admin can lock or unlock schedules.
 *  - Locked schedules are immutable: the auto-scheduler skips them.
 *  - lockAll() / unlockAll() operate on an entire semester × academic_year.
 */
class ScheduleLockController extends Controller
{
    /**
     * POST /schedule/lock/{id}
     * Lock a single schedule.
     */
    public function lock(int $id): JsonResponse
    {
        $schedule = CourseSchedule::with('mataKuliah')->findOrFail($id);

        if ($schedule->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal sudah terkunci.',
            ], 422);
        }

        $schedule->lock(Auth::id());

        Log::info('Schedule locked', ['id' => $id, 'by' => Auth::id()]);

        return response()->json([
            'success'   => true,
            'message'   => 'Jadwal berhasil dikunci.',
            'is_locked' => true,
            'locked_by' => Auth::user()->name ?? Auth::id(),
            'locked_at' => $schedule->locked_at?->format('d/m/Y H:i'),
        ]);
    }

    /**
     * POST /schedule/unlock/{id}
     * Unlock a single schedule.
     */
    public function unlock(int $id): JsonResponse
    {
        $schedule = CourseSchedule::with('mataKuliah')->findOrFail($id);

        if (!$schedule->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak dalam kondisi terkunci.',
            ], 422);
        }

        $schedule->unlock();

        Log::info('Schedule unlocked', ['id' => $id, 'by' => Auth::id()]);

        return response()->json([
            'success'   => true,
            'message'   => 'Jadwal berhasil dibuka kuncinya.',
            'is_locked' => false,
        ]);
    }

    /**
     * POST /schedule/lock-all
     * Lock all schedules for a given semester × academic_year.
     */
    public function lockAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'semester'      => 'required|integer|min:1|max:8',
            'academic_year' => 'required|string',
        ]);

        $updated = CourseSchedule::where('semester', $validated['semester'])
            ->where('academic_year', $validated['academic_year'])
            ->where('is_locked', false)
            ->update([
                'is_locked' => true,
                'locked_by' => Auth::id(),
                'locked_at' => now(),
            ]);

        Log::info('Schedules batch-locked', $validated + ['count' => $updated, 'by' => Auth::id()]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} jadwal berhasil dikunci.",
            'count'   => $updated,
        ]);
    }

    /**
     * POST /schedule/unlock-all
     * Unlock all schedules for a given semester × academic_year.
     */
    public function unlockAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'semester'      => 'required|integer|min:1|max:8',
            'academic_year' => 'required|string',
        ]);

        $updated = CourseSchedule::where('semester', $validated['semester'])
            ->where('academic_year', $validated['academic_year'])
            ->where('is_locked', true)
            ->update([
                'is_locked' => false,
                'locked_by' => null,
                'locked_at' => null,
            ]);

        Log::info('Schedules batch-unlocked', $validated + ['count' => $updated, 'by' => Auth::id()]);

        return response()->json([
            'success' => true,
            'message' => "{$updated} jadwal berhasil dibuka kuncinya.",
            'count'   => $updated,
        ]);
    }

    /**
     * GET /schedule/lock-status
     * Summary of locked vs unlocked for a TA.
     */
    public function status(Request $request): JsonResponse
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        $total    = CourseSchedule::where('academic_year', $ta)->count();
        $locked   = CourseSchedule::where('academic_year', $ta)->where('is_locked', true)->count();
        $unlocked = $total - $locked;

        $bySemester = CourseSchedule::where('academic_year', $ta)
            ->selectRaw('semester, COUNT(*) as total, SUM(is_locked) as locked')
            ->groupBy('semester')
            ->orderBy('semester')
            ->get();

        return response()->json([
            'success'     => true,
            'ta'          => $ta,
            'total'       => $total,
            'locked'      => $locked,
            'unlocked'    => $unlocked,
            'by_semester' => $bySemester,
        ]);
    }
}
