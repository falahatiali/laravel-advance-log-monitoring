#!/bin/bash

# Simorgh Logger - Quick Demo Setup Script
# This script creates a demo Laravel application with Simorgh Logger

set -e

echo "🦅 Simorgh Logger - Demo Setup"
echo "================================"
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo -e "${BLUE}📌 Detected PHP version: $PHP_VERSION${NC}"

if ! php -r "exit(version_compare(PHP_VERSION, '8.1.0', '>=') ? 0 : 1);"; then
    echo -e "${YELLOW}⚠️  Warning: PHP 8.1+ required. You have $PHP_VERSION${NC}"
    exit 1
fi

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install composer first."
    echo "Visit: https://getcomposer.org/"
    exit 1
fi

echo -e "${GREEN}✅ Prerequisites check passed${NC}"
echo ""

# Create demo directory
DEMO_DIR="demo-app"

if [ -d "$DEMO_DIR" ]; then
    echo -e "${YELLOW}⚠️  Demo directory already exists${NC}"
    read -p "Do you want to delete it and start fresh? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        rm -rf "$DEMO_DIR"
        echo -e "${GREEN}✅ Cleaned up old demo${NC}"
    else
        echo "❌ Aborted"
        exit 1
    fi
fi

echo ""
echo "📦 Step 1: Creating fresh Laravel application..."
composer create-project laravel/laravel "$DEMO_DIR" --prefer-dist --quiet
cd "$DEMO_DIR"

echo ""
echo "📦 Step 2: Adding Simorgh Logger package..."

# Add local repository
composer config repositories.simorgh-logger path ../ --quiet

# Require the package
composer require falahatiali/simorgh-logger:@dev --quiet

echo ""
echo "⚙️  Step 3: Configuring environment..."

# Setup SQLite database
touch database/database.sqlite

# Configure .env
cat > .env << 'EOF'
APP_NAME="Simorgh Logger Demo"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=sqlite

ADVANCED_LOGGER_ENABLED=true
LOG_STORAGE_DRIVER=database
LOG_DASHBOARD_ENABLED=true
LOG_DASHBOARD_PREFIX=logs

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

BROADCAST_DRIVER=log
FILESYSTEM_DISK=local
EOF

# Generate app key
php artisan key:generate --quiet

echo ""
echo "🗄️  Step 4: Setting up database..."
php artisan migrate --quiet

echo ""
echo "📝 Step 5: Publishing package configuration..."
php artisan vendor:publish --provider="Simorgh\Logger\SimorghLoggerServiceProvider" --tag="simorgh-logger-config" --quiet

# Update config to disable auth for demo
cat > config/advanced-logger.php << 'EOF'
<?php

return [
    'enabled' => env('ADVANCED_LOGGER_ENABLED', true),
    
    'storage' => [
        'driver' => env('LOG_STORAGE_DRIVER', 'database'),
        'table' => env('LOG_STORAGE_TABLE', 'advanced_logs'),
    ],
    
    'dashboard' => [
        'enabled' => env('LOG_DASHBOARD_ENABLED', true),
        'prefix' => env('LOG_DASHBOARD_PREFIX', 'logs'),
        'middleware' => ['web'], // No auth for demo
        'pagination' => 50,
        'real_time' => [
            'enabled' => true,
            'refresh_interval' => 5000,
        ],
    ],
    
    'database' => [
        'connection' => null,
        'table' => 'advanced_logs',
    ],
    
    'alerts' => [
        'enabled' => false,
    ],
    
    'retention' => [
        'enabled' => true,
        'days' => [
            'local' => 7,
            'staging' => 14,
            'production' => 30,
        ],
        'cleanup_schedule' => '0 2 * * *',
    ],
    
    'categories' => [
        'auth' => 'Authentication',
        'api' => 'API',
        'payments' => 'Payments',
        'security' => 'Security',
        'database' => 'Database',
    ],
    
    'security' => [
        'sanitize_sensitive_data' => true,
        'sensitive_patterns' => [
            '/password/i',
            '/token/i',
            '/secret/i',
            '/key/i',
        ],
        'mask_replacement' => '[REDACTED]',
    ],
];
EOF

echo ""
echo "🎲 Step 6: Generating demo logs..."

# Create seeder
mkdir -p database/seeders
cat > database/seeders/DemoLogsSeeder.php << 'EOF'
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

        // Info logs
        for ($i = 0; $i < 50; $i++) {
            Simorgh::info('Application started successfully', [
                'version' => '1.0.0',
                'environment' => 'demo',
                'server' => 'web-' . rand(1, 5),
            ]);
        }

        // Auth warnings
        for ($i = 0; $i < 30; $i++) {
            Simorgh::category('auth')
                ->warning('Failed login attempt', [
                    'email' => 'user' . rand(1, 100) . '@example.com',
                    'ip' => '192.168.1.' . rand(1, 255),
                    'attempts' => rand(1, 5),
                ]);
        }

        // API errors
        for ($i = 0; $i < 20; $i++) {
            Simorgh::category('api')
                ->error('API rate limit exceeded', [
                    'endpoint' => '/api/users',
                    'limit' => 100,
                    'current' => rand(101, 200),
                    'reset_in' => rand(60, 3600) . 's',
                ]);
        }

        // Payment critical errors
        for ($i = 0; $i < 15; $i++) {
            Simorgh::category('payments')
                ->critical('Payment processing failed', [
                    'amount' => rand(10, 1000),
                    'currency' => 'USD',
                    'gateway' => 'stripe',
                    'error_code' => ['CARD_DECLINED', 'INSUFFICIENT_FUNDS', 'EXPIRED_CARD'][rand(0, 2)],
                ]);
        }

        // Security warnings
        for ($i = 0; $i < 25; $i++) {
            Simorgh::category('security')
                ->warning('Suspicious activity detected', [
                    'type' => 'brute_force_attempt',
                    'ip' => '203.0.113.' . rand(1, 255),
                    'country' => ['US', 'RU', 'CN', 'BR'][rand(0, 3)],
                ]);
        }

        // Successful API calls
        for ($i = 0; $i < 40; $i++) {
            Simorgh::category('api')
                ->info('API request successful', [
                    'endpoint' => ['/api/products', '/api/users', '/api/orders'][rand(0, 2)],
                    'method' => 'GET',
                    'response_time' => rand(50, 500) . 'ms',
                    'status' => 200,
                ]);
        }

        // Database operations
        for ($i = 0; $i < 20; $i++) {
            Simorgh::category('database')
                ->info('Database query executed', [
                    'query' => 'SELECT * FROM users WHERE...',
                    'time' => rand(10, 500) . 'ms',
                    'rows' => rand(1, 1000),
                ]);
        }

        echo "✅ Demo logs generated successfully!\n";
        echo "📊 Total: 200 log entries created\n";
    }
}
EOF

# Run seeder
php artisan db:seed --class=DemoLogsSeeder

echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✅ Demo setup completed!${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo "🚀 To start the demo:"
echo ""
echo -e "   ${BLUE}cd $DEMO_DIR${NC}"
echo -e "   ${BLUE}php artisan serve${NC}"
echo ""
echo "Then visit: ${GREEN}http://localhost:8000/logs${NC}"
echo ""
echo "📍 Demo Routes:"
echo "   • http://localhost:8000/logs          - Main Dashboard"
echo "   • http://localhost:8000/logs/logs     - All Logs"
echo "   • http://localhost:8000/logs/stats    - Statistics"
echo "   • http://localhost:8000/logs/alerts   - Alerts"
echo ""
echo "🔄 To generate more logs:"
echo "   php artisan db:seed --class=DemoLogsSeeder"
echo ""
echo "🧹 To cleanup old logs:"
echo "   php artisan logs:cleanup --dry-run"
echo ""
echo "🗑️  To reset everything:"
echo "   php artisan migrate:fresh"
echo "   php artisan db:seed --class=DemoLogsSeeder"
echo ""
echo -e "${YELLOW}⚠️  Note: Authentication is disabled for demo purposes${NC}"
echo ""

