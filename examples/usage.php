<?php

/**
 * Advanced Logger Usage Examples
 * 
 * This file demonstrates various ways to use the Advanced Logger package
 * in your Laravel application.
 */

use AFM\AdvancedLogger\Facades\Logger;

// ============================================================================
// BASIC LOGGING
// ============================================================================

// Simple logging
Logger::info('Application started successfully');
Logger::warning('High memory usage detected');
Logger::error('Database connection failed');
Logger::debug('User session data', ['session_id' => 'abc123']);

// ============================================================================
// CATEGORIZED LOGGING
// ============================================================================

// Authentication events
Logger::category('auth')
    ->info('User logged in', [
        'user_id' => 123,
        'email' => 'user@example.com',
        'ip_address' => '192.168.1.1'
    ]);

Logger::category('auth')
    ->warning('Failed login attempt', [
        'email' => 'user@example.com',
        'attempts' => 3,
        'ip_address' => '192.168.1.1'
    ]);

// API events
Logger::category('api')
    ->info('API request processed', [
        'endpoint' => '/api/users',
        'method' => 'GET',
        'status_code' => 200,
        'response_time' => 0.150
    ]);

Logger::category('api')
    ->error('API rate limit exceeded', [
        'endpoint' => '/api/payments',
        'user_id' => 123,
        'limit' => 100,
        'current_requests' => 150
    ]);

// Payment events
Logger::category('payments')
    ->info('Payment processed successfully', [
        'user_id' => 123,
        'amount' => 99.99,
        'currency' => 'USD',
        'payment_method' => 'credit_card',
        'transaction_id' => 'txn_abc123'
    ]);

Logger::category('payments')
    ->critical('Payment gateway error', [
        'user_id' => 123,
        'amount' => 99.99,
        'gateway' => 'stripe',
        'error_code' => 'card_declined',
        'error_message' => 'Your card was declined.'
    ]);

// ============================================================================
// CHAINABLE METHODS
// ============================================================================

// Set user and context for multiple logs
Logger::category('api')
    ->user(123)
    ->context([
        'request_id' => 'req_' . uniqid(),
        'session_id' => session()->getId(),
        'ip_address' => request()->ip()
    ])
    ->info('API request started', [
        'endpoint' => '/api/orders',
        'method' => 'POST'
    ]);

// ============================================================================
// EXCEPTION LOGGING
// ============================================================================

try {
    // Some risky operation
    $result = riskyOperation();
} catch (\Exception $e) {
    Logger::category('errors')
        ->user(auth()->id())
        ->exception($e, [
            'context' => 'Payment processing',
            'user_id' => auth()->id(),
            'order_id' => $orderId ?? null
        ]);
}

// ============================================================================
// PERFORMANCE LOGGING
// ============================================================================

$startTime = microtime(true);
$startMemory = memory_get_usage(true);

// Some operation
performDatabaseQuery();

$executionTime = microtime(true) - $startTime;
$memoryUsage = memory_get_usage(true) - $startMemory;

Logger::performance('Database query execution', $executionTime, [
    'query_type' => 'SELECT',
    'table' => 'users',
    'memory_usage' => $memoryUsage,
    'rows_affected' => 150
]);

// ============================================================================
// SECURITY EVENTS
// ============================================================================

// Suspicious activity
Logger::security('Multiple failed login attempts', [
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'attempts' => 5,
    'time_window' => '5 minutes'
]);

// Unauthorized access attempt
Logger::security('Unauthorized API access', [
    'endpoint' => '/api/admin/users',
    'ip_address' => request()->ip(),
    'user_id' => auth()->id(),
    'required_role' => 'admin',
    'user_role' => 'user'
]);

// ============================================================================
// BUSINESS LOGIC EVENTS
// ============================================================================

// User registration
Logger::category('users')
    ->info('New user registered', [
        'user_id' => $user->id,
        'email' => $user->email,
        'registration_source' => 'website',
        'ip_address' => request()->ip()
    ]);

// Order creation
Logger::category('orders')
    ->info('New order created', [
        'order_id' => $order->id,
        'user_id' => $order->user_id,
        'total_amount' => $order->total,
        'items_count' => $order->items->count()
    ]);

// Email sending
Logger::category('mail')
    ->info('Email sent successfully', [
        'to' => $email,
        'subject' => $subject,
        'template' => 'welcome',
        'user_id' => $user->id
    ]);

// ============================================================================
// SYSTEM EVENTS
// ============================================================================

// Cache operations
Logger::category('cache')
    ->info('Cache cleared', [
        'cache_type' => 'user_sessions',
        'cleared_by' => auth()->id(),
        'affected_keys' => 150
    ]);

// Queue jobs
Logger::category('queue')
    ->info('Job processed successfully', [
        'job_class' => 'SendWelcomeEmail',
        'job_id' => $jobId,
        'processing_time' => 2.5,
        'user_id' => $user->id
    ]);

Logger::category('queue')
    ->error('Job failed', [
        'job_class' => 'ProcessPayment',
        'job_id' => $jobId,
        'error_message' => $exception->getMessage(),
        'attempts' => 3
    ]);

// ============================================================================
// FILE OPERATIONS
// ============================================================================

Logger::category('file')
    ->info('File uploaded successfully', [
        'file_name' => $file->getClientOriginalName(),
        'file_size' => $file->getSize(),
        'mime_type' => $file->getMimeType(),
        'user_id' => auth()->id(),
        'storage_path' => $file->store('uploads')
    ]);

// ============================================================================
// DEBUGGING
// ============================================================================

// Debug information
Logger::category('debug')
    ->debug('Variable dump', [
        'variable_name' => '$userData',
        'value' => $userData,
        'type' => gettype($userData),
        'size' => is_array($userData) ? count($userData) : strlen($userData)
    ]);

// Function execution
Logger::category('debug')
    ->debug('Function executed', [
        'function_name' => 'calculateTax',
        'parameters' => ['amount' => 100, 'rate' => 0.1],
        'result' => 10,
        'execution_time' => 0.001
    ]);

// ============================================================================
// RETRIEVING AND FILTERING LOGS
// ============================================================================

// Get logs with filters
$errorLogs = Logger::getLogs([
    'level' => ['error', 'critical'],
    'date_from' => '2025-01-01',
    'date_to' => '2025-01-31'
], 50);

// Get statistics
$stats = Logger::getStats([
    'category' => 'auth',
    'date_from' => '2025-01-01'
]);

// ============================================================================
// EXPORT FUNCTIONALITY
// ============================================================================

// Export logs as JSON
$jsonLogs = Logger::exportLogs(['level' => 'error'], 'json');

// Export logs as CSV
$csvLogs = Logger::exportLogs(['category' => 'payments'], 'csv');

// Export logs as XML
$xmlLogs = Logger::exportLogs(['date_from' => '2025-01-01'], 'xml');

// ============================================================================
// CLEANUP OPERATIONS
// ============================================================================

// Clear old logs
$deletedCount = Logger::clearLogs([
    'level' => 'debug',
    'before_date' => '2025-01-01'
]);

// ============================================================================
// TAGS AND EXTRA METADATA
// ============================================================================

Logger::category('api')
    ->info('API request', [
        'endpoint' => '/api/data',
        '_tags' => ['api', 'v1', 'external'],
        '_extra' => [
            'version' => '1.0',
            'environment' => 'production'
        ]
    ]);

// ============================================================================
// REAL-WORLD EXAMPLES
// ============================================================================

// E-commerce order processing
class OrderController
{
    public function store(Request $request)
    {
        try {
            Logger::category('orders')
                ->user(auth()->id())
                ->info('Order creation started', [
                    'items_count' => count($request->items),
                    'total_amount' => $request->total
                ]);

            $order = Order::create($request->validated());

            Logger::category('orders')
                ->user(auth()->id())
                ->info('Order created successfully', [
                    'order_id' => $order->id,
                    'status' => 'pending'
                ]);

            // Process payment
            $paymentResult = $this->processPayment($order);

            if ($paymentResult->success) {
                Logger::category('payments')
                    ->user(auth()->id())
                    ->info('Payment processed', [
                        'order_id' => $order->id,
                        'payment_id' => $paymentResult->id,
                        'amount' => $order->total
                    ]);
            } else {
                Logger::category('payments')
                    ->user(auth()->id())
                    ->error('Payment failed', [
                        'order_id' => $order->id,
                        'error' => $paymentResult->error,
                        'amount' => $order->total
                    ]);
            }

            return response()->json(['order' => $order]);

        } catch (\Exception $e) {
            Logger::category('orders')
                ->user(auth()->id())
                ->exception($e, [
                    'order_data' => $request->validated(),
                    'step' => 'order_creation'
                ]);

            return response()->json(['error' => 'Order creation failed'], 500);
        }
    }
}

// API middleware logging
class ApiLoggingMiddleware
{
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);

        Logger::category('api')
            ->context([
                'request_id' => $request->header('X-Request-ID'),
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ])
            ->info('API request started', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'endpoint' => $request->path()
            ]);

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;
        $statusCode = $response->getStatusCode();

        $level = $statusCode >= 400 ? 'warning' : 'info';

        Logger::category('api')
            ->context([
                'request_id' => $request->header('X-Request-ID'),
                'status_code' => $statusCode,
                'execution_time' => round($executionTime, 3)
            ])
            ->{$level}('API request completed', [
                'status_code' => $statusCode,
                'execution_time' => $executionTime
            ]);

        return $response;
    }
}
