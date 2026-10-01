<?php

namespace App\Http\Controllers;

use App\Services\RoomAnalyticsService;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class RoomAnalyticsController extends Controller
{
    public function __construct(private RoomAnalyticsService $analytics) {}

    /**
     * GET /rooms/dashboard
     * Room Utilization Dashboard (Blade view).
     */
    public function dashboard(Request $request): View
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        try {
            $summary      = $this->analytics->getDashboardSummary($ta);
            $utilization  = $this->analytics->getUtilizationPerRoom($ta);
            $mostUsed     = $this->analytics->getMostUsedRooms($ta, 5);
            $leastUsed    = $this->analytics->getLeastUsedRooms($ta, 5);
            $conflicts    = $this->analytics->getConflictStats($ta);
            $obeCorrel    = $this->analytics->getRoomObeCorrelation($ta);
        } catch (\Throwable $e) {
            Log::error('RoomAnalytics dashboard error', ['error' => $e->getMessage()]);
            $summary     = ['ta' => $ta, 'total_rooms' => 0, 'total_schedules' => 0, 'avg_utilization' => 0, 'conflict_count' => 0, 'by_type' => collect(), 'by_day' => collect()];
            $utilization = collect();
            $mostUsed    = collect();
            $leastUsed   = collect();
            $conflicts   = ['conflict_count' => 0, 'details' => collect()];
            $obeCorrel   = collect();
        }

        return view('rooms.analytics-dashboard', compact(
            'ta',
            'summary',
            'utilization',
            'mostUsed',
            'leastUsed',
            'conflicts',
            'obeCorrel'
        ));
    }

    /**
     * GET /api/rooms/analytics
     * JSON API untuk dashboard atau chart frontend.
     */
    public function utilization(Request $request): JsonResponse
    {
        $ta = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));

        try {
            return response()->json([
                'success'    => true,
                'ta'         => $ta,
                'summary'    => $this->analytics->getDashboardSummary($ta),
                'rooms'      => $this->analytics->getUtilizationPerRoom($ta)->values(),
                'most_used'  => $this->analytics->getMostUsedRooms($ta)->values(),
                'least_used' => $this->analytics->getLeastUsedRooms($ta)->values(),
                'conflicts'  => $this->analytics->getConflictStats($ta),
                'obe_correl' => $this->analytics->getRoomObeCorrelation($ta),
            ]);
        } catch (\Throwable $e) {
            Log::error('RoomAnalytics API error', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/rooms/analytics/correlation
     * Khusus OBE correlation data.
     */
    public function obeCorrelation(Request $request): JsonResponse
    {
        $ta   = $request->input('ta', config('obe.tahun_akademik', '2025/2026'));
        $data = $this->analytics->getRoomObeCorrelation($ta);
        return response()->json(['success' => true, 'ta' => $ta, 'data' => $data]);
    }
}
