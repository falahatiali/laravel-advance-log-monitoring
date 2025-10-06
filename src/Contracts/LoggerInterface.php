<?php

namespace AFM\AdvancedLogger\Contracts;

interface LoggerInterface
{
    /**
     * Set the category for the next log entry.
     */
    public function category(string $category): self;

    /**
     * Set the context for the next log entry.
     */
    public function context(array $context): self;

    /**
     * Set the user ID for the next log entry.
     */
    public function user(?int $userId): self;

    /**
     * Log an emergency message.
     */
    public function emergency(string $message, array $context = []): void;

    /**
     * Log an alert message.
     */
    public function alert(string $message, array $context = []): void;

    /**
     * Log a critical message.
     */
    public function critical(string $message, array $context = []): void;

    /**
     * Log an error message.
     */
    public function error(string $message, array $context = []): void;

    /**
     * Log a warning message.
     */
    public function warning(string $message, array $context = []): void;

    /**
     * Log a notice message.
     */
    public function notice(string $message, array $notice, array $context = []): void;

    /**
     * Log an info message.
     */
    public function info(string $message, array $context = []): void;

    /**
     * Log a debug message.
     */
    public function debug(string $message, array $context = []): void;

    /**
     * Log a message with a specific level.
     */
    public function log(string $level, string $message, array $context = []): void;

    /**
     * Log an exception.
     */
    public function exception(\Throwable $exception, array $context = []): void;

    /**
     * Log a performance metric.
     */
    public function performance(string $operation, float $duration, array $context = []): void;

    /**
     * Log a security event.
     */
    public function security(string $event, array $context = []): void;

    /**
     * Get logs with filters.
     */
    public function getLogs(array $filters = [], int $perPage = 50);

    /**
     * Get log statistics.
     */
    public function getStats(array $filters = []): array;

    /**
     * Clear logs based on criteria.
     */
    public function clearLogs(array $criteria = []): int;

    /**
     * Export logs to a specific format.
     */
    public function exportLogs(array $filters = [], string $format = 'json'): string;
}
