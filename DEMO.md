# 🎮 Demo Guide - Simorgh Logger

This guide shows you how to run a live demo of Simorgh Logger without installing it into your Laravel project.

## 🚀 Quick Start (3 Methods)

### Method 1: One-Command Setup (Recommended)

```bash
# Clone and setup in one go
git clone https://github.com/falahatiali/laravel-advance-log-monitoring.git
cd laravel-advance-log-monitoring
chmod +x demo.sh
./demo.sh
```

The script will:
1. Create a fresh Laravel installation
2. Install Simorgh Logger from local package
3. Generate sample logs
4. Start development server

Then visit: **http://localhost:8000/logs**

---

### Method 2: Docker Setup (Easiest - No PHP Required)

If you have Docker installed:

```bash
# Clone the repository
git clone https://github.com/falahatiali/laravel-advance-log-monitoring.git
cd laravel-advance-log-monitoring

# Run with Docker
docker-compose up -d

# Install dependencies
docker-compose exec app composer install

# Setup database and generate demo data
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed --class=DemoLogsSeeder
```

Visit: **http://localhost:8080/logs**

Stop demo: `docker-compose down`

---

### Method 3: Manual Setup

**Requirements:**
- PHP 8.1+
- Composer
- SQLite (or MySQL/PostgreSQL)

**Step 1: Clone Repository**
```bash
git clone https://github.com/falahatiali/laravel-advance-log-monitoring.git
cd laravel-advance-log-monitoring
```

**Step 2: Install Dependencies**
```bash
composer install
```

**Step 3: Create Demo Laravel App**
```bash
# Create a fresh Laravel app in demo-app directory
composer create-project laravel/laravel demo-app
cd demo-app
```

**Step 4: Link Package Locally**
```bash
# Add local package to composer.json
composer config repositories.simorgh-logger path ../

# Require the package
composer require falahatiali/simorgh-logger:@dev
```

**Step 5: Configure Environment**
```bash
cp .env.example .env
php artisan key:generate

# Configure for SQLite (easiest for demo)
# Edit .env and set:
# DB_CONNECTION=sqlite
# Remove DB_DATABASE line or set to: DB_DATABASE=/absolute/path/database/database.sqlite
```

**Step 6: Setup Database**
```bash
# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Publish package assets
php artisan vendor:publish --provider="Simorgh\Logger\SimorghLoggerServiceProvider"
```

**Step 7: Configure Package**

Edit `config/advanced-logger.php`:
```php
'dashboard' => [
    'enabled' => true,
    'prefix' => 'logs',
    'middleware' => ['web'], // No auth for demo
    'pagination' => 50,
],
```

**Step 8: Generate Demo Logs**

Create `database/seeders/DemoLogsSeeder.php`:
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Simorgh\Logger\Facades\Simorgh;
use Carbon\Carbon;

class DemoLogsSeeder extends Seeder
{
    public function run(): void
    {
        echo "Generating demo logs...\n";

        // Generate various log levels
        for ($i = 0; $i < 50; $i++) {
            Simorgh::info('Application started successfully', [
                'version' => '1.0.0',
                'environment' => 'demo',
            ]);
        }

        for ($i = 0; $i < 30; $i++) {
            Simorgh::category('auth')
                ->warning('Failed login attempt', [
                    'email' => 'demo@example.com',
                    'ip' => '192.168.1.' . rand(1, 255),
                ]);
        }

        for ($i = 0; $i < 20; $i++) {
            Simorgh::category('api')
                ->error('API rate limit exceeded', [
                    'endpoint' => '/api/users',
                    'limit' => 100,
                    'current' => rand(101, 200),
                ]);
        }

        for ($i = 0; $i < 15; $i++) {
            Simorgh::category('payments')
                ->critical('Payment processing failed', [
                    'amount' => rand(10, 1000),
                    'gateway' => 'stripe',
                    'error_code' => 'CARD_DECLINED',
                ]);
        }

        for ($i = 0; $i < 25; $i++) {
            Simorgh::category('security')
                ->warning('Suspicious activity detected', [
                    'type' => 'multiple_failed_logins',
                    'ip' => '203.0.113.' . rand(1, 255),
                ]);
        }

        for ($i = 0; $i < 40; $i++) {
            Simorgh::category('api')
                ->info('API request successful', [
                    'endpoint' => '/api/products',
                    'method' => 'GET',
                    'response_time' => rand(50, 500) . 'ms',
                ]);
        }

        echo "Demo logs generated successfully!\n";
    }
}
```

Run seeder:
```bash
php artisan db:seed --class=DemoLogsSeeder
```

**Step 9: Start Server**
```bash
php artisan serve
```

**Step 10: Access Dashboard**

Visit: **http://localhost:8000/logs**

You should see:
- Main Dashboard with statistics
- Logs Browser with filters
- Statistics & Analytics
- Alert Configuration

---

## 🎯 Demo Routes

| URL | Description |
|-----|-------------|
| `/logs` | Main dashboard |
| `/logs/logs` | All logs browser |
| `/logs/stats` | Statistics & analytics |
| `/logs/alerts` | Alert configuration |
| `/logs/settings` | Settings page |

---

## 📊 Generate More Demo Data

You can generate more logs programmatically:

```php
// In tinker: php artisan tinker
use Simorgh\Logger\Facades\Simorgh;

// Generate 100 random logs
for ($i = 0; $i < 100; $i++) {
    $levels = ['info', 'warning', 'error', 'critical'];
    $categories = ['auth', 'api', 'payments', 'security'];
    
    $level = $levels[array_rand($levels)];
    $category = $categories[array_rand($categories)];
    
    Simorgh::category($category)
        ->{$level}('Demo log entry #' . $i, [
            'demo' => true,
            'timestamp' => now(),
            'random_data' => rand(1, 1000),
        ]);
}
```

---

## 🧪 Test Features

### 1. View Different Log Levels
- Navigate to `/logs/logs`
- Use the level filter to view: Info, Warning, Error, Critical

### 2. Filter by Category
- Select category: auth, api, payments, security
- See categorized logs

### 3. Search Logs
- Use the search box to find specific messages
- Try searching: "failed", "API", "payment"

### 4. Export Logs
- Click "Export JSON" to download logs
- Try CSV and XML export options

### 5. View Statistics
- Navigate to `/logs/stats`
- See distribution by level and category
- View logs trend chart

### 6. Resolve/Delete Logs
- In logs browser, click "Resolve" on any log
- Try deleting a log entry

### 7. Test Cleanup Command
```bash
# See what would be deleted (dry run)
php artisan logs:cleanup --days=30 --dry-run

# Actually cleanup old logs
php artisan logs:cleanup --days=30
```

---

## 🎨 Demo Customization

### Change Dashboard URL
Edit `config/advanced-logger.php`:
```php
'dashboard' => [
    'prefix' => 'admin/logs',  // Changes URL to /admin/logs
],
```

### Enable Authentication
```php
'dashboard' => [
    'middleware' => ['web', 'auth'],  // Requires login
],
```

### Change Retention Period
```php
'retention' => [
    'days' => [
        'local' => 3,  // Keep only 3 days in demo
    ],
],
```

---

## 🐳 Docker Configuration

The `docker-compose.yml` includes:
- PHP 8.2 with required extensions
- MySQL 8.0
- Nginx web server
- Automatic package installation

To customize, edit `docker-compose.yml` in the repository.

---

## 🔄 Reset Demo

To start fresh:

```bash
# Drop all tables and recreate
php artisan migrate:fresh

# Generate new demo data
php artisan db:seed --class=DemoLogsSeeder
```

---

## 📝 Demo Notes

- **No authentication** by default - anyone can access
- **SQLite database** - easy setup, no server required
- **Sample data** - pre-generated logs for testing
- **All features enabled** - test everything

---

## 🎬 Live Demo

Want to see it live without installing? 

**Coming soon:** Online demo at `https://demo-simorgh-logger.example.com`

---

## 🆘 Demo Troubleshooting

### Issue: Dashboard shows 404
**Solution:** Check `config/advanced-logger.php` - ensure dashboard is enabled and prefix is correct.

### Issue: No logs appearing
**Solution:** Run the seeder: `php artisan db:seed --class=DemoLogsSeeder`

### Issue: Permission denied
**Solution:** Ensure storage directory is writable: `chmod -R 775 storage`

### Issue: Database error
**Solution:** Run migrations: `php artisan migrate`

---

## 📚 After Demo

Once you've tested the demo and like what you see:

1. **Install in your project:**
   ```bash
   composer require falahatiali/simorgh-logger
   ```

2. **Follow the [Installation Guide](INSTALLATION.md)**

3. **Check [Integration Guide](INTEGRATION.md)** for production setup

4. **Configure authentication and permissions** for security

---

**Enjoy the demo! 🎉**

Questions? Check our [README](README.md) or create an issue on GitHub.

