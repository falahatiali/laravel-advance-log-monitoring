#!/bin/bash

# Simorgh Logger - Docker Demo Setup Script

set -e

echo "🐳 Simorgh Logger - Docker Demo Setup"
echo "======================================"
echo ""

# Check if docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    echo "Visit: https://docs.docker.com/get-docker/"
    exit 1
fi

# Check if docker-compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

echo "✅ Docker and Docker Compose found"
echo ""

echo "🚀 Starting Docker containers..."
docker-compose up -d

echo ""
echo "⏳ Waiting for services to be ready..."
sleep 10

echo ""
echo "📦 Setting up Laravel application..."

# Create demo app if it doesn't exist
if [ ! -d "demo-docker" ]; then
    docker-compose exec -T app composer create-project laravel/laravel . --prefer-dist
fi

echo ""
echo "📝 Installing Simorgh Logger..."
docker-compose exec -T app composer config repositories.simorgh-logger path ../
docker-compose exec -T app composer require falahatiali/simorgh-logger:@dev

echo ""
echo "⚙️  Configuring environment..."
docker-compose exec -T app php artisan key:generate

echo ""
echo "🗄️  Running migrations..."
docker-compose exec -T app php artisan migrate --force

echo ""
echo "📊 Generating demo data..."
docker-compose exec -T app php artisan db:seed --class=DemoLogsSeeder

echo ""
echo "================================"
echo "✅ Docker demo setup completed!"
echo "================================"
echo ""
echo "🌐 Access the demo at: http://localhost:8080/logs"
echo ""
echo "🛑 To stop the demo:"
echo "   docker-compose down"
echo ""
echo "🔄 To reset the demo:"
echo "   docker-compose down -v"
echo "   ./demo-docker.sh"
echo ""

