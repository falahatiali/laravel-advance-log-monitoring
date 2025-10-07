# Integration Guide - Simorgh Logger

This guide shows you how to integrate Simorgh Logger into your existing Laravel application with custom admin panel and authentication system.

## 🎯 Integration Scenarios

### Scenario 1: Existing Admin Panel Integration

If you already have an admin panel at `mysite.com/admin/*`, you can integrate Simorgh Logger seamlessly.

#### Step 1: Configure Custom Route Prefix

Add to your `.env` file:
```env
LOG_DASHBOARD_PREFIX=admin/logs
```

Or edit `config/advanced-logger.php`:
```php
'dashboard' => [
    'enabled' => true,
    'prefix' => 'admin/logs',
    'middleware' => ['web', 'auth', 'admin'], // Use your admin middleware
],
```

#### Step 2: Access Your Logs
Now access the dashboard at:
- `mysite.com/admin/logs` - Main dashboard
- `mysite.com/admin/logs/logs` - All logs
- `mysite.com/admin/logs/stats` - Statistics

---

### Scenario 2: Role-Based Access Control

If you're using **Spatie Laravel Permission** or similar package:

```php
// config/advanced-logger.php
'dashboard' => [
    'prefix' => 'admin/monitoring/logs',
    'middleware' => ['web', 'auth', 'role:admin|developer'],
],
```

Only users with `admin` or `developer` role can access the dashboard.

---

### Scenario 3: Permission-Based Access

Using specific permissions:

```php
// config/advanced-logger.php
'dashboard' => [
    'prefix' => 'admin/system/logs',
    'middleware' => ['web', 'auth', 'permission:view-system-logs'],
],
```

Only users with `view-system-logs` permission can access.

---

### Scenario 4: Nested Admin Routes

For complex admin structures:

```php
// config/advanced-logger.php
'dashboard' => [
    'prefix' => 'admin/dashboard/monitoring/logs/panel',
    'middleware' => [
        'web', 
        'auth', 
        'admin.verified',
        'role:super-admin|admin',
        'permission:logs.view'
    ],
],
```

Access at: `mysite.com/admin/dashboard/monitoring/logs/panel`

---

### Scenario 5: Multi-Guard Authentication

If you have separate admin guard:

```php
// config/advanced-logger.php
'dashboard' => [
    'prefix' => 'admin/logs',
    'middleware' => ['web', 'auth:admin', 'role:admin'],
],
```

This uses the `admin` guard instead of default `web` guard.

---

## 🔐 Custom Middleware Examples

### Example 1: IP Restriction

Create middleware to restrict access by IP:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class RestrictLogsDashboard
{
    protected $allowedIps = [
        '127.0.0.1',
        '192.168.1.100',
    ];

    public function handle($request, Closure $next): Response
    {
        if (!in_array($request->ip(), $this->allowedIps)) {
            abort(403, 'Access denied to logs dashboard');
        }

        return $next($request);
    }
}
```

Register and use:
```php
// app/Http/Kernel.php
protected $routeMiddleware = [
    'logs.restrict' => \App\Http\Middleware\RestrictLogsDashboard::class,
];

// config/advanced-logger.php
'dashboard' => [
    'middleware' => ['web', 'auth', 'logs.restrict'],
],
```

### Example 2: Time-Based Access

Restrict access to business hours:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class BusinessHoursOnly
{
    public function handle($request, Closure $next)
    {
        $now = Carbon::now();
        $isBusinessHours = $now->hour >= 9 && $now->hour < 18;
        $isWeekday = $now->isWeekday();

        if (!$isBusinessHours || !$isWeekday) {
            abort(403, 'Logs dashboard only accessible during business hours');
        }

        return $next($request);
    }
}
```

### Example 3: Audit Logging

Log who accesses the dashboard:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Simorgh\Logger\Facades\Simorgh;

class AuditLogsDashboardAccess
{
    public function handle($request, Closure $next)
    {
        Simorgh::category('security')
            ->info('Logs Dashboard Accessed', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()?->email,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

        return $next($request);
    }
}
```

---

## 🎨 Custom Admin Menu Integration

### Laravel Backpack Integration

```php
// In your admin menu config or sidebar
Menu::add([
    'text' => 'System Logs',
    'url'  => config('advanced-logger.dashboard.prefix', 'admin/logs'),
    'icon' => 'las la-file-alt',
    'permission' => 'view-logs',
]);
```

### Filament Integration

```php
// In your Filament admin panel
use Filament\Navigation\NavigationItem;

NavigationItem::make('System Logs')
    ->url(url(config('advanced-logger.dashboard.prefix')))
    ->icon('heroicon-o-clipboard-list')
    ->group('Monitoring')
    ->sort(5);
```

### Custom Sidebar

```html
<!-- resources/views/admin/layouts/sidebar.blade.php -->
<li class="nav-item">
    <a href="{{ url(config('advanced-logger.dashboard.prefix')) }}" class="nav-link">
        <i class="nav-icon fas fa-file-alt"></i>
        <p>System Logs</p>
    </a>
</li>
```

---

## 📊 Usage in Your Application

### Basic Logging

```php
use Simorgh\Logger\Facades\Simorgh;

// In your controllers
public function store(Request $request)
{
    try {
        $user = User::create($request->validated());
        
        Simorgh::category('auth')
            ->user(auth()->id())
            ->info('New user registered', [
                'new_user_id' => $user->id,
                'email' => $user->email,
            ]);
            
        return redirect()->route('users.index');
        
    } catch (\Exception $e) {
        Simorgh::category('auth')
            ->error('User registration failed', [
                'error' => $e->getMessage(),
                'input' => $request->except('password'),
            ]);
            
        return back()->withErrors(['error' => 'Registration failed']);
    }
}
```

### Payment Logging

```php
public function processPayment(Request $request)
{
    Simorgh::category('payments')
        ->user(auth()->id())
        ->info('Payment initiated', [
            'amount' => $request->amount,
            'method' => $request->payment_method,
            'order_id' => $request->order_id,
        ]);
        
    // Process payment...
    
    if ($paymentSuccess) {
        Simorgh::category('payments')
            ->info('Payment successful', [
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount,
            ]);
    } else {
        Simorgh::category('payments')
            ->error('Payment failed', [
                'reason' => $paymentGateway->getError(),
                'order_id' => $request->order_id,
            ]);
    }
}
```

### API Request Logging

```php
public function apiMiddleware($request, Closure $next)
{
    $startTime = microtime(true);
    
    $response = $next($request);
    
    $duration = microtime(true) - $startTime;
    
    Simorgh::category('api')
        ->info('API Request', [
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'status' => $response->status(),
            'duration' => round($duration * 1000, 2) . 'ms',
            'ip' => $request->ip(),
        ]);
        
    return $response;
}
```

---

## 🔧 Environment-Specific Configuration

### Development Environment

```php
// config/advanced-logger.php - or use .env
if (app()->environment('local')) {
    return [
        'dashboard' => [
            'prefix' => 'dev/logs',
            'middleware' => ['web'], // No auth in development
        ],
        'retention' => [
            'days' => 7, // Keep logs for 7 days only
        ],
    ];
}
```

### Staging Environment

```env
# .env.staging
LOG_DASHBOARD_PREFIX=staging/admin/logs
LOG_RETENTION_DAYS=14
LOG_ALERTS_ENABLED=false
```

### Production Environment

```env
# .env.production
LOG_DASHBOARD_PREFIX=admin/system/logs
LOG_RETENTION_DAYS=90
LOG_ALERTS_ENABLED=true
LOG_ALERT_EMAIL_ENABLED=true
LOG_ALERT_SLACK_ENABLED=true
LOG_USE_QUEUE=true
```

---

## 🚀 Performance Tips

### 1. Use Queue for High Traffic

```env
LOG_USE_QUEUE=true
LOG_QUEUE_NAME=logs
```

### 2. Exclude GET Requests

```php
'auto_logging' => [
    'requests' => [
        'enabled' => true,
        'exclude_methods' => ['GET', 'HEAD', 'OPTIONS'],
    ],
],
```

### 3. Index Your Database

Add indexes to `advanced_logs` table:
```php
Schema::table('advanced_logs', function (Blueprint $table) {
    $table->index(['level', 'created_at']);
    $table->index(['category', 'created_at']);
    $table->index(['is_resolved']);
});
```

---

## 📝 Complete Integration Example

Here's a complete example for a typical Laravel admin panel:

```php
// config/advanced-logger.php
return [
    'enabled' => env('ADVANCED_LOGGER_ENABLED', true),
    
    'dashboard' => [
        'enabled' => true,
        'prefix' => env('LOG_DASHBOARD_PREFIX', 'admin/dashboard/logs/panel'),
        'middleware' => [
            'web',
            'auth',
            'role:admin|developer', // Spatie permission
            'audit.access', // Custom audit middleware
        ],
        'pagination' => 50,
    ],
    
    'alerts' => [
        'enabled' => env('LOG_ALERTS_ENABLED', true),
        'channels' => [
            'email' => [
                'enabled' => env('LOG_ALERT_EMAIL_ENABLED', true),
                'to' => env('LOG_ALERT_EMAIL_TO', 'admin@example.com'),
            ],
            'slack' => [
                'enabled' => env('LOG_ALERT_SLACK_ENABLED', true),
                'webhook' => env('LOG_ALERT_SLACK_WEBHOOK'),
            ],
        ],
    ],
    
    'retention' => [
        'enabled' => true,
        'days' => env('LOG_RETENTION_DAYS', 30),
    ],
];
```

```env
# .env
LOG_DASHBOARD_PREFIX=admin/dashboard/logs/panel
LOG_ALERTS_ENABLED=true
LOG_ALERT_EMAIL_TO=admin@yoursite.com
LOG_ALERT_SLACK_WEBHOOK=https://hooks.slack.com/services/YOUR/WEBHOOK
LOG_RETENTION_DAYS=90
LOG_USE_QUEUE=true
```

Now your logs dashboard will be available at:
**`https://mysite.com/admin/dashboard/logs/panel`**

---

## ✅ Integration Checklist

- [ ] Configure custom route prefix
- [ ] Set up authentication middleware
- [ ] Add role/permission checks
- [ ] Integrate with admin menu
- [ ] Test access with different user roles
- [ ] Configure alerts for your team
- [ ] Set up automatic cleanup
- [ ] Enable queue for production
- [ ] Add custom categories for your app
- [ ] Test logging in key application flows

---

**Need help?** Check the [INSTALLATION.md](INSTALLATION.md) for basic setup or [README.md](README.md) for feature documentation.

