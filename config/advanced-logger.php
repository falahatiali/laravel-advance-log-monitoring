<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Advanced Logger Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Advanced Logger
    | package. You can modify these values to customize the logging behavior.
    |
    */

    'enabled' => env('ADVANCED_LOGGER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where and how logs should be stored.
    |
    */

    'storage' => [
        'driver' => env('LOG_STORAGE_DRIVER', 'database'), // database, file, sentry, elasticsearch
        'table' => env('LOG_STORAGE_TABLE', 'advanced_logs'),
        'file_path' => storage_path('logs/advanced'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Database-specific settings for log storage.
    |
    */

    'database' => [
        'connection' => env('LOG_DB_CONNECTION', null), // null = default connection
        'table' => env('LOG_STORAGE_TABLE', 'advanced_logs'),
        'chunk_size' => 1000, // For bulk operations
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automated alerts and notifications.
    |
    */

    'alerts' => [
        'enabled' => env('LOG_ALERTS_ENABLED', true),
        
        'thresholds' => [
            'critical' => [
                'count' => env('LOG_ALERT_CRITICAL_COUNT', 5),
                'time_window' => env('LOG_ALERT_CRITICAL_WINDOW', '1 hour'),
            ],
            'error' => [
                'count' => env('LOG_ALERT_ERROR_COUNT', 20),
                'time_window' => env('LOG_ALERT_ERROR_WINDOW', '1 hour'),
            ],
        ],

        'channels' => [
            'email' => [
                'enabled' => env('LOG_ALERT_EMAIL_ENABLED', false),
                'to' => env('LOG_ALERT_EMAIL_TO'),
                'subject_prefix' => '[Log Alert]',
            ],
            'slack' => [
                'enabled' => env('LOG_ALERT_SLACK_ENABLED', false),
                'webhook' => env('LOG_ALERT_SLACK_WEBHOOK'),
                'channel' => env('LOG_ALERT_SLACK_CHANNEL', '#alerts'),
            ],
            'telegram' => [
                'enabled' => env('LOG_ALERT_TELEGRAM_ENABLED', false),
                'bot_token' => env('LOG_ALERT_TELEGRAM_BOT_TOKEN'),
                'chat_id' => env('LOG_ALERT_TELEGRAM_CHAT_ID'),
            ],
        ],

        'filters' => [
            'exclude_levels' => ['debug', 'info'],
            'include_categories' => null, // null = all categories
            'exclude_categories' => ['debug'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the web dashboard interface.
    |
    */

    'dashboard' => [
        'enabled' => env('LOG_DASHBOARD_ENABLED', true),
        'middleware' => ['auth'], // Add 'admin' or custom middleware as needed
        'pagination' => 50,
        'max_display_levels' => ['emergency', 'alert', 'critical', 'error', 'warning'],
        'real_time' => [
            'enabled' => env('LOG_DASHBOARD_REALTIME', true),
            'refresh_interval' => 5000, // milliseconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Categories
    |--------------------------------------------------------------------------
    |
    | Predefined categories for organizing logs.
    |
    */

    'categories' => [
        'auth' => 'Authentication & Authorization',
        'api' => 'API Requests & Responses',
        'payments' => 'Payment Processing',
        'database' => 'Database Operations',
        'mail' => 'Email Operations',
        'queue' => 'Queue Processing',
        'cache' => 'Cache Operations',
        'file' => 'File Operations',
        'security' => 'Security Events',
        'performance' => 'Performance Monitoring',
        'debug' => 'Debug Information',
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automatic logging of Laravel events.
    |
    */

    'auto_logging' => [
        'requests' => [
            'enabled' => env('LOG_AUTO_REQUESTS', true),
            'middleware' => 'log.requests',
            'exclude_methods' => ['GET'],
            'exclude_paths' => ['/health', '/status'],
            'log_response' => false,
        ],
        'models' => [
            'enabled' => env('LOG_AUTO_MODELS', false),
            'events' => ['created', 'updated', 'deleted'],
            'exclude_attributes' => ['password', 'remember_token', 'api_token'],
        ],
        'queries' => [
            'enabled' => env('LOG_AUTO_QUERIES', false),
            'slow_query_threshold' => 1000, // milliseconds
            'log_bindings' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Observing
    |--------------------------------------------------------------------------
    |
    | Configure which models should be automatically observed.
    |
    */

    'auto_observe_models' => env('LOG_AUTO_OBSERVE_MODELS', false),
    'observed_models' => [
        // 'App\Models\User',
        // 'App\Models\Order',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retention Policy
    |--------------------------------------------------------------------------
    |
    | Configure log retention and cleanup policies.
    |
    */

    'retention' => [
        'enabled' => env('LOG_RETENTION_ENABLED', true),
        'days' => [
            'local' => 7,
            'staging' => 14,
            'production' => 30,
        ],
        'compress_before_delete' => env('LOG_COMPRESS_BEFORE_DELETE', true),
        'cleanup_schedule' => '0 2 * * *', // Daily at 2 AM
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configure performance-related settings.
    |
    */

    'performance' => [
        'use_queue' => env('LOG_USE_QUEUE', false),
        'queue_name' => env('LOG_QUEUE_NAME', 'logs'),
        'batch_size' => 100,
        'memory_limit' => '256M',
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security-related settings for log sanitization.
    |
    */

    'security' => [
        'sanitize_sensitive_data' => env('LOG_SANITIZE_DATA', true),
        'sensitive_patterns' => [
            '/password/i',
            '/token/i',
            '/secret/i',
            '/key/i',
            '/credit_card/i',
            '/ssn/i',
            '/social_security/i',
        ],
        'mask_replacement' => '[REDACTED]',
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configure integrations with external services.
    |
    */

    'integrations' => [
        'sentry' => [
            'enabled' => env('LOG_SENTRY_ENABLED', false),
            'dsn' => env('SENTRY_LARAVEL_DSN'),
        ],
        'datadog' => [
            'enabled' => env('LOG_DATADOG_ENABLED', false),
            'api_key' => env('DATADOG_API_KEY'),
        ],
        'elasticsearch' => [
            'enabled' => env('LOG_ELASTICSEARCH_ENABLED', false),
            'host' => env('ELASTICSEARCH_HOST', 'localhost:9200'),
            'index' => env('ELASTICSEARCH_LOG_INDEX', 'laravel-logs'),
        ],
    ],
];
