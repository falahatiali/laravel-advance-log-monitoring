<?php

namespace AFM\AdvancedLogger\Commands;

use AFM\AdvancedLogger\Models\LogEntry;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'logs:cleanup 
                            {--days= : Number of days to keep logs (overrides config)}
                            {--level= : Only cleanup logs of specific level}
                            {--category= : Only cleanup logs of specific category}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--compress : Compress logs before deletion}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up old log entries based on retention policy';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting log cleanup process...');

        $days = $this->option('days') ?? $this->getRetentionDays();
        $level = $this->option('level');
        $category = $this->option('category');
        $dryRun = $this->option('dry-run');
        $compress = $this->option('compress');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No logs will be deleted');
        }

        $cutoffDate = Carbon::now()->subDays($days);
        
        $this->info("Cleaning up logs older than {$days} days (before {$cutoffDate->format('Y-m-d H:i:s')})");

        // Build query
        $query = LogEntry::where('created_at', '<', $cutoffDate);

        if ($level) {
            $query->where('level', $level);
            $this->info("Filtering by level: {$level}");
        }

        if ($category) {
            $query->where('category', $category);
            $this->info("Filtering by category: {$category}");
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No logs found matching criteria.');
            return 0;
        }

        $this->info("Found {$count} logs to cleanup");

        if ($dryRun) {
            $this->showSampleLogs($query);
            $this->info('DRY RUN: Would delete ' . $count . ' logs');
            return 0;
        }

        // Handle compression if requested
        if ($compress && config('advanced-logger.retention.compress_before_delete', true)) {
            $this->compressLogs($query, $cutoffDate);
        }

        // Delete logs
        $deleted = $query->delete();

        $this->info("Successfully deleted {$deleted} logs");

        // Cleanup file logs if using file storage
        if (config('advanced-logger.storage.driver') === 'file') {
            $this->cleanupFileLogs($days);
        }

        return 0;
    }

    /**
     * Get retention days based on environment.
     */
    protected function getRetentionDays(): int
    {
        $config = config('advanced-logger.retention.days', []);
        $environment = app()->environment();

        return $config[$environment] ?? $config['production'] ?? 30;
    }

    /**
     * Show sample logs that would be deleted.
     */
    protected function showSampleLogs($query): void
    {
        $sampleLogs = $query->limit(10)->get(['id', 'level', 'category', 'message', 'created_at']);

        if ($sampleLogs->isEmpty()) {
            return;
        }

        $this->info('Sample logs that would be deleted:');
        $this->table(
            ['ID', 'Level', 'Category', 'Message', 'Created At'],
            $sampleLogs->map(function ($log) {
                return [
                    $log->id,
                    $log->level,
                    $log->category ?? 'N/A',
                    substr($log->message, 0, 50) . '...',
                    $log->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray()
        );
    }

    /**
     * Compress logs before deletion.
     */
    protected function compressLogs($query, Carbon $cutoffDate): void
    {
        $this->info('Compressing logs before deletion...');

        // Group logs by date for compression
        $dates = $query->selectRaw('DATE(created_at) as log_date')
            ->distinct()
            ->where('created_at', '<', $cutoffDate)
            ->pluck('log_date');

        foreach ($dates as $date) {
            $logs = $query->whereDate('created_at', $date)->get();
            
            if ($logs->isEmpty()) {
                continue;
            }

            $filename = "logs-{$date}.json";
            $filePath = storage_path("logs/compressed/{$filename}");

            // Ensure directory exists
            $directory = dirname($filePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Write logs to file
            file_put_contents($filePath, $logs->toJson(JSON_PRETTY_PRINT));

            // Compress file
            $gz = gzopen($filePath . '.gz', 'w9');
            gzwrite($gz, file_get_contents($filePath));
            gzclose($gz);

            // Remove uncompressed file
            unlink($filePath);

            $this->info("Compressed {$logs->count()} logs from {$date} to {$filename}.gz");
        }
    }

    /**
     * Cleanup file-based logs.
     */
    protected function cleanupFileLogs(int $days): void
    {
        $logPath = config('advanced-logger.storage.file_path', storage_path('logs/advanced'));
        
        if (!is_dir($logPath)) {
            return;
        }

        $this->info('Cleaning up file-based logs...');

        $files = glob($logPath . '/*.log');
        $cutoffDate = Carbon::now()->subDays($days);
        $deletedFiles = 0;

        foreach ($files as $file) {
            $fileTime = filemtime($file);
            $fileDate = Carbon::createFromTimestamp($fileTime);

            if ($fileDate->lt($cutoffDate)) {
                if (unlink($file)) {
                    $deletedFiles++;
                    $this->info("Deleted file: " . basename($file));
                }
            }
        }

        $this->info("Deleted {$deletedFiles} log files");
    }
}
