<?php

namespace Simorgh\Logger\Services;

use Simorgh\Logger\Contracts\LoggerInterface;
use Simorgh\Logger\Models\LogEntry;
use Simorgh\Logger\Handlers\AlertHandler;
use Simorgh\Logger\Handlers\StorageHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Throwable;

class LoggerService implements LoggerInterface
{
    protected Application $app;
    protected StorageHandler $storageHandler;
    protected AlertHandler $alertHandler;
    protected ?string $currentCategory = null;
    protected array $currentContext = [];
    protected ?int $currentUserId = null;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->storageHandler = $app->make(StorageHandler::class);
        $this->alertHandler = $app->make(AlertHandler::class);
    }

    /**
     * Set the category for the next log entry.
     */
    public function category(string $category): self
    {
        $this->currentCategory = $category;
        return $this;
    }

    /**
     * Set the context for the next log entry.
     */
    public function context(array $context): self
    {
        $this->currentContext = array_merge($this->currentContext, $context);
        return $this;
    }

    /**
     * Set the user ID for the next log entry.
     */
    public function user(?int $userId): self
    {
        $this->currentUserId = $userId;
        return $this;
    }

    /**
     * Log an emergency message.
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Log an alert message.
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Log a critical message.
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log an error message.
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log a warning message.
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log a notice message.
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Log an info message.
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log a debug message.
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log a message with a specific level.
     */
    public function log(string $level, string $message, array $context = []): void
    {
        if (!config('advanced-logger.enabled', true)) {
            return;
        }

        // Merge current context with provided context
        $context = array_merge($this->currentContext, $context);

        // Sanitize sensitive data
        $context = $this->sanitizeContext($context);

        // Prepare log data
        $logData = $this->prepareLogData($level, $message, $context);

        // Store the log
        $this->storageHandler->store($logData);

        // Check for alerts
        $this->alertHandler->checkAlerts($logData);

        // Reset current context for next log
        $this->resetCurrentContext();
    }

    /**
     * Log an exception.
     */
    public function exception(Throwable $exception, array $context = []): void
    {
        $context = array_merge($context, [
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'stack_trace' => $exception->getTraceAsString(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        $this->log('error', "Exception: {$exception->getMessage()}", $context);
    }

    /**
     * Log a performance metric.
     */
    public function performance(string $operation, float $duration, array $context = []): void
    {
        $context = array_merge($context, [
            'operation' => $operation,
            'duration' => $duration,
            'memory_usage' => memory_get_usage(true),
        ]);

        $this->log('info', "Performance: {$operation} took {$duration}s", $context);
    }

    /**
     * Log a security event.
     */
    public function security(string $event, array $context = []): void
    {
        $this->category('security')->log('warning', "Security: {$event}", $context);
    }

    /**
     * Get logs with filters.
     */
    public function getLogs(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = LogEntry::query()->filter($filters)->latest();

        return $query->paginate($perPage);
    }

    /**
     * Get log statistics.
     */
    public function getStats(array $filters = []): array
    {
        return LogEntry::getStats($filters);
    }

    /**
     * Clear logs based on criteria.
     */
    public function clearLogs(array $criteria = []): int
    {
        $query = LogEntry::query();

        if (isset($criteria['level'])) {
            $query->level($criteria['level']);
        }

        if (isset($criteria['category'])) {
            $query->category($criteria['category']);
        }

        if (isset($criteria['before_date'])) {
            $query->where('created_at', '<', $criteria['before_date']);
        }

        if (isset($criteria['is_resolved'])) {
            $query->where('is_resolved', $criteria['is_resolved']);
        }

        $count = $query->count();
        $query->delete();

        return $count;
    }

    /**
     * Export logs to a specific format.
     */
    public function exportLogs(array $filters = [], string $format = 'json'): string
    {
        $logs = LogEntry::filter($filters)->get();

        return match ($format) {
            'json' => $logs->toJson(JSON_PRETTY_PRINT),
            'csv' => $this->exportToCsv($logs),
            'xml' => $this->exportToXml($logs),
            default => throw new \InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }

    /**
     * Prepare log data for storage.
     */
    protected function prepareLogData(string $level, string $message, array $context): array
    {
        $request = Request::instance();
        $user = Auth::user();

        return [
            'level' => $level,
            'category' => $this->currentCategory,
            'message' => $message,
            'context' => $context,
            'user_id' => $this->currentUserId ?? ($user ? $user->getKey() : null),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => $request->header('X-Request-ID') ?? Str::uuid()->toString(),
            'session_id' => session()->getId(),
            'route_name' => $request->route()?->getName(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status_code' => null, // Will be set by middleware
            'execution_time' => null, // Will be set by middleware
            'memory_usage' => memory_get_usage(true),
            'file' => null,
            'line' => null,
            'exception_class' => null,
            'exception_message' => null,
            'stack_trace' => null,
            'tags' => $this->extractTags($context),
            'extra' => $this->extractExtra($context),
            'is_resolved' => false,
            'resolved_at' => null,
        ];
    }

    /**
     * Sanitize sensitive data from context.
     */
    protected function sanitizeContext(array $context): array
    {
        if (!config('advanced-logger.security.sanitize_sensitive_data', true)) {
            return $context;
        }

        $patterns = config('advanced-logger.security.sensitive_patterns', []);
        $replacement = config('advanced-logger.security.mask_replacement', '[REDACTED]');

        return $this->recursiveSanitize($context, $patterns, $replacement);
    }

    /**
     * Recursively sanitize array data.
     */
    protected function recursiveSanitize(array $data, array $patterns, string $replacement): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->recursiveSanitize($value, $patterns, $replacement);
            } else {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $key)) {
                        $data[$key] = $replacement;
                        break;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Extract tags from context.
     */
    protected function extractTags(array $context): array
    {
        $tags = $context['_tags'] ?? [];
        unset($context['_tags']);
        
        return is_array($tags) ? $tags : [];
    }

    /**
     * Extract extra metadata from context.
     */
    protected function extractExtra(array $context): array
    {
        $extra = $context['_extra'] ?? [];
        unset($context['_extra']);
        
        return is_array($extra) ? $extra : [];
    }

    /**
     * Reset current context.
     */
    protected function resetCurrentContext(): void
    {
        $this->currentCategory = null;
        $this->currentContext = [];
        $this->currentUserId = null;
    }

    /**
     * Export logs to CSV format.
     */
    protected function exportToCsv($logs): string
    {
        $csv = "ID,Level,Category,Message,User ID,IP Address,Created At\n";
        
        foreach ($logs as $log) {
            $csv .= sprintf(
                "%s,%s,%s,\"%s\",%s,%s,%s\n",
                $log->id,
                $log->level,
                $log->category ?? '',
                str_replace('"', '""', $log->message),
                $log->user_id ?? '',
                $log->ip_address ?? '',
                $log->created_at->format('Y-m-d H:i:s')
            );
        }

        return $csv;
    }

    /**
     * Export logs to XML format.
     */
    protected function exportToXml($logs): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<logs>' . "\n";

        foreach ($logs as $log) {
            $xml .= '  <log>' . "\n";
            $xml .= "    <id>{$log->id}</id>\n";
            $xml .= "    <level>{$log->level}</level>\n";
            $xml .= "    <category>" . ($log->category ?? '') . "</category>\n";
            $xml .= "    <message><![CDATA[{$log->message}]]></message>\n";
            $xml .= "    <user_id>" . ($log->user_id ?? '') . "</user_id>\n";
            $xml .= "    <ip_address>" . ($log->ip_address ?? '') . "</ip_address>\n";
            $xml .= "    <created_at>{$log->created_at->format('Y-m-d H:i:s')}</created_at>\n";
            $xml .= '  </log>' . "\n";
        }

        $xml .= '</logs>';
        return $xml;
    }
}
