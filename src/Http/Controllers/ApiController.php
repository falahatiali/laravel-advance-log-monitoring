<?php

namespace Simorgh\Logger\Http\Controllers;

use Simorgh\Logger\Facades\Logger;
use Simorgh\Logger\Models\LogEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Get logs via API.
     */
    public function logs(Request $request): JsonResponse
    {
        $filters = $request->only(['level', 'category', 'search', 'date_from', 'date_to', 'is_resolved']);
        $perPage = $request->get('per_page', config('advanced-logger.dashboard.pagination', 50));
        
        $logs = Logger::getLogs($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
                'has_more' => $logs->hasMorePages(),
            ],
        ]);
    }

    /**
     * Get statistics via API.
     */
    public function stats(Request $request): JsonResponse
    {
        $filters = $request->only(['level', 'category', 'date_from', 'date_to']);
        $stats = Logger::getStats($filters);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get real-time logs.
     */
    public function realTime(Request $request): JsonResponse
    {
        $since = $request->get('since', now()->subMinutes(5));
        $limit = $request->get('limit', 50);

        $logs = LogEntry::where('created_at', '>', $since)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
            'timestamp' => now()->toISOString(),
        ]);
    }
}
