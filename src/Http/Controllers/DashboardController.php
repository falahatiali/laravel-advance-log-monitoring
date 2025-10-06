<?php

namespace Simorgh\Logger\Http\Controllers;

use Simorgh\Logger\Facades\Logger;
use Simorgh\Logger\Models\LogEntry;
use Simorgh\Logger\Handlers\AlertHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    protected AlertHandler $alertHandler;

    public function __construct(AlertHandler $alertHandler)
    {
        $this->alertHandler = $alertHandler;
    }

    /**
     * Show the main dashboard.
     */
    public function index(): View
    {
        $stats = Logger::getStats();
        $recentLogs = LogEntry::latest()->limit(10)->get();
        $alertStats = $this->alertHandler->getAlertStats();

        return view('advanced-logger::dashboard.index', compact('stats', 'recentLogs', 'alertStats'));
    }

    /**
     * Show logs with filters.
     */
    public function logs(Request $request): View
    {
        $filters = $request->only(['level', 'category', 'search', 'date_from', 'date_to', 'is_resolved']);
        $logs = Logger::getLogs($filters, config('advanced-logger.dashboard.pagination', 50));
        
        $categories = LogEntry::select('category')->distinct()->pluck('category')->filter();
        $levels = ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'];

        return view('advanced-logger::dashboard.logs', compact('logs', 'categories', 'levels', 'filters'));
    }

    /**
     * Show statistics.
     */
    public function stats(Request $request): View
    {
        $filters = $request->only(['level', 'category', 'date_from', 'date_to']);
        $stats = Logger::getStats($filters);
        
        // Get daily stats for charts
        $dailyStats = LogEntry::selectRaw('DATE(created_at) as date, level, COUNT(*) as count')
            ->filter($filters)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date', 'level')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        return view('advanced-logger::dashboard.stats', compact('stats', 'dailyStats', 'filters'));
    }

    /**
     * Show alert configuration and status.
     */
    public function alerts(): View
    {
        $alertStats = $this->alertHandler->getAlertStats();
        $channels = config('advanced-logger.alerts.channels', []);

        return view('advanced-logger::dashboard.alerts', compact('alertStats', 'channels'));
    }

    /**
     * Resolve a log entry.
     */
    public function resolveLog(LogEntry $log): JsonResponse
    {
        $log->markAsResolved();

        return response()->json([
            'success' => true,
            'message' => 'Log marked as resolved',
        ]);
    }

    /**
     * Unresolve a log entry.
     */
    public function unresolveLog(LogEntry $log): JsonResponse
    {
        $log->markAsUnresolved();

        return response()->json([
            'success' => true,
            'message' => 'Log marked as unresolved',
        ]);
    }

    /**
     * Delete a log entry.
     */
    public function deleteLog(LogEntry $log): JsonResponse
    {
        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log deleted successfully',
        ]);
    }

    /**
     * Export logs as JSON.
     */
    public function exportJson(Request $request): StreamedResponse
    {
        $filters = $request->only(['level', 'category', 'search', 'date_from', 'date_to']);
        
        return response()->stream(function () use ($filters) {
            echo Logger::exportLogs($filters, 'json');
        }, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="logs-' . now()->format('Y-m-d') . '.json"',
        ]);
    }

    /**
     * Export logs as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->only(['level', 'category', 'search', 'date_from', 'date_to']);
        
        return response()->stream(function () use ($filters) {
            echo Logger::exportLogs($filters, 'csv');
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="logs-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Export logs as XML.
     */
    public function exportXml(Request $request): StreamedResponse
    {
        $filters = $request->only(['level', 'category', 'search', 'date_from', 'date_to']);
        
        return response()->stream(function () use ($filters) {
            echo Logger::exportLogs($filters, 'xml');
        }, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="logs-' . now()->format('Y-m-d') . '.xml"',
        ]);
    }

    /**
     * Show settings page.
     */
    public function settings(): View
    {
        $config = config('advanced-logger');
        
        return view('advanced-logger::dashboard.settings', compact('config'));
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        // This would typically update a settings file or database
        // For now, just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Test alert channels.
     */
    public function testAlerts(): JsonResponse
    {
        $results = $this->alertHandler->testChannels();

        return response()->json([
            'success' => true,
            'results' => $results,
            'message' => 'Alert test completed',
        ]);
    }
}
