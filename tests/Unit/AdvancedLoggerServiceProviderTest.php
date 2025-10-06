<?php

namespace AFM\AdvancedLogger\Tests\Unit;

use AFM\AdvancedLogger\AdvancedLoggerServiceProvider;
use AFM\AdvancedLogger\Contracts\LoggerInterface;
use AFM\AdvancedLogger\Facades\Logger;
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
