<?php

namespace Simorgh\Logger\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \AFM\AdvancedLogger\Contracts\LoggerInterface category(string $category)
 * @method static \AFM\AdvancedLogger\Contracts\LoggerInterface context(array $context)
 * @method static \AFM\AdvancedLogger\Contracts\LoggerInterface user(?int $userId)
 * @method static void emergency(string $message, array $context = [])
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void log(string $level, string $message, array $context = [])
 * @method static void exception(\Throwable $exception, array $context = [])
 * @method static void performance(string $operation, float $duration, array $context = [])
 * @method static void security(string $event, array $context = [])
 * @method static mixed getLogs(array $filters = [], int $perPage = 50)
 * @method static array getStats(array $filters = [])
 * @method static int clearLogs(array $criteria = [])
 * @method static string exportLogs(array $filters = [], string $format = 'json')
 *
 * @see \AFM\AdvancedLogger\Services\LoggerService
 */
class Logger extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'advanced-logger';
    }
}
