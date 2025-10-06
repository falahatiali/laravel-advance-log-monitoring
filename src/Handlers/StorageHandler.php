<?php

namespace AFM\AdvancedLogger\Handlers;

use AFM\AdvancedLogger\Models\LogEntry;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorageHandler
{
    protected Application $app;
    protected string $driver;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->driver = config('advanced-logger.storage.driver', 'database');
    }

    /**
     * Store the log entry.
     */
    public function store(array $logData): void
    {
        try {
            match ($this->driver) {
                'database' => $this->storeInDatabase($logData),
                'file' => $this->storeInFile($logData),
                'sentry' => $this->storeInSentry($logData),
                'elasticsearch' => $this->storeInElasticsearch($logData),
                default => throw new \InvalidArgumentException("Unsupported storage driver: {$this->driver}"),
            };
        } catch (\Exception $e) {
            // Fallback to Laravel's default logging
            Log::error('Advanced Logger storage failed', [
                'error' => $e->getMessage(),
                'log_data' => $logData,
            ]);
        }
    }

    /**
     * Store log in database.
     */
    protected function storeInDatabase(array $logData): void
    {
        if (config('advanced-logger.performance.use_queue', false)) {
            $this->app->make('queue')->push('AFM\\AdvancedLogger\\Jobs\\StoreLogJob', $logData);
        } else {
            LogEntry::create($logData);
        }
    }

    /**
     * Store log in file.
     */
    protected function storeInFile(array $logData): void
    {
        $filePath = config('advanced-logger.storage.file_path', storage_path('logs/advanced'));
        
        // Ensure directory exists
        if (!is_dir($filePath)) {
            mkdir($filePath, 0755, true);
        }

        $filename = date('Y-m-d') . '.log';
        $fullPath = $filePath . '/' . $filename;

        $logLine = $this->formatLogLine($logData);
        
        file_put_contents($fullPath, $logLine . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Store log in Sentry.
     */
    protected function storeInSentry(array $logData): void
    {
        if (!config('advanced-logger.integrations.sentry.enabled', false)) {
            return;
        }

        $sentry = $this->app->make('sentry');
        
        $level = match ($logData['level']) {
            'emergency', 'alert', 'critical' => 'error',
            'error' => 'error',
            'warning' => 'warning',
            'notice', 'info' => 'info',
            'debug' => 'debug',
            default => 'info',
        };

        $sentry->captureMessage($logData['message'], $level, [
            'extra' => $logData['context'],
            'user' => [
                'id' => $logData['user_id'],
                'ip_address' => $logData['ip_address'],
            ],
            'tags' => $logData['tags'] ?? [],
        ]);
    }

    /**
     * Store log in Elasticsearch.
     */
    protected function storeInElasticsearch(array $logData): void
    {
        if (!config('advanced-logger.integrations.elasticsearch.enabled', false)) {
            return;
        }

        // This would require an Elasticsearch client
        // Implementation depends on your Elasticsearch setup
        $client = $this->app->make('elasticsearch');
        $index = config('advanced-logger.integrations.elasticsearch.index', 'laravel-logs');
        
        $client->index([
            'index' => $index,
            'body' => $logData,
        ]);
    }

    /**
     * Format log line for file storage.
     */
    protected function formatLogLine(array $logData): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $level = strtoupper($logData['level']);
        $category = $logData['category'] ? "[{$logData['category']}]" : '';
        $message = $logData['message'];
        
        $context = '';
        if (!empty($logData['context'])) {
            $context = ' ' . json_encode($logData['context']);
        }

        return "[{$timestamp}] {$level}{$category}: {$message}{$context}";
    }

    /**
     * Get the storage driver name.
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * Set the storage driver.
     */
    public function setDriver(string $driver): self
    {
        $this->driver = $driver;
        return $this;
    }
}
