<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class SystemReportController extends Controller
{
    /**
     * Get aggregated system usage statistics.
     */
    public function getUsageStats()
    {
        try {
            // 1. Top 10 Most viewed pages
            // Filter out the system-reports endpoint to avoid skewing data
            $topPages = ActivityLog::select('url', DB::raw('count(*) as views'))
                ->where('url', 'not like', '%system-reports%')
                ->groupBy('url')
                ->orderByDesc('views')
                ->limit(10)
                ->get();

            // 2. Top 10 Most active users
            $activeUsers = ActivityLog::select('user_id', DB::raw('count(*) as active_count'))
                ->whereNotNull('user_id')
                ->with('staff:staffID,staffName,staffEmail,staffProfile,departmentID')
                ->groupBy('user_id')
                ->orderByDesc('active_count')
                ->limit(10)
                ->get()
                ->map(function ($log) {
                    return [
                        'staffID' => $log->user_id,
                        'staffName' => $log->staff ? $log->staff->staffName : 'Unknown',
                        'staffEmail' => $log->staff ? $log->staff->staffEmail : null,
                        'staffProfile' => $log->staff ? $log->staff->staffProfile : null,
                        'activityCount' => $log->active_count,
                    ];
                });

            // 3. Browser usage totals
            $browsers = ActivityLog::select('browser', DB::raw('count(*) as count'))
                ->groupBy('browser')
                ->orderByDesc('count')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'System usage statistics retrieved successfully.',
                'data' => [
                    'mostViewedPages' => $topPages,
                    'mostActiveUsers' => $activeUsers,
                    'browserUsage' => $browsers
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system usage stats.',
                'data' => $e->getMessage()
            ], 500);
        }
    }
}
