<?php

namespace Simorgh\Logger\Tests\Unit;

use Simorgh\Logger\AdvancedLoggerServiceProvider;
use Simorgh\Logger\Contracts\LoggerInterface;
use Simorgh\Logger\Facades\Logger;
use Orchestra\Testbench\TestCase;

class AdvancedLoggerServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            AdvancedLoggerServiceProvider::class,
        ];
    }

    /** @test */
    public function it_registers_logger_service()
    {
        $this->assertInstanceOf(
            LoggerInterface::class,
            $this->app->make(LoggerInterface::class)
        );
    }

    /** @test */
    public function it_registers_facade()
    {
        $this->assertInstanceOf(
            LoggerInterface::class,
            Logger::getFacadeRoot()
        );
    }

    /** @test */
    public function it_publishes_config()
    {
        $this->artisan('vendor:publish', [
            '--provider' => AdvancedLoggerServiceProvider::class,
            '--tag' => 'advanced-logger-config'
        ]);

        $this->assertFileExists(config_path('advanced-logger.php'));
    }

    /** @test */
    public function it_publishes_migrations()
    {
        $this->artisan('vendor:publish', [
            '--provider' => AdvancedLoggerServiceProvider::class,
            '--tag' => 'advanced-logger-migrations'
        ]);

        $this->assertFileExists(database_path('migrations'));
    }
}
