# Installation Guide - Advanced Logger

This guide will walk you through the complete installation and setup process for the Advanced Logger package.

## 📋 Prerequisites

- PHP 8.1 or higher
- Laravel 10.0 or higher
- Composer
- Database (MySQL, PostgreSQL, SQLite, etc.)

## 🚀 Step-by-Step Installation

### Step 1: Install the Package

```bash
composer require afm/advanced-logger
```

### Step 2: Publish Configuration

Publish the configuration file to customize the package settings:

```bash
php artisan vendor:publish --provider="AFM\AdvancedLogger\AdvancedLoggerServiceProvider" --tag="advanced-logger-config"
```

This will create `config/advanced-logger.php` in your Laravel application.

### Step 3: Publish Migrations

Publish the database migrations:

```bash
php artisan vendor:publish --provider="AFM\AdvancedLogger\AdvancedLoggerServiceProvider" --tag="advanced-logger-migrations"
```

### Step 4: Run Migrations

Create the necessary database tables:

```bash
php artisan migrate
```

This will create the `advanced_logs` table in your database.

### Step 5: Configure Environment Variables

Add these variables to your `.env` file:

```env
# Advanced Logger Configuration
ADVANCED_LOGGER_ENABLED=true
LOG_STORAGE_DRIVER=database
LOG_DASHBOARD_ENABLED=true

# Alert Configuration (Optional)
LOG_ALERTS_ENABLED=true
LOG_ALERT_EMAIL_ENABLED=false
LOG_ALERT_EMAIL_TO=admin@example.com
LOG_ALERT_SLACK_ENABLED=false
LOG_ALERT_SLACK_WEBHOOK=https://hooks.slack.com/services/...
LOG_ALERT_TELEGRAM_ENABLED=false
LOG_ALERT_TELEGRAM_BOT_TOKEN=your_bot_token
LOG_ALERT_TELEGRAM_CHAT_ID=your_chat_id

# Auto-logging (Optional)
LOG_AUTO_REQUESTS=true
LOG_AUTO_MODELS=false
LOG_AUTO_QUERIES=false

# Performance Settings
LOG_USE_QUEUE=false
LOG_RETENTION_ENABLED=true
LOG_RETENTION_DAYS=30
```

### Step 6: Configure Middleware (Optional)

If you want to automatically log HTTP requests, add the middleware to your `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ... other middleware
    \AFM\AdvancedLogger\Middleware\LogRequestsMiddleware::class,
];
```

Or apply it to specific routes:

```php
Route::middleware(['auth', 'log.requests'])->group(function () {
    // Your protected routes
});
```

### Step 7: Customize Route & Middleware (Optional)

If you want to integrate the dashboard into your existing admin panel with custom routes and permissions, configure the prefix and middleware in your `.env`:

```env
# Custom route prefix (default: advanced-logger)
LOG_DASHBOARD_PREFIX=admin/dashboard/logs/panel

# Or edit config/advanced-logger.php
```

```php
// config/advanced-logger.php
'dashboard' => [
    'enabled' => true,
    'prefix' => 'admin/dashboard/logs/panel', // Your custom prefix
    'middleware' => ['web', 'auth', 'role:admin'], // Your middleware
    // ...
],
```

### Step 8: Access the Dashboard

Visit the dashboard URL based on your configuration:
- Default: `/advanced-logger`
- Custom: `/admin/dashboard/logs/panel` (based on your prefix)

**Note**: The dashboard is protected by authentication middleware by default. Make sure you're logged in and have the required permissions.

## ⚙️ Configuration Options

### Basic Configuration

Edit `config/advanced-logger.php` to customize the package:

```php
return [
    'enabled' => env('ADVANCED_LOGGER_ENABLED', true),
    
    'storage' => [
        'driver' => env('LOG_STORAGE_DRIVER', 'database'),
        'table' => 'advanced_logs',
    ],
    
    'dashboard' => [
        'enabled' => env('LOG_DASHBOARD_ENABLED', true),
        'middleware' => ['auth'], // Add custom middleware as needed
        'pagination' => 50,
    ],
    
    // ... other options
];
```

### Alert Configuration

Configure alerts for critical events:

```php
'alerts' => [
    'enabled' => env('LOG_ALERTS_ENABLED', true),
    
    'thresholds' => [
        'critical' => [
            'count' => 5,
            'time_window' => '1 hour',
        ],
        'error' => [
            'count' => 20,
            'time_window' => '1 hour',
        ],
    ],
    
    'channels' => [
        'email' => [
            'enabled' => env('LOG_ALERT_EMAIL_ENABLED', false),
            'to' => env('LOG_ALERT_EMAIL_TO'),
        ],
        'slack' => [
            'enabled' => env('LOG_ALERT_SLACK_ENABLED', false),
            'webhook' => env('LOG_ALERT_SLACK_WEBHOOK'),
            'channel' => '#alerts',
        ],
    ],
],
```

## 🔧 Advanced Setup

### Integration with Existing Admin Panel

If you have an existing admin panel with role/permission system, you can easily integrate Simorgh Logger:

**Example 1: Using Spatie Laravel Permission**
```php
// config/advanced-logger.php
'dashboard' => [
    'prefix' => 'admin/logs',
    'middleware' => ['web', 'auth', 'role:admin'],
],
```

**Example 2: Using Custom Permission Middleware**
```php
// config/advanced-logger.php
'dashboard' => [
    'prefix' => 'admin/dashboard/logs/panel',
    'middleware' => ['web', 'auth', 'can:view-logs'],
],
```

**Example 3: Using Multiple Roles**
```php
// config/advanced-logger.php
'dashboard' => [
    'prefix' => 'panel/logs',
    'middleware' => ['web', 'auth', 'role:admin|developer|support'],
],
```

**Example 4: Nested Admin Routes**
```php
// config/advanced-logger.php
'dashboard' => [
    'prefix' => 'admin/system/monitoring/logs',
    'middleware' => ['web', 'auth', 'admin.access', 'permission:system.logs'],
],
```

This gives you:
- `mysite.com/admin/system/monitoring/logs` - Main dashboard
- `mysite.com/admin/system/monitoring/logs/logs` - All logs
- `mysite.com/admin/system/monitoring/logs/stats` - Statistics
- `mysite.com/admin/system/monitoring/logs/alerts` - Alerts

### Custom Middleware

Create custom middleware for specific logging needs:

```php
<?php

namespace App\Http\Middleware;

use AFM\AdvancedLogger\Facades\Logger;
use Closure;

class CustomLoggingMiddleware
{
    public function handle($request, Closure $next)
    {
        Logger::category('custom')
            ->info('Custom middleware executed', [
                'route' => $request->route()->getName(),
                'user_id' => auth()->id(),
            ]);

        return $next($request);
    }
}
```

### Model Observing

To automatically log model changes, add observers:

```php
<?php

namespace App\Providers;

use App\Models\User;
use AFM\AdvancedLogger\Observers\LogModelObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        User::observe(LogModelObserver::class);
    }
}
```

### Queue Configuration

For high-traffic applications, enable queue processing:

```env
LOG_USE_QUEUE=true
LOG_QUEUE_NAME=logs
```

Then run the queue worker:

```bash
php artisan queue:work --queue=logs
```

## 🧪 Testing the Installation

### Basic Test

Create a simple test to verify the installation:

```php
<?php

use AFM\AdvancedLogger\Facades\Logger;

// Test basic logging
Logger::info('Installation test successful');

// Test categorized logging
Logger::category('test')
    ->warning('Test warning', ['test' => true]);

// Test exception logging
try {
    throw new \Exception('Test exception');
} catch (\Exception $e) {
    Logger::exception($e, ['context' => 'installation test']);
}
```

### Dashboard Test

1. Visit `/advanced-logger` in your browser
2. Check if the dashboard loads correctly
3. Verify that your test logs appear in the dashboard

### Alert Test

Test alert functionality:

```php
// This will trigger an alert if configured
for ($i = 0; $i < 6; $i++) {
    Logger::critical('Test critical error #' . $i);
}
```

## 🚨 Troubleshooting

### Common Issues

#### 1. Dashboard Not Accessible

**Problem**: Getting 404 or permission denied when accessing `/advanced-logger`

**Solution**: 
- Check if you're logged in
- Verify middleware configuration in `config/advanced-logger.php`
- Clear route cache: `php artisan route:clear`

#### 2. Database Errors

**Problem**: Migration fails or table doesn't exist

**Solution**:
- Check database connection
- Run migrations: `php artisan migrate`
- Check migration file exists in `database/migrations/`

#### 3. Logs Not Appearing

**Problem**: Logs are not being stored

**Solution**:
- Check `ADVANCED_LOGGER_ENABLED=true` in `.env`
- Verify database connection
- Check `LOG_STORAGE_DRIVER=database` in `.env`

#### 4. Alerts Not Working

**Problem**: Alerts are not being sent

**Solution**:
- Verify alert configuration in `.env`
- Test alert channels manually
- Check queue processing if using queues

### Debug Mode

Enable debug mode to troubleshoot issues:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Log Files

Check Laravel's default log files for errors:

```bash
tail -f storage/logs/laravel.log
```

## 🗑️ Automatic Log Cleanup

The package automatically cleans up old logs - **no manual setup needed!**

### How It Works

The cleanup runs automatically based on your configuration:
- **Local environment**: Deletes logs older than 7 days
- **Staging environment**: Deletes logs older than 14 days  
- **Production environment**: Deletes logs older than 30 days
- **Default schedule**: Daily at 2:00 AM

### Configuration

```php
// config/advanced-logger.php
'retention' => [
    'enabled' => true,
    'days' => [
        'local' => 7,
        'staging' => 14,
        'production' => 30,
    ],
    'compress_before_delete' => true,
    'cleanup_schedule' => '0 2 * * *', // Cron expression
],
```

### Requirements

Make sure Laravel's task scheduler is running. Add this to your crontab:

```bash
# Edit crontab
crontab -e

# Add this line
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Manual Cleanup

You can also run cleanup manually:

```bash
# Use environment defaults
php artisan logs:cleanup

# Custom retention period
php artisan logs:cleanup --days=30

# Test first (dry run)
php artisan logs:cleanup --days=30 --dry-run

# Compress before deletion
php artisan logs:cleanup --days=30 --compress
```

## 📚 Next Steps

After successful installation:

1. **Configure Alerts**: Set up email, Slack, or Telegram alerts
2. **Customize Categories**: Add your own log categories
3. **Verify Cleanup**: Test automatic cleanup with `php artisan logs:cleanup --dry-run`
4. **Monitor Performance**: Use the dashboard to monitor your application
5. **Export Data**: Use export features for log analysis

## 🆘 Support

If you encounter issues:

1. Check this installation guide
2. Review the [README.md](README.md) for usage examples
3. Check [CHANGELOG.md](CHANGELOG.md) for known issues
4. Create an issue on GitHub with:
   - Laravel version
   - PHP version
   - Error messages
   - Configuration details

## ✅ Verification Checklist

- [ ] Package installed via Composer
- [ ] Configuration published
- [ ] Migrations run successfully
- [ ] Environment variables configured
- [ ] Dashboard accessible at `/advanced-logger`
- [ ] Basic logging works
- [ ] Alerts configured (if needed)
- [ ] Cleanup command works: `php artisan logs:cleanup --dry-run`

---

**Congratulations!** Your Advanced Logger package is now installed and ready to use. 🎉
