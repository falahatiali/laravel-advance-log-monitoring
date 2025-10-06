<div align="center">
  <img src="public/simorgh.png" alt="Simorgh Logger" width="200" height="200">
  
  # 🦅 Simorgh Logger for Laravel
  
  [![Latest Version on Packagist](https://img.shields.io/packagist/v/falahatiali/simorgh-logger.svg?style=flat-square)](https://packagist.org/packages/falahatiali/simorgh-logger)
  [![Total Downloads](https://img.shields.io/packagist/dt/falahatiali/simorgh-logger.svg?style=flat-square)](https://packagist.org/packages/falahatiali/simorgh-logger)
  [![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
  [![GitHub Repository](https://img.shields.io/badge/GitHub-laravel--advance--log--monitoring-blue.svg?style=flat-square)](https://github.com/falahatiali/laravel-advance-log-monitoring)
</div>

🦅 **Simorgh Logger** - A powerful and feature-rich logging package for Laravel applications with dashboard, alerts, and intelligent categorization. Named after the legendary Persian bird that watches over and protects all under its wings.

> *"Just as the mythical Simorgh watches over all birds under its wings, this package watches over and protects all your application logs."*

## ✨ Features

- 🎯 **Smart Categorization** - Organize logs by modules (auth, api, payments, etc.)
- 📊 **Visual Dashboard** - Beautiful web interface with real-time updates
- 🚨 **Automated Alerts** - Email, Slack, Telegram notifications with intelligent triggers
- 🔍 **Advanced Filtering** - Search and filter logs with powerful query builder
- 📈 **Analytics & Stats** - Comprehensive statistics and performance metrics
- 🔒 **Security** - Automatic sanitization of sensitive data
- 📁 **Multiple Storage** - Database, File, Sentry, Elasticsearch support
- 🎨 **Export Options** - JSON, CSV, XML export capabilities
- ⚡ **Performance** - Queue support and optimized queries
- 🧹 **Auto Cleanup** - Configurable retention policies

## 📦 Installation

### Composer

```bash
composer require falahatiali/simorgh-logger
```

### Laravel Auto-Discovery

The package will automatically register itself. If auto-discovery is disabled, add the service provider to your `config/app.php`:

```php
'providers' => [
    // ...
    AFM\SimorghLogger\SimorghLoggerServiceProvider::class,
],
```

### Publish Configuration

```bash
php artisan vendor:publish --provider="AFM\SimorghLogger\SimorghLoggerServiceProvider" --tag="simorgh-logger-config"
```

### Publish Migrations

```bash
php artisan vendor:publish --provider="AFM\SimorghLogger\SimorghLoggerServiceProvider" --tag="simorgh-logger-migrations"
```

### Run Migrations

```bash
php artisan migrate
```

## 🚀 Quick Start

### Basic Usage

```php
use AFM\SimorghLogger\Facades\Simorgh;

// Simple logging
Simorgh::info('Application started successfully');

// With context
Simorgh::error('Payment failed', [
    'user_id' => 123,
    'amount' => 99.99,
    'payment_method' => 'credit_card'
]);

// Categorized logging
Simorgh::category('auth')
    ->warning('Failed login attempt', [
        'email' => 'user@example.com',
        'ip' => request()->ip()
    ]);
```

### Advanced Usage

```php
// Chainable methods
Simorgh::category('api')
    ->user(auth()->id())
    ->context(['request_id' => Str::uuid()])
    ->error('API rate limit exceeded', [
        'endpoint' => '/api/users',
        'limit' => 100,
        'current' => 150
    ]);

// Performance logging
Simorgh::performance('Database query', 0.250, [
    'query' => 'SELECT * FROM users WHERE...',
    'rows' => 1500
]);

// Security events
Simorgh::security('Suspicious activity detected', [
    'type' => 'multiple_failed_logins',
    'ip' => request()->ip(),
    'user_agent' => request()->userAgent()
]);

// Exception logging
try {
    // Some risky operation
} catch (\Exception $e) {
    Simorgh::exception($e, [
        'context' => 'Payment processing',
        'user_id' => auth()->id()
    ]);
}
```

## 📊 Dashboard

Access the dashboard at `/simorgh-logger` (or your configured prefix).

### Features:
- Real-time log monitoring
- Advanced filtering and search
- Log statistics and charts
- Alert management
- Export functionality
- Settings configuration

### Dashboard Routes:
- `/simorgh-logger` - Main dashboard
- `/simorgh-logger/logs` - Log browser
- `/simorgh-logger/stats` - Statistics
- `/simorgh-logger/alerts` - Alert management
- `/simorgh-logger/settings` - Configuration

## 🚨 Alerts Configuration

### Email Alerts

```php
// config/simorgh-logger.php
'alerts' => [
    'enabled' => true,
    'channels' => [
        'email' => [
            'enabled' => true,
            'to' => 'admin@example.com',
            'subject_prefix' => '[Simorgh Alert]',
        ],
    ],
    'thresholds' => [
        'critical' => [
            'count' => 5,
            'time_window' => '1 hour',
        ],
    ],
],
```

### Slack Alerts

```php
'alerts' => [
    'channels' => [
        'slack' => [
            'enabled' => true,
            'webhook' => 'https://hooks.slack.com/services/...',
            'channel' => '#alerts',
        ],
    ],
],
```

### Telegram Alerts

```php
'alerts' => [
    'channels' => [
        'telegram' => [
            'enabled' => true,
            'bot_token' => '123456789:ABCdefGHIjklMNOpqrsTUVwxyz',
            'chat_id' => '-123456789',
        ],
    ],
],
```

## 🔧 Configuration

### Environment Variables

```env
# Enable/disable Simorgh Logger
SIMORGH_LOGGER_ENABLED=true

# Storage driver (database, file, sentry, elasticsearch)
LOG_STORAGE_DRIVER=database

# Alert settings
LOG_ALERTS_ENABLED=true
LOG_ALERT_EMAIL_ENABLED=true
LOG_ALERT_EMAIL_TO=admin@example.com
LOG_ALERT_SLACK_ENABLED=true
LOG_ALERT_SLACK_WEBHOOK=https://hooks.slack.com/services/...

# Dashboard settings
LOG_DASHBOARD_ENABLED=true
LOG_DASHBOARD_REALTIME=true

# Auto-logging
LOG_AUTO_REQUESTS=true
LOG_AUTO_MODELS=false
LOG_AUTO_QUERIES=false

# Performance
LOG_USE_QUEUE=false
LOG_RETENTION_ENABLED=true
LOG_RETENTION_DAYS=30
```

### Auto-Logging Middleware

Add to your `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \AFM\SimorghLogger\Middleware\LogRequestsMiddleware::class,
];
```

## 📈 Statistics & Analytics

```php
// Get comprehensive stats
$stats = Simorgh::getStats();

// Get stats with filters
$stats = Simorgh::getStats([
    'level' => ['error', 'critical'],
    'date_from' => '2025-01-01',
    'date_to' => '2025-01-31'
]);

// Get logs with pagination
$logs = Simorgh::getLogs([
    'category' => 'auth',
    'search' => 'login'
], 50);
```

## 📤 Export & Cleanup

### Export Logs

```php
// Export as JSON
$json = Simorgh::exportLogs(['level' => 'error'], 'json');

// Export as CSV
$csv = Simorgh::exportLogs(['category' => 'auth'], 'csv');

// Export as XML
$xml = Simorgh::exportLogs(['date_from' => '2025-01-01'], 'xml');
```

### Cleanup Commands

```bash
# Cleanup logs older than 30 days
php artisan logs:cleanup --days=30

# Cleanup only error logs
php artisan logs:cleanup --level=error

# Dry run (see what would be deleted)
php artisan logs:cleanup --dry-run

# Compress before deletion
php artisan logs:cleanup --compress
```

## 🔒 Security Features

### Automatic Sanitization

The package automatically sanitizes sensitive data:

```php
// These will be automatically masked
Simorgh::info('User data', [
    'password' => 'secret123',        // → '[REDACTED]'
    'api_token' => 'abc123',         // → '[REDACTED]'
    'credit_card' => '4111111111111111', // → '[REDACTED]'
    'ssn' => '123-45-6789',          // → '[REDACTED]'
]);
```

### Custom Sanitization Patterns

```php
// config/simorgh-logger.php
'security' => [
    'sensitive_patterns' => [
        '/password/i',
        '/token/i',
        '/secret/i',
        '/key/i',
        '/credit_card/i',
        '/ssn/i',
        '/social_security/i',
        '/my_custom_field/i',  // Add your own patterns
    ],
    'mask_replacement' => '[REDACTED]',
],
```

## 🎯 Categories

Predefined categories for better organization:

- `auth` - Authentication & Authorization
- `api` - API Requests & Responses
- `payments` - Payment Processing
- `database` - Database Operations
- `mail` - Email Operations
- `queue` - Queue Processing
- `cache` - Cache Operations
- `file` - File Operations
- `security` - Security Events
- `performance` - Performance Monitoring
- `debug` - Debug Information

## 🔌 Integrations

### Sentry Integration

```php
'integrations' => [
    'sentry' => [
        'enabled' => true,
        'dsn' => env('SENTRY_LARAVEL_DSN'),
    ],
],
```

### Elasticsearch Integration

```php
'integrations' => [
    'elasticsearch' => [
        'enabled' => true,
        'host' => env('ELASTICSEARCH_HOST', 'localhost:9200'),
        'index' => env('ELASTICSEARCH_LOG_INDEX', 'laravel-logs'),
    ],
],
```

## 🧪 Testing

```bash
# Run tests
composer test

# Run with coverage
composer test-coverage
```

### Testing in Your Application

```php
// Test logging
Simorgh::shouldReceive('error')->once()->with('Test message', []);
Simorgh::error('Test message', []);

// Test alerts
$alertHandler = app(\AFM\SimorghLogger\Handlers\AlertHandler::class);
$results = $alertHandler->testChannels();
```

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🐛 Bug Reports

If you discover a security vulnerability, please send an email to [your-email@example.com](mailto:your-email@example.com). All security vulnerabilities will be promptly addressed.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## 🙏 Credits

- [Falahati Ali](https://github.com/falahatiali) - Creator and maintainer
- [All Contributors](https://github.com/falahatiali/laravel-advance-log-monitoring/contributors) - Contributors

## 📚 Additional Resources

- [Installation Guide](INSTALLATION.md) - Complete setup guide
- [Laravel Logging Documentation](https://laravel.com/docs/logging)
- [Monolog Documentation](https://github.com/Seldaek/monolog)
- [Laravel Package Development](https://laravel.com/docs/packages)

---

**Made with ❤️ and 🦅 for the Laravel community**

> *"Just as the mythical Simorgh watches over all birds under its wings, this package watches over and protects all your application logs."*