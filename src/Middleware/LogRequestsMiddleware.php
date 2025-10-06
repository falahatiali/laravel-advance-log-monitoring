<?php

namespace Simorgh\Logger\Middleware;

use Simorgh\Logger\Facades\Logger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogRequestsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('advanced-logger.auto_logging.requests.enabled', true)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        // Skip logging for excluded methods and paths
        if ($this->shouldSkipLogging($request)) {
            return $next($request);
        }

        // Log the incoming request
        $this->logRequest($request);

        // Process the request
        $response = $next($request);

        // Log the response
        $this->logResponse($request, $response, $startTime, $startMemory);

        return $response;
    }

    /**
     * Check if request should be skipped from logging.
     */
    protected function shouldSkipLogging(Request $request): bool
    {
        // Skip excluded methods
        $excludedMethods = config('advanced-logger.auto_logging.requests.exclude_methods', ['GET']);
        if (in_array($request->method(), $excludedMethods)) {
            return true;
        }

        // Skip excluded paths
        $excludedPaths = config('advanced-logger.auto_logging.requests.exclude_paths', ['/health', '/status']);
        foreach ($excludedPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the incoming request.
     */
    protected function logRequest(Request $request): void
    {
        Logger::category('api')
            ->context([
                'request_id' => $request->header('X-Request-ID'),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
                'headers' => $this->sanitizeHeaders($request->headers->all()),
                'input' => $this->sanitizeInput($request->all()),
            ])
            ->info("Incoming request: {$request->method()} {$request->path()}");
    }

    /**
     * Log the response.
     */
    protected function logResponse(Request $request, Response $response, float $startTime, int $startMemory): void
    {
        $executionTime = microtime(true) - $startTime;
        $memoryUsage = memory_get_usage(true) - $startMemory;
        $statusCode = $response->getStatusCode();

        $level = $this->determineLogLevel($statusCode);
        $message = "Request completed: {$request->method()} {$request->path()} - {$statusCode}";

        Logger::category('api')
            ->context([
                'request_id' => $request->header('X-Request-ID'),
                'status_code' => $statusCode,
                'execution_time' => round($executionTime, 3),
                'memory_usage' => $memoryUsage,
                'response_size' => strlen($response->getContent()),
                'user_id' => Auth::id(),
            ])
            ->{$level}($message);

        // Log performance if response is slow
        if ($executionTime > config('advanced-logger.auto_logging.queries.slow_query_threshold', 1000) / 1000) {
            Logger::performance(
                "Slow request: {$request->method()} {$request->path()}",
                $executionTime,
                [
                    'status_code' => $statusCode,
                    'memory_usage' => $memoryUsage,
                ]
            );
        }
    }

    /**
     * Determine log level based on status code.
     */
    protected function determineLogLevel(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            $statusCode >= 300 => 'info',
            default => 'info',
        };
    }

    /**
     * Sanitize headers to remove sensitive information.
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
        ];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['[REDACTED]'];
            }
        }

        return $headers;
    }

    /**
     * Sanitize input data to remove sensitive information.
     */
    protected function sanitizeInput(array $input): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'api_token',
            'secret',
            'key',
            'token',
        ];

        return $this->recursiveSanitize($input, $sensitiveFields);
    }

    /**
     * Recursively sanitize array data.
     */
    protected function recursiveSanitize(array $data, array $sensitiveFields): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower($key), $sensitiveFields)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = $this->recursiveSanitize($value, $sensitiveFields);
            }
        }

        return $data;
    }
}
